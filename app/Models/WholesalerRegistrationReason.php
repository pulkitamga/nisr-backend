<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class WholesalerRegistrationReason extends Model
{
    use HasFactory, HasTranslations;


    protected $fillable = [
        'id',
        'title',
        'description',
        'priority',
        'status',
        'created_at',
        'updated_at'
    ];
    protected $casts = [
        'id' => 'integer',
        'title' => 'string',
        'description' => 'string',
        'priority' => 'integer',
        'status' => 'integer',
    ];

}
