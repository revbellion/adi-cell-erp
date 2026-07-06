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
        $data['source'] = 'manual';

        return DB::transaction(function () use ($data) {
            return Mutation::create($data);
        });
    }

    public function update(int $id, array $data): Mutation
    {
        $data['date'] = Carbon::parse($data['date'])->format('Y-m-d') . ' ' . now()->format('H:i:s');

        return DB::transaction(function () use ($id, $data) {
            $mutation = Mutation::findOrFail($id);

            if ($mutation->source !== 'manual') {
                throw new \DomainException('Mutasi dari sistem tidak bisa diedit.');
            }

            $mutation->update($data);
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
}
