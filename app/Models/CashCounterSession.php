<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashCounterSession extends Model
{
    protected $fillable = [
        'user_id',
        'account_id',
        'opening_balance',
        'title',
        'denominations',
        'total_amount',
    ];

    protected function casts(): array
    {
        return [
            'denominations' => 'array',
            'opening_balance' => 'integer',
            'total_amount' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

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

    public function getReconciliationDiffAttribute(): int
    {
        return $this->total_amount - $this->expected_closing;
    }

    public function getReconciliationStatusAttribute(): string
    {
        $diff = $this->reconciliation_diff;
        if ($diff === 0) return 'balanced';
        return $diff > 0 ? 'surplus' : 'deficit';
    }

    public function getReconciliationBadgeAttribute(): string
    {
        return match ($this->reconciliation_status) {
            'balanced' => '<span class="badge bg-success">Seimbang</span>',
            'surplus'  => '<span class="badge bg-warning text-dark">Kelebihan</span>',
            'deficit'  => '<span class="badge bg-danger">Kekurangan</span>',
        };
    }
}
