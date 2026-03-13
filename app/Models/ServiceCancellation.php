<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceCancellation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'service_cancellations';

    protected $fillable = [
        'ticket_id',
        'job_id',
        'cancellation_reason',
        'fee_amount',
        'refund_amount',
    ];

    protected $casts = [
        'fee_amount' => 'float',
        'refund_amount' => 'float',
    ];

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function job()
    {
        return $this->belongsTo(ServiceJob::class, 'job_id');
    }
}