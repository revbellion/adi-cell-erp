# STRUKTUR PROYEK LARAVEL

```
ADI CELL | Cash Tracker/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AccountController.php        # CRUD akun (soft-deactivate)
│   │   │   ├── BackupController.php         # download/restore/reset DB
│   │   │   ├── DashboardController.php      # dashboard page + data
│   │   │   ├── ExpenseController.php        # CRUD pengeluaran
│   │   │   ├── IncomeController.php         # CRUD pendapatan
│   │   │   ├── MutationController.php       # CRUD mutasi antar akun
│   │   │   ├── OpeningBalanceController.php # CRUD modal awal per periode
│   │   │   ├── ReceivableController.php     # CRUD piutang + bayar + WA
│   │   │   └── SummaryController.php        # ringkasan bulanan
│   │   └── Requests/
│   │       ├── StoreAccountRequest.php
│   │       ├── StoreExpenseRequest.php
│   │       ├── StoreIncomeRequest.php
│   │       ├── StoreMutationRequest.php
│   │       ├── StoreOpeningBalanceRequest.php
│   │       ├── StoreReceivablePaymentRequest.php
│   │       ├── StoreReceivableRequest.php
│   │       ├── UpdateAccountRequest.php
│   │       ├── UpdateExpenseRequest.php
│   │       ├── UpdateIncomeRequest.php
│   │       ├── UpdateMutationRequest.php
│   │       └── UpdateReceivableRequest.php
│   ├── Models/
│   │   ├── Account.php
│   │   ├── Expense.php
│   │   ├── Income.php
│   │   ├── Mutation.php
│   │   ├── OpeningBalance.php
│   │   ├── Receivable.php
│   │   ├── ReceivablePayment.php
│   │   └── User.php              # Laravel default, tidak dipakai
│   ├── Providers/
│   │   └── AppServiceProvider.php # view composer → unpaid piutang count
│   ├── Services/
│   │   ├── DashboardService.php  # hitung equity, profit, saldo per akun
│   │   ├── ExpenseService.php    # CRUD pengeluaran
│   │   ├── IncomeService.php     # CRUD pendapatan
│   │   ├── MutationService.php   # CRUD mutasi
│   │   └── ReceivableService.php # CRUD piutang + bayar + WA + auto due_date + auto fee
│   └── helpers.php               # rp() + tgl() functions
├── database/
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   ├── 2026_06_10_115248_create_accounts_table.php
│   │   ├── 2026_06_10_115249_create_opening_balances_table.php
│   │   ├── 2026_06_10_115249_create_mutations_table.php
│   │   ├── 2026_06_10_115250_create_expenses_table.php
│   │   ├── 2026_06_10_115250_create_incomes_table.php
│   │   ├── 2026_06_10_115250_create_receivables_table.php
│   │   ├── 2026_06_10_115251_create_receivable_payments_table.php
│   │   ├── 2026_06_10_123123_add_category_to_incomes_table.php
│   │   └── 2026_06_10_172755_add_fee_to_receivables_table.php
│   └── seeders/
│       ├── AccountSeeder.php     # 10 akun default
│       └── DatabaseSeeder.php    # panggil AccountSeeder
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php     # sidebar layout + all CSS inline (~520 baris)
│       ├── accounts/
│       │   └── index.blade.php   # table + modal CRUD
│       ├── backups/
│       │   └── index.blade.php   # 3 cards: download/restore/reset
│       ├── dashboard/
│       │   └── index.blade.php   # 8 stat cards + 3 tables + 2 quick modals
│       ├── expenses/
│       │   └── index.blade.php   # table + filter + modal CRUD
│       ├── incomes/
│       │   └── index.blade.php   # table + filter + modal CRUD
│       ├── mutations/
│       │   └── index.blade.php   # table + filter + modal CRUD
│       ├── opening-balances/
│       │   └── index.blade.php   # bulk form per periode
│       ├── receivables/
│       │   └── index.blade.php   # tabs + filter + table + 3 modals
│       ├── summary/
│       │   └── index.blade.php   # tabel per bulan + expandable rows
│       └── welcome.blade.php     # default Laravel (tidak dipakai)
├── routes/
│   ├── web.php                   # 35 routes
│   └── console.php               # command inspire (default)
├── composer.json                 # Laravel ^13.8, maatwebsite/excel, autoload helpers.php
└── Exports/
    ├── ExpensesExport.php
    ├── IncomesExport.php
    ├── MutationsExport.php
    └── ReceivablesExport.php
```

## Arsitektur
- **Controller** → tipis, hanya passing data ke view / delegasi ke service
- **Service** → business logic perhitungan saldo & profit
- **Form Request** → validasi input + custom Indonesian messages
- **Blade** → template + modal inline (no partials/component)
- **CSS** → semua inline di `<style>` layout (no build step, ~520 baris)
- **Assets** → semua via CDN (Bootstrap 5.3, Font Awesome 6, jQuery 3.7, Inter font)
- **jQuery** → minimal, populate data ke modal edit/bayar via data attributes
- **View Composer** → AppServiceProvider → badge unpaid count di sidebar

## Yang Tidak Ada
- Tidak ada autentikasi (User model ada tapi tidak dipakai)
- Tidak ada API routes
- Tidak ada queue job
- Tidak ada Vite/Tailwind build (CSS inline + CDN)
- Tidak ada AJAX submit (form submit biasa, redirect back)
- Tidak ada partials/components Blade
