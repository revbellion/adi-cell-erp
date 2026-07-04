<?php

namespace App\Http\Controllers;

use App\Models\InitialCapital;
use App\Services\BalanceSheetService;
use Illuminate\Http\Request;

class ReportBalanceSheetController extends Controller
{
    public function __construct(
        protected BalanceSheetService $balanceSheetService
    ) {}

    public function index(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));
        $data = $this->balanceSheetService->getData($date);
        $availableDates = $this->balanceSheetService->getAvailableDates();

        return view('reports.balance-sheet', array_merge($data, [
            'selectedDate' => $date,
            'availableDates' => $availableDates,
        ]));
    }

    public function saveInitialCapital(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:0',
        ]);

        InitialCapital::truncate();
        InitialCapital::create([
            'amount' => $request->amount,
            'date' => now()->toDateString(),
            'description' => 'Modal awal (diset manual)',
        ]);

        return redirect()->back()->with('success', 'Modal awal berhasil disimpan.');
    }
}
