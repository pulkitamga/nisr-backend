<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Policy extends Model
{
    use HasFactory;
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

    public function translations(): MorphMany
    {
        return $this->morphMany('App\Models\Translation', 'translationable');
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    public function getValueAttribute(): ?string
    {
        return $this->content_html ?? $this->content_text;
    }

    public function setValueAttribute(?string $value): void
    {
        $this->attributes['content_html'] = $value;
        $this->attributes['content_text'] = $value !== null ? strip_tags($value) : null;
    }
}
