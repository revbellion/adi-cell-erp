# Rencana Refactoring Cash Counter - Versi Sederhana

## Context

Fitur Cash Counter saat ini terlalu kompleks dengan sistem adjustment yang membingungkan. User membutuhkan fitur yang lebih sederhana untuk menghitung omzet harian berdasarkan kas fisik.

**Requirement Baru:**
- User input: saldo awal + saldo akhir (hasil hitung kas)
- Sistem hitung: selisih kas, total omzet, hasil rekonsiliasi
- Logika sederhana, akurat, mudah digunakan
- Tidak perlu tracking setiap transaksi detail
- Akumulasi seluruh pemasukan dan pengeluaran

## Masalah Implementasi Saat Ini

1. **Tidak ada konsep opening/closing balance** - hanya ada `total_amount` dan `target_amount` yang membingungkan
2. **Sistem adjustment terlalu kompleks** - max 2 adjustments, pattern matching, tidak ada FK langsung
3. **Tidak ada rekonsiliasi otomatis** - user harus manual create adjustments
4. **UI terlalu banyak fitur** - adjustment panel, target panel, dll yang membingungkan
5. **Tidak ada summary incomes/expenses** - user tidak bisa lihat breakdown pemasukan/pengeluaran

## Solusi yang Diusulkan

### 1. Database Changes

**Migration: `add_opening_balance_to_cash_counter_sessions`**
```php
Schema::table('cash_counter_sessions', function (Blueprint $table) {
    $table->integer('opening_balance')->default(0)->after('account_id');
    // opening_balance = saldo awal saat sesi dimulai
    // total_amount = saldo akhir (hasil hitung kas)
    // Selisih = total_amount - opening_balance
});
```

**Migration: `add_cash_counter_session_id_to_incomes_expenses`**
```php
// incomes table
Schema::table('incomes', function (Blueprint $table) {
    $table->foreignId('cash_counter_session_id')
        ->nullable()
        ->after('receivable_id')
        ->constrained('cash_counter_sessions')
        ->nullOnDelete();
});

// expenses table  
Schema::table('expenses', function (Blueprint $table) {
    $table->foreignId('cash_counter_session_id')
        ->nullable()
        ->after('receivable_id')
        ->constrained('cash_counter_sessions')
        ->nullOnDelete();
});
```

**Migration: `backfill_cash_counter_session_id`**
- Backfill data existing dari pattern matching ke FK langsung
- Cari Income/Expense dengan category='OMSET' dan description pattern 'Penyesuaian kas #{session_id}%'
- Update cash_counter_session_id

### 2. Model Updates

**CashCounterSession Model:**
```php
protected $fillable = [
    'user_id',
    'account_id',
    'title',
    'opening_balance',  // NEW
    'denominations',
    'total_amount',
];

// Relationships
public function incomes(): HasMany
{
    return $this->hasMany(Income::class);
}

public function expenses(): HasMany
{
    return $this->hasMany(Expense::class);
}

// Accessors
public function getCashDifferenceAttribute(): int
{
    return $this->total_amount - $this->opening_balance;
}

public function getTotalIncomeAttribute(): int
{
    return $this->incomes()->sum('amount');
}

public function getTotalExpenseAttribute(): int
{
    return $this->expenses()->sum('amount');
}

public function getExpectedClosingAttribute(): int
{
    return $this->opening_balance + $this->total_income - $this->total_expense;
}

public function getReconciliationStatusAttribute(): string
{
    $diff = $this->total_amount - $this->expected_closing;
    if ($diff == 0) return 'balanced';
    if ($diff > 0) return 'surplus';
    return 'deficit';
}
```

**Income & Expense Models:**
```php
public function cashCounterSession(): BelongsTo
{
    return $this->belongsTo(CashCounterSession::class);
}
```

### 3. Service Layer

**Buat `app/Services/CashCounterService.php`:**

