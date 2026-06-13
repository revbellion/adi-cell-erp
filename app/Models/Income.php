<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Income extends Model
{
    protected $fillable = [
        'account_id',
        'date',
        'amount',
        'description',
        'category',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'amount' => 'integer',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
