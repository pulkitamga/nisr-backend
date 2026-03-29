<?php

namespace App\Models;

use App\Traits\HasTranslations;
use App\Utils\Helpers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;


class HelpTopic extends Model
{
    use HasTranslations;
    protected $table = 'help_topics';
    protected $casts = [
        'type' => 'string',
        'ranking'    => 'integer',
        'status'     => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    protected $fillable = [
        'type',
        'question',
        'answer',
        'status',
        'ranking',
    ];

    public function scopeStatus($query)
    {
        return $query->where('status', 1);
    }

    public function getQuestionAttribute($question): string|null
    {
        return $this->getTranslatedFieldValue('question', $question);
    }

    public function getAnswerAttribute($answer): string|null
    {
        return $this->getTranslatedFieldValue('answer', $answer);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saved(function ($model) {
            cacheRemoveByType(type: 'help_topics');
        });

        static::deleted(function ($model) {
            cacheRemoveByType(type: 'help_topics');
        });

        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function ($query) {
                if (strpos(url()->current(), '/api')) {
                    return $query->where('locale', App::getLocale());
                }

                return $query->where('locale', getDefaultLanguage());
            }]);
        });
    }

    // private function getTranslatedFieldValue(string $key, string|null $fallback): string|null
    // {
    //     if (strpos(url()->current(), '/admin') || strpos(url()->current(), '/vendor') || strpos(url()->current(), '/seller')) {
    //         return $fallback;
    //     }

    //     if (!$this->relationLoaded('translations')) {
    //         $this->load('translations');
    //     }

    //     return $this->translations
    //         ->first(function ($translation) use ($key) {
    //             return $translation->key === $key;
    //         })?->value ?? $fallback;
    // }
    private function getTranslatedFieldValue(string $key, string|null $fallback): string|null
    {
        // 1. Skip translation for admin/seller panels
        if (strpos(url()->current(), '/admin') || strpos(url()->current(), '/vendor') || strpos(url()->current(), '/seller')) {
            return $fallback;
        }
 
        // 2. Load translations relationship if not already loaded
        if (!$this->relationLoaded('translations')) {
            $this->load('translations');
        }
 
        // 3. Get the CURRENT locale (e.g., 'en' from your Postman header)
        $locale = app()->getLocale();
 
        // 4. Look for a translation that matches BOTH the key AND the current locale
        $translation = $this->translations
            ->where('key', $key)
            ->where('locale', $locale) // <--- THIS IS THE FIX
            ->first();
 
        // 5. If found, return it. If not (like when in English), return the English $fallback
        return $translation ? $translation->value : $fallback;
    }
}
