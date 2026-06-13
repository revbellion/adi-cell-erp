<?php

namespace App\Services;

use App\Models\BillPayment;
use App\Models\Expense;
use App\Models\RecurringBill;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BillService
{
    public function getActiveBills(): Collection
    {
        return RecurringBill::with('account')->where('is_active', true)->orderBy('due_day')->get();
    }

    public function getBillsWithStatus(string $period): Collection
    {
        $bills = $this->getActiveBills();

        foreach ($bills as $bill) {
            $payment = $bill->getPaymentForPeriod($period);
            $bill->payment = $payment;
            $bill->is_paid = $payment && $payment->is_paid;
        }

        return $bills;
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
        return DB::transaction(function () use ($bill, $period, $overrideAmount, $overrideAccountId) {
            $amount = $overrideAmount ?? $bill->amount;
            $accountId = $overrideAccountId ?? $bill->account_id;

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
        $bill->update($data);
        return $bill;
    }

    public function deleteBill(RecurringBill $bill): void
    {
        $bill->payments()->delete();
        $bill->delete();
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
