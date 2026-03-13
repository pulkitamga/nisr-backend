<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $f_name
 * @property string $l_name
 * @property string $country_code
 * @property string $phone
 * @property string $image
 * @property string $email
 * @property string $password
 * @property string $status
 * @property string $bank_name
 * @property string $branch
 * @property string $account_no
 * @property string $holder_name
 * @property string $auth_token
 * @property float $sales_commission_percentage
 * @property float $gst
 * @property string $cm_firebase_token
 * @property string $pos_status
 * @property float $minimum_order_amount
 * @property string $free_delivery_status
 * @property float $free_delivery_over_amount
 * @property string $app_language
 */
class WholesaleProductPriceRange extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "wholesale_price_ranges";
    protected $fillable = [
        'id',
        'wholesale_id',
        'tier', // <- New
        'min_qty',
        'max_qty',
        'price_per_piece',
        'discount', // <- New
        'status',
        'created_at',
        'updated_at',
    ];

     protected $dates = ['deleted_at'];

    public $timestamps = false;
}
