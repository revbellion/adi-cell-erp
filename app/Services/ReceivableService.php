<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Receivable;
use App\Models\ReceivablePayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReceivableService
{
    public function create(array $data): Receivable
    {
        $data['phone'] = normalizePhone($data['phone'] ?? null);

        $now = Carbon::now();
        $parsedDate = Carbon::parse($data['date']);
        $data['date'] = $parsedDate->format('Y-m-d') . ' ' . $now->format('H:i:s');
        $data['due_date'] = $parsedDate->copy()->addDays(3)->setTimeFrom($now);
        $data['status'] = 'unpaid';

        return DB::transaction(function () use ($data, $now) {
            $data = $this->resolveCustomer($data);

            $existing = Receivable::where('status', 'unpaid')
                ->whereDoesntHave('receivablePayments')
                ->where(function ($q) use ($data) {
                    if (!empty($data['customer_id'])) {
                        $q->where('customer_id', $data['customer_id']);
                    }
                    $q->orWhere('name', $data['name']);
                })
                ->latest('date')
                ->first();
            if ($existing) {
                return $this->addNominal($existing->id, $data['amount']);
            }

            $receivable = Receivable::create($data);

            // Buat Expense: kasih pinjaman ke customer
            $expense = Expense::create([
                'account_id'     => $data['account_id'] ?? $this->resolveCashAccountId(),
                'category'       => 'Piutang',
                'amount'         => $data['amount'],
                'description'    => "Piutang {$data['name']}",
                'date'           => $data['date'],
                'receivable_id'  => $receivable->id,
            ]);

            $receivable->update(['expense_id' => $expense->id]);

            return $receivable;
        });
    }

    public function addNominal(int $id, int $additionalAmount): Receivable
    {
        return DB::transaction(function () use ($id, $additionalAmount) {
            $receivable = Receivable::lockForUpdate()->findOrFail($id);

            if ($receivable->status !== 'unpaid') {
                throw new \DomainException('Hanya piutang unpaid yang bisa ditambah nominal.');
            }

            if ($receivable->receivablePayments()->exists()) {
                throw new \DomainException('Piutang yang sudah memiliki pembayaran tidak bisa ditambah nominal.');
            }

            $now = Carbon::now();
            $newAmount = $receivable->amount + $additionalAmount;
            $newDate = $now->format('Y-m-d H:i:s');
            $newDueDate = $now->copy()->addDays(3)->format('Y-m-d H:i:s');

            $receivable->update([
                'amount'   => $newAmount,
                'date'     => $newDate,
                'due_date' => $newDueDate,
            ]);

            if ($receivable->expense_id) {
                Expense::where('id', $receivable->expense_id)->update([
                    'amount'      => $newAmount,
                    'date'        => $newDate,
                    'description' => "Piutang {$receivable->name} (+Rp " . number_format($additionalAmount, 0, ',', '.') . ')',
                ]);
            }

            return $receivable;
        });
    }

    public function update(int $id, array $data): Receivable
    {
        $data['phone'] = normalizePhone($data['phone'] ?? null);

        return DB::transaction(function () use ($id, $data) {
            $receivable = Receivable::lockForUpdate()->findOrFail($id);

            if ($receivable->status !== 'unpaid') {
                throw new \DomainException('Hanya piutang unpaid yang bisa diedit.');
            }

            if ($receivable->receivablePayments()->exists()) {
                throw new \DomainException('Piutang yang sudah memiliki pembayaran tidak bisa diedit.');
            }

            $now = Carbon::now();
            $parsedDate = Carbon::parse($data['date']);
            $data['date'] = $parsedDate->format('Y-m-d') . ' ' . $now->format('H:i:s');
            $data['due_date'] = $parsedDate->copy()->addDays(3)->setTimeFrom($now);

            $receivable->update($data);

            // Sync Expense jika jumlah berubah
            if ($receivable->expense_id && isset($data['amount'])) {
                Expense::where('id', $receivable->expense_id)->update(['amount' => $data['amount']]);
            }

            return $receivable;
        });
    }

    public function pay(int $receivableId, array $data): ReceivablePayment
    {
        return DB::transaction(function () use ($receivableId, $data) {
            $receivable = Receivable::lockForUpdate()->findOrFail($receivableId);

            if ($receivable->status !== 'unpaid') {
                throw new \DomainException('Hanya piutang unpaid yang bisa dibayar.');
            }

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

            // Buat Income untuk setiap pembayaran (termasuk parsial)
            $income = \App\Models\Income::create([
                'account_id'    => $data['account_id'],
                'amount'        => $data['amount'],
                'category'      => 'Piutang',
                'description'   => "Pembayaran piutang {$receivable->name} (#{$receivable->id})",
                'date'          => $paymentDate,
                'receivable_id' => $receivable->id,
            ]);

            $totalPaid = $receivable->receivablePayments()->sum('amount');

            if ($totalPaid >= $receivable->amount) {
                $receivable->update(['status' => 'paid', 'income_id' => $income->id]);
            }

            return $payment;
        });
    }

    public function void(int $id): Receivable
    {
        return DB::transaction(function () use ($id) {
            $receivable = Receivable::lockForUpdate()->findOrFail($id);

            if ($receivable->status !== 'unpaid') {
                throw new \DomainException('Hanya piutang berstatus unpaid yang bisa dibatalkan.');
            }

            // Hapus Expense terkait (cash keluar pas buat piutang)
            if ($receivable->expense_id) {
                Expense::where('id', $receivable->expense_id)->delete();
            }

            $receivable->update([
                'status' => 'voided',
                'expense_id' => null,
            ]);

            return $receivable;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $receivable = Receivable::lockForUpdate()->findOrFail($id);

            if ($receivable->status === 'paid') {
                throw new \DomainException('Piutang yang sudah lunas tidak bisa dihapus.');
            }

            // Hapus Expense terkait
            if ($receivable->expense_id) {
                Expense::where('id', $receivable->expense_id)->delete();
            }

            // Hapus semua Income terkait pembayaran
            $paymentCount = $receivable->receivablePayments()->count();
            if ($paymentCount > 0) {
                \App\Models\Income::where('category', 'Piutang')
                    ->where('receivable_id', $receivable->id)
                    ->delete();
            }

            // Hapus payments
            $receivable->receivablePayments()->delete();

            // Hapus receivable
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
            $s = addcslashes($filters['search'], '%_');
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

    private function resolveCashAccountId(): int
    {
        $account = Account::active()->where('name', config('accounts.cash_name'))->first();

        if (! $account) {
            throw new \DomainException('Akun cash tidak ditemukan. Silakan buat akun cash terlebih dahulu.');
        }

        return $account->id;
    }

    private function resolveCustomer(array $data): array
    {
        if (!empty($data['customer_id'])) {
            return $data;
        }

        $existing = Customer::where('name', $data['name'])->first();

        if ($existing) {
            $data['customer_id'] = $existing->id;
            return $data;
        }

        $customer = Customer::create([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
        ]);

        $data['customer_id'] = $customer->id;
        return $data;
    }
}
