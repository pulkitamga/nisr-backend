<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Utils\Helpers;
use Illuminate\Database\Eloquent\SoftDeletes;


class WholesalePurchaseOrder extends Model
{
    use LogsActivity, SoftDeletes;

    protected $table = 'wholesale_purchase_orders';

    protected $fillable = [
        'purchase_order_no',
        'order_id',
        'wholeseller_id',
        'wholeseller_tier',
        'status',
        'approved_at',
        'final_price',
    ];
    protected $dates = ['deleted_at'];

    /**
     * Relationships
     */
    public function wholeseller()
    {
        return $this->belongsTo(User::class, 'wholeseller_id');
    }

    public function items()
    {
        return $this->hasMany(WholesalePurchaseOrderItem::class, 'wholesale_order_id');
    }

    public function payments()
    {
        return $this->hasMany(WholesaleOrderPayment::class);
    }

    public function confirmOrder()
{
    return $this->hasOne(WholesaleConfirmOrder::class, 'purchase_order_no', 'purchase_order_no');
}



    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('wholesale_purchase_order')
            ->logOnly([
                'purchase_order_no',
                'status',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }


    public function tapActivity(\Spatie\Activitylog\Models\Activity $activity, string $eventName)
    {
        $user = Helpers::getLoggedInUser();
        $activity->causer()->associate($user);

        if ($user) {
            $activity->properties = $activity->properties->merge([
                'causer_type_name' => class_basename(get_class($user)),
                'causer_name' => $user->name ?? null,
            ]);
        }
        $activity->order_id = $this->order_id ?? null;
    }
}
