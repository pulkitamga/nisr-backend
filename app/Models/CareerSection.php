<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\MorphMany;

class CareerSection extends Model
{
    use HasFactory;
    protected $fillable = ['section', 'title', 'description', 'button_text', 'button_link' , 'image'];
    public function translations(): MorphMany
    {
        return $this->morphMany('App\Models\Translation', 'translationable');
    }
}
