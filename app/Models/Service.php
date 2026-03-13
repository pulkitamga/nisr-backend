<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;


class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'product_id',
        'title',
        'base_price_inshop',
        'base_price_mobile',
        'parts_cost',
        'included_km_mobile',
        'travel_fee_per_km',
        'labor_hours',
        'parts_included',
        'call_center_flag',
    ];

    protected $casts = [
        'parts_included' => 'array',
        'call_center_flag' => 'boolean',
        'base_price_inshop' => 'float',
        'base_price_mobile' => 'float',
        'parts_cost' => 'float',
        'labor_hours' => 'float',
        'travel_fee_per_km' => 'float',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

     public function translations(): MorphMany
    {
        return $this->morphMany('App\Models\Translation', 'translationable');
    }
}
