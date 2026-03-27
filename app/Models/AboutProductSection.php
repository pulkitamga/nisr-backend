<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;


class AboutProductSection extends Model
{
    use HasFactory;

    protected $table = 'about_product_sections';

    protected $fillable = ['title', 'description', 'image'];

    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image);
    }
    public function translations(): MorphMany
    {
        return $this->morphMany('App\Models\Translation', 'translationable');
    }
}
