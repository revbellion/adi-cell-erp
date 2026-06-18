<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $permissionLabels = [
            'dashboard' => 'Dashboard',
            'pos' => 'POS Penjualan',
            'stock_in' => 'Stok Masuk',
            'stock_opname' => 'Stok Opname',
            'products' => 'Data Barang',
            'categories' => 'Kategori Barang',
            'stock_report' => 'Laporan Stok',
            'accounts' => 'Akun & Modal Awal',
            'mutations' => 'Mutasi',
            'incomes' => 'Pendapatan',
            'expenses' => 'Pengeluaran',
            'receivables' => 'Piutang',
            'bills' => 'Tagihan',
            'summary' => 'Ringkasan',
            'cash_counter' => 'Cash Counter',
        ];

        return view('profile.index', compact('user', 'permissionLabels'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
