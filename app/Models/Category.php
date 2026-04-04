<?php

namespace App\Models;

use App\Traits\CacheManagerTrait;
use App\Traits\HasTranslations;
use App\Traits\StorageTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $icon
 * @property int $parent_id
 * @property int $position
 * @property int $home_status
 * @property int $priority
 */
class Category extends Model
{
    use StorageTrait, CacheManagerTrait;
    use HasTranslations;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'icon_storage_type',
        'parent_id',
        'position',
        'home_status',
        'priority',
    ];

    protected $casts = [
        'name' => 'string',
        'slug' => 'string',
        'icon' => 'string',
        'icon_storage_type' => 'string',
        'parent_id' => 'integer',
        'position' => 'integer',
        'home_status' => 'integer',
        'priority' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id')->orderBy('priority', 'desc');
    }

    public function childes(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('priority', 'asc');
    }

    public function product(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }

    // Old Relation: sub_category_product
    public function subCategoryProduct(): HasMany
    {
        return $this->hasMany(Product::class, 'sub_category_id', 'id');
    }

    // Old Relation: sub_sub_category_product
    public function subSubCategoryProduct(): HasMany
    {
        return $this->hasMany(Product::class, 'sub_sub_category_id', 'id');
    }
    public function wholesalePriceTiers()
    {
        return $this->hasMany(WholesalePriceTier::class, 'subcategory_id');
    }
    public function extraCharges()
    {
        return $this->hasMany(ManageExtraCharge::class, 'category_id');
    }

    public function getNameAttribute($name): string|null
    {
        return $this->getTranslatedField('name', fallback: $name);
    }

    public function scopePriority($query): mixed
    {
        return $query->orderBy('priority', 'asc');
    }

    public function getIconFullUrlAttribute(): array
    {
        $value = $this->icon;
        return $this->storageLink('category', $value, $this->icon_storage_type ?? 'public');
    }
    protected $appends = ['icon_full_url', 'defaultName'];

    public function getDefaultNameAttribute(): ?string
    {
        return $this->getTranslatedField('name');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saved(function ($model) {
            cacheRemoveByType(type: 'categories');
        });

        static::deleted(function ($model) {
            cacheRemoveByType(type: 'categories');
        });

        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function ($query) {
                if (strpos(url()->current(), '/api')) {
                    return $query->where('locale', App::getLocale());
                } else {
                    return $query->where('locale', getDefaultLanguage());
                }
            }]);
        });
    }
}
