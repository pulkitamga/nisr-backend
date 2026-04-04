<?php

namespace App\Traits;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasTranslations
{
    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function getTranslatedField(string $key, ?string $locale = null, $fallback = null): ?string
    {
        $locale = resolveAppLocale($locale ?? getActiveTranslationLocale());

        if (!$this->relationLoaded('translations')) {
            $this->load('translations');
        }

        $translatedValue = $this->translations->first(
            fn(Translation $t) => $t->locale === $locale && $t->key === $key
        )?->value;

        if ($translatedValue !== null && $translatedValue !== '') {
            return $translatedValue;
        }

        $translatedValue = $this->translations()
            ->where('locale', $locale)
            ->where('key', $key)
            ->value('value');

        if ($translatedValue !== null && $translatedValue !== '') {
            return $translatedValue;
        }

        return $fallback ?? $this->getRawOriginal($key);
    }

    public function getTranslation(string $key, ?string $locale = null): ?string
    {
        return $this->getTranslatedField($key, $locale);
    }
}
