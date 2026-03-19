<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CronSenderDetail extends Model
{
    use HasFactory;

    protected $table = 'cron_sender_details'; // Explicitly defining the table name

    protected $fillable = [
        'ticket_id',
        'send_for',
        'sender_id',
        'title',
        'message',
        'send_date',
        'ticket_status',
        'status',
        'is_active',
    ];

    protected $casts = [
        'send_for' => 'integer',
        'send_date' => 'date',
    ];

    /**
     * Get the ticket associated with this cron sender detail.
     */
    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    /**
     * Get the sender associated with this cron sender detail.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
