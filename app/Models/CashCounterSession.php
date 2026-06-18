<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashCounterSession extends Model
{
    protected $fillable = [
        'user_id',
        'account_id',
        'title',
        'denominations',
        'target_amount',
        'total_amount',
    ];

    protected function casts(): array
    {
        return [
            'denominations' => 'array',
            'target_amount' => 'integer',
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
}
