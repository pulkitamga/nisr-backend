<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Builder;

class Service extends Model
{
    use HasFactory, HasTranslations;

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

    public function getTitleAttribute($value): ?string
    {
        return $this->getTranslatedField('title', fallback: $value);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with('translations'); // load all
        });
    }
}
