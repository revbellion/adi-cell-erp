<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Receivable extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'amount',
        'date',
        'due_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'due_date' => 'datetime',
            'amount' => 'integer',
        ];
    }

    public function receivablePayments(): HasMany
    {
        return $this->hasMany(ReceivablePayment::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function expense(): HasOne
    {
        return $this->hasOne(Expense::class);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'unpaid')
            ->where('due_date', '<', now()->startOfDay());
    }

    public function getRemainingAttribute(): int
    {
        return $this->amount - $this->receivablePayments->sum('amount');
    }

    public function getStatusBadgeAttribute(): string
    {
        if ($this->status === 'paid') {
            return '<span class="badge bg-success">Lunas</span>';
        }

        if ($this->due_date && $this->due_date->startOfDay()->lt(now()->startOfDay())) {
            return '<span class="badge bg-danger">Telat</span>';
        }

        return '<span class="badge bg-warning text-dark">Belum</span>';
    }
}
