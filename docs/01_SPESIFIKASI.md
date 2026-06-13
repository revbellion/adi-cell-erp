# SPESIFIKASI APLIKASI — ADI CELL | Cash Tracker

## Tujuan
Aplikasi pencatatan keuangan untuk toko konter (ADI CELL). Mencatat saldo multi-akun (bank, ewallet, cash, ppob, other), mutasi antar akun, pendapatan, pengeluaran, piutang, dan menghitung profit otomatis.

## Tech Stack
- **Laravel 13** (PHP 8.3+)
- **MySQL 8**
- **Bootstrap 5.3 CDN** + custom CSS inline (CSS custom properties, no build step)
- **Font Awesome 6 CDN**
- **jQuery 3.7 CDN** — populate data modal edit & bayar
- **Inter Font** (Google Fonts CDN)
- **Maatwebsite/Laravel-Excel** — export XLSX

## Branding
- Nama toko: **ADI CELL**
- Title: `ADI CELL | Cash Tracker`

## Helper Functions (`app/helpers.php`)
- `rp(int $amount): string` — format Rp, contoh: `Rp 1.500.000`
- `tgl(Carbon|string $date): string` — format Indonesia: `Kamis, 21 Mei 2026 14:30`

## Alur Bisnis
1. **Awal Pakai** — input saldo real semua akun (opening_balance) via Settings
2. **Setiap Hari** — catat: pendapatan, pengeluaran, mutasi perpindahan antar akun, piutang
3. **Sistem** — hitung otomatis saldo terkini + profit berdasarkan selisih equity - modal awal

## Perhitungan
```
Saldo Akun = Opening Balance + Mutasi Masuk - Mutasi Keluar - Pengeluaran + Pembayaran Piutang
Total Equity = ∑ saldo semua akun + ∑ piutang belum dibayar
Profit Bersih = Total Equity - ∑ Opening Balance
```

## Akun Default (11 akun)
| # | Nama | Type |
|---|------|------|
| 1 | SHOPEEPAY | ewallet |
| 2 | DANA | ewallet |
| 3 | ORDERKUOTA | ppob |
| 4 | GOPAY | ewallet |
| 5 | RITA | ppob |
| 6 | SIDIVA | ppob |
| 7 | SIMPEL | ppob |
| 8 | DIGIPOS | ppob |
| 9 | BCA | bank |
| 10 | CASH | cash |
| 11 | EDC Pending | bank |

## Tipe Akun
`cash`, `bank`, `ewallet`, `ppob`, `other`

## Fitur Tambahan
- **Dark Mode** — toggle dengan localStorage, CSS custom properties, override Bootstrap variables
- **Sidebar Collapse** — desktop collapse ke icon-only, mobile hamburger + overlay
- **Export Excel** — semua halaman (pendapatan, pengeluaran, mutasi, piutang)
- **Backup/Restore** — download SQL via `mysqldump`, restore via upload file, reset data transaksi
- **WhatsApp Reminder** — link `wa.me` dengan pesan otomatis (overdue / reminder)
- **Service Layer** — business logic terpisah dari controller (DashboardService, IncomeService, ExpenseService, MutationService, ReceivableService)

## Fitur Tidak Ada
- Tidak ada autentikasi (single-user)
- Tidak ada laporan/export PDF
- Tidak ada manajemen stok barang
- Tidak ada notifikasi/pengingat otomatis
- Tidak ada API
