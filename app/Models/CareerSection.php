<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CareerSection extends Model
{
    use HasFactory;
    use HasTranslations;
    protected $fillable = ['section', 'title', 'description', 'button_text', 'button_link' , 'image'];
}
