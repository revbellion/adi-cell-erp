# FITUR & HALAMAN

## Dashboard (`/`)
**Middleware:** `permission:dashboard`

### Admin Dashboard
Filter periode (month YYYY-MM, auto-submit on change)

**12 Stat Cards (col-lg-3, 3 baris):**
| Card | Warna | Icon |
|------|-------|------|
| Total Equity | biru `#3b82f6` | fa-landmark |
| Piutang Belum Dibayar | hijau `#10b981` | fa-hand-holding-usd |
| Pengeluaran Bulan Ini | kuning `#f59e0b` | fa-shopping-cart |
| Profit Bersih | hijau/merah dinamis | fa-arrow-up/down |
| Saldo BCA | biru `#2563eb` | fa-university |
| AVG Harian | kuning `#f59e0b` | fa-calendar-day |
| Omset Bulan Ini | cyan `#06b6d4` | fa-chart-line |
| Saldo Cash | teal `#14b8a6` | fa-money-bill-wave |
| Nilai Stok | ungu | fa-boxes |
| Barang Hampir Habis | merah | fa-exclamation-triangle |
| Pembelian Stok | orange | fa-truck |
| Penjualan Stok | hijau | fa-shopping-cart |

**Tabel:**
- **Saldo Akun** — semua akun + saldo terkini
- **Mutasi Terakhir** — 10 mutasi terbaru
- **Profit 7 Hari** — daily omset, expense, profit (line chart)
- **Trend 6 Bulan** — income vs expense per bulan (line chart)
- **Piutang Terbaru** — 10 piutang terbaru

**Quick Add Modals:**
- Tombol Pendapatan → modal catat pendapatan langsung
- Tombol Pengeluaran → modal catat pengeluaran langsung

### Kasir Dashboard
- **Ringkasan**: penjualan hari ini (jumlah transaksi, total omset, total item)
- **Nilai Stok**: total nilai stok, jumlah barang hampir habis
- **Barang Hampir Habis**: daftar 10 produk dengan stok minimum
- **Penjualan Terakhir**: 5 nota terbaru

---

## Login (`/login`)
**Middleware:** guest

- Form username + password
- Redirect: admin → dashboard, kasir → POS
- Throttle: 5 attempts/menit
- Tampilan: light mode, gradient background

---

## POS Penjualan (`/stock/sales`)
**Middleware:** `permission:pos`

- Form: pilih produk (dari dropdown), qty, harga jual, deskripsi opsional
- Baris bisa ditambah/dihapus (min 1 item)
- Pilih akun pembayaran (akun PPOB tidak tampil di dropdown)
- Tanggal penjualan
- Validasi stok cukup sebelum simpan
- **Auto-create Income** saat simpan
- Nota: format `INV-YYYYMMDD-xxxxx`
- Tombol hapus nota hanya untuk admin
- **Receipt**: HTML view + cetak PDF via DomPDF
- Riwayat: tabel per-nota (receipt_id, total, item count, tanggal)

---

## Stok Masuk (`/stock/in`)
**Middleware:** `permission:stock_in`

- Form: pilih produk, qty, harga beli, akun, tanggal, deskripsi
- **Auto-create Expense** (kategori: "Stok Masuk") saat simpan
- Tombol hapus hanya untuk admin
- Riwayat: tabel paginated stok masuk

---

## Stok Opname (`/stock/opname`)
**Middleware:** `permission:stock_opname`

- List semua produk aktif dalam form
- Input stok fisik + harga beli baru
- Filter: hanya tampilkan baris dengan stok > 0
- Catat transaksi type `opname`

---

## Laporan Stok (`/stock/report`)
**Middleware:** `permission:stock_report`

- Tabel semua produk: nama, kategori, harga beli, harga jual, stok, stok min, nilai stok
- Total nilai stok, total pembelian, total penjualan
- Filter: barang hampir habis

---

## Data Barang (`/products`)
**Middleware:** `permission:products`

- CRUD via modal inline
- Kolom: nama, kategori, harga beli, harga jual, stok, stok min, unit
- Soft-delete (is_active = false)
- History stok per produk (riwayat transaksi)

---

## Kategori Barang (`/product-categories`)
**Middleware:** `permission:categories`

- CRUD via modal inline
- Validasi: tidak bisa hapus jika masih ada produk terkait
- Tampilkan jumlah produk per kategori

---

