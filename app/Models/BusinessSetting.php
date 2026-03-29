<?php

namespace App\Models;

use App\Traits\CacheManagerTrait;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;


class BusinessSetting extends Model
{
    use CacheManagerTrait;
    use HasTranslations;

    protected $fillable = ['type', 'value', 'is_active', 'created_at', 'updated_at'];

    protected $casts = [
        'id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saved(function ($model) {
            cacheRemoveByType(type: 'business_settings');
        });

        static::deleted(function ($model) {
            cacheRemoveByType(type: 'business_settings');
        });
    }

}
