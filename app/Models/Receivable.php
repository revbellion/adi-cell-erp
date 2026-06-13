<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
            return '<span class="label label-success">Lunas</span>';
        }

        return '<span class="label label-danger">Belum</span>';
    }
}
