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
    public function create(array $data): PendingTransaction
    {
        $now = Carbon::now();
        $pendingDate = !empty($data['pending_date'])
            ? Carbon::parse($data['pending_date'])->format('Y-m-d') . ' ' . $now->format('H:i:s')
            : $now->format('Y-m-d H:i:s');

        return DB::transaction(function () use ($data, $pendingDate) {
            $pending = PendingTransaction::create([
                'type' => $data['type'],
                'description' => $data['description'],
                'amount' => $data['amount'],
                'status' => 'pending',
                'pending_date' => $pendingDate,
            ]);

            // Flow berdasarkan tipe
            if ($data['type'] === 'transfer') {
                // Transfer: BCA langsung bertambah (Income)
                $bca = Account::where('name', config('accounts.bca_name'))->first();
                if ($bca) {
                    Income::create([
                        'account_id' => $bca->id,
                        'amount' => $pending->amount,
                        'category' => 'Transfer Masuk',
                        'description' => "Transfer dari {$pending->description}",
                        'date' => $pendingDate,
                    ]);
                }
            } else {
                // EDC/QRIS: Cash langsung berkurang (Expense)
                $cash = Account::where('name', config('accounts.cash_name'))->first();
                if ($cash) {
                    Expense::create([
                        'account_id' => $cash->id,
                        'amount' => $pending->amount,
                        'category' => 'Pending ' . strtoupper($pending->type),
                        'description' => "Cash keluar untuk {$pending->description}",
                        'date' => $pendingDate,
                    ]);
                }
            }

            return $pending;
        });
    }

    public function complete(int $id, array $data): PendingTransaction
    {
        return DB::transaction(function () use ($id, $data) {
            $pending = PendingTransaction::findOrFail($id);

            if ($pending->status !== 'pending') {
                throw new \DomainException('Transaksi ini sudah selesai.');
            }

            $now = Carbon::now();
            $completedDate = !empty($data['completed_date'])
                ? Carbon::parse($data['completed_date'])->format('Y-m-d') . ' ' . $now->format('H:i:s')
                : $now->format('Y-m-d H:i:s');

            // Update pending transaction
            $pending->update([
                'status' => 'completed',
                'completed_date' => $completedDate,
                'completed_type' => $data['completed_type'],
                'completed_account_id' => $data['completed_account_id'] ?? null,
            ]);

            // Flow berdasarkan tipe
            if ($pending->type === 'transfer') {
                // Transfer selesai: Cash berkurang (Expense)
                Expense::create([
                    'account_id' => $data['completed_account_id'],
                    'amount' => $pending->amount,
                    'category' => 'Cash Keluar',
                    'description' => "Cash keluar untuk {$pending->description}",
                    'date' => $completedDate,
                ]);
            } else {
                // EDC/QRIS selesai: BCA bertambah (Income)
                Income::create([
                    'account_id' => $data['completed_account_id'],
                    'amount' => $pending->amount,
                    'category' => 'Pending ' . strtoupper($pending->type),
                    'description' => "BCA terima dari {$pending->description}",
                    'date' => $completedDate,
                ]);
            }

            return $pending;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $pending = PendingTransaction::findOrFail($id);

            // TODO: Aktifkan lagi setelah testing selesai
            // if ($pending->status !== 'pending') {
            //     throw new \DomainException('Hanya transaksi pending yang bisa dihapus.');
            // }

            // Hapus Expense/Income terkait
            $this->deleteLinkedTransactions($pending);

            return $pending->delete();
        });
    }

    private function deleteLinkedTransactions(PendingTransaction $pending): void
    {
        // Hapus Expense terkait (description berisi deskripsi pending)
        Expense::where('description', 'like', "%{$pending->description}%")
            ->where('category', 'like', 'Pending%')
            ->delete();

        // Hapus Income terkait (description berisi deskripsi pending)
        Income::where('description', 'like', "%{$pending->description}%")
            ->where('category', 'like', 'Pending%')
            ->delete();

        // Hapus Income untuk Transfer
        Income::where('description', 'like', "Transfer dari {$pending->description}%")
            ->delete();
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
            $s = $filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%");
            });
        }

        $totalPending = (clone $query)->pending()->sum('amount');
        $totalCompleted = (clone $query)->completed()->sum('amount');

        $pendings = $query->latest('pending_date')->paginate(20);

        return compact('pendings', 'totalPending', 'totalCompleted');
    }
}
