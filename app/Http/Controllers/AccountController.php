<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Models\Account;
use App\Models\Mutation;
use App\Models\Expense;
use App\Models\Income;
use App\Models\OpeningBalance;
use App\Models\ReceivablePayment;
use App\Models\StockTransaction;
use App\Models\CashCounterSession;
use App\Models\PendingTransaction;

class AccountController extends Controller
{
    // Controller untuk Akun Keuangan (cash, bank, ewallet, ppob, other)
    // BUKAN untuk akun user/login — itu di UserController
    public function index()
    {
        $accounts = Account::orderBy('is_active', 'desc')->orderBy('name')->get();
        $totalAccounts = $accounts->count();
        $totalActive = $accounts->where('is_active', true)->count();
        return view('accounts.index', compact('accounts', 'totalAccounts', 'totalActive'));
    }

    public function store(StoreAccountRequest $request)
    {
        try {
            Account::create($request->validated());
            return redirect()->back()->with('success', 'Akun berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan akun: ' . $e->getMessage());
        }
    }

    public function update(UpdateAccountRequest $request, Account $account)
    {
        try {
            $account->update($request->validated());
            return redirect()->back()->with('success', 'Akun berhasil diubah.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengubah akun: ' . $e->getMessage());
        }
    }

    public function destroy(Account $account)
    {
        try {
            // Cek apakah ada transaksi terkait
            $hasTransactions = $this->hasRelatedTransactions($account);

            if ($hasTransactions) {
                return redirect()->back()->with('error', 'Akun tidak bisa dinonaktifkan karena masih memiliki transaksi terkait. Hapus transaksi terkait terlebih dahulu.');
            }

            $account->update(['is_active' => false]);
            return redirect()->back()->with('success', 'Akun berhasil dinonaktifkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menonaktifkan akun: ' . $e->getMessage());
        }
    }

    private function hasRelatedTransactions(Account $account): bool
    {
        // Cek mutasi
        if (Mutation::where('from_account_id', $account->id)->orWhere('to_account_id', $account->id)->exists()) {
            return true;
        }

        // Cek expenses
        if (Expense::where('account_id', $account->id)->exists()) {
            return true;
        }

        // Cek incomes
        if (Income::where('account_id', $account->id)->exists()) {
            return true;
        }

        // Cek opening balances
        if (OpeningBalance::where('account_id', $account->id)->exists()) {
            return true;
        }

        // Cek receivable payments
        if (ReceivablePayment::where('account_id', $account->id)->exists()) {
            return true;
        }

        // Cek stock transactions
        if (StockTransaction::where('account_id', $account->id)->exists()) {
            return true;
        }

        // Cek cash counter sessions
        if (CashCounterSession::where('account_id', $account->id)->exists()) {
            return true;
        }

        // Cek pending transactions
        if (PendingTransaction::where('completed_account_id', $account->id)->exists()) {
            return true;
        }

        return false;
    }
}
