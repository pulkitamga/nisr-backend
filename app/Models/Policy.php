<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Policy extends Model
{
    use HasFactory;
    protected $table = 'warranty_policies';
    protected $fillable = [
        'version',
        'value',
        'created_by',
        'published_at',
    ];

    protected $casts = [
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
}
