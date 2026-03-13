<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Utils\Helpers;
use Illuminate\Database\Eloquent\SoftDeletes;

class WholesaleOrderPayment extends Model
{
    use LogsActivity, SoftDeletes;

    protected $fillable = [
        'order_id',
        'wholesale_confirm_order_id',
        'date',
        'amount',
        'remaining_amount',
        'payment_through',
        'reference',
        'notes'
    ];
    protected $dates = ['deleted_at'];

    public function order()
    {
        return $this->belongsTo(WholesaleConfirmOrder::class, 'wholesale_confirm_order_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('wholesale_order_payment')
            ->logOnly([
                'date',
                'amount',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function tapActivity(\Spatie\Activitylog\Models\Activity $activity, string $eventName)
    {
        $user = Helpers::getLoggedInUser();
        if ($user) {
            $activity->causer()->associate($user);
            $activity->properties = $activity->properties->merge([
                'causer_type_name' => class_basename(get_class($user)),
                'causer_name' => $user->name ?? null,
            ]);
        }
        $activity->order_id = $this->order_id ?? null;

        $activity->properties = $activity->properties->merge([
            'action_type' => 'payment store', 
        ]);
    }
}
