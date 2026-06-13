<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Models\Account;

class AccountController extends Controller
{
    public function index()
    {
        return view('accounts.index', [
            'accounts' => Account::orderBy('is_active', 'desc')->orderBy('name')->get(),
        ]);
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