```php
class CashCounterService
{
    // Hitung total dari denominations (server-side validation)
    public function calculateTotal(array $denominations): int
    
    // Buat session baru
    public function createSession(array $data): CashCounterSession
    {
        // Validasi total_amount sesuai denominations
        // Set opening_balance dari data atau dari session sebelumnya
    }
    
    // Update session
    public function updateSession(CashCounterSession $session, array $data): CashCounterSession
    
    // Hapus session + cascade adjustments
    public function deleteSession(CashCounterSession $session): void
    
    // Get summary incomes/expenses untuk session
    public function getSessionSummary(CashCounterSession $session): array
    {
        return [
            'total_income' => $session->incomes()->sum('amount'),
            'total_expense' => $session->expenses()->sum('amount'),
            'incomes' => $session->incomes()->get(),
            'expenses' => $session->expenses()->get(),
        ];
    }
    
    // Get opening balance dari session terakhir
    public function getLastClosingBalance(int $accountId): int
    {
        $lastSession = CashCounterSession::where('account_id', $accountId)
            ->latest()
            ->first();
        return $lastSession?->total_amount ?? 0;
    }
    
    // Authorize session ownership
    private function authorizeSession(CashCounterSession $session): void
}
```

### 4. Controller Refactoring

**CashCounterController - Simplified:**

```php
// GET /cash-counter
public function index()
{
    // Load accounts dengan balance
    // Load last session untuk pre-fill opening_balance
}

// POST /cash-counter/sessions
public function store(StoreCashCounterSessionRequest $request)
{
    // Create session dengan opening_balance
    // opening_balance = last closing balance atau manual input
}

// PUT /cash-counter/sessions/{session}
public function update(UpdateCashCounterSessionRequest $request, CashCounterSession $session)
{
    // Update session
}

// DELETE /cash-counter/sessions/{session}
public function destroy(CashCounterSession $session)
{
    // Delete session + cascade adjustments
}

// GET /cash-counter/sessions/{session}
public function show(CashCounterSession $session)
{
    // Return session + summary (incomes/expenses breakdown)
}

// GET /cash-counter/history
public function history()
{
    // Return paginated sessions dengan reconciliation status
}
```

**HAPUS endpoints:**
- `POST /cash-counter/sessions/{session}/adjust` - tidak perlu lagi
- `DELETE /cash-counter/sessions/{session}/adjust` - tidak perlu lagi

### 5. Form Requests

**StoreCashCounterSessionRequest:**
```php
'account_id' => 'required|exists:accounts,id',
'title' => 'required|string|max:255',
'opening_balance' => 'required|integer|min:0',
'denominations' => 'required|array',
'denominations.*' => 'integer|min:0',
'total_amount' => 'required|integer|min:0',
```

**UpdateCashCounterSessionRequest:**
```php
// Same as store
```

### 6. View Simplification

**HAPUS dari UI:**
- Adjustment panel (btn-adjust-income, btn-adjust-expense, btn-delete-adjustment)
- Target amount input
- Target result panel
- Adjustment count badge

**KEEP dari UI:**
- Denomination counting grid (8 denominations)
- Account selector
- Balance info (Saldo Sistem vs Uang Fisik vs Selisih)
- Distribution chart
- History list
- Save/Load/Delete session

**TAMBAH ke UI:**
- Opening balance input (auto-fill dari last closing balance)
- Reconciliation summary card:
  - Saldo Awal: Rp X
  - Total Pemasukan: Rp X
  - Total Pengeluaran: Rp X
  - Saldo Akhir Diharapkan: Rp X
  - Saldo Akhir Aktual: Rp X
  - Selisih: Rp X (Hijau jika 0, Merah jika != 0)
- Breakdown incomes/expenses (collapsible)

### 7. User Flow Baru

**Skenario 1: Mulai Hari Baru**
1. User buka Cash Counter
2. Sistem auto-fill opening_balance dari closing balance session terakhir
3. User bisa edit opening_balance jika perlu
4. User hitung kas fisik dengan denomination grid
5. Sistem hitung total_amount dari denominations
6. Sistem hitung selisih = total_amount - opening_balance
7. User save session

