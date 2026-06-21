<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\CashCounterSession;
use App\Models\Expense;
use App\Models\Income;
use App\Models\OpeningBalance;
use App\Models\Mutation;
use App\Models\ReceivablePayment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CashCounterController extends Controller
{
    public function index(): View
    {
        $accounts = Account::active()->where('type', 'cash')->get();
        $cashAccount = Account::active()->where('name', config('accounts.cash_name'))->first();
        $hasCashAccounts = $accounts->isNotEmpty();
        $period = now()->format('Y-m');
        $balances = $this->getAccountBalances($accounts, $period);

        return view('cash-counter.index', compact('accounts', 'cashAccount', 'balances', 'hasCashAccounts'));
    }

    public function history(): JsonResponse
    {
        $sessions = CashCounterSession::with('account')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get(['id', 'account_id', 'title', 'total_amount', 'created_at']);

        return response()->json($sessions);
    }

    public function show(CashCounterSession $session): JsonResponse
    {
        if ($session->user_id !== auth()->id()) {
            abort(403);
        }
        $session->load('account');
        return response()->json($session);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'account_id' => 'nullable|exists:accounts,id',
            'title' => 'required|string|max:255',
            'denominations' => 'required|array',
            'target_amount' => 'nullable|integer|min:0',
            'total_amount' => 'required|integer|min:0',
        ]);

        $session = CashCounterSession::create([
            'user_id' => auth()->id(),
            'account_id' => $data['account_id'] ?? null,
            'title' => $data['title'],
            'denominations' => $data['denominations'],
            'target_amount' => $data['target_amount'],
            'total_amount' => $data['total_amount'],
        ]);

        $session->load('account');

        return response()->json($session);
    }

    public function update(Request $request, CashCounterSession $session): JsonResponse
    {
        if ($session->user_id !== auth()->id()) {
            abort(403);
        }

        $data = $request->validate([
            'account_id' => 'nullable|exists:accounts,id',
            'title' => 'required|string|max:255',
            'denominations' => 'required|array',
            'target_amount' => 'nullable|integer|min:0',
            'total_amount' => 'required|integer|min:0',
        ]);

        $session->update([
            'account_id' => $data['account_id'] ?? null,
            'title' => $data['title'],
            'denominations' => $data['denominations'],
            'target_amount' => $data['target_amount'],
            'total_amount' => $data['total_amount'],
        ]);

        return response()->json($session);
    }

    public function destroy(CashCounterSession $session): JsonResponse
    {
        if ($session->user_id !== auth()->id()) {
            abort(403);
        }

        $session->delete();

        return response()->json(['ok' => true]);
    }

    public function adjust(Request $request, CashCounterSession $session): JsonResponse
    {
        if ($session->user_id !== auth()->id()) {
            abort(403);
        }

        $data = $request->validate([
            'type' => 'required|in:income,expense',
            'amount' => 'required|integer|min:1',
            'account_id' => 'nullable|exists:accounts,id',
        ]);

        $accountId = $data['account_id'] ?? $session->account_id;
        if (!$accountId) {
            return response()->json(['message' => 'Pilih akun terlebih dahulu'], 422);
        }

        try {
            DB::transaction(function () use ($data, $session, $accountId) {
                $label = $data['type'] === 'income' ? 'lebih' : 'kurang';
                $description = 'Penyesuaian kas: ' . $session->title . ' (' . $label . ' Rp ' . number_format($data['amount'], 0, ',', '.') . ')';

                if ($data['type'] === 'income') {
                    Income::create([
                        'account_id' => $accountId,
                        'date' => now(),
                        'amount' => $data['amount'],
                        'description' => $description,
                        'category' => 'Penyesuaian Kas',
                    ]);
                } else {
                    Expense::create([
                        'account_id' => $accountId,
                        'date' => now(),
                        'amount' => $data['amount'],
                        'description' => $description,
                        'category' => 'Penyesuaian Kas',
                    ]);
                }
            });

            return response()->json(['ok' => true, 'message' => 'Penyesuaian berhasil dibuat']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal membuat penyesuaian: ' . $e->getMessage()], 500);
        }
    }

    private function getAccountBalances(Collection $accounts, string $period): array
    {
        [$year, $month] = explode('-', $period);
        $dateStart = sprintf('%04d-%02d-01', (int) $year, (int) $month);
        $dateEnd = Carbon::parse($dateStart)->endOfMonth();

        $openingBalances = OpeningBalance::where('period', $period)
            ->pluck('amount', 'account_id');

        $mutationsIn = Mutation::whereBetween('date', [$dateStart, $dateEnd])
            ->selectRaw('to_account_id, SUM(amount) as total')
            ->groupBy('to_account_id')
            ->pluck('total', 'to_account_id');

        $mutationsOut = Mutation::whereBetween('date', [$dateStart, $dateEnd])
            ->selectRaw('from_account_id, SUM(amount) as total')
            ->groupBy('from_account_id')
            ->pluck('total', 'from_account_id');

        $expenses = Expense::whereBetween('date', [$dateStart, $dateEnd])
            ->selectRaw('account_id, SUM(amount) as total')
            ->groupBy('account_id')
            ->pluck('total', 'account_id');

        $payments = ReceivablePayment::whereBetween('date', [$dateStart, $dateEnd])
            ->selectRaw('account_id, SUM(amount) as total')
            ->groupBy('account_id')
            ->pluck('total', 'account_id');

        $incomes = Income::whereBetween('date', [$dateStart, $dateEnd])
            ->whereNotNull('account_id')
            ->selectRaw('account_id, SUM(amount) as total')
            ->groupBy('account_id')
            ->pluck('total', 'account_id');

        $balances = [];
        foreach ($accounts as $account) {
            $balances[$account->id] = (int) (
                ($openingBalances[$account->id] ?? 0)
                + ($mutationsIn[$account->id] ?? 0)
                - ($mutationsOut[$account->id] ?? 0)
                - ($expenses[$account->id] ?? 0)
                + ($payments[$account->id] ?? 0)
                + ($incomes[$account->id] ?? 0)
            );
        }

        // Kurangkan total piutang unpaid dari cash
        $totalPiutangUnpaid = \App\Models\Receivable::where('status', 'unpaid')->sum('amount');
        $cashAccountName = config('accounts.cash_name');
        foreach ($accounts as $account) {
            if ($account->name === $cashAccountName) {
                $balances[$account->id] -= $totalPiutangUnpaid;
            }
        }

        return $balances;
    }
}
