<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class AboutHeroSection extends Model
{
    use HasFactory;

    protected $table = 'about_hero_sections'; // Make sure your table name matches the one you created in migration

    // Define the fillable fields for mass assignment
    protected $fillable = ['image', 'heading', 'subheading', 'is_active'];

    // Optionally, if you need to manipulate the image (e.g., to handle storage or get the full URL)
    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image); // Assuming images are stored in the 'storage' folder
    }

    public function translations(): MorphMany
    {
        return $this->morphMany('App\Models\Translation', 'translationable');
    }
}
