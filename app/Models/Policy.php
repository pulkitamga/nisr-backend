<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Policy extends Model
{
    use HasFactory, HasTranslations;
    protected $table = 'policies';
    protected $fillable = [
        'version',
        'locale',
        'effective_date',
        'content_html',
        'content_text',
        'slug',
        'created_by',
        'published_at',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'published_at' => 'datetime',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at');
    }

    public static function normalizeLocale(?string $locale): string
    {
        $fallbackLocale = app()->getLocale();
        $normalizedLocale = strtolower(trim((string) $locale));

        if (
            $normalizedLocale === ''
            || !preg_match('/^[a-z]{2,3}(?:[_-][a-z]{2,3})?$/', $normalizedLocale)
        ) {
            $normalizedLocale = $fallbackLocale;
        }

        return function_exists('resolveAppLocale')
            ? resolveAppLocale($normalizedLocale)
            : $normalizedLocale;
    }

    public function getTranslatedFieldValue(string $key, ?string $locale = null, ?string $fallback = null): ?string
    {
        if (!$this->relationLoaded('translations')) {
            $this->load('translations');
        }

        $resolvedLocale = self::normalizeLocale($locale);

        return $this->translations
            ->first(fn(Translation $translation) => $translation->locale === $resolvedLocale && $translation->key === $key)
            ?->value ?? $fallback;
    }

    public function getLocalizedContentHtml(?string $locale = null): ?string
    {
        // Use $this->value (the actual DB column) as the fallback
        return $this->getTranslatedFieldValue('value', $locale, $this->value);
    }

    public function getLocalizedContentText(?string $locale = null): ?string
    {
        $localizedContent = $this->getLocalizedContentHtml($locale);

        if ($localizedContent === null) {
            return $this->content_text;
        }

        return trim(strip_tags($localizedContent));
    }

    public function getValueAttribute(): ?string
    {
        // Simply return the raw attribute from the DB
        return $this->attributes['value'] ?? null;
    }

    public function setValueAttribute(?string $value): void
    {
        $this->attributes['value'] = $value;
    }
}
