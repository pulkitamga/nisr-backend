<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'jti', 'warranty_public_id', 'recipient_hash', 'scope', 'issued_at', 'expires_at', 'used_at', 'ip', 'user_agent',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];
}