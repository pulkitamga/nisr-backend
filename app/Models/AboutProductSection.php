<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class AboutProductSection extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'about_product_sections';

    protected $fillable = ['title', 'description', 'card_label', 'card_note', 'image'];

    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image);
    }
}
