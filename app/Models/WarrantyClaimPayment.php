<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarrantyClaimPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'warranty_claim_id',
        'payment_channel',
        'payment_status',
        'amount',
        'charge_ids',
        'payment_reference',
        'payment_link',
        'payment_link_token',
        'payment_link_expires_at',
        'gateway_payment_method',
        'gateway_transaction_id',
        'paid_at',
        'paid_by_user_id',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'charge_ids' => 'array',
        'payment_link_expires_at' => 'datetime',
        'paid_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(WarrantyClaim::class, 'warranty_claim_id');
    }
}
