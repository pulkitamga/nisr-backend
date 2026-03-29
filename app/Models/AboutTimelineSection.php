<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class AboutTimelineSection extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'about_timeline_sections';

    protected $fillable = ['year', 'title', 'description', 'image'];

    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image);
    }
}
