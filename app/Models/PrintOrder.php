<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintOrder extends Model
{
    protected $fillable = [
        'date',
        'service_type',
        'quantity',
        'price_per_unit',
        'total',
        'description',
        'print_calc_ref',
        'account_id',
        'income_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'quantity' => 'integer',
            'price_per_unit' => 'integer',
            'total' => 'integer',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function income(): BelongsTo
    {
        return $this->belongsTo(Income::class);
    }
}
