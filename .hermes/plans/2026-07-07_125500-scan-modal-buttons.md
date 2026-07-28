## 🔍 Scan Modal Action Buttons

### Kartu Temuan — Modal Footer

| # | Tombol | File | Baris | Status |
|---|--------|------|-------|--------|
| 1 | **Batal** | Semua modal (20+ modal) | — | ✅ **Konsisten — tidak ada icon** (by design) |
| 2 | **Simpan** | `accounts/index.blade.php` | 121, 161 | ⚠️ Tanpa `fa-save` |
| 3 | **Simpan** | `bills/index.blade.php` | 144, 202 | ⚠️ Tanpa `fa-save` |
| 4 | **Simpan** | `cash-counter/index.blade.php` | 122 | ⚠️ Tanpa `fa-save` |
| 5 | **Simpan** | `customers/index.blade.php` | 153, 193 | ⚠️ Tanpa `fa-save` |
| 6 | **Simpan** | `dashboard/index.blade.php` | 401 | ⚠️ Tanpa `fa-save` (btn-success) |
| 7 | **Simpan** | `expenses/index.blade.php` | 171, 223 | ⚠️ Tanpa `fa-save` |
| 8 | **Simpan** | `incomes/index.blade.php` | 175, 227 | ⚠️ Tanpa `fa-save` |
| 9 | **Simpan** | `mutations/index.blade.php` | 188, 244 | ⚠️ Tanpa `fa-save` |
| 10 | **Simpan** | `print-orders/index.blade.php` | 196, 252 | ⚠️ Tanpa `fa-save` |
| 11 | **Simpan** | `product-categories/index.blade.php` | 98, 121 | ⚠️ Tanpa `fa-save` |
| 12 | **Simpan** | `products/index.blade.php` | 192, 244 | ⚠️ Tanpa `fa-save` |
| 13 | **Simpan** | `receivables/index.blade.php` | 229, 278 | ⚠️ Tanpa `fa-save` |
| 14 | **Simpan** | `repair-services/index.blade.php` | 244, 329 | ⚠️ Tanpa `fa-save` |
| 15 | **Bayar** | `receivables/index.blade.php` | 321 | ❌ **Tidak konsisten** — Bayar lainnya pakai `fa-check` |

### Pembanding — yang sudah pakai icon:

| Tombol | File | Icon |
|--------|------|------|
| Simpan | `pending-transactions:255` | ✅ `fa-save me-1` |
| Simpan | `opening-balances:55` | ✅ `fa-save me-1` |
| Simpan | `opname-saldo:25` | ✅ `fa-save me-1` |
| Simpan | `profile:80` | ✅ `fa-save me-1` |
| Simpan | `dashboard:539` | ✅ `fa-save me-1` |
| Simpan | `stock/opname:77` | ✅ `fa-save me-1` |
| Bayar | `bills:245` | ✅ `fa-check` |
| Bayar | `dashboard:588` | ✅ `fa-check me-1` |
| Bayar | `receivables:129` | ✅ `fa-check me-1` |

### Kesimpulan

- **Batal** → ✅ BY DESIGN, semua seragam tanpa icon
- **Simpan** → ⚠️ Sebagian besar modal CRUD tidak pakai icon. Tapi beberapa sudah pakai `fa-save`. Perlu diseragamkan.
- **Bayar** (receivables:321) → ❌ Satu-satunya tombol Bayar tanpa icon, sementara yang lain sudah pakai `fa-check`
