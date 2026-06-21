<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendingTransaction extends Model
{
    protected $fillable = [
        'type',
        'bank_type',
        'description',
        'amount',
        'mdr_rate',
        'mdr_amount',
        'net_amount',
        'status',
        'pending_date',
        'completed_date',
        'completed_type',
        'completed_account_id',
    ];

    protected function casts(): array
    {
        return [
            'pending_date' => 'datetime',
            'completed_date' => 'datetime',
            'amount' => 'integer',
            'mdr_rate' => 'decimal:2',
            'mdr_amount' => 'integer',
            'net_amount' => 'integer',
        ];
    }

    public function completedAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'completed_account_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'edc' => 'EDC',
            'qris' => 'QRIS',
            'transfer' => 'Transfer',
            'other' => 'Lainnya',
            default => $this->type,
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        if ($this->status === 'completed') {
            return '<span class="badge bg-success">Selesai</span>';
        }
        return '<span class="badge bg-warning text-dark">Pending</span>';
    }
}
