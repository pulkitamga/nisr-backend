<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutHeroSection extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'about_hero_sections'; // Make sure your table name matches the one you created in migration

    // Define the fillable fields for mass assignment
    protected $fillable = ['image', 'heading', 'subheading'];

    // Optionally, if you need to manipulate the image (e.g., to handle storage or get the full URL)
    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image); // Assuming images are stored in the 'storage' folder
    }

}
