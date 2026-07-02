<?php

namespace App\Services;

use App\Models\Account;
use App\Models\OpnameSaldo;
use App\Models\Mutation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OpnameSaldoService
{
    public function getAccountBalances(string $date): array
    {
        $period = Carbon::parse($date)->format('Y-m');
        $dateStart = Carbon::parse($date)->startOfMonth();
        $dateEnd = Carbon::parse($date)->endOfMonth();

        // Ambil semua akun PPOB dan e-wallet
        $accounts = Account::active()->whereIn('type', ['ppob', 'ewallet'])->get();

        $balances = [];
        foreach ($accounts as $account) {
            // Hitung saldo dari transaksi
            $balance = $this->calculateBalance($account, $period, $dateStart, $dateEnd);
            $balances[$account->id] = [
                'account' => $account,
                'balance' => $balance,
            ];
        }

        return $balances;
    }

    private function calculateBalance(Account $account, string $period, $dateStart, $dateEnd): int
    {
        $openingBalance = \App\Models\OpeningBalance::where('period', $period)
            ->where('account_id', $account->id)
            ->sum('amount');

        $mutationsIn = \App\Models\Mutation::whereBetween('date', [$dateStart, $dateEnd])
            ->where('to_account_id', $account->id)
            ->sum('amount');

        $mutationsOut = \App\Models\Mutation::whereBetween('date', [$dateStart, $dateEnd])
            ->where('from_account_id', $account->id)
            ->sum('amount');

        $expenses = \App\Models\Expense::whereBetween('date', [$dateStart, $dateEnd])
            ->where('account_id', $account->id)
            ->sum('amount');

        $incomes = \App\Models\Income::whereBetween('date', [$dateStart, $dateEnd])
            ->where('account_id', $account->id)
            ->sum('amount');

        // ponytail: receivable_payments sudah dihitung sebagai Income, tidak perlu ditambah lagi
        return (int) (
            $openingBalance
            + $mutationsIn
            - $mutationsOut
            - $expenses
            + $incomes
        );
    }

    public function processOpname(array $data, string $date): array
    {
        return DB::transaction(function () use ($data, $date) {
            $opnameRecords = [];
            $warnings = [];
            $cashAccountId = $this->getCashAccountId();

            foreach ($data['accounts'] as $accountId => $closingBalance) {
                if ($closingBalance === null || $closingBalance === '') {
                    continue;
                }

                $account = Account::findOrFail($accountId);
                $period = Carbon::parse($date)->format('Y-m');
                $dateStart = Carbon::parse($date)->startOfMonth();
                $dateEnd = Carbon::parse($date)->endOfMonth();

                // Cek apakah sudah ada opname untuk akun & tanggal ini
                $existing = OpnameSaldo::where('account_id', $accountId)
                    ->where('opname_date', $date)
                    ->first();

                $openingBalance = $this->calculateBalance($account, $period, $dateStart, $dateEnd);
                $closingBalance = (int) $closingBalance;
                $difference = $openingBalance - $closingBalance;

                // Skip jika selisih 0 — tidak perlu disimpan
                if ($difference == 0) {
                    continue;
                }

                if ($existing) {
                    // Update: hapus mutasi lama (gunakan source + account_id + tanggal, bukan nama akun)
                    Mutation::where('source', 'opname')
                        ->whereDate('date', $date)
                        ->where(function ($q) use ($accountId) {
                            $q->where('from_account_id', $accountId)
                              ->orWhere('to_account_id', $accountId);
                        })
                        ->delete();

                    // Update record opname
                    $existing->update([
                        'opening_balance' => $openingBalance,
                        'closing_balance' => $closingBalance,
                        'difference' => $difference,
                    ]);
                    $opnameRecords[] = $existing;
                } else {
                    // Buat baru
                    $opnameRecord = OpnameSaldo::create([
                        'account_id' => $accountId,
                        'opening_balance' => $openingBalance,
                        'closing_balance' => $closingBalance,
                        'difference' => $difference,
                        'opname_date' => $date,
                    ]);
                    $opnameRecords[] = $opnameRecord;
                }

                // Buat mutasi baru berdasarkan selisih
                if ($difference != 0) {
                    if (!$cashAccountId) {
                        $warnings[] = "Akun Cash ('" . config('accounts.cash_name') . "') tidak ditemukan. Mutasi opname {$account->name} tidak dibuat.";
                    } else {
                        if ($difference > 0) {
                            Mutation::create([
                                'from_account_id' => $accountId,
                                'to_account_id' => $cashAccountId,
                                'amount' => $difference,
                                'date' => $date . ' ' . Carbon::now()->format('H:i:s'),
                                'description' => 'Opname saldo ' . $account->name,
                                'source' => 'opname',
                            ]);
                        } else {
                            Mutation::create([
                                'from_account_id' => $cashAccountId,
                                'to_account_id' => $accountId,
                                'amount' => abs($difference),
                                'date' => $date . ' ' . Carbon::now()->format('H:i:s'),
                                'description' => 'Opname saldo ' . $account->name,
                                'source' => 'opname',
                            ]);
                        }
                    }
                }
            }

            return [
                'opname_records' => $opnameRecords,
                'warnings' => $warnings,
            ];
        });
    }

    private function getCashAccountId(): ?int
    {
        $cashAccount = Account::active()->where('name', config('accounts.cash_name'))->first();
        return $cashAccount?->id;
    }

    public function getOpnameHistory(string $date = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = OpnameSaldo::with('account');

        if ($date) {
            $query->where('opname_date', $date);
        }

        return $query->orderBy('opname_date', 'desc')->get();
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $opname = OpnameSaldo::findOrFail($id);

            // Hapus mutasi spesifik untuk opname ini (gunakan source + account_id + tanggal, bukan nama akun)
            \App\Models\Mutation::where('source', 'opname')
                ->whereDate('date', $opname->opname_date)
                ->where(function ($q) use ($opname) {
                    $q->where('from_account_id', $opname->account_id)
                      ->orWhere('to_account_id', $opname->account_id);
                })
                ->delete();

            return $opname->delete();
        });
    }
}
