# SPESIFIKASI APLIKASI — ADI CELL | POS

## Tujuan
Aplikasi POS dan pencatatan keuangan untuk toko konter HP **ADI CELL**.  
Mencatat penjualan, stok barang, multi-akun keuangan (bank, ewallet, cash, ppob), mutasi, pendapatan, pengeluaran, piutang, tagihan rutin, dengan RBAC dan perhitungan profit otomatis.

## Tech Stack
- **Laravel 13** (PHP 8.3+)
- **MySQL 8**
- **Bootstrap 5.3 CDN** + custom CSS inline (CSS custom properties, dark mode, no build step)
- **Font Awesome 6 CDN**
- **jQuery 3.7 CDN** — modal populate & select2-like features
- **Inter Font** (Google Fonts CDN)
- **Maatwebsite/Laravel-Excel** — export XLSX
- **Barryvdh/DomPDF** — cetak receipt PDF

## Branding
- Nama toko: **ADI CELL**
- App name: `ADI CELL | POS` (dari env `APP_NAME`)
- Title halaman: `ADI CELL | POS - {page}`

## Helper Functions (`app/helpers.php`)
- `rp(int $amount): string` — format Rp: `Rp 1.500.000`
- `tgl(Carbon|string $date): string` — format Indonesia: `Kamis, 21 Mei 2026 14:30`

## Autentikasi
- **Username-based login** (bukan email)
- Password minimal 6 karakter
- Login throttle: **5 percobaan per menit**
- Session-based auth (Laravel built-in)
- Logout via POST form

## Role & Permission
### Admin
- `permissions` kolom = `null` di database
- Method `isAdmin()`: `return empty($this->permissions)` (karena cast array ubah null jadi [])
- Admin bisa akses SEMUA halaman tanpa pengecualian

### Kasir (non-admin)
- `permissions` kolom = array of strings
- 15 permission keys:

| Key | Modul |
|-----|-------|
| `dashboard` | Dashboard |
| `pos` | POS Penjualan |
| `stock_in` | Stok Masuk |
| `stock_opname` | Stok Opname |
| `products` | Data Barang |
| `categories` | Kategori Barang |
| `stock_report` | Laporan Stok |
| `accounts` | Akun & Modal Awal |
| `mutations` | Mutasi |
| `incomes` | Pendapatan |
| `expenses` | Pengeluaran |
| `receivables` | Piutang |
| `bills` | Tagihan |
| `summary` | Ringkasan |
| `cash_counter` | Cash Counter |

- Default untuk user baru: POS, Stok Masuk, Stok Opname
- Middleware: `permission:key` via `CheckRole`, `admin` via `AdminMiddleware`

## Alur Bisnis
1. **Setup Awal** — Buat akun (cash, bank, ewallet, ppob, other), input saldo awal via Modal Awal
2. **Stok Masuk** — Catat pembelian barang → stok bertambah, otomatis catat pengeluaran
3. **POS** — Catat penjualan → stok berkurang, otomatis catat pendapatan (Income)
4. **Stok Opname** — Sesuaikan stok fisik jika ada selisih
5. **Setiap Hari** — Catat: pendapatan lain, pengeluaran lain, mutasi antar akun, piutang, bayar tagihan rutin
6. **Sistem** — Hitung otomatis saldo terkini + profit

## Perhitungan
```
Saldo Akun = Opening Balance + Mutasi Masuk - Mutasi Keluar - Pengeluaran + Pembayaran Piutang
Total Equity = ∑ saldo semua akun + ∑ piutang belum dibayar
Profit Bersih = Total Equity - ∑ Opening Balance (semua periode)
```

## Tipe Akun
`cash`, `bank`, `ewallet`, `ppob`, `other`

## Akun Default
| Nama | Type |
|------|------|
| SHOPEEPAY | ewallet |
| DANA | ewallet |
| GOPAY | ewallet |
| BCA | bank |
| CASH | cash |
| EDC Pending | bank |
| ORDERKUOTA | ppob |
| RITA | ppob |
| SIDIVA | ppob |
| SIMPEL | ppob |
| DIGIPOS | ppob |

## Service Layer
Business logic terpisah dari controller:
- `DashboardService` — equity, profit, saldo, chart
- `StockService` — stok in/out/opname, receipt, history
- `IncomeService` / `ExpenseService` / `MutationService` — CRUD + filter + export
- `ReceivableService` — piutang + bayar + WA + due_date auto
- `BillService` — tagihan rutin + status per bulan

## Fitur Keamanan
- Force HTTPS di production (via `AppServiceProvider@boot`)
- Login throttle: 5 attempts/menit
- Delete routes stok & penjualan hanya untuk admin
- Backup & Reset hanya untuk admin
- Permission check per-modul (bukan 1 grup multi-key)
- XSS protection via Blade `@json()` + escaped output
- Validasi filter date range di semua controller
- `cascadeOnDelete` → `nullOnDelete` (data financial tidak hilang saat parent dihapus)
- `ltrim(null, '+')` safety — cast ke string (PHP 8.1+)

## Fitur Tidak Ada
- Tidak ada registrasi public (hanya admin yang bisa buat user)
- Tidak ada API
- Tidak ada queue / job scheduler
- Tidak ada Vite / Tailwind (CDN + inline CSS)
- Tidak ada factory / seeder untuk data testing
- Tidak ada password reset
