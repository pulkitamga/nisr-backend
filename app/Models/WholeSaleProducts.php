<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class WholeSaleProducts extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = "wholesale_products";

    protected $fillable = [
        'category_id',
        'sub_category_id',
        'product_id',
        'variation_type',     // NEW: Size, Color, Model etc.
        'variation_key',      // NEW: color:Red|size:XL
        'status',
    ];

    public $timestamps = false;

    public function price_list()
    {
        return $this->hasMany(WholesaleProductPriceRange::class, 'wholesale_id');
    }


       public function price_list_for_user()
    {
        $userTier = auth()->guard('customer')->user()->tier;
        return $this->hasMany(WholesaleProductPriceRange::class, 'wholesale_id', 'id')
            ->where('tier', $userTier);
    }
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id')->withTrashed();
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subcategory()
    {
        return $this->belongsTo(Category::class, 'sub_category_id');
    }

    // Helper: Readable variation
    public function getVariationLabelAttribute()
    {
        if ($this->variation_type) {
            $key = $this->variation_key 
                ? str_replace([':', '|'], [' : ', ' • '], $this->variation_key)
                : '';
            return $this->variation_type . ($key ? " → ({$key})" : '');
        }
        return 'Default';
    }

    protected static function booted()
    {
        static::deleting(function ($product) {
            $product->price_list()->delete();
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('wholesale_products')
            ->logOnly(['category_id', 'sub_category_id', 'product_id', 'variation_type', 'variation_key', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}