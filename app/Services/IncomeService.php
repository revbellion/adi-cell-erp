<?php

namespace App\Services;

use App\Models\Income;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\DB;

class IncomeService
{
    public function create(array $data): Income
    {
        return DB::transaction(function () use ($data) {
            return Income::create($data);
        });
    }

    public function update(int $id, array $data): Income
    {
        return DB::transaction(function () use ($id, $data) {
            $income = Income::findOrFail($id);

            if (in_array($income->category, $this->systemCategories())) {
                throw new \DomainException('Pendapatan sistem tidak bisa diedit.');
            }

            $income->update($data);
            return $income;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $income = Income::findOrFail($id);

            if (in_array($income->category, $this->systemCategories())) {
                throw new \DomainException('Pendapatan sistem tidak bisa dihapus.');
            }

            if ($income->stock_transaction_id !== null) {
                throw new \DomainException('Pendapatan ini terkait transaksi stok dan tidak bisa dihapus.');
            }

            $income->delete();
            return true;
        });
    }

    public function getAll(array $filters = []): array
    {
        $query = Income::with('account');

        if (!empty($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['type'])) {
            $movementCats = $this->cashMovementCategories();
            if ($filters['type'] === 'real') {
                $query->whereNotIn('category', $movementCats);
            } elseif ($filters['type'] === 'cash_movement') {
                $query->whereIn('category', $movementCats);
            }
        }

        if (!empty($filters['search'])) {
            $s = addcslashes($filters['search'], '%_');
            $query->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%")
                  ->orWhere('category', 'like', "%{$s}%");
            });
        }

        $totalAmount = (clone $query)->sum('amount');
        $incomes = $query->latest()->paginate(20);

        return compact('incomes', 'totalAmount');
    }

    public function getCategories(): \Illuminate\Support\Collection
    {
        return Income::select('category')->distinct()->pluck('category');
    }

    /**
     * Kategori income yang tergolong cash movement (mutasi) untuk filter tab.
     */
    private function cashMovementCategories(): array
    {
        return collect(config('categories.income.system'))
            ->where('filter', 'cash_movement')
            ->pluck('key')
            ->values()
            ->all();
    }

    /**
     * Kategori sistem yang tidak bisa diedit/dihapus user.
     */
    private function systemCategories(): array
    {
        return collect(config('categories.income.system'))
            ->pluck('key')
            ->values()
            ->all();
    }
}
