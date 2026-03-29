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
        $locale = $locale ?? getDefaultLanguage();

        if (!$this->relationLoaded('translations')) {
            $this->load('translations');
        }

        return $this->translations->first(
            fn(Translation $t) => $t->locale === $locale && $t->key === $key
        )?->value ?? $fallback ?? $this->getRawOriginal($key);
    }

    public function getTranslation(string $key, ?string $locale = null): ?string
    {
        return $this->getTranslatedField($key, $locale);
    }
}
