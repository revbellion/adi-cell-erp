<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpnameSaldo extends Model
{
    protected $table = 'opname_saldo';

    protected $fillable = [
        'account_id',
        'opening_balance',
        'closing_balance',
        'difference',
        'opname_date',
    ];

    protected function casts(): array
    {
        return [
            'opname_date' => 'date',
            'opening_balance' => 'integer',
            'closing_balance' => 'integer',
            'difference' => 'integer',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
