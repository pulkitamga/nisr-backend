<?php

namespace App\Models;

use App\Traits\StorageTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

/**
 * App\Models\BusinessPage
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property int $status
 * @property int $default_status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class BusinessPage extends Model
{
    use StorageTrait;
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'status',
        'default_status',
        'created_at',
        'updated_at'
    ];

    protected static function booted()
    {
        static::saved(function () {
            Cache::forget(CACHE_FOR_BUSINESS_PAGES_LIST);
        });

        static::deleted(function () {
            Cache::forget(CACHE_FOR_BUSINESS_PAGES_LIST);
        });
    }
    protected $casts = [
        'id' => 'integer',
        'title' => 'string',
        'slug' => 'string',
        'description' => 'string',
        'status' => 'integer',
        'default_status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['banner_full_url'];

    public function banner(): MorphOne
    {
        return $this->morphOne(Attachment::class, 'attachable')->where(['file_type' => 'banner']);
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function getTitleAttribute($title): string|null
    {
        return $this->getTranslatedFieldValue('title', $title);
    }

    public function getDescriptionAttribute($description): string|null
    {
        return $this->getTranslatedFieldValue('description', $description);
    }

    public function getBannerFullUrlAttribute(): string|null|array
    {
        $banner = $this->banner;
        return $this->storageLink('business-pages', $banner?->file_name, $banner?->storage_disk ?? 'public');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saved(function ($model) {
            cacheRemoveByType(type: 'business_pages');
        });

        static::deleted(function ($model) {
            cacheRemoveByType(type: 'business_pages');
        });
    }

    private function getTranslatedFieldValue(string $key, string|null $fallback): string|null
    {
        if (strpos(url()->current(), '/admin') || strpos(url()->current(), '/vendor') || strpos(url()->current(), '/seller')) {
            return $fallback;
        }

        if (!$this->relationLoaded('translations')) {
            $this->load('translations');
        }

        $locale = strpos(url()->current(), '/api') ? App::getLocale() : getDefaultLanguage();
        $locale = resolveAppLocale($locale);

        return $this->translations
            ->first(function ($translation) use ($locale, $key) {
                return $translation->locale === $locale && $translation->key === $key;
            })?->value ?? $fallback;
    }
}
