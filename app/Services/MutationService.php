<?php

namespace App\Services;

use App\Models\Mutation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MutationService
{
    public function create(array $data): Mutation
    {
        $data['date'] = Carbon::parse($data['date'])->format('Y-m-d') . ' ' . now()->format('H:i:s');

        return DB::transaction(function () use ($data) {
            return Mutation::create($data);
        });
    }

    public function update(int $id, array $data): Mutation
    {
        $data['date'] = Carbon::parse($data['date'])->format('Y-m-d') . ' ' . now()->format('H:i:s');

        return DB::transaction(function () use ($id, $data) {
            $mutation = Mutation::findOrFail($id);
            $mutation->update($data);
            return $mutation;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            return Mutation::findOrFail($id)->delete();
        });
    }

    public function getAll(array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Mutation::with('fromAccount', 'toAccount');

        if (!empty($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%");
            });
        }

        return $query->latest()->paginate(20);
    }
}
