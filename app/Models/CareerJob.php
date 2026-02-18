<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;


class CareerJob extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'location', 'experience', 'skills', 'job_description', 'is_active'];

    protected $casts = [
        'skills' => 'array',
    ];

     public function translations(): MorphMany
    {
        return $this->morphMany('App\Models\Translation', 'translationable');
    }
}
