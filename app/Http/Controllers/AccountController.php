<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Models\Account;

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
        Account::create($request->validated());

        return redirect()->back()->with('success', 'Akun berhasil ditambahkan.');
    }

    public function update(UpdateAccountRequest $request, Account $account)
    {
        $account->update($request->validated());

        return redirect()->back()->with('success', 'Akun berhasil diubah.');
    }

    public function destroy(Account $account)
    {
        $account->update(['is_active' => false]);

        return redirect()->back()->with('success', 'Akun berhasil dinonaktifkan.');
    }
}
