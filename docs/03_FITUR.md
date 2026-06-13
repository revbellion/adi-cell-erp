# FITUR & HALAMAN

## 1. Dashboard (`/`)
**Filter periode** (input month YYYY-MM, auto-submit on change)

**8 Stat Cards (col-lg-3, 2 baris):**
| Card | Border | Icon |
|------|--------|------|
| Total Equity | biru `#3b82f6` | fa-landmark |
| Piutang Belum Dibayar | hijau `#10b981` | fa-hand-holding-usd |
| Pengeluaran Bulan Ini | kuning `#f59e0b` | fa-shopping-cart |
| Profit Bersih | hijau/merah (dinamis) | fa-arrow-up/down |
| Saldo BCA | biru `#2563eb` | fa-university |
| AVG Harian | kuning `#f59e0b` | fa-calendar-day |
| Omset Bulan Ini | cyan `#06b6d4` | fa-chart-line |
| Saldo CASH | teal `#14b8a6` | fa-money-bill-wave |

**Tabel (col-lg-4):**
- **Saldo Digital** — semua akun + saldo terkini
- **Mutasi Terakhir** — 10 mutasi terbaru
- **Profit 7 Hari** — daily omset, expense, profit 7 hari terakhir

**Quick Add Modals:**
- Tombol Pendapatan → modal catat pendapatan langsung
- Tombol Pengeluaran → modal catat pengeluaran langsung

---

## 2. Pendapatan (`/incomes`)
- CRUD dengan modal inline
- Filter: date range, kategori (datalist dari history), search (deskripsi/kategori)
- Pagination 20/halaman
- Export Excel (respect filter)

---

## 3. Pengeluaran (`/expenses`)
- CRUD dengan modal inline
- Filter: date range, kategori (datalist), search (deskripsi/kategori)
- Pilih akun (foreign key to accounts)
- Pagination 20/halaman
- Export Excel (respect filter)

---

## 4. Mutasi / Transfer (`/mutations`)
- CRUD dengan modal inline
- Filter: date range, search (deskripsi)
- Validasi: from_account ≠ to_account
- Pagination 20/halaman
- Export Excel (respect filter)

---

## 5. Piutang (`/receivables`)
- Tabs: **All | Unpaid | Paid**
- Filter: date range, search (nama/HP)
- Input: nama, no HP (opsional), nominal, modal, tanggal
- Auto **fee** = amount - modal
- Auto **due_date** = date + 3 hari
- Tombol **Bayar** — record payment (pilih akun, nominal, tanggal), auto-create income (senilai fee) saat lunas
- Tombol **WA** — link wa.me reminder (hanya kalo ada no HP), pesan berbeda untuk overdue vs reminder
- Status badge: Lunas (hijau) / Belum (biru)
- Pagination 20/halaman
- Export Excel (respect filter)

---

## 6. Kelola Akun (`/accounts`)
- CRUD dengan modal inline
- Tipe: cash / bank / ewallet / ppob / other
- Soft-deactivate (is_active = false) — bukan hard delete
- Unique name validation

---

## 7. Atur Modal Awal (`/opening-balances`)
- Filter periode (YYYY-MM)
- Bulk input: semua akun aktif dalam satu form
- `updateOrCreate` per (account_id, period)

---

## 8. Ringkasan Bulanan (`/summary`)
- Pilih: 3 / 6 / 12 bulan
- Tabel per bulan: omset, expense, profit (hijau/merah)
- Expandable row: detail pendapatan & pengeluaran per kategori
- Label bulan Indonesia (e.g. "Januari 2026")

---

## 9. Backup Database (`/backups`)
- **Download Backup** — `mysqldump` export full database (SQL)
- **Restore Database** — upload file .sql
- **Reset Semua Data** — truncate 6 tabel transaksi (incomes, expenses, mutations, receivable_payments, receivables, opening_balances) — accounts tetap aman
- Konfirmasi: harus ketik `RESET`

---

