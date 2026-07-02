<?php

namespace App\Services;

use App\Models\Account;
use App\Models\PendingTransaction;
use App\Models\Income;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PendingTransactionService
{
    private function getTransitAccount(): Account
    {
        $transit = Account::where('name', config('accounts.in_transit_name'))->first();
        if (!$transit) {
            throw new \DomainException('Akun "Dalam Perjalanan" tidak ditemukan.');
        }
        return $transit;
    }

    public function create(array $data): PendingTransaction
    {
        $now = Carbon::now();
        $pendingDate = !empty($data['pending_date'])
            ? Carbon::parse($data['pending_date'])->format('Y-m-d') . ' ' . $now->format('H:i:s')
            : $now->format('Y-m-d H:i:s');

        return DB::transaction(function () use ($data, $pendingDate) {
            $mdrRate = 0;
            $mdrAmount = 0;
            $netAmount = $data['amount'];

            if ($data['type'] === 'edc') {
                $mdrRate = ($data['bank_type'] ?? 'non_bca') === 'bca' ? 0.15 : 1.00;
                $mdrAmount = (int) ($data['amount'] * $mdrRate / 100);
                $netAmount = $data['amount'] - $mdrAmount;
            }

            $pending = PendingTransaction::create([
                'type' => $data['type'],
                'bank_type' => $data['bank_type'] ?? null,
                'description' => $data['description'],
                'amount' => $data['amount'],
                'mdr_rate' => $mdrRate,
                'mdr_amount' => $mdrAmount,
                'net_amount' => $netAmount,
                'status' => 'pending',
                'pending_date' => $pendingDate,
            ]);

            if ($data['type'] === 'transfer') {
                // Transfer: Uang sudah masuk BCA, tapi cash belum keluar
                $bca = Account::where('name', config('accounts.bca_name'))->first();
                if (!$bca) {
                    throw new \DomainException('Akun BCA tidak ditemukan.');
                }
                $income = Income::create([
                    'account_id' => $bca->id,
                    'amount' => $pending->amount,
                    'category' => 'Transfer Masuk',
                    'description' => "Transfer dari {$pending->description}",
                    'date' => $pendingDate,
                ]);
                $pending->update(['income_id' => $income->id]);
            } else {
                // EDC: Cash sudah keluar, tapi bank belum terima
                $transit = $this->getTransitAccount();
                $expense = Expense::create([
                    'account_id' => $transit->id,
                    'amount' => $pending->amount,
                    'category' => 'Pending EDC',
                    'description' => "EDC pending: {$pending->description}",
                    'date' => $pendingDate,
                ]);
                $pending->update(['expense_id' => $expense->id]);
            }

            return $pending;
        });
    }

    public function complete(int $id, array $data): PendingTransaction
    {
        return DB::transaction(function () use ($id, $data) {
            $pending = PendingTransaction::lockForUpdate()->findOrFail($id);

            if ($pending->status !== 'pending') {
                throw new \DomainException('Transaksi ini sudah selesai.');
            }

            $account = Account::findOrFail($data['completed_account_id']);
            if ($pending->type === 'transfer' && $account->type !== 'cash') {
                throw new \DomainException('Transfer harus diselesaikan ke akun Cash.');
            }
            if ($pending->type === 'edc' && $account->type !== 'bank') {
                throw new \DomainException('EDC harus diselesaikan ke akun Bank.');
            }

            $now = Carbon::now();
            $completedDate = !empty($data['completed_date'])
                ? Carbon::parse($data['completed_date'])->format('Y-m-d') . ' ' . $now->format('H:i:s')
                : $now->format('Y-m-d H:i:s');

            if ($pending->type === 'transfer') {
                $expense = Expense::create([
                    'account_id' => $data['completed_account_id'],
                    'amount' => $pending->net_amount,
                    'category' => 'Cash Keluar',
                    'description' => "Cash keluar untuk {$pending->description}",
                    'date' => $completedDate,
                ]);
                $pending->update([
                    'status' => 'completed',
                    'completed_date' => $completedDate,
                    'completed_type' => $data['completed_type'],
                    'completed_account_id' => $data['completed_account_id'],
                    'expense_id' => $expense->id,
                ]);
            } else {
                $income = Income::create([
                    'account_id' => $data['completed_account_id'],
                    'amount' => $pending->net_amount,
                    'category' => 'Pending EDC',
                    'description' => "EDC settlement: {$pending->description}",
                    'date' => $completedDate,
                ]);

                if ($pending->mdr_amount > 0) {
                    Expense::create([
                        'account_id' => $data['completed_account_id'],
                        'amount' => $pending->mdr_amount,
                        'category' => 'Biaya MDR',
                        'description' => "Biaya MDR EDC (" . ($pending->bank_type ?? 'umum') . ") untuk {$pending->description}",
                        'date' => $completedDate,
                    ]);
                }

                $pending->update([
                    'status' => 'completed',
                    'completed_date' => $completedDate,
                    'completed_type' => $data['completed_type'],
                    'completed_account_id' => $data['completed_account_id'],
                    'income_id' => $income->id,
                ]);
            }

            return $pending;
        });
    }

    public function cancel(int $id): PendingTransaction
    {
        return DB::transaction(function () use ($id) {
            $pending = PendingTransaction::lockForUpdate()->findOrFail($id);

            if ($pending->status !== 'pending') {
                throw new \DomainException('Hanya transaksi pending yang bisa dibatalkan.');
            }

            $this->deleteLinkedTransactions($pending);

            $pending->update([
                'status' => 'cancelled',
                'completed_date' => now(),
            ]);

            return $pending;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $pending = PendingTransaction::lockForUpdate()->findOrFail($id);

            if ($pending->status !== 'pending') {
                throw new \DomainException('Hanya transaksi pending yang bisa dihapus.');
            }

            $this->deleteLinkedTransactions($pending);

            return $pending->delete();
        });
    }

    private function deleteLinkedTransactions(PendingTransaction $pending): void
    {
        if ($pending->income_id) {
            Income::where('id', $pending->income_id)->delete();
        }

        if ($pending->expense_id) {
            Expense::where('id', $pending->expense_id)->delete();
        }
    }

    public function getAll(array $filters = []): array
    {
        $query = PendingTransaction::query();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['search'])) {
            $s = addcslashes($filters['search'], '%_');
            $query->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%");
            });
        }

        $totalPending = (clone $query)->pending()->sum('net_amount');
        $totalCompleted = (clone $query)->completed()->sum('net_amount');
        $totalCancelled = (clone $query)->cancelled()->sum('net_amount');

        $pendings = $query->latest('pending_date')->paginate(20);

        return compact('pendings', 'totalPending', 'totalCompleted', 'totalCancelled');
    }
}
