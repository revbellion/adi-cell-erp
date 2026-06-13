<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecurringBill extends Model
{
    protected $fillable = [
        'name',
        'category',
        'account_id',
        'amount',
        'due_day',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'amount' => 'decimal:2',
        ];
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function payments()
    {
        return $this->hasMany(BillPayment::class);
    }

    public function getPaymentForPeriod(string $period): ?BillPayment
    {
        return $this->payments()->where('period', $period)->first();
    }

    public function getDueDayTextAttribute(): string
    {
        return 'Tgl ' . $this->due_day;
    }
}
