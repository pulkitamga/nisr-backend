<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutDealerSection extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'about_dealer_sections';

    protected $fillable = [
        'dealer_name',
        'partner_type',
        'show_partner_type_filter',
        'location',
        'show_location_filter',
        'coverage_area',
        'description',
        'image',
    ];

    protected $casts = [
        'show_partner_type_filter' => 'boolean',
        'show_location_filter' => 'boolean',
    ];

    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image);
    }
}
