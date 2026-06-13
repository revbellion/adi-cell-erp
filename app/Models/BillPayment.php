<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillPayment extends Model
{
    protected $fillable = [
        'recurring_bill_id',
        'period',
        'amount',
        'paid_at',
        'expense_id',
    ];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'amount' => 'decimal:2',
        ];
    }

    public function recurringBill()
    {
        return $this->belongsTo(RecurringBill::class);
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function getIsPaidAttribute(): bool
    {
        return !is_null($this->paid_at);
    }
}
