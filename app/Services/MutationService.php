<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Mutation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MutationService
{
    public function create(array $data): Mutation
    {
        $adminFee = $data['admin_fee'] ?? null;

        $data['date'] = Carbon::parse($data['date'])->format('Y-m-d') . ' ' . now()->format('H:i:s');
        $data['source'] = 'manual';

        return DB::transaction(function () use ($data, $adminFee) {
            /** @var Mutation $mutation */
            $mutation = Mutation::create($data);

            if ($adminFee && $adminFee > 0) {
                $this->createAdminFeeExpense($mutation, $adminFee);
            }

            return $mutation;
        });
    }

    public function update(int $id, array $data): Mutation
    {
        $adminFee = $data['admin_fee'] ?? null;

        $data['date'] = Carbon::parse($data['date'])->format('Y-m-d') . ' ' . now()->format('H:i:s');

        return DB::transaction(function () use ($id, $data, $adminFee) {
            $mutation = Mutation::findOrFail($id);

            if ($mutation->source !== 'manual') {
                throw new \DomainException('Mutasi dari sistem tidak bisa diedit.');
            }

            $mutation->update($data);

            // Handle admin_fee changes
            $existingExpense = $mutation->adminFeeExpense;

            if ($adminFee && $adminFee > 0) {
                if ($existingExpense) {
                    // Update existing expense
                    $existingExpense->update([
                        'date' => $mutation->date,
                        'account_id' => $mutation->to_account_id,
                        'category' => 'Biaya Admin Topup',
                        'amount' => $adminFee,
                        'description' => 'Biaya admin: ' . ($mutation->description ?? 'Topup'),
                    ]);
                } else {
                    // Create new expense
                    $this->createAdminFeeExpense($mutation, $adminFee);
                }
            } elseif ($existingExpense) {
                // Admin fee removed — delete linked expense
                $existingExpense->delete();
            }

            return $mutation;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $mutation = Mutation::findOrFail($id);

            if ($mutation->source !== 'manual') {
                throw new \DomainException('Mutasi dari sistem tidak bisa dihapus.');
            }

            // Delete linked admin fee expense first
            if ($mutation->adminFeeExpense) {
                $mutation->adminFeeExpense->delete();
            }

            return $mutation->delete();
        });
    }

    public function getAll(array $filters = []): array
    {
        $query = Mutation::with('fromAccount', 'toAccount');

        if (!empty($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $s = addcslashes($filters['search'], '%_');
            $query->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%")
                  ->orWhereHas('fromAccount', fn($q) => $q->where('name', 'like', "%{$s}%"))
                  ->orWhereHas('toAccount', fn($q) => $q->where('name', 'like', "%{$s}%"));
            });
        }

        $totalAmount = (clone $query)->sum('amount');
        $mutations = $query->latest()->paginate(20);

        return compact('mutations', 'totalAmount');
    }

    private function createAdminFeeExpense(Mutation $mutation, int $adminFee): Expense
    {
        return Expense::create([
            'date' => $mutation->date,
            'account_id' => $mutation->to_account_id,
            'category' => 'Biaya Admin Topup',
            'amount' => $adminFee,
            'description' => 'Biaya admin: ' . ($mutation->description ?? 'Topup'),
            'mutation_id' => $mutation->id,
        ]);
    }
}