## Pendapatan (`/incomes`)
**Middleware:** `permission:incomes`

- CRUD via modal inline
- Filter: date range, kategori (datalist), search
- Pagination 20/halaman
- Export Excel (respect filter)

---

## Pengeluaran (`/expenses`)
**Middleware:** `permission:expenses`

- CRUD via modal inline
- Filter: date range, kategori (datalist), search
- Pagination 20/halaman
- Export Excel (respect filter)

---

## Mutasi (`/mutations`)
**Middleware:** `permission:mutations`

- CRUD via modal inline
- Filter: date range, search
- Validasi: from_account ≠ to_account
- Pagination 20/halaman
- Export Excel (respect filter)

---

## Piutang (`/receivables`)
**Middleware:** `permission:receivables`

- Tabs: All | Unpaid | Paid
- Filter: status, date range, search (nama/HP)
- **Input**: nama, no HP (opsional), nominal, modal, tanggal
- Auto **due_date** = date + 3 hari
- Tombol **Bayar** — record payment (pilih akun, nominal, tanggal)
- Tombol **WA** — link wa.me reminder (beda pesan untuk overdue vs unpaid)
- Status badge: Lunas (hijau) / Belum (kuning) / Telat (merah)
- Pagination 20/halaman
- Export Excel (respect filter)

---

## Tagihan Rutin (`/bills`)
**Middleware:** `permission:bills`

- CRUD via modal inline
- Input: nama, kategori, akun default, nominal, tanggal jatuh tempo (1-31)
- Tabel per periode (YYYY-MM): list tagihan + status Lunas/Belum
- Tombol **Bayar** — pilih periode, override nominal & akun jika perlu
- Auto-create Expense saat bayar
- Badge: jumlah tagihan belum dibayar

---

## Kelola Akun (`/accounts`)
**Middleware:** `permission:accounts`

- CRUD via modal inline
- Tipe: cash / bank / ewallet / ppob / other
- Soft-deactivate (is_active = false)

---

## Modal Awal (`/opening-balances`)
**Middleware:** `permission:accounts`

- Filter periode (YYYY-MM)
- Bulk input: semua akun aktif dalam satu form
- `updateOrCreate` per (account_id, period)

---

## Ringkasan Bulanan (`/summary`)
**Middleware:** `permission:summary`

- Pilih: 3 / 6 / 12 bulan (capped min 1, max 120)
- Tabel per bulan: omset, expense, profit (hijau/merah)
- Expandable row: detail pendapatan & pengeluaran per kategori
- Label bulan Indonesia (e.g., "Januari 2026")

---

## Cash Counter (`/cash-counter`)
**Middleware:** `permission:cash_counter`

- **Kalkulator Denominasi** — input jumlah uang kertas & logam per pecahan
- Tabs: Uang Kertas / Uang Logam
- Shortcuts: +10, +50, +100 per pecahan
- **Total otomatis**: grand total, subtotal kertas, subtotal logam
- **Target Kas** — input nominal target, tampilkan status: Pas (hijau), Lebih (kuning), Kurang (merah) + selisih
- **Tombol Isi Target** — isi target otomatis dari saldo sistem akun terpilih
- **Akun Kas** — pilih akun, tampilkan perbandingan saldo sistem vs uang fisik + selisih
- **Distribusi Donut Chart** — visual persentase per pecahan (Chart.js)
- **Tombol Salin** — copy ringkasan perhitungan ke clipboard
- **Simpan Sesi** — simpan perhitungan via modal (nama sesi, denominations JSON, total, target, akun)
- **Riwayat Sesi** — daftar sesi per user, bisa dimuat kembali / dihapus
- **Hapus Semua** — bersihkan riwayat sesi
- **Adjust** — jika ada selisih antara uang fisik & saldo sistem, bisa auto-create Income (jika lebih) atau Expense (jika kurang) dengan kategori "Penyesuaian Kas"
- Fully AJAX-based (CRUD via JSON)

---

## Manajemen User (`/users`)
**Middleware:** admin

- CRUD user
- 15 permission checkboxes
- Default permission (user baru): POS, Stok Masuk, Opname, Cash Counter
- Admin (kosongkan semua) = full akses
- Soft-delete user (bisa hapus, kecuali admin sendiri)

---

## Backup Database (`/backups`)
**Middleware:** admin

- **Download Backup** — `mysqldump` → file SQL
- **Restore Database** — upload file .sql
- **Reset Semua Data** — truncate 7 tabel transaksi, akun tetap aman
- Konfirmasi: harus ketik `RESET`

