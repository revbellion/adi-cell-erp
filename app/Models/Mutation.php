<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Mutation extends Model
{
    protected $fillable = [
        'date',
        'from_account_id',
        'to_account_id',
        'amount',
        'description',
        'source',
        'receivable_id',
        'admin_fee',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'amount' => 'integer',
        ];
    }

    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    public function adminFeeExpense(): HasOne
    {
        return $this->hasOne(Expense::class, 'mutation_id');
    }
}
