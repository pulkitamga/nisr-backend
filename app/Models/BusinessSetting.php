<?php

namespace App\Models;

use App\Traits\CacheManagerTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;


class BusinessSetting extends Model
{
    use CacheManagerTrait;

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

    public function translations()
    {
        return $this->morphMany(\App\Models\Translation::class, 'translationable');
    }
}
