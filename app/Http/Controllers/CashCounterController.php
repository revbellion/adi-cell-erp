<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCashCounterSessionRequest;
use App\Models\Account;
use App\Services\CashCounterService;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashCounterController extends Controller
{
    public function __construct(
        protected CashCounterService $cashCounterService,
        protected DashboardService $dashboardService
    ) {}

    public function index(): View
    {
        $accounts = Account::active()->where('type', 'cash')->get();
        $cashAccount = Account::active()->where('name', config('accounts.cash_name'))->first();
        $hasCashAccounts = $accounts->isNotEmpty();
        $period = now()->format('Y-m');
        $balances = $this->dashboardService->calculateAccountBalances($accounts, $period);

        $today = now()->format('Y-m-d');
        $periodTransactions = $this->cashCounterService->getPeriodTransactions(
            $cashAccount?->id,
            $today
        );

        return view('cash-counter.index', compact(
            'accounts', 'cashAccount', 'balances', 'hasCashAccounts',
            'periodTransactions'
        ));
    }

    public function store(StoreCashCounterSessionRequest $request): JsonResponse
    {
        $data = $request->validated();

        $calculatedTotal = $this->cashCounterService->calculateTotal($data['denominations']);
        if ($calculatedTotal !== (int) $data['total_amount']) {
            return response()->json(['message' => 'Total tidak sesuai dengan jumlah denominasi.'], 422);
        }

        $result = $this->cashCounterService->saveAdjustment($data);

        return response()->json($result, $result['adjusted'] ? 201 : 200);
    }

    public function periodTransactions(Request $request): JsonResponse
    {
        $date = $request->input('date', now()->format('Y-m-d'));
        $accountId = $request->input('account_id');

        $result = $this->cashCounterService->getPeriodTransactions($accountId, $date);

        return response()->json($result);
    }
}
