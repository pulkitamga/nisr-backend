<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;


class CmsProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'heading',
        'description',
        'image',
    ];

     public function translations(): MorphMany
    {
        return $this->morphMany('App\Models\Translation', 'translationable');
    }
}
