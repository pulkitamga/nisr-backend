<?php

namespace App\Models;

use App\Models\Scopes\RememberScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Watson\Rememberable\Rememberable;

/**
 * Class Currency
 *
 * @property int $id Primary
 * @property string $name
 * @property string $symbol
 * @property string $code
 * @property string $exchange_rate
 * @property bool $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 */
class Currency extends Model
{
//    use Rememberable;

    protected $casts = [
        'id' => 'integer',
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $fillable = [
        'name',
        'symbol',
        'code',
        'exchange_rate',
        'status',
    ];

    protected $table = 'currencies';

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function getNameAttribute($name): string|null
    {
        return $this->getTranslationValue(key: 'name') ?? $name;
    }

    public function getSymbolAttribute($symbol): string|null
    {
        return $this->getTranslationValue(key: 'symbol') ?? $symbol;
    }

    private function getTranslationValue(string $key): ?string
    {
        $currentLanguage = getDefaultLanguage();
        if ($this->relationLoaded('translations')) {
            return $this->translations
                ->first(fn($translation) => $translation->locale === $currentLanguage && $translation->key === $key)
                ?->value;
        }

        return $this->translations()
            ->where('locale', $currentLanguage)
            ->where('key', $key)
            ->value('value');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saved(function ($model) {
            cacheRemoveByType(type: 'currencies');
        });

        static::deleted(function ($model) {
            cacheRemoveByType(type: 'currencies');
        });
    }
}
