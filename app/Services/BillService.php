<?php

namespace App\Services;

use App\Models\BillPayment;
use App\Models\Expense;
use App\Models\RecurringBill;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BillService
{
    public function getBillsWithStatus(string $period): Collection
    {
        $bills = RecurringBill::with(['account', 'payments' => function ($q) use ($period) {
            $q->where('period', $period);
        }])->where('is_active', true)->orderBy('due_day')->get();

        return $bills->map(function ($bill) use ($period) {
            $payment = $bill->payments->first();
            return (object) [
                'id' => $bill->id,
                'name' => $bill->name,
                'category' => $bill->category,
                'amount' => $bill->amount,
                'due_day' => $bill->due_day,
                'due_day_text' => $bill->due_day_text,
                'account_id' => $bill->account_id,
                'account' => $bill->account,
                'is_active' => $bill->is_active,
                'is_paid' => $payment && $payment->is_paid,
                'payment' => $payment,
            ];
        });
    }

    public function getDueBillsCount(string $period): array
    {
        $bills = $this->getBillsWithStatus($period);
        $total = $bills->count();
        $paid = $bills->where('is_paid', true)->count();
        $unpaid = $total - $paid;

        return [
            'total' => $total,
            'paid' => $paid,
            'unpaid' => $unpaid,
            'bills' => $bills,
        ];
    }

    public function payBill(RecurringBill $bill, string $period, ?int $overrideAmount = null, ?int $overrideAccountId = null): BillPayment
    {
        // Validasi format periode Y-m
        if (!preg_match('/^\d{4}-\d{2}$/', $period)) {
            throw new \InvalidArgumentException('Format periode tidak valid. Gunakan format Y-m (contoh: 2026-06).');
        }

        return DB::transaction(function () use ($bill, $period, $overrideAmount, $overrideAccountId) {
            // Lock bill biar 2 request gak bisa bayar bersamaan
            $bill = RecurringBill::lockForUpdate()->findOrFail($bill->id);

            $amount = $overrideAmount ?? $bill->amount;
            $accountId = $overrideAccountId ?? $bill->account_id;

            // Hapus Expense lama jika ada pembayaran sebelumnya untuk periode ini
            $existingPayment = BillPayment::lockForUpdate()
                ->where('recurring_bill_id', $bill->id)
                ->where('period', $period)
                ->first();
            if ($existingPayment && $existingPayment->expense_id) {
                Expense::where('id', $existingPayment->expense_id)->delete();
            }

            $expense = Expense::create([
                'account_id' => $accountId,
                'category' => $bill->category,
                'amount' => $amount,
                'description' => 'Pembayaran ' . $bill->name . ' ' . str_replace('-', ' ', $period),
                'date' => now()->format('Y-m-d') . ' ' . now()->format('H:i:s'),
            ]);

            $payment = BillPayment::updateOrCreate(
                [
                    'recurring_bill_id' => $bill->id,
                    'period' => $period,
                ],
                [
                    'amount' => $amount,
                    'paid_at' => now(),
                    'expense_id' => $expense->id,
                ]
            );

            return $payment;
        });
    }

    public function createBill(array $data): RecurringBill
    {
        return RecurringBill::create($data);
    }

    public function updateBill(RecurringBill $bill, array $data): RecurringBill
    {
        return DB::transaction(function () use ($bill, $data) {
            $oldAccountId = $bill->account_id;
            $bill->update($data);

            if (array_key_exists('account_id', $data) && $data['account_id'] != $oldAccountId) {
                $expenseIds = $bill->payments()->pluck('expense_id')->filter();
                if ($expenseIds->isNotEmpty()) {
                    Expense::whereIn('id', $expenseIds)
                        ->update(['account_id' => $data['account_id']]);
                }
            }

            return $bill;
        });
    }

    public function deleteBill(RecurringBill $bill): void
    {
        DB::transaction(function () use ($bill) {
            $bill->payments()->each(function ($payment) {
                if ($payment->expense_id) {
                    Expense::where('id', $payment->expense_id)->delete();
                }
            });
            $bill->payments()->delete();
            $bill->delete();
        });
    }

    public function getPaymentCategories(): array
    {
        return RecurringBill::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->orderBy('category')
            ->pluck('category')
            ->toArray();
    }
}
