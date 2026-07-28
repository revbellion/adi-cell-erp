<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Income;
use Carbon\Carbon;

class CashCounterService
{
    public function calculateTotal(array $denominations): int
    {
        $denomValues = [
            '100k' => 100000,
            '50k' => 50000,
            '20k' => 20000,
            '10k' => 10000,
            '5k' => 5000,
            '2k' => 2000,
            '1k' => 1000,
            '500' => 500,
        ];

        $total = 0;
        foreach ($denominations as $key => $count) {
            if (isset($denomValues[$key])) {
                $total += $denomValues[$key] * max(0, (int) $count);
            }
        }

        return $total;
    }

    public function saveAdjustment(array $data): array
    {
        $diff = (int) $data['total_amount'] - (int) $data['opening_balance'];
        if ($diff === 0) {
            return ['adjusted' => false, 'message' => 'Saldo seimbang, tidak ada penyesuaian.'];
        }

        $record = [
            'account_id' => $data['account_id'] ?? null,
            'date' => now()->format('Y-m-d H:i:s'),
            'description' => "Selisih Cash Fisik vs Sistem ({$data['title']})",
        ];

        if ($diff > 0) {
            $record = Income::create(array_merge($record, [
                'amount' => $diff,
                'category' => 'OMSET',
            ]));
        } else {
            $record = Expense::create(array_merge($record, [
                'amount' => abs($diff),
                'category' => 'OMSET',
            ]));
        }

        return ['adjusted' => true, 'record' => $record];
    }

    public function getPeriodTransactions(?int $accountId, string $date): array
    {
        $dateStart = Carbon::parse($date)->startOfDay();
        $dateEnd = Carbon::parse($date)->endOfDay();

        $incomes = Income::whereBetween('date', [$dateStart, $dateEnd])
            ->when($accountId, fn($q) => $q->where('account_id', $accountId))
            ->whereIn('category', ['Penjualan', 'OMSET', 'Jasa Servis', 'Jasa Cetak', 'Jasa Tarik Tunai EDC'])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        $expenses = Expense::whereBetween('date', [$dateStart, $dateEnd])
            ->when($accountId, fn($q) => $q->where('account_id', $accountId))
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        return [
            'incomes' => $incomes,
            'expenses' => $expenses,
            'total_income' => $incomes->sum(),
            'total_expense' => $expenses->sum(),
        ];
    }
}
