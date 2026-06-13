<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OpeningBalanceController;
use App\Http\Controllers\MutationController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ReceivableController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\SummaryController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('opening-balances', OpeningBalanceController::class)->only(['index', 'store', 'update']);
Route::resource('mutations', MutationController::class)->only(['index', 'store', 'update', 'destroy']);
Route::resource('expenses', ExpenseController::class)->only(['index', 'store', 'update', 'destroy']);
Route::resource('incomes', IncomeController::class)->only(['index', 'store', 'update', 'destroy']);
Route::resource('accounts', AccountController::class)->only(['index', 'store', 'update', 'destroy']);
Route::resource('bills', BillController::class)->only(['index', 'store', 'update', 'destroy']);
Route::post('bills/{recurring_bill}/pay', [BillController::class, 'pay'])->name('bills.pay');
Route::resource('receivables', ReceivableController::class)->only(['index', 'store', 'update', 'destroy']);
Route::post('receivables/pay', [ReceivableController::class, 'pay'])->name('receivables.pay');

Route::get('backups', [BackupController::class, 'index'])->name('backups.index');
Route::get('backups/download', [BackupController::class, 'download'])->name('backups.download');
Route::post('backups/restore', [BackupController::class, 'restore'])->name('backups.restore');
Route::post('backups/reset', [BackupController::class, 'resetData'])->name('backups.reset');
Route::get('receivables/{receivable}/whatsapp', [ReceivableController::class, 'whatsappLink'])->name('receivables.whatsapp');
Route::get('summary', [SummaryController::class, 'index'])->name('summary.index');

Route::get('incomes/export', [IncomeController::class, 'export'])->name('incomes.export');
Route::get('expenses/export', [ExpenseController::class, 'export'])->name('expenses.export');
Route::get('mutations/export', [MutationController::class, 'export'])->name('mutations.export');
Route::get('receivables/export', [ReceivableController::class, 'export'])->name('receivables.export');

// Auth routes removed — single-user app
// require __DIR__.'/auth.php';
