<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WholesaleConfirmOrderItem extends Model
{
    use HasFactory,SoftDeletes;


    protected $table = 'wholesale_confirmorder_item';

    protected $fillable = [
        'confirmed_order_id',
        'product_id',
        'product_quantity',
        'product_variation_type',
        'base_price',
        'tax',
        'final_price', 
        'price_range_id', 
        'quantity_sent',
        'remaining',
    ];
    protected $dates = ['deleted_at'];

    public function confirmOrder()
    {
        return $this->belongsTo(WholesaleConfirmOrder::class, 'confirmed_order_id');
    }
    public function wholesaleProduct()
    {
        return $this->belongsTo(WholeSaleProducts::class, 'product_id', 'product_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
