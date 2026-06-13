# RENCANA IMPLEMENTASI (ARSIP — Aplikasi Sudah Jadi)

Dokumen ini mencatat bagaimana aplikasi **seharusnya di-setup** dari awal untuk referensi future setup.

## Setup Project
1. `composer create-project laravel/laravel .` (Laravel 13)
2. Konfigurasi `.env` (DB_DATABASE=cash_tracker, DB_USERNAME=root, DB_PASSWORD=)
3. Hapus auth scaffolding (tidak perlu login/register)
4. Set `APP_LOCALE=id`, `APP_FALLBACK_LOCALE=id`, `APP_FAKER_LOCALE=id_ID` di `.env`

## Package Tambahan
```bash
composer require maatwebsite/excel
```

## Database
1. Buat migration: accounts, opening_balances, mutations, expenses, incomes, receivables, receivable_payments
2. Migration alter: `add_category_to_incomes`, `add_fee_to_receivables`
3. Seeder: `AccountSeeder.php` (10 akun default)
4. `php artisan migrate --seed`
5. Buat akun **EDC Pending** (ID 11) manual via `php artisan tinker`

## Backend
1. **Models** — Eloquent + relationships + casts (`date` → date, `amount` → integer)
   - Receivable: accessors `remaining`, `principal`, `status_badge`; scopes `unpaid`, `overdue`
   - Account: scopes `active()`, `byType()`
2. **Services** — business logic per fitur (DashboardService, IncomeService, ExpenseService, MutationService, ReceivableService)
3. **Form Requests** — 12 class validasi dengan custom Indonesian messages
4. **Controllers** — tipis, delegasi ke service
5. **Routes** — 35 routes (resource + custom: pay, whatsapp, export)
6. **AppServiceProvider** — view composer untuk badge unpaid count di sidebar

## Exports (Maatwebsite/Laravel-Excel)
4 export classes: IncomesExport, ExpensesExport, MutationsExport, ReceivablesExport
Semua respect filter yang sama dengan index view.

## Frontend (Bootstrap 5 CDN — No Build Step)

### Layout
- Sidebar 260px (gradient blue) + topbar sticky — semua inline CSS di `<style>`
- CSS custom properties (`--bg-body`, `--bg-card`, `--text-primary`, dll) — memudahkan dark mode

### Dark Mode
- Toggle dengan button → toggle class `.dark-mode` di `<body>`
- localStorage persistence
- Override CSS variables di `.dark-mode` selector
- Override Bootstrap internal variables (`--bs-body-color`, `--bs-card-color`, `--bs-table-color`)
- Override inline styles yang hardcode warna via `[style*="..."]`

### Sidebar Collapse
- Desktop: collapse ke icon-only (toggle + localStorage)
- Mobile: hamburger + overlay (transform X)

### Halaman
- Dashboard: 8 stat cards + 3 tabel + 2 quick add modals
- Setiap halaman CRUD: card + table + modal inline (create/edit) + filter + pagination + export
- Modal awal: bulk form per periode
- Ringkasan: tabel bulanan expandable
- Backup: 3 cards (download, restore, reset)

### Komponen CSS (inline)
- `stat-card` — colored left border + hover lift
- `card-modern` — rounded 12px + shadow + hover shadow
- `table-modern` — uppercase header + hover row
- `btn-modern` — rounded 10px + hover effect
- `modal-modern` — rounded 16px
- `badge-status` — rounded pill
- `pagination-modern` — styled pagination

### Custom Setup
1. `composer.json` — tambah `"files": ["app/helpers.php"]` di autoload
2. `composer dump-autoload` setelah helper baru
3. Ubah kolom date menjadi `date` di beberapa migration awal (ALTER)
4. Buat `helpers.php` — fungsi `rp()` (format Rp) + `tgl()` (format tanggal Indonesia)

## Catatan
- `public/template/` ada Ninja Admin sisa (tidak dipakai, bisa dihapus)
- `npm run dev` / Vite tidak diperlukan (asset CDN)
- `php artisan storage:link` tidak diperlukan
- Semua JS inline di layout & view (jQuery CDN)
