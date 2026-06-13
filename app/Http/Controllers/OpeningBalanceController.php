<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\OpeningBalance;
use Illuminate\Http\Request;

class OpeningBalanceController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', now()->format('Y-m'));

        $accounts = Account::active()->get();
        $openingBalances = OpeningBalance::where('period', $period)
            ->get()
            ->keyBy('account_id');

        return view('opening-balances.index', compact('accounts', 'openingBalances', 'period'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'period' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'balances' => ['required', 'array'],
            'balances.*' => ['required', 'integer', 'min:0'],
        ]);

        $period = $validated['period'];
        $balances = $validated['balances'];

        foreach ($balances as $accountId => $amount) {
            OpeningBalance::updateOrCreate(
                [
                    'account_id' => $accountId,
                    'period' => $period,
                ],
                ['amount' => $amount]
            );
        }

        return redirect()->back()->with('success', 'Opening balances saved successfully.');
    }

    public function update(Request $request)
    {
        return $this->store($request);
    }
}
