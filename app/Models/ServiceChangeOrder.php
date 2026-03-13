<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceChangeOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'service_change_orders';

    protected $fillable = [
        'ticket_id',
        'job_id',
        'additional_charges',
        'description',
        'image',
        'approved_at',
    ];

    protected $casts = [
        'additional_charges' => 'float',
        'approved_at' => 'datetime',
    ];

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function job()
    {
        return $this->belongsTo(ServiceJob::class, 'job_id');
    }

    public function invoice()
    {
        return $this->hasOne(ServiceInvoice::class, 'change_order_id');
    }
}
