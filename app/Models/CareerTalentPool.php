<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CareerTalentPool extends Model
{
    use SoftDeletes;

    protected $table = 'career_talent_pool';

    protected $fillable = [
        'ticket_id',
        'consent',
        'recontact_date',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'consent' => 'boolean',
        'recontact_date' => 'date',
    ];

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }
}