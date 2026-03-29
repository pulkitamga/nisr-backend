<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Blog extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $fillable = [
        'heading',
        'description',
        'image',
        'blog_type',
        'category',
    ];

}
