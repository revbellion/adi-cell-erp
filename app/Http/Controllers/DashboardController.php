<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Expense;
use App\Models\Income;
use App\Services\BillService;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService,
        protected BillService $billService
    ) {}

    public function index(Request $request)
    {
        $period = $request->get('period', now()->format('Y-m'));

        $data = $this->dashboardService->getDashboardData($period);
        $data['period'] = $period;
        $data['accountBalances'] = $data['accounts']->pluck('balance', 'id');
        $data['accountList'] = Account::active()->get();
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

        return view('dashboard.index', $data);
    }
}