**Skenario 2: Rekonsiliasi Kas**
1. User buka session yang sudah ada
2. Sistem tampilkan:
   - Opening balance
   - Total incomes selama periode
   - Total expenses selama periode
   - Expected closing = opening + incomes - expenses
   - Actual closing (dari denomination count)
   - Difference = actual - expected
3. Jika difference != 0, user bisa lihat detail incomes/expenses untuk investigasi

**Skenario 3: Investigasi Selisih**
1. User klik "Lihat Detail" di reconciliation summary
2. Sistem tampilkan list incomes dan expenses selama periode
3. User bisa cek transaksi mana yang mungkin bermasalah

### 8. Migration Strategy

**Step 1: Backup database**
```bash
php artisan backup:run
```

**Step 2: Run migrations**
```bash
php artisan migrate
```

**Step 3: Backfill data**
- Migration `backfill_cash_counter_session_id` akan otomatis jalankan
- Update existing OMSET adjustments dengan cash_counter_session_id

**Step 4: Update existing sessions**
- Set opening_balance = 0 untuk semua existing sessions (karena tidak ada data historis)
- Atau buat script untuk calculate opening_balance dari session sebelumnya

**Step 5: Test**
- Test create new session
- Test load existing session
- Test reconciliation calculation
- Test history view

### 9. Benefits

**Performa:**
- Adjustment query dari full table scan (LIKE) menjadi indexed FK lookup
- 10-100x lebih cepat untuk query adjustments

**Data Integrity:**
- Proper FK relationships mencegah orphaned data
- Cascade delete otomatis hapus adjustments saat session dihapus

**Maintainability:**
- Service layer memisahkan business logic dari controller
- Form requests untuk validasi yang konsisten
- Code lebih terstruktur dan mudah di-debug

**User Experience:**
- UI lebih sederhana dan fokus
- Konsep opening/closing balance lebih jelas
- Reconciliation otomatis, tidak perlu manual adjustments
- Breakdown incomes/expenses untuk investigasi

**Scalability:**
- Bisa handle lebih banyak data dan user
- Mudah tambah fitur baru (reporting, export, dll)

### 10. Testing Checklist

- [ ] Create new session dengan opening_balance
- [ ] Update existing session
- [ ] Delete session + verify adjustments cascade
- [ ] Load session dari history
- [ ] Verify reconciliation calculation
- [ ] Verify denomination total calculation
- [ ] Verify opening_balance auto-fill dari last session
- [ ] Verify incomes/expenses summary
- [ ] Test dengan data existing (backfill migration)
- [ ] Test edge cases (opening_balance = 0, total_amount = 0, dll)

### 11. Future Enhancements (Opsional)

- Export reconciliation report ke Excel/PDF
- Chart tren reconciliation (balanced/surplus/deficit per hari)
- Notification jika selisih > threshold
- Multi-user support dengan approval workflow
- Integration dengan POS untuk auto-capture incomes/expenses

## Implementation Order

1. **Database migrations** (3 migrations)
2. **Model updates** (CashCounterSession, Income, Expense)
3. **Service layer** (CashCounterService)
4. **Form requests** (Store, Update)
5. **Controller refactoring** (hapus adjustment endpoints)
6. **View updates** (hapus adjustment UI, tambah reconciliation summary)
7. **Testing** (manual + automated jika ada)
8. **Documentation** (update user guide jika ada)

## Estimated Effort

- **Database + Models**: 1-2 jam
- **Service + Controller**: 2-3 jam
- **View updates**: 2-3 jam
- **Testing**: 1-2 jam
- **Total**: 6-10 jam

## Risks & Mitigation

**Risk 1: Data migration gagal**
- Mitigation: Backup database sebelum migration, test di staging dulu

**Risk 2: Breaking changes untuk existing users**
- Mitigation: Keep backward compatibility, opening_balance default = 0

**Risk 3: Performance issue dengan banyak sessions**
- Mitigation: Add indexes, pagination untuk history

**Risk 4: User bingung dengan konsep baru**
- Mitigation: Add tooltip/help text di UI, buat tutorial singkat
