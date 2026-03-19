<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTicketNotification extends Model
{
    use HasFactory;

    protected $table = 'support_tickets_notification';

    protected $fillable = [
        'ticket_id',
        'notification_for',
        'user_id',
        'customer_id',
        'title',
        'message',
        'status',
        'is_active',
    ];

    protected $casts = [
        'notification_for' => 'integer',
    ];

    /**
     * Get the user associated with the notification.
     */
    public function user()
    {
        return $this->belongsTo(Admin::class, 'user_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function supportTicket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }
}
