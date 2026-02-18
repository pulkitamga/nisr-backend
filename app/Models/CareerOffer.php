<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CareerOffer extends Model
{
    use SoftDeletes;

    protected $table = 'career_offers';

    protected $fillable = [
        'ticket_id',
        'attachment',
        'start_date',
        'signed_at',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'signed_at' => 'datetime',
    ];

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }
}