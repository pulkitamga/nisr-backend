<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Builder;

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
    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function ($query) {
                if (strpos(url()->current(), '/api')) {
                    return $query->where('locale', app()->getLocale())
                        ->where('key', 'title');
                } else {
                    return $query->where('locale', getDefaultLanguage())
                        ->where('key', 'title');
                }
            }]);
        });
    }
    public function getTitleAttribute($title): string|null
    {
        if (
            strpos(url()->current(), '/admin') ||
            strpos(url()->current(), '/vendor') ||
            strpos(url()->current(), '/seller')
        ) {
            return $title;
        }

        $currentLocale = app()->getLocale();
        $translation = $this->translations
            ->where('locale', $currentLocale)
            ->where('key', 'title')
            ->first();

        return $translation->value ?? $title;
    }
}
