<?php

namespace App\Services;

use App\Models\BillPayment;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExpenseService
{
    public function create(array $data): Expense
    {
        $data['date'] = Carbon::parse($data['date'])->format('Y-m-d') . ' ' . now()->format('H:i:s');

        return DB::transaction(function () use ($data) {
            return Expense::create($data);
        });
    }

    public function update(int $id, array $data): Expense
    {
        $data['date'] = Carbon::parse($data['date'])->format('Y-m-d') . ' ' . now()->format('H:i:s');

        return DB::transaction(function () use ($id, $data) {
            $expense = Expense::findOrFail($id);

            // Blokir edit expense sistem
            if (in_array($expense->category, $this->systemCategories())) {
                throw new \DomainException('Pengeluaran sistem tidak bisa diedit.');
            }

            // Blokir edit expense dari stok masuk
            if ($expense->stock_transaction_id !== null) {
                throw new \DomainException('Pengeluaran stok masuk tidak bisa diedit.');
            }

            // Blokir edit expense dari tagihan bulanan
            if (BillPayment::where('expense_id', $expense->id)->exists()) {
                throw new \DomainException('Pengeluaran dari tagihan bulanan tidak bisa diedit. Ubah dari modul Tagihan Bulanan.');
            }

            $expense->update($data);
            return $expense;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $expense = Expense::findOrFail($id);

            // Blokir hapus expense sistem
            if (in_array($expense->category, $this->systemCategories())) {
                throw new \DomainException('Pengeluaran sistem tidak bisa dihapus.');
            }

            // Blokir hapus expense dari stok masuk
            if ($expense->stock_transaction_id !== null) {
                throw new \DomainException('Pengeluaran stok masuk tidak bisa dihapus langsung.');
            }

            // Blokir hapus expense dari tagihan bulanan
            if (BillPayment::where('expense_id', $expense->id)->exists()) {
                throw new \DomainException('Pengeluaran dari tagihan bulanan tidak bisa dihapus. Hapus dari modul Tagihan Bulanan.');
            }

            $expense->delete();
            return true;
        });
    }

    public function getAll(array $filters = []): array
    {
        $query = Expense::with(['account', 'billPayment']);

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
            if ($filters['type'] === 'cash_movement') {
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
        $expenses = $query->latest()->paginate(20);

        return compact('expenses', 'totalAmount');
    }

    public function getCategories(): \Illuminate\Support\Collection
    {
        return Expense::select('category')->distinct()->pluck('category');
    }

    /**
     * Kategori yang tergolong cash movement (mutasi) untuk filter tab.
     */
    private function cashMovementCategories(): array
    {
        $system = collect(config('categories.expense.system'))
            ->where('filter', 'cash_movement')->pluck('key');
        $user = collect(config('categories.expense.user'))
            ->where('filter', 'cash_movement')->pluck('key');
        return $system->merge($user)->values()->all();
    }

    /**
     * Kategori sistem yang tidak bisa diedit/dihapus user.
     */
    private function systemCategories(): array
    {
        return collect(config('categories.expense.system'))
            ->pluck('key')
            ->values()
            ->all();
    }
}
