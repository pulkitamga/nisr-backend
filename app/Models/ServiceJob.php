<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceJob extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'service_jobs';

    protected $fillable = [
        'ticket_id',
        'technician_id',
        'status',
        'service_mode',
        'scheduled_at',
        'started_at',
        'completed_at',
        'odometer_start',
        'odometer_end',
        'gps_location',
        'remarks',
        'attachments',
        'priority',
        'sla_hours',
        'service_sku',
        'is_mobile',
        'customer_signature',
        'description',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_mobile' => 'boolean',
        'attachments' => 'array',
    ];

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function technician()
    {
        return $this->belongsTo(Admin::class, 'technician_id');
    }

    public function activities()
    {
        return $this->hasMany(ServiceJobActivity::class, 'job_id');
    }

    public function invoice()
    {
        return $this->hasOne(ServiceInvoice::class, 'job_id');
    }

    public function items()
    {
        return $this->hasMany(ServiceJobItem::class, 'job_id');
    }

    public function changeOrders()
    {
        return $this->hasMany(ServiceChangeOrder::class, 'job_id');
    }

    public function cancellation()
    {
        return $this->hasOne(ServiceCancellation::class, 'job_id');
    }
}