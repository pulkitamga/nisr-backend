<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class CmsService extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $fillable = [
        'type',
        'heading',
        'description',
        'image',
        'button_text',
    ];

}
