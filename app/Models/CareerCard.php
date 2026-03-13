<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;


class CareerCard extends Model
{
    use HasFactory;

    protected $fillable = [ 'icon', 'title', 'description', 'is_active'];


     public function translations(): MorphMany
    {
        return $this->morphMany('App\Models\Translation', 'translationable');
    }
}
