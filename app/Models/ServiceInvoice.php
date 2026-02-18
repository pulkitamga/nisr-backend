<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'service_invoices';

    protected $fillable = [
        'ticket_id',
        'job_id',
        'subtotal',
        'tax',
        'total',
        'payment_status',
        'payment_link',
        'generated_at',
        'estimate_id',
        'is_estimate',
        'change_order_id',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'is_estimate' => 'boolean',
        'subtotal' => 'float',
        'tax' => 'float',
        'total' => 'float',
    ];

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function job()
    {
        return $this->belongsTo(ServiceJob::class, 'job_id');
    }

    public function estimate()
    {
        return $this->belongsTo(ServiceEstimate::class, 'estimate_id');
    }

    public function changeOrder()
    {
        return $this->belongsTo(ServiceChangeOrder::class, 'change_order_id');
    }
}