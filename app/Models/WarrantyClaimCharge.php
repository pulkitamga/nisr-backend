<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarrantyClaimCharge extends Model
{
    use HasFactory;

    protected $fillable = [
        'warranty_claim_id',
        'charge_type',
        'amount',
        'is_paid',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'amount'  => 'decimal:2',
    ];

    public function claim()
    {
        return $this->belongsTo(WarrantyClaim::class);
    }
}
