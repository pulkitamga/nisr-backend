<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManageBranchProductStock extends Model
{
    use HasFactory;

    protected $table = 'manage_branch_product_stock';

    protected $fillable = [
        'branch_id',
        'product_id',
        'attribute_id',         // NEW
        'variation_type',       // NEW
        'variation_key',        // NEW (e.g., "color:Red", "power:50")
        'attributes',           // OLD (keep for backward compatibility)
        'current_stock',
    ];

    protected $casts = [
        'current_stock' => 'integer',
    ];

    // Relationships
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class, 'attribute_id');
    }

    // Helper: Get readable variation
    public function getVariationLabelAttribute()
    {
        return $this->variation_key 
            ? str_replace(':', ' : ', $this->variation_key)
            : ($this->attributes ?? 'Default');
    }

    // Scope: Find stock by variation key
    public function scopeForVariation($query, $branchId, $productId, $variationKey)
    {
        return $query->where('branch_id', $branchId)
                     ->where('product_id', $productId)
                     ->where('variation_key', $variationKey);
    }

    // Scope: Find default stock (no variation)
    public function scopeDefaultStock($query, $branchId, $productId)
    {
        return $query->where('branch_id', $branchId)
                     ->where('product_id', $productId)
                     ->whereNull('variation_key');
    }
}