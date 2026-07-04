<?php

namespace App\Services;

use App\Models\Account;
use App\Models\CashCounterSession;
use App\Models\Expense;
use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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

    public function createSession(array $data): CashCounterSession
    {
        $calculatedTotal = $this->calculateTotal($data['denominations']);
        if ($calculatedTotal !== (int) $data['total_amount']) {
            throw new \DomainException('Total tidak sesuai dengan jumlah denominasi.');
        }

        return DB::transaction(function () use ($data) {
            return CashCounterSession::create([
                'user_id' => auth()->id(),
                'account_id' => $data['account_id'] ?? null,
                'opening_balance' => $data['opening_balance'] ?? 0,
                'title' => $data['title'],
                'denominations' => $data['denominations'],
                'total_amount' => $data['total_amount'],
            ]);
        });
    }

    public function updateSession(CashCounterSession $session, array $data): CashCounterSession
    {
        $this->authorize($session);

        $calculatedTotal = $this->calculateTotal($data['denominations']);
        if ($calculatedTotal !== (int) $data['total_amount']) {
            throw new \DomainException('Total tidak sesuai dengan jumlah denominasi.');
        }

        return DB::transaction(function () use ($session, $data) {
            $session->update([
                'account_id' => $data['account_id'] ?? null,
                'opening_balance' => $data['opening_balance'] ?? 0,
                'title' => $data['title'],
                'denominations' => $data['denominations'],
                'total_amount' => $data['total_amount'],
            ]);

            return $session->fresh();
        });
    }

    public function deleteSession(CashCounterSession $session): void
    {
        $this->authorize($session);

        DB::transaction(function () use ($session) {
            $session->incomes()->delete();
            $session->expenses()->delete();
            $session->delete();
        });
    }

    public function getLastClosingBalance(?int $accountId): int
    {
        if (!$accountId) return 0;

        $last = CashCounterSession::where('account_id', $accountId)
            ->latest()
            ->first();

        return $last?->total_amount ?? 0;
    }

    public function getSessionSummary(CashCounterSession $session): array
    {
        $session->load(['incomes', 'expenses']);

        $incomes = $session->incomes()
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        $expenses = $session->expenses()
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        return [
            'total_income' => $session->incomes()->sum('amount'),
            'total_expense' => $session->expenses()->sum('amount'),
            'incomes_by_category' => $incomes,
            'expenses_by_category' => $expenses,
            'expected_closing' => $session->expected_closing,
            'reconciliation_diff' => $session->reconciliation_diff,
            'reconciliation_status' => $session->reconciliation_status,
            'reconciliation_badge' => $session->reconciliation_badge,
        ];
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

    private function authorize(CashCounterSession $session): void
    {
        if ((int) $session->user_id !== (int) auth()->id()) {
            abort(403, 'Unauthorized');
        }
    }
}
