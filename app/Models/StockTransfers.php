<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransfers extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_branch_id',
        'to_branch_id',
        'transfer_date',
        'status'
    ];

    public function products()
    {
        return $this->hasMany(StockTransferProduct::class, 'stock_transfers_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    // Helper: Total items transferred
    public function getTotalQuantityAttribute()
    {
        return $this->products()->sum('quantity');
    }
}