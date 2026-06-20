<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Expense;
use App\Models\Income;
use App\Services\BillService;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService,
        protected BillService $billService
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $period = $request->get('period', now()->format('Y-m'));

        if ($user->isAdmin()) {
            $data = $this->dashboardService->getDashboardData($period);
            $data['period'] = $period;
            $data['accountBalances'] = $data['accounts']->pluck('balance', 'id');
            $data['accountList'] = Account::active()->get();
            $data['cashAccount'] = Account::active()->where('name', config('accounts.cash_name'))->first();
            $data['cashAccounts'] = Account::active()->where('type', 'cash')->get();
            $data['categories'] = Expense::select('category')->distinct()->pluck('category');
            $data['incomeCategories'] = Income::select('category')->distinct()->pluck('category');

            [$year, $month] = explode('-', $period);
            $start = sprintf('%04d-%02d-01', $year, $month);
            $end = \Carbon\Carbon::parse($start)->endOfMonth();
            $data['expenseCategories'] = Expense::whereBetween('date', [$start, $end])
                ->selectRaw('category, SUM(amount) as total')
                ->groupBy('category')
                ->orderByDesc('total')
                ->pluck('total', 'category');

            $data['billSummary'] = $this->billService->getDueBillsCount($period);
            $data['products'] = \App\Models\Product::activeWithCategory()->get();
            $data['unpaidBills'] = \App\Models\RecurringBill::with('account')
                ->where('is_active', true)
                ->whereDoesntHave('payments', function ($q) use ($period) {
                    $q->where('period', $period);
                })
                ->orderBy('due_day')
                ->get();

            return view('dashboard.index', $data);
        }

        $data = $this->dashboardService->getKasirData();
        return view('dashboard.kasir', $data);
    }
}
