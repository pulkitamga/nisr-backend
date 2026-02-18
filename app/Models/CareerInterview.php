<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CareerInterview extends Model
{
    use SoftDeletes;

    protected $table = 'career_interviews';

    protected $fillable = [
        'ticket_id',
        'scheduled_at',
        'conducted_at',
        'panel',
        'outcome',
        'notes',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'panel' => 'array',
        'scheduled_at' => 'datetime',
        'conducted_at' => 'datetime',
    ];

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }
}