## Fitur UI/UX Global

### Layout
- Sidebar fixed 260px (gradient blue) — navigasi utama
- Topbar sticky — dark mode toggle + sidebar collapse button
- Page content: padding 1.5rem
- Sticky topbar, fixed sidebar, scrollable content

### Dark Mode
- Toggle via button di topbar, persisted ke localStorage
- CSS custom properties (`--bg-body`, `--bg-card`, `--text-primary`, dll)
- Override Bootstrap variables (`--bs-body-color`, `--bs-card-color`, `--bs-table-color`)
- Override inline styles via `[style*="..."]` selectors
- Stat card icon backgrounds, badge-status colors, form controls, modal, pagination

### Sidebar
- Desktop: collapse ke icon-only (60px) — persisted ke localStorage
- Mobile: hamburger toggle + overlay (transform X)
- Badge: jumlah piutang unpaid (via AppServiceProvider view composer)

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
- `pagination-modern` — styled pagination with active blue

### Lainnya
- Semua form via Bootstrap modal (no page reload)
- Delete dengan confirm() JavaScript
- Flash messages (success/error) via session
- Format Rp via helper `rp()`, tanggal Indonesia via `tgl()`

---

## Routes (35 routes)

### Dashboard
| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/` | DashboardController@index | dashboard |

### Pendapatan
| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/incomes` | IncomeController@index | incomes.index |
| POST | `/incomes` | IncomeController@store | incomes.store |
| PUT | `/incomes/{id}` | IncomeController@update | incomes.update |
| DELETE | `/incomes/{id}` | IncomeController@destroy | incomes.destroy |
| GET | `/incomes/export` | IncomeController@export | incomes.export |

### Pengeluaran
| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/expenses` | ExpenseController@index | expenses.index |
| POST | `/expenses` | ExpenseController@store | expenses.store |
| PUT | `/expenses/{id}` | ExpenseController@update | expenses.update |
| DELETE | `/expenses/{id}` | ExpenseController@destroy | expenses.destroy |
| GET | `/expenses/export` | ExpenseController@export | expenses.export |

### Mutasi
| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/mutations` | MutationController@index | mutations.index |
| POST | `/mutations` | MutationController@store | mutations.store |
| PUT | `/mutations/{id}` | MutationController@update | mutations.update |
| DELETE | `/mutations/{id}` | MutationController@destroy | mutations.destroy |
| GET | `/mutations/export` | MutationController@export | mutations.export |

### Piutang
| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/receivables` | ReceivableController@index | receivables.index |
| POST | `/receivables` | ReceivableController@store | receivables.store |
| PUT | `/receivables/{id}` | ReceivableController@update | receivables.update |
| DELETE | `/receivables/{id}` | ReceivableController@destroy | receivables.destroy |
| POST | `/receivables/pay` | ReceivableController@pay | receivables.pay |
| GET | `/receivables/{id}/whatsapp` | ReceivableController@whatsappLink | receivables.whatsapp |
| GET | `/receivables/export` | ReceivableController@export | receivables.export |

### Akun
| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/accounts` | AccountController@index | accounts.index |
| POST | `/accounts` | AccountController@store | accounts.store |
| PUT | `/accounts/{id}` | AccountController@update | accounts.update |
| DELETE | `/accounts/{id}` | AccountController@destroy | accounts.destroy |

### Modal Awal
| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/opening-balances` | OpeningBalanceController@index | opening-balances.index |
| POST | `/opening-balances` | OpeningBalanceController@store | opening-balances.store |
| PUT | `/opening-balances` | OpeningBalanceController@update | opening-balances.update |

### Ringkasan
| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/summary` | SummaryController@index | summary.index |

### Backup
| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/backups` | BackupController@index | backups.index |
| GET | `/backups/download` | BackupController@download | backups.download |
| POST | `/backups/restore` | BackupController@restore | backups.restore |
| POST | `/backups/reset` | BackupController@resetData | backups.reset |
