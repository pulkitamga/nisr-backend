<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;


class WholesalerRegistrationReason extends Model
{
    use HasFactory;


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

       public function translations()
    {
        return $this->morphMany(\App\Models\Translation::class, 'translationable');
    }
}
