<?php

namespace App\Services;

use App\Models\Account;
use App\Models\OpnameSaldo;
use App\Models\Mutation;
use App\Models\DashboardService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OpnameSaldoService
{
    public function getAccountBalances(string $date): array
    {
        $period = Carbon::parse($date)->format('Y-m');
        $dateStart = Carbon::parse($date)->startOfMonth()->format('Y-m-d');
        $dateEnd = Carbon::parse($date)->endOfMonth()->format('Y-m-d');

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

    private function calculateBalance(Account $account, string $period, string $dateStart, string $dateEnd): int
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
            $cashAccountId = $this->getCashAccountId();

            foreach ($data['accounts'] as $accountId => $closingBalance) {
                if ($closingBalance === null || $closingBalance === '') {
                    continue;
                }

                $account = Account::findOrFail($accountId);
                $period = Carbon::parse($date)->format('Y-m');
                $dateStart = Carbon::parse($date)->startOfMonth()->format('Y-m-d');
                $dateEnd = Carbon::parse($date)->endOfMonth()->format('Y-m-d');

                $openingBalance = $this->calculateBalance($account, $period, $dateStart, $dateEnd);
                $closingBalance = (int) $closingBalance;
                $difference = $openingBalance - $closingBalance;

                // Simpan record opname
                $opnameRecord = OpnameSaldo::create([
                    'account_id' => $accountId,
                    'opening_balance' => $openingBalance,
                    'closing_balance' => $closingBalance,
                    'difference' => $difference,
                    'opname_date' => $date,
                ]);

                $opnameRecords[] = $opnameRecord;

                // Buat mutasi berdasarkan selisih
                if ($difference != 0 && $cashAccountId) {
                    if ($difference > 0) {
                        // Selisih positif (aktual < sistem): Mutasi dari PPOB/e-wallet ke Cash
                        Mutation::create([
                            'from_account_id' => $accountId,
                            'to_account_id' => $cashAccountId,
                            'amount' => $difference,
                            'date' => $date . ' ' . Carbon::now()->format('H:i:s'),
                            'description' => 'Opname saldo ' . $account->name,
                        ]);
                    } else {
                        // Selisih negatif (aktual > sistem): Mutasi dari Cash ke PPOB/e-wallet
                        Mutation::create([
                            'from_account_id' => $cashAccountId,
                            'to_account_id' => $accountId,
                            'amount' => abs($difference),
                            'date' => $date . ' ' . Carbon::now()->format('H:i:s'),
                            'description' => 'Opname saldo ' . $account->name,
                        ]);
                    }
                }
            }

            return [
                'opname_records' => $opnameRecords,
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
}
