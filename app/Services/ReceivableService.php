<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Expense;
use App\Models\Receivable;
use App\Models\ReceivablePayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReceivableService
{
    public function create(array $data): Receivable
    {
        $now = Carbon::now();
        $parsedDate = Carbon::parse($data['date']);
        $data['date'] = $parsedDate->format('Y-m-d') . ' ' . $now->format('H:i:s');
        $data['due_date'] = $parsedDate->copy()->addDays(3);
        $data['status'] = 'unpaid';

        return DB::transaction(function () use ($data) {
            $receivable = Receivable::create($data);

            Expense::create([
                'account_id' => $this->resolveCashAccountId(),
                'amount' => $receivable->amount,
                'category' => 'Piutang',
                'description' => "Piutang a/n {$receivable->name}",
                'date' => $receivable->date,
                'receivable_id' => $receivable->id,
            ]);

            return $receivable;
        });
    }

    public function update(int $id, array $data): Receivable
    {
        return DB::transaction(function () use ($id, $data) {
            $receivable = Receivable::findOrFail($id);

            if ($receivable->status !== 'unpaid') {
                throw new \DomainException('Hanya piutang unpaid yang bisa diedit.');
            }

            if ($receivable->receivablePayments()->exists()) {
                throw new \DomainException('Piutang yang sudah memiliki pembayaran tidak bisa diedit.');
            }

            $now = Carbon::now();
            $parsedDate = Carbon::parse($data['date']);
            $data['date'] = $parsedDate->format('Y-m-d') . ' ' . $now->format('H:i:s');
            $data['due_date'] = $parsedDate->copy()->addDays(3);

            $receivable->update($data);

            if ($expense = $receivable->expense) {
                $expense->update([
                    'account_id' => $this->resolveCashAccountId(),
                    'amount' => $receivable->amount,
                    'description' => "Piutang a/n {$receivable->name}",
                    'date' => $receivable->date,
                ]);
            }

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

            $remaining = $receivable->amount - $receivable->receivablePayments()->sum('amount');

            if ($data['amount'] > $remaining) {
                throw new \DomainException('Pembayaran melebihi sisa piutang. Sisa: Rp ' . number_format($remaining, 0, ',', '.'));
            }

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
            $receivable->expense()->delete();
            return $receivable->delete();
        });
    }

    public function getAll(array $filters = []): array
    {
        $query = Receivable::query();

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'overdue') {
                $query->where('status', 'unpaid')
                      ->where('due_date', '<', now()->startOfDay());
            } else {
                $query->where('status', $filters['status']);
            }
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

        $totalAmount = (clone $query)->sum('amount');
        $totalPaid = ReceivablePayment::whereIn('receivable_id', (clone $query)->select('id'))->sum('amount');
        $totalRemaining = $totalAmount - $totalPaid;

        $receivables = $query->with('receivablePayments')->latest()->paginate(20);

        return compact('receivables', 'totalAmount', 'totalRemaining');
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

        $phone = preg_replace('/[^0-9]/', '', ltrim((string) $receivable->phone, '+'));

        return 'https://wa.me/' . $phone . '?text=' . urlencode($text);
    }

    private function resolveCashAccountId(): int
    {
        $account = Account::active()->where('name', config('accounts.cash_name'))->first();

        if (! $account) {
            throw new \DomainException('Akun cash tidak ditemukan. Silakan buat akun cash terlebih dahulu.');
        }

        return $account->id;
    }
}
