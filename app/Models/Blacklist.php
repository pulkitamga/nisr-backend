<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Blacklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'serial_number',
        'user_id',
        'reason',
        'blacklisted_at',
    ];

    protected $casts = [
        'blacklisted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function warranty(): BelongsTo
    {
        return $this->belongsTo(Warranty::class, 'serial_number', 'serial_number');
    }
}
