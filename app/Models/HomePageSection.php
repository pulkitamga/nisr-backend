<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class HomePageSection extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'type',
        'name',
        'value',
        'is_active',
    ];

}
