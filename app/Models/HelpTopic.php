<?php

namespace App\Models;

use App\Utils\Helpers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\App;


class HelpTopic extends Model
{
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

    public function translations(): MorphMany
    {
        return $this->morphMany('App\Models\Translation', 'translationable');
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

                return $query->where('locale', Helpers::default_lang());
            }]);
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

        return $this->translations
            ->first(function ($translation) use ($key) {
                return $translation->key === $key;
            })?->value ?? $fallback;
    }
}
