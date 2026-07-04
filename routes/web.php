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
use App\Http\Controllers\ReportBalanceSheetController;
use App\Http\Controllers\ReportProfitLossController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\SalesReportController;
use App\Http\Controllers\PrintOrderController;
use App\Http\Controllers\RepairServiceController;
use App\Http\Controllers\CashCounterController;
use App\Http\Controllers\CustomerController;
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
        Route::post('stock/quick-add-product', [StockController::class, 'quickAddProduct'])->name('stock.quick-add-product');
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
        Route::get('stock/opname/pdf', [StockController::class, 'opnamePdf'])->name('stock.opname.pdf');
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

    // Catatan HPP
    Route::middleware('permission:stock_report')->get('hpp-records', [\App\Http\Controllers\HppRecordController::class, 'index'])->name('hpp-records.index');

    // Produk — akses berdasar permission products
    Route::middleware('permission:products')->group(function () {
        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::post('products', [ProductController::class, 'store'])->name('products.store');
        Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::post('products/bulk-delete', [ProductController::class, 'bulkDelete'])->name('products.bulk-delete');
        Route::get('products/{product}/history', [ProductController::class, 'history'])->name('products.history');
        Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
        Route::get('products/template', [ProductController::class, 'downloadTemplate'])->name('products.template');
        Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
    });

    // Kategori — middleware permission:categories
    Route::middleware('permission:categories')->group(function () {
        Route::get('product-categories', [ProductCategoryController::class, 'index'])->name('product-categories.index');
        Route::post('product-categories', [ProductCategoryController::class, 'store'])->name('product-categories.store');
        Route::put('product-categories/{product_category}', [ProductCategoryController::class, 'update'])->name('product-categories.update');
        Route::delete('product-categories/{product_category}', [ProductCategoryController::class, 'destroy'])->name('product-categories.destroy');
        Route::post('product-categories/bulk-delete', [ProductCategoryController::class, 'bulkDelete'])->name('product-categories.bulk-delete');
    });

    // Keuangan — permission per modul
    Route::middleware('permission:accounts')->group(function () {
        Route::resource('accounts', AccountController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('opening-balances', OpeningBalanceController::class)->only(['index', 'store', 'update']);
    });
    Route::middleware('permission:mutations')->group(function () {
        Route::resource('mutations', MutationController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('mutations/bulk-delete', fn() => redirect()->route('mutations.index'))->name('mutations.bulk-delete.get');
        Route::post('mutations/bulk-delete', [MutationController::class, 'bulkDelete'])->name('mutations.bulk-delete');
        Route::get('mutations/export', [MutationController::class, 'export'])->name('mutations.export');
    });
    Route::middleware('permission:incomes')->group(function () {
        Route::resource('incomes', IncomeController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::post('incomes/bulk-delete', [IncomeController::class, 'bulkDelete'])->name('incomes.bulk-delete');
        Route::get('incomes/export', [IncomeController::class, 'export'])->name('incomes.export');
    });
    Route::middleware('permission:expenses')->group(function () {
        Route::resource('expenses', ExpenseController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::post('expenses/bulk-delete', [ExpenseController::class, 'bulkDelete'])->name('expenses.bulk-delete');
        Route::get('expenses/export', [ExpenseController::class, 'export'])->name('expenses.export');
    });
    Route::middleware('permission:bills')->group(function () {
        Route::resource('bills', BillController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::post('bills/{recurring_bill}/pay', [BillController::class, 'pay'])->name('bills.pay');
    });
    Route::middleware('permission:receivables')->group(function () {
        Route::delete('receivables/bulk-delete', [ReceivableController::class, 'bulkDelete'])->name('receivables.bulk-delete');
        Route::resource('receivables', ReceivableController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::post('receivables/pay', [ReceivableController::class, 'pay'])->name('receivables.pay');
        Route::post('receivables/batch-pay', [ReceivableController::class, 'batchPay'])->name('receivables.batch-pay');
        Route::post('receivables/{id}/void', [ReceivableController::class, 'void'])->name('receivables.void');
        Route::post('receivables/{id}/add-nominal', [ReceivableController::class, 'addNominal'])->name('receivables.add-nominal');
        Route::get('receivables/export', [ReceivableController::class, 'export'])->name('receivables.export');
    });
    Route::middleware('permission:pending')->group(function () {
        Route::get('pending', [\App\Http\Controllers\PendingTransactionController::class, 'index'])->name('pending.index');
        Route::post('pending', [\App\Http\Controllers\PendingTransactionController::class, 'store'])->name('pending.store');
        Route::post('pending/{id}/complete', [\App\Http\Controllers\PendingTransactionController::class, 'complete'])->name('pending.complete');
        Route::post('pending/{id}/cancel', [\App\Http\Controllers\PendingTransactionController::class, 'cancel'])->name('pending.cancel');
        Route::delete('pending/{id}', [\App\Http\Controllers\PendingTransactionController::class, 'destroy'])->name('pending.destroy');
        Route::post('pending/bulk-delete', [\App\Http\Controllers\PendingTransactionController::class, 'bulkDelete'])->name('pending.bulk-delete');
        Route::get('pending/export', [\App\Http\Controllers\PendingTransactionController::class, 'export'])->name('pending.export');
    });
    Route::middleware('permission:accounts')->group(function () {
        Route::get('opname-saldo', [\App\Http\Controllers\OpnameSaldoController::class, 'index'])->name('opname-saldo.index');
        Route::post('opname-saldo', [\App\Http\Controllers\OpnameSaldoController::class, 'store'])->name('opname-saldo.store');
        Route::delete('opname-saldo/{opnameSaldo}', [\App\Http\Controllers\OpnameSaldoController::class, 'destroy'])->name('opname-saldo.destroy');
    });
    Route::middleware('permission:summary')->group(function () {
        Route::get('summary', [SummaryController::class, 'index'])->name('summary.index');
    });

    // Laporan Keuangan
    Route::middleware('permission:reports')->group(function () {
        Route::get('reports/profit-loss', [ReportProfitLossController::class, 'index'])->name('reports.profit-loss');
        Route::get('reports/balance-sheet', [ReportBalanceSheetController::class, 'index'])->name('reports.balance-sheet');
        Route::post('reports/balance-sheet/initial-capital', [ReportBalanceSheetController::class, 'saveInitialCapital'])->name('reports.balance-sheet.initial-capital');
    });

    Route::middleware('permission:sales_report')->group(function () {
        Route::get('sales-report', [SalesReportController::class, 'index'])->name('sales-report.index');
        Route::get('sales-report/export', [SalesReportController::class, 'export'])->name('sales-report.export');
    });

    // Backup & reset data — admin only
    Route::middleware(['admin', 'throttle:10,1'])->group(function () {
        Route::get('backups', [BackupController::class, 'index'])->name('backups.index');
        Route::get('backups/download', [BackupController::class, 'download'])->name('backups.download');
        Route::post('backups/restore', [BackupController::class, 'restore'])->name('backups.restore');
        Route::post('backups/reset', [BackupController::class, 'resetData'])->name('backups.reset');
    });

    // SOP Tutup Buku
    Route::get('sop', function () {
        return view('sop.index');
    })->name('sop.index');

    // Customer management
    Route::middleware('permission:customers')->group(function () {
        Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::put('customers/{id}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('customers/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');
        Route::get('customers/{id}/history', [CustomerController::class, 'history'])->name('customers.history');
        Route::get('customers/search', [CustomerController::class, 'search'])->name('customers.search');
    });

    // Returns
    Route::middleware('permission:returns')->group(function () {
        Route::get('returns', [ReturnController::class, 'index'])->name('returns.index');
        Route::post('returns', [ReturnController::class, 'store'])->name('returns.store');
        Route::post('returns/purchase', [ReturnController::class, 'storePurchase'])->name('returns.store-purchase');
        Route::get('returns/get-receipt', [ReturnController::class, 'getReceipt'])->name('returns.get-receipt');
    });

    // Jasa Cetak
    Route::middleware('permission:print_orders')->group(function () {
        Route::resource('print-orders', PrintOrderController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::post('print-orders/bulk-delete', [PrintOrderController::class, 'bulkDelete'])->name('print-orders.bulk-delete');
    });

    // Jasa Servis
    Route::middleware('permission:repair_services')->group(function () {
        Route::resource('repair-services', RepairServiceController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::post('repair-services/bulk-delete', [RepairServiceController::class, 'bulkDelete'])->name('repair-services.bulk-delete');
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
        Route::delete('/sessions/{session}/adjust', [CashCounterController::class, 'destroyAdjustment'])->name('sessions.adjust.destroy');
    });

    // Users management — cuma admin
    Route::middleware('admin')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('users/bulk-delete', [UserController::class, 'bulkDelete'])->name('users.bulk-delete');
    });
});
