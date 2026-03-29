<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutDealerSection extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'about_dealer_sections';

    protected $fillable = ['dealer_name', 'location', 'description', 'image'];

    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image);
    }
}
