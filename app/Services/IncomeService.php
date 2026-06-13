<?php

namespace App\Services;

use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class IncomeService
{
    public function create(array $data): Income
    {
        $data['date'] = Carbon::parse($data['date'])->format('Y-m-d') . ' ' . now()->format('H:i:s');

        return DB::transaction(function () use ($data) {
            return Income::create($data);
        });
    }

    public function update(int $id, array $data): Income
    {
        $data['date'] = Carbon::parse($data['date'])->format('Y-m-d') . ' ' . now()->format('H:i:s');

        return DB::transaction(function () use ($id, $data) {
            $income = Income::findOrFail($id);
            $income->update($data);
            return $income;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            return Income::findOrFail($id)->delete();
        });
    }

    public function getAll(array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
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

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%")
                  ->orWhere('category', 'like', "%{$s}%");
            });
        }

        return $query->latest()->paginate(20);
    }

    public function getCategories(): \Illuminate\Support\Collection
    {
        return Income::select('category')->distinct()->pluck('category');
    }
}
