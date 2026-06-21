<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OpeningBalanceController;
use App\Http\Controllers\MutationController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ReceivableController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SummaryController;
use App\Http\Controllers\CashCounterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Auth routes (guest only)
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->middleware('throttle:5,1');
});

Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// All protected routes
Route::middleware('auth')->group(function () {

    // Dashboard — semua user bisa
    Route::middleware('permission:dashboard')->get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Profile — semua user bisa (ubah password sendiri)
    Route::get('profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Stok — akses berdasar permission
    Route::middleware('permission:stock_in')->group(function () {
        Route::get('stock/in', [StockController::class, 'stockIn'])->name('stock.in');
        Route::post('stock/in', [StockController::class, 'storeIn'])->name('stock.in.store');
    });
    Route::middleware('permission:pos')->group(function () {
        Route::get('stock/sales', [StockController::class, 'sales'])->name('stock.sales');
        Route::post('stock/sales', [StockController::class, 'storeSale'])->name('stock.sales.store');
    });
    Route::middleware('permission:stock_report')->group(function () {
        Route::get('stock/report', [StockController::class, 'report'])->name('stock.report');
    });
    Route::middleware('permission:stock_opname')->group(function () {
        Route::get('stock/opname', [StockController::class, 'opname'])->name('stock.opname');
        Route::post('stock/opname', [StockController::class, 'storeOpname'])->name('stock.opname.store');
    });
    // Receipt — akses untuk user dengan permission POS atau Stock In
    Route::middleware('permission:pos,stock_in')->group(function () {
        Route::get('stock/receipt/{receiptId}', [StockController::class, 'receipt'])->name('stock.receipt');
        Route::get('stock/receipt/{receiptId}/pdf', [StockController::class, 'receiptPdf'])->name('stock.receipt.pdf');
    });
    // Hapus transaksi — admin only
    Route::middleware('admin')->group(function () {
        Route::delete('stock/in/{stock_transaction}', [StockController::class, 'destroyStockIn'])->name('stock.in.destroy');
        Route::delete('stock/sales/{receiptId}', [StockController::class, 'destroy'])->name('stock.sales.destroy');
    });

    // Produk — akses berdasar permission products
    Route::middleware('permission:products')->group(function () {
        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::post('products', [ProductController::class, 'store'])->name('products.store');
        Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::get('products/{product}/history', [ProductController::class, 'history'])->name('products.history');
    });

    // Kategori — middleware permission:categories
    Route::middleware('permission:categories')->group(function () {
        Route::get('product-categories', [ProductCategoryController::class, 'index'])->name('product-categories.index');
        Route::post('product-categories', [ProductCategoryController::class, 'store'])->name('product-categories.store');
        Route::put('product-categories/{product_category}', [ProductCategoryController::class, 'update'])->name('product-categories.update');
        Route::delete('product-categories/{product_category}', [ProductCategoryController::class, 'destroy'])->name('product-categories.destroy');
    });

    // Keuangan — permission per modul
    Route::middleware('permission:accounts')->group(function () {
        Route::resource('accounts', AccountController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('opening-balances', OpeningBalanceController::class)->only(['index', 'store', 'update']);
    });
    Route::middleware('permission:mutations')->group(function () {
        Route::resource('mutations', MutationController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('mutations/export', [MutationController::class, 'export'])->name('mutations.export');
    });
    Route::middleware('permission:incomes')->group(function () {
        Route::resource('incomes', IncomeController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('incomes/export', [IncomeController::class, 'export'])->name('incomes.export');
    });
    Route::middleware('permission:expenses')->group(function () {
        Route::resource('expenses', ExpenseController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('expenses/export', [ExpenseController::class, 'export'])->name('expenses.export');
    });
    Route::middleware('permission:bills')->group(function () {
        Route::resource('bills', BillController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::post('bills/{recurring_bill}/pay', [BillController::class, 'pay'])->name('bills.pay');
    });
    Route::middleware('permission:receivables')->group(function () {
        Route::resource('receivables', ReceivableController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::post('receivables/pay', [ReceivableController::class, 'pay'])->name('receivables.pay');
        Route::get('receivables/{receivable}/whatsapp', [ReceivableController::class, 'whatsappLink'])->name('receivables.whatsapp');
        Route::get('receivables/export', [ReceivableController::class, 'export'])->name('receivables.export');
    });
    Route::middleware('permission:receivables')->group(function () {
        Route::get('pending', [\App\Http\Controllers\PendingTransactionController::class, 'index'])->name('pending.index');
        Route::post('pending', [\App\Http\Controllers\PendingTransactionController::class, 'store'])->name('pending.store');
        Route::post('pending/{id}/complete', [\App\Http\Controllers\PendingTransactionController::class, 'complete'])->name('pending.complete');
        Route::delete('pending/{id}', [\App\Http\Controllers\PendingTransactionController::class, 'destroy'])->name('pending.destroy');
        Route::get('pending/export', [\App\Http\Controllers\PendingTransactionController::class, 'export'])->name('pending.export');
    });
    Route::middleware('permission:accounts')->group(function () {
        Route::get('opname-saldo', [\App\Http\Controllers\OpnameSaldoController::class, 'index'])->name('opname-saldo.index');
        Route::post('opname-saldo', [\App\Http\Controllers\OpnameSaldoController::class, 'store'])->name('opname-saldo.store');
    });
    Route::middleware('permission:summary')->group(function () {
        Route::get('summary', [SummaryController::class, 'index'])->name('summary.index');
    });

    // Backup & reset data — admin only
    Route::middleware(['admin', 'throttle:3,1'])->group(function () {
        Route::get('backups', [BackupController::class, 'index'])->name('backups.index');
        Route::get('backups/download', [BackupController::class, 'download'])->name('backups.download');
        Route::post('backups/restore', [BackupController::class, 'restore'])->name('backups.restore');
        Route::post('backups/reset', [BackupController::class, 'resetData'])->name('backups.reset');
    });

    // Cash Counter
    Route::middleware('permission:cash_counter')->prefix('cash-counter')->name('cash-counter.')->group(function () {
        Route::get('/', [CashCounterController::class, 'index'])->name('index');
        Route::get('/history', [CashCounterController::class, 'history'])->name('history');
        Route::post('/sessions', [CashCounterController::class, 'store'])->name('sessions.store');
        Route::get('/sessions/{session}', [CashCounterController::class, 'show'])->name('sessions.show');
        Route::put('/sessions/{session}', [CashCounterController::class, 'update'])->name('sessions.update');
        Route::delete('/sessions/{session}', [CashCounterController::class, 'destroy'])->name('sessions.destroy');
        Route::post('/sessions/{session}/adjust', [CashCounterController::class, 'adjust'])->name('sessions.adjust');
    });

    // Users management — cuma admin
    Route::middleware('admin')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
