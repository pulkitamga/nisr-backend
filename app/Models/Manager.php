<?php

namespace App\Models;

use App\Traits\StorageTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

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
class Manager extends Authenticatable
{
    use Notifiable, StorageTrait;

    protected $fillable = [
        'id',
        'branch_id',
        'name',
        'phone',
        'email',
        'status',
        'password',
         
        
    ];

    protected $casts = [
        
        'branch_id' => 'integer',
                 
    ];

    

}
