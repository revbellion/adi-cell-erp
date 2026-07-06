<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Mutation;
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

        return DB::transaction(function () use ($data) {
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

            $accounts = $this->getPiutangAccounts();
            if ($accounts['cash'] && $accounts['piutang']) {
                Mutation::create([
                    'from_account_id' => $accounts['cash']->id,
                    'to_account_id' => $accounts['piutang']->id,
                    'amount' => $data['amount'],
                    'date' => $data['date'],
                    'description' => "Piutang {$data['name']}",
                    'source' => 'piutang',
                ]);
            }

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

            $accounts = $this->getPiutangAccounts();
            if ($accounts['cash'] && $accounts['piutang']) {
                Mutation::create([
                    'from_account_id' => $accounts['cash']->id,
                    'to_account_id' => $accounts['piutang']->id,
                    'amount' => $additionalAmount,
                    'date' => $newDate,
                    'description' => "Tambah piutang {$receivable->name}",
                    'source' => 'piutang',
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

            $oldAmount = $receivable->amount;
            $receivable->update($data);
            $diff = $receivable->amount - $oldAmount;

            if ($diff !== 0) {
                $accounts = $this->getPiutangAccounts();
                if ($accounts['cash'] && $accounts['piutang']) {
                    if ($diff > 0) {
                        Mutation::create([
                            'from_account_id' => $accounts['cash']->id,
                            'to_account_id' => $accounts['piutang']->id,
                            'amount' => $diff,
                            'date' => $data['date'],
                            'description' => "Edit piutang {$receivable->name} (+{$diff})",
                            'source' => 'piutang',
                        ]);
                    } else {
                        Mutation::create([
                            'from_account_id' => $accounts['piutang']->id,
                            'to_account_id' => $accounts['cash']->id,
                            'amount' => abs($diff),
                            'date' => $data['date'],
                            'description' => "Edit piutang {$receivable->name} ({$diff})",
                            'source' => 'piutang',
                        ]);
                    }
                }
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

            $accounts = $this->getPiutangAccounts();
            if ($accounts['piutang']) {
                Mutation::create([
                    'from_account_id' => $accounts['piutang']->id,
                    'to_account_id' => $data['account_id'],
                    'amount' => $data['amount'],
                    'date' => $paymentDate,
                    'description' => "Bayar piutang {$receivable->name}",
                    'source' => 'piutang',
                ]);
            }

            $totalPaid = $receivable->receivablePayments()->sum('amount');

            if ($totalPaid >= $receivable->amount) {
                $receivable->update(['status' => 'paid']);
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

            $sisa = $receivable->amount - $receivable->receivablePayments()->sum('amount');

            if ($sisa > 0) {
                $accounts = $this->getPiutangAccounts();
                if ($accounts['cash'] && $accounts['piutang']) {
                    Mutation::create([
                        'from_account_id' => $accounts['piutang']->id,
                        'to_account_id' => $accounts['cash']->id,
                        'amount' => $sisa,
                        'date' => now()->format('Y-m-d H:i:s'),
                        'description' => "Batal piutang {$receivable->name}",
                        'source' => 'piutang',
                    ]);
                }
            }

            $receivable->update(['status' => 'voided']);

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

            // Hapus payments
            $receivable->receivablePayments()->delete();

            // Hapus mutation Piutang terkait — hapus yg source = piutang
            Mutation::where('source', 'piutang')
                ->where('description', 'like', "%{$receivable->name}%")
                ->whereIn('amount', [$receivable->amount])
                ->delete();

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

    private function getPiutangAccounts(): array
    {
        $cash = Account::active()->where('name', config('accounts.cash_name'))->first();
        $piutang = Account::where('type', 'receivable')->first();

        return [
            'cash' => $cash,
            'piutang' => $piutang,
        ];
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
