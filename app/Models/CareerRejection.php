<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CareerRejection extends Model
{
    use SoftDeletes;

    protected $table = 'career_rejections';

    protected $fillable = [
        'ticket_id',
        'reason_code',
        'closure_message',
        'created_at',
        'updated_at',
    ];

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }
}