<?php

namespace App\Services;

use App\Models\Receivable;
use App\Models\ReceivablePayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReceivableService
{
    public function create(array $data): Receivable
    {
        $now = Carbon::now();
        $data['date'] = Carbon::parse($data['date'])->format('Y-m-d') . ' ' . $now->format('H:i:s');
        $data['due_date'] = Carbon::parse($data['date'])->addDays(3);
        $data['status'] = 'unpaid';

        return DB::transaction(function () use ($data) {
            return Receivable::create($data);
        });
    }

    public function update(int $id, array $data): Receivable
    {
        return DB::transaction(function () use ($id, $data) {
            $receivable = Receivable::findOrFail($id);

            if ($receivable->status !== 'unpaid') {
                abort(403, 'Hanya piutang unpaid yang bisa diedit.');
            }

            $now = Carbon::now();
            $data['date'] = Carbon::parse($data['date'])->format('Y-m-d') . ' ' . $now->format('H:i:s');
            $data['due_date'] = Carbon::parse($data['date'])->addDays(3);

            $receivable->update($data);
            return $receivable;
        });
    }

    public function pay(int $receivableId, array $data): ReceivablePayment
    {
        return DB::transaction(function () use ($receivableId, $data) {
            $receivable = Receivable::findOrFail($receivableId);

            $now = Carbon::now();
            $paymentDate = !empty($data['date'])
                ? Carbon::parse($data['date'])->format('Y-m-d') . ' ' . $now->format('H:i:s')
                : $now->format('Y-m-d H:i:s');

            $payment = ReceivablePayment::create([
                'receivable_id' => $receivableId,
                'account_id' => $data['account_id'],
                'amount' => $data['amount'],
                'date' => $paymentDate,
            ]);

            $totalPaid = $receivable->receivablePayments()->sum('amount');

            if ($totalPaid >= $receivable->amount) {
                $receivable->update(['status' => 'paid']);
            }

            return $payment;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $receivable = Receivable::findOrFail($id);
            $receivable->receivablePayments()->delete();
            return $receivable->delete();
        });
    }

    public function getAll(array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Receivable::with('receivablePayments');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        return $query->latest()->paginate(20);
    }

    public function generateWhatsAppLink(Receivable $receivable): string
    {
        $today = Carbon::now()->startOfDay();
        $due = $receivable->due_date->startOfDay();
        $diffDays = $today->diffInDays($due, false);

        if ($diffDays > 0) {
            $text = sprintf(
                "Halo %s, hutang Rp %s sudah telat %d hari. Mohon segera dibayar ya.",
                $receivable->name,
                number_format($receivable->amount, 0, ',', '.'),
                $diffDays
            );
        } else {
            $text = sprintf(
                "Halo %s, ini pengingat untuk hutang Rp %s yang jatuh tempo %s. Mohon segera dibayar ya.",
                $receivable->name,
                number_format($receivable->amount, 0, ',', '.'),
                $receivable->due_date->format('d/m/Y')
            );
        }

        $phone = preg_replace('/[^0-9]/', '', ltrim($receivable->phone, '+'));

        return 'https://wa.me/' . $phone . '?text=' . urlencode($text);
    }
}
