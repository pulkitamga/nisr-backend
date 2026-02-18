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
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphMany;



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
class Branch extends Authenticatable
{
    use Notifiable, StorageTrait, SoftDeletes;

    protected $fillable = [
        'branch_name',
        'branch_country',
        'branch_state',
        'phone',
        'email',
        'status',
        'branch_address',
        'branch_zipcode',
        'branch_hours_from',
        'branch_hours_to',
        'sun_branch_hours_from',
        'sun_branch_hours_to',
        'mon_branch_hours_from',
        'mon_branch_hours_to',
        'tue_branch_hours_from',
        'tue_branch_hours_to',
        'wed_branch_hours_from',
        'wed_branch_hours_to',
        'thu_branch_hours_from',
        'thu_branch_hours_to',
        'fri_branch_hours_from',
        'fri_branch_hours_to',
        'sat_branch_hours_from',
        'sat_branch_hours_to',
        'shipping_method_city',
        'shipping_methods_area',
        'delivery_restriction',
        'branch_latitude',
        'branch_longitude',
        'manager_id',


    ];

    protected $casts = [
        'id' => 'integer',
        'branch_name' => 'string',
        'branch_country' => 'string',

    ];

    protected $dates = ['deleted_at'];


    public function manager()
    {
        return $this->belongsTo(Admin::class, 'manager_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Seller::class, 'vendor_id');
    }


    public function getShippingMethodsAreas()
    {
        $shippingMethodAreaIds = explode(',', $this->shipping_methods_area);
        $areas = ShippingMethodArea::whereIn('id', $shippingMethodAreaIds)
            ->pluck('area')
            ->toArray();

        return implode(', ', $areas);
        // return ShippingMethodArea::whereIn('id', $shippingMethodAreaIds)->get();
    }

    public function getDeliveryRestriction()
    {
        $deliverRestrictionIds = explode(',', $this->delivery_restriction);
        $areas = DeliveryArea::whereIn('id', $deliverRestrictionIds)->get()
            ->pluck('area')
            ->toArray();

        return implode(', ', $areas);
    }

        public function translations(): MorphMany
    {
        return $this->morphMany('App\Models\Translation', 'translationable');
    }

    public function isOpenNow()
{
    $now = now();
    $day = strtolower($now->format('D')); // sun, mon, tue, wed...

    // like mon_branch_hours_from / mon_branch_hours_to
    $fromField = "{$day}_branch_hours_from";
    $toField   = "{$day}_branch_hours_to";

    $from = $this->{$fromField};
    $to   = $this->{$toField};

    if (!$from || !$to) return false; // no schedule assigned

    $fromTime = Carbon::parse($from);
    $toTime = Carbon::parse($to);

    return $now->between($fromTime, $toTime);
}

}
