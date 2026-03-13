<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductStock extends Model
{
    protected $table = 'product_stocks';

    protected $fillable = [
        'product_id',
        'variant',
        'sku',
        'price',
        'qty',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'price'      => 'float',
        'qty'        => 'integer',
    ];

    /* ================= RELATIONS ================= */

    // Each stock belongs to one product
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(ProductStockTransaction::class, 'product_stock_id');
    }


    /**
     * Get current stock from transactions (REAL stock)
     */
    public function getCurrentStockAttribute(): int
    {
        if (!$this->relationLoaded('transactions')) {
            $this->load('transactions');
        }

        $transactionStock = $this->transactions->sum(function ($t) {
            return $t->type === 'IN'
                ? $t->quantity
                : -$t->quantity;
        });

        return (int)($transactionStock ?: $this->qty);
    }
}
