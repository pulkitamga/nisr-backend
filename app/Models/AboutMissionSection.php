<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutMissionSection extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'about_mission_sections';

    protected $fillable = ['title', 'content'];

    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image);
    }
}
