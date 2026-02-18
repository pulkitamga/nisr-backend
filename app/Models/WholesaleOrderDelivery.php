<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Utils\Helpers;
use Illuminate\Database\Eloquent\SoftDeletes;

class WholesaleOrderDelivery extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'wholesale_order_delivery';

    protected $fillable = [
        'order_id',
        'confirmed_order_id',
        'product_variation_type',
        'product_id',
        'quantity_sent',
        'serial_csv_path',
        'note',
        'branch_id',
        'delivery_date',
    ];
    protected $dates = ['deleted_at'];

    // Activity Log Options
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('wholesale_order_delivery')
            ->logOnly([
                'product_id',
                'quantity_sent',
                'branch_id',
                'delivery_date',
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
         $activity->properties = $activity->properties->merge([
            'action_type' => 'delivery store', 
        ]);
    }

    // Relationships
    public function confirmedOrder()
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

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
