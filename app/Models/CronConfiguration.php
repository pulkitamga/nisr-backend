<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CronConfiguration extends Model
{
    use HasFactory;

    protected $table = 'cron_configuration'; // Explicitly defining the table name

    protected $fillable = [
        'ticket_status_id',
        'type',
        'duration',
        'status',
    ];

    /**
     * Get the ticket status associated with this cron configuration.
     */
    public function ticketStatus()
    {
        return $this->belongsTo(TicketStatus::class, 'ticket_status_id');
    }
}