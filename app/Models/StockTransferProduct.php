<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransferProduct extends Model
{
    use HasFactory;

    protected $table = 'stock_transfer_products';

    protected $fillable = [
        'stock_transfers_id',
        'product_id',
        'category_id',
        'attribute_id',         // NEW
        'variation_type',       // NEW (e.g., "50", "Red")
        'variation_key',        // NEW (e.g., "power:50")
        'variation_data',       // NEW (JSON)
        'attributes',       // NEW (JSON)
        'quantity',
        'serial_csv_path',
        'status'
    ];

    protected $casts = [
        'variation_data' => 'array',
    ];

    // Relationships
    public function stockTransfer()
    {
        return $this->belongsTo(StockTransfers::class, 'stock_transfers_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class, 'attribute_id');
    }

    // Helper: Get readable variation label
    public function getVariationLabelAttribute()
    {
        if ($this->variation_key) {
            return str_replace(':', ' : ', $this->variation_key);
        }
        return '—';
    }

    // Scope: Find by variation key
    public function scopeByVariation($query, $productId, $variationKey)
    {
        return $query->where('product_id', $productId)
                     ->where('variation_key', $variationKey);
    }
}