<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SerialTransferHistory extends Model
{
    use HasFactory;


    protected $fillable = [
        'stock_transfer_id',
        'wholesale_delivery_id',
        'serial_number',
        'from_branch_id',
        'to_branch_id',
        'distributor_id',
        'transfer_type',
        'transferred_at'
    ];


    protected $casts = [
        'transferred_at' => 'datetime',
    ];

    public function fromBranch()
    {
        return $this->belongsTo(\App\Models\Branch::class, 'from_branch_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(\App\Models\Branch::class, 'to_branch_id');
    }

    public function distributor()
    {
        return $this->belongsTo(\App\Models\WholeSalerBusiness::class, 'distributor_id', 'wholesaler_id');
    }


    public function stockTransfer()
    {
        return $this->belongsTo(\App\Models\StockTransfers::class, 'stock_transfer_id');
    }

    public function wholesaleDelivery()
    {
        return $this->belongsTo(\App\Models\WholesaleOrderDelivery::class, 'wholesale_delivery_id');
    }
}
