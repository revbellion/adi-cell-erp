<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'name', 'purchase_price', 'selling_price',
        'stock', 'stock_min', 'unit', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(StockTransaction::class, 'product_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getStockValueAttribute(): int
    {
        return $this->stock * $this->purchase_price;
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock_min > 0 && $this->stock <= $this->stock_min;
    }
}
