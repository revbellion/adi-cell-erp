<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Expense extends Model
{
    protected $fillable = [
        'date',
        'account_id',
        'category',
        'amount',
        'description',
        'stock_transaction_id',
        'receivable_id',
        'cash_counter_session_id',
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

    public function stockTransaction(): BelongsTo
    {
        return $this->belongsTo(StockTransaction::class);
    }

    public function receivable(): BelongsTo
    {
        return $this->belongsTo(Receivable::class);
    }

    public function billPayment(): HasOne
    {
        return $this->hasOne(BillPayment::class);
    }

    public function cashCounterSession(): BelongsTo
    {
        return $this->belongsTo(CashCounterSession::class);
    }
}
