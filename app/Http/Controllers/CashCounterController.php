<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCashCounterSessionRequest;
use App\Models\Account;
use App\Models\CashCounterSession;
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

        $lastClosingBalances = [];
        foreach ($accounts as $account) {
            $lastClosingBalances[$account->id] = $this->cashCounterService->getLastClosingBalance($account->id);
        }

        $today = now()->format('Y-m-d');
        $periodTransactions = $this->cashCounterService->getPeriodTransactions(
            $cashAccount?->id,
            $today
        );

        return view('cash-counter.index', compact(
            'accounts', 'cashAccount', 'balances', 'hasCashAccounts',
            'lastClosingBalances', 'periodTransactions'
        ));
    }

    public function history(): JsonResponse
    {
        $sessions = CashCounterSession::with('account')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get(['id', 'account_id', 'title', 'opening_balance', 'total_amount', 'created_at']);

        return response()->json($sessions);
    }

    public function show(CashCounterSession $session): JsonResponse
    {
        if ((int) $session->user_id !== (int) auth()->id()) {
            abort(403);
        }

        $session->load('account');
        $session->summary = $this->cashCounterService->getSessionSummary($session);

        return response()->json($session);
    }

    public function store(StoreCashCounterSessionRequest $request): JsonResponse
    {
        try {
            $session = $this->cashCounterService->createSession($request->validated());
            $session->load('account');

            return response()->json($session, 201);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function update(StoreCashCounterSessionRequest $request, CashCounterSession $session): JsonResponse
    {
        try {
            $session = $this->cashCounterService->updateSession($session, $request->validated());

            return response()->json($session);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy(CashCounterSession $session): JsonResponse
    {
        try {
            $this->cashCounterService->deleteSession($session);

            return response()->json(['ok' => true]);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function periodTransactions(Request $request): JsonResponse
    {
        $date = $request->input('date', now()->format('Y-m-d'));
        $accountId = $request->input('account_id');

        $result = $this->cashCounterService->getPeriodTransactions($accountId, $date);

        return response()->json($result);
    }
}
