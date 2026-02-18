<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class WholesaleQuotationItem extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'wholesale_quotation_id',
        'product_id',
        'product_quantity',
        'base_price',
        'product_variation_type',
        'final_price',
        'price_range_id',
        'tax',
        'discount',
    ];

     protected $dates = ['deleted_at'];

    public function quotation()
    {
        return $this->belongsTo(WholesaleQuotation::class, 'wholesale_quotation_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function wholesaleProduct()
{
    return $this->belongsTo(WholeSaleProducts::class, 'product_id');
}

}
