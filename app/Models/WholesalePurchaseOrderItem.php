<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class WholesalePurchaseOrderItem extends Model
{
    use HasFactory,SoftDeletes;


    protected $table = 'wholesale_purchase_order_items';
    protected $fillable = [
        'wholesale_order_id', 
        'product_id', 
        'product_quantity', 
        'product_variation_type', 
        'base_price',
        'tax',
        'final_price', 
        'quantity_sent',
        'remaining',
    ];
    protected $dates = ['deleted_at'];

    public function order()
    {
        return $this->belongsTo(WholesalePurchaseOrder::class, 'wholesale_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
