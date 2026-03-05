<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosCartState extends Model
{
    protected $fillable = [
        'cart_id',
        'actor_type',
        'actor_id',
        'branch_id',
        'payload',
        'last_activity_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'last_activity_at' => 'datetime',
    ];
}

