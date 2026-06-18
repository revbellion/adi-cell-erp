# ADI CELL | POS

Aplikasi POS dan pencatatan keuangan untuk konter HP **ADI CELL**.  
Built with **Laravel 13**, **MySQL 8**, **Bootstrap 5.3 CDN** (no build step).

## Fitur Utama

- **POS** — Penjualan multi-item, cetak receipt (HTML/PDF), stok otomatis berkurang
- **Stok Masuk** — Catat pembelian stok, otomatis catat pengeluaran
- **Stok Opname** — Sesuaikan stok fisik + harga beli
- **Dashboard** — Admin (keuangan lengkap) + Kasir (penjualan hari ini, stok, barang hampir habis)
- **Manajemen Akun** — Multi-akun (cash, bank, ewallet, ppob, other)
- **Modal Awal** — Input saldo awal per akun per periode
- **Pendapatan & Pengeluaran** — Catat transaksi keuangan harian
- **Mutasi** — Transfer antar akun
- **Piutang** — Catat piutang pelanggan + bayar + link WA reminder
- **Tagihan Rutin** — Recurring bills per bulan
- **Ringkasan** — Laporan bulanan (3/6/12 bulan)
- **Cash Counter** — Hitung fisik uang kertas/logam, cocokkan saldo sistem, catat selisih
- **Export Excel** — Semua modul bisa export XLSX
- **Role-Based Access** — Admin (full) + permission-based kasir (15 permission keys)
- **Backup & Restore** — Download SQL, restore, reset data transaksi
- **Dark Mode** — Toggle, persist ke localStorage
- **Error Pages** — 403, 404, 419, 500, 503 (light mode)

## Tech Stack

| Layer | Tech |
|-------|------|
| Backend | Laravel 13 (PHP 8.3+), Service Layer Pattern |
| Database | MySQL 8 |
| Frontend | Bootstrap 5.3 CDN, Font Awesome 6 CDN, jQuery 3.7 CDN, Inter Font |
| Export | Maatwebsite/Laravel-Excel |
| CSS | Inline (CSS custom properties, no build step) |
| Auth | Username-based, throttle 5/menit |

## Setup

```bash
composer install
cp .env.example .env
# Setup: DB_DATABASE=cash_tracker, APP_NAME="ADI CELL | POS"
php artisan key:generate
php artisan migrate
php artisan serve
```

### Admin Default
- Username: `admin` / Password: `admin123` (seed manually via tinker or register)

## Struktur Route

~67 routes grouped:
- **Guest**: login, logout
- **Stock**: POS, stok masuk, opname, receipt, report, hapus (admin only)
- **Master**: products, categories, accounts
- **Keuangan**: mutations, incomes, expenses, receivables, bills, summary, cash counter
- **Pengaturan**: backups, users (admin only)

## Dokumentasi

Lihat folder [`docs/`](./docs) untuk detail spesifikasi, struktur DB, fitur, dan struktur proyek.