---

## Fitur UI/UX Global

### Layout
- Sidebar fixed 260px (gradient blue) — navigasi utama
- Topbar sticky — dark mode toggle, sidebar collapse, user info + logout
- Page content: padding 1.5rem

### Dark Mode
- Toggle via button di topbar, persisted ke localStorage
- CSS custom properties (`--bg-body`, `--bg-card`, `--text-primary`, dll)
- Override Bootstrap variables via `.dark-mode` class
- Override inline styles via `[style*="..."]` selectors

### Sidebar
- Desktop: collapse ke icon-only (60px) — persisted ke localStorage
- Mobile: hamburger toggle + overlay (transform X)
- Badge: jumlah piutang unpaid (via AppServiceProvider view composer)
- Group collapsible: Akun (accounts + opening balances), Pengaturan (users + backups)

### Responsive
- Breakpoint 991.98px: sidebar hidden default, toggle via hamburger
- Cards: col-lg-3 → col-sm-6 → full width

### CSS Design System (inline di layout)
- `card-modern` — rounded 12px, shadow, hover effect
- `stat-card` — metric cards with colored left border + hover lift
- `table-modern` — uppercase header, hover row, subtle borders
- `btn-modern` — rounded 10px, hover lift + shadow
- `modal-modern` — rounded 16px, backdrop blur
- `badge-status` — rounded pill badges
- `pagination-modern` — styled pagination
- `alert-modern` — rounded alerts

### Lainnya
- Semua form `autocomplete="off"`
- Semua form via Bootstrap modal (no page reload)
- Flash messages (success/error) via session
- Format Rp via helper `rp()`, tanggal Indonesia via `tgl()`
- `@json()` untuk data JS (XSS safe)
- Error pages: 403, 404, 419, 500, 503 — light mode, konsisten dengan login

---

## Route Summary (~67 routes)

### Auth (guest)
| Method | URI | Middleware |
|--------|-----|-----------|
| GET | `/login` | guest |
| POST | `/login` | guest, throttle:5,1 |
| POST | `/logout` | - |

### Dashboard
| Method | URI | Middleware |
|--------|-----|-----------|
| GET | `/` | auth, permission:dashboard |

### Stock
| Method | URI | Middleware |
|--------|-----|-----------|
| GET | `/stock/in` | permission:stock_in |
| POST | `/stock/in` | permission:stock_in |
| GET | `/stock/sales` | permission:pos |
| POST | `/stock/sales` | permission:pos |
| GET | `/stock/report` | permission:stock_report |
| GET | `/stock/opname` | permission:stock_opname |
| POST | `/stock/opname` | permission:stock_opname |
| GET | `/stock/receipt/{id}` | permission:pos,stock_in |
| GET | `/stock/receipt/{id}/pdf` | permission:pos,stock_in |
| DELETE | `/stock/in/{id}` | **admin** |
| DELETE | `/stock/sales/{id}` | **admin** |

### Products & Categories
All routes: `permission:products` or `permission:categories`
CRUD + product history

### Accounts & Opening Balances
All routes: `permission:accounts`

### Keuangan
| Modul | Prefix | Permission |
|-------|--------|------------|
| Mutasi | `/mutations` | mutations |
| Pendapatan | `/incomes` | incomes |
| Pengeluaran | `/expenses` | expenses |
| Piutang | `/receivables` | receivables |
| Tagihan | `/bills` | bills |
| Ringkasan | `/summary` | summary |
| Cash Counter | `/cash-counter` | cash_counter |

Semua + export

### Cash Counter Routes
| Method | URI | Middleware |
|--------|-----|-----------|
| GET | `/cash-counter` | permission:cash_counter |
| GET | `/cash-counter/history` | permission:cash_counter |
| POST | `/cash-counter/sessions` | permission:cash_counter |
| GET | `/cash-counter/sessions/{session}` | permission:cash_counter |
| PUT | `/cash-counter/sessions/{session}` | permission:cash_counter |
| DELETE | `/cash-counter/sessions/{session}` | permission:cash_counter |
| POST | `/cash-counter/sessions/{session}/adjust` | permission:cash_counter |

### Admin Only
| Modul | Prefix |
|-------|--------|
| Backup | `/backups` |
| Users | `/users` |
| Delete stock | `/stock/*/destroy` |
