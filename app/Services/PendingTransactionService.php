<?php

namespace App\Services;

use App\Models\Account;
use App\Models\PendingTransaction;
use App\Models\Income;
use App\Models\Expense;
use App\Models\Mutation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PendingTransactionService
{
    private function getTransitAccount(): Account
    {
        $transit = Account::where('name', config('accounts.in_transit_name'))->first();
        if (!$transit) {
            throw new \DomainException('Akun "Pending" tidak ditemukan.');
        }
        return $transit;
    }

    private function getCashAccount(): Account
    {
        $cash = Account::where('name', config('accounts.cash_name'))->first();
        if (!$cash) {
            throw new \DomainException('Akun Cash tidak ditemukan.');
        }
        return $cash;
    }

    private function getBcaAccount(): Account
    {
        $bca = Account::where('name', config('accounts.bca_name'))->first();
        if (!$bca) {
            throw new \DomainException('Akun BCA tidak ditemukan.');
        }
        return $bca;
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
                $transit = $this->getTransitAccount();
                $bca = $this->getBcaAccount();
                $mutation = Mutation::create([
                    'date' => $pendingDate,
                    'from_account_id' => $bca->id,
                    'to_account_id' => $transit->id,
                    'amount' => $pending->amount,
                    'description' => "Transfer pending: {$pending->description}",
                    'source' => 'pending',
                ]);
                $pending->update(['mutation_id' => $mutation->id]);
            } elseif ($data['type'] === 'tf_masuk') {
                $transit = $this->getTransitAccount();
                $bca = $this->getBcaAccount();
                $mutation = Mutation::create([
                    'date' => $pendingDate,
                    'from_account_id' => $transit->id,
                    'to_account_id' => $bca->id,
                    'amount' => $pending->amount,
                    'description' => "TF masuk: {$pending->description}",
                    'source' => 'pending',
                ]);
                $pending->update(['mutation_id' => $mutation->id]);
            } else {
                $transit = $this->getTransitAccount();
                $cash = $this->getCashAccount();
                $mutation = Mutation::create([
                    'date' => $pendingDate,
                    'from_account_id' => $cash->id,
                    'to_account_id' => $transit->id,
                    'amount' => $pending->amount,
                    'description' => "EDC pending: {$pending->description}",
                    'source' => 'pending',
                ]);
                $pending->update(['mutation_id' => $mutation->id]);

                $feeServiceAmount = (int) round($data['amount'] * 2 / 100);
                $feeBersih = $feeServiceAmount - $mdrAmount;
                if ($feeBersih > 0) {
                    Income::create([
                        'account_id' => $cash->id,
                        'amount' => $feeBersih,
                        'category' => 'Jasa Tarik Tunai EDC',
                        'description' => "Fee EDC bersih: {$data['description']}",
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
            if ($pending->type === 'tf_masuk' && $account->type !== 'cash') {
                throw new \DomainException('Transfer harus diselesaikan ke akun Cash.');
            }

            $now = Carbon::now();
            $completedDate = !empty($data['completed_date'])
                ? Carbon::parse($data['completed_date'])->format('Y-m-d') . ' ' . $now->format('H:i:s')
                : $now->format('Y-m-d H:i:s');

            if ($pending->type === 'transfer') {
                $transit = $this->getTransitAccount();
                Mutation::create([
                    'date' => $completedDate,
                    'from_account_id' => $transit->id,
                    'to_account_id' => $data['completed_account_id'],
                    'amount' => $pending->amount,
                    'description' => "Selesai transfer: {$pending->description}",
                    'source' => 'pending',
                ]);
                $pending->update([
                    'status' => 'completed',
                    'completed_date' => $completedDate,
                    'completed_type' => $data['completed_type'],
                    'completed_account_id' => $data['completed_account_id'],
                ]);
            } elseif ($pending->type === 'tf_masuk') {
                $transit = $this->getTransitAccount();
                Mutation::create([
                    'date' => $completedDate,
                    'from_account_id' => $data['completed_account_id'],
                    'to_account_id' => $transit->id,
                    'amount' => $pending->amount,
                    'description' => "Selesai TF masuk: {$pending->description}",
                    'source' => 'pending',
                ]);
                $pending->update([
                    'status' => 'completed',
                    'completed_date' => $completedDate,
                    'completed_type' => $data['completed_type'],
                    'completed_account_id' => $data['completed_account_id'],
                ]);
            } else {
                $transit = $this->getTransitAccount();
                Mutation::create([
                    'date' => $completedDate,
                    'from_account_id' => $transit->id,
                    'to_account_id' => $data['completed_account_id'],
                    'amount' => $pending->amount,
                    'description' => "EDC settlement: {$pending->description}",
                    'source' => 'pending',
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

            $this->deleteLinkedTransactions($pending);

            return $pending->delete();
        });
    }

    private function deleteLinkedTransactions(PendingTransaction $pending): void
    {
        if ($pending->status === 'pending') {
            if ($pending->mutation_id) {
                Mutation::where('id', $pending->mutation_id)->delete();
            }
            if ($pending->type === 'edc') {
                $feeServiceAmount = (int) round($pending->amount * 2 / 100);
                $feeBersih = $feeServiceAmount - $pending->mdr_amount;
                if ($feeBersih > 0) {
                    Income::where('amount', $feeBersih)
                        ->where('category', 'Jasa Tarik Tunai EDC')
                        ->where('description', "Fee EDC bersih: {$pending->description}")
                        ->delete();
                }
            }
            return;
        }

        // Cascade delete untuk completed — hapus semua yg dibuat oleh create + complete
        if ($pending->mutation_id) {
            Mutation::where('id', $pending->mutation_id)->delete();
        }

        $transit = $this->getTransitAccount();
        if ($pending->type === 'transfer') {
            Mutation::where('from_account_id', $transit->id)
                ->where('to_account_id', $pending->completed_account_id)
                ->where('amount', $pending->amount)
                ->where('source', 'pending')
                ->where('description', "Selesai transfer: {$pending->description}")
                ->delete();
        } elseif ($pending->type === 'tf_masuk') {
            Mutation::where('from_account_id', $pending->completed_account_id)
                ->where('to_account_id', $transit->id)
                ->where('amount', $pending->amount)
                ->where('source', 'pending')
                ->where('description', "Selesai TF masuk: {$pending->description}")
                ->delete();
        } else {
            Mutation::where('from_account_id', $transit->id)
                ->where('to_account_id', $pending->completed_account_id)
                ->where('amount', $pending->amount)
                ->where('source', 'pending')
                ->where('description', "EDC settlement: {$pending->description}")
                ->delete();

            Expense::where('account_id', $pending->completed_account_id)
                ->where('amount', $pending->mdr_amount)
                ->where('category', 'Biaya MDR')
                ->where('description', "Biaya MDR EDC (" . ($pending->bank_type ?? 'umum') . ") untuk {$pending->description}")
                ->delete();

            $feeService = (int) round($pending->amount * 2 / 100);
            $feeBersih = $feeService - $pending->mdr_amount;
            if ($feeBersih > 0) {
                $cashAccount = Account::where('name', config('accounts.cash_name'))->first();
                if ($cashAccount) {
                    Income::where('account_id', $cashAccount->id)
                        ->where('amount', $feeBersih)
                        ->where('category', 'Jasa Tarik Tunai EDC')
                        ->where('description', "Fee EDC bersih: {$pending->description}")
                        ->delete();
                }
            }
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
