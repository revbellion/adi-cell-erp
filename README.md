# ADI CELL | Cash Tracker

Aplikasi pencatatan keuangan konter HP (ADI CELL) berbasis **Laravel 13** + **MySQL 8** + **Bootstrap 5.3 CDN**.

Multi-akun (bank, ewallet, cash, ppob), mutasi antar akun, pengeluaran, pendapatan, piutang dengan perhitungan profit otomatis.

## Fitur Utama

- **Dashboard** — 8 stat card (equity, piutang, expense, profit, BCA, avg harian, omset, cash) + tabel saldo akun + mutasi terakhir + profit 7 hari
- **Multi-Account** — Kelola akun (CRUD) dengan tipe ewallet/bank/cash/ppob/other
- **Modal Awal** — Input opening balance per akun per periode
- **Pendapatan** — Catat pendapatan dengan kategori & deskripsi
- **Pengeluaran** — Catat pengeluaran per akun dengan kategori & deskripsi
- **Mutasi** — Transfer antar akun
- **Piutang** — Kelola piutang pelanggan + fee otomatis + pembayaran + status + link WA reminder
- **Ringkasan Bulanan** — View 3/6/12 bulan dengan detail per kategori (expandable)
- **Export Excel** — Semua data (pendapatan, pengeluaran, mutasi, piutang) bisa di-export ke Excel
- **Backup & Restore** — Download SQL, restore dari file, reset semua data (accounts tetap aman)
- **Dark Mode** — Toggle dengan localStorage persistence
- **Sidebar Collapse** — Desktop collapse ke icon & mobile responsive hamburger

## Tech Stack

- **Backend:** Laravel 13 (PHP 8.3+), Service Layer Pattern
- **Database:** MySQL 8
- **Frontend:** Bootstrap 5.3 CDN, Font Awesome 6 CDN, jQuery 3.7 CDN, Inter Font
- **Export:** Maatwebsite/Laravel-Excel
- **CSS:** Semua inline (no build step)

## Setup

```bash
composer install
cp .env.example .env   # config DB_DATABASE=cash_tracker
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Dokumentasi lengkap: [`docs/`](./docs)
