<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockRequestProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_requests_id',
        'product_id',
        'category_id',
        'variation_type',     // NEW
        'variation_key',      // NEW
        'attributes',         // NEW
        'quantity',
        'status',
        'received_from_branch',
        'received_time'
    ];

    public function stockRequest()
    {
        return $this->belongsTo(StockRequests::class, 'stock_requests_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function received_from()
    {
        return $this->belongsTo(Branch::class, 'received_from_branch');
    }

    // Helper: Readable variation label
    public function getVariationLabelAttribute()
    {
        if ($this->variation_type) {
            $key = $this->variation_key ? str_replace(':', ' : ', $this->variation_key) : '';
            return $this->variation_type . ($key ? " → ({$key})" : '');
        }
        return 'Default';
    }
}