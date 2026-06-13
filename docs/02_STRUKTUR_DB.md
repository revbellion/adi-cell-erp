# STRUKTUR DATABASE

Database: `cash_tracker` (MySQL 8, engine InnoDB, charset utf8mb4)

## Laravel Default Tables
- `users` — tidak dipakai (single-user, auth disabled)
- `cache` — cache Laravel
- `cache_locks` — cache lock
- `sessions` — session (file driver, tabel ada untuk cadangan)
- `jobs` — queue jobs (tidak dipakai)

## Tabel: `accounts`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | auto increment |
| name | varchar(100) | unique, SHOPEEPAY, DANA, BCA, CASH, dll |
| type | varchar(50) | ewallet / bank / cash / ppob / other |
| is_active | boolean | default true (soft-deactivate) |
| created_at | timestamp | |
| updated_at | timestamp | |

**11 default accounts** dari seeder + manual (EDC Pending).

## Tabel: `opening_balances`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| account_id | bigint FK → accounts | |
| period | varchar(7) | YYYY-MM |
| amount | integer | saldo modal awal (dalam rupiah) |
| created_at | timestamp | |
| updated_at | timestamp | |
| **UNIQUE** | | (account_id, period) |

## Tabel: `incomes`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| date | date | |
| amount | integer | nominal pendapatan |
| description | varchar(255) nullable | |
| category | varchar(100) nullable | dengan datalist dari history |
| created_at | timestamp | |
| updated_at | timestamp | |

## Tabel: `expenses`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| date | date | |
| account_id | bigint FK → accounts | akun yg digunakan |
| category | varchar(100) | dengan datalist dari history |
| amount | integer | |
| description | varchar(255) nullable | |
| created_at | timestamp | |
| updated_at | timestamp | |

## Tabel: `mutations`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| date | date | |
| from_account_id | bigint FK → accounts | akun asal |
| to_account_id | bigint FK → accounts | akun tujuan |
| amount | integer | nominal |
| description | varchar(255) nullable | |
| created_at | timestamp | |
| updated_at | timestamp | |

## Tabel: `receivables`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| name | varchar(150) | nama pelanggan |
| phone | varchar(20) nullable | no HP utk WA |
| amount | integer | nominal total piutang |
| fee | integer | fee = amount - modal (auto dari service) |
| date | date | |
| due_date | date | auto +3 hari dari date (via service) |
| status | enum('unpaid','paid') | default unpaid, auto-update saat lunas |
| created_at | timestamp | |
| updated_at | timestamp | |

### Accessors (Receivable Model)
- `remaining` — amount - sum(payments)
- `principal` — amount - fee
- `status_badge` — HTML `<span>` badge

### Scopes
- `unpaid()` — status = unpaid
- `overdue()` — unpaid + due_date < today

## Tabel: `receivable_payments`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| receivable_id | bigint FK → receivables | cascade on delete |
| account_id | bigint FK → accounts | akun yg menerima pembayaran |
| amount | integer | |
| date | date | |
| created_at | timestamp | |
| updated_at | timestamp | |
