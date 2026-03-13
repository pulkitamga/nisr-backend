<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceEstimate extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'service_estimates';

    protected $fillable = [
        'ticket_id',
        'service_id',
        'subtotal',
        'tax',
        'extra',
        'description',
        'total',
    ];

    protected $casts = [
        'subtotal' => 'float',
        'tax' => 'float',
        'total' => 'float',
    ];

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function invoice()
    {
        return $this->hasOne(ServiceInvoice::class, 'estimate_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
