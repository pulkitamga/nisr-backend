<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Utils\Helpers;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\StorageTrait;

class WholesaleConfirmOrder extends Model
{
    use HasFactory, LogsActivity, SoftDeletes, StorageTrait;

    protected $fillable = [
        'order_id',
        'purchase_order_no',
        'external_po_number',
        'quotation_no',
        'invoice_no',
        'confirm_order_no',
        'wholesaler_id',
        'status',
        'delivery_status',
        'payment_status',
        'confirmed_at',
        'final_price',
        'attachments',
    ];

    protected $dates = ['confirmed_at', 'created_at', 'updated_at', 'deleted_at'];

    // Relationships
    public function metas()
    {
        return $this->hasMany(QuotationMeta::class, 'wholesale_quotation_id', 'quotation_no');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(WholesalePurchaseOrder::class, 'purchase_order_no', 'purchase_order_no');
    }


    public function wholeseller()
    {
        return $this->belongsTo(User::class, 'wholesaler_id');
    }

    public function items()
    {
        return $this->hasMany(WholesalePurchaseOrderItem::class, 'wholesale_order_id', 'id');
    }


    public function approver()
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }

    public function payments()
    {
        return $this->hasMany(WholesaleOrderPayment::class, 'wholesale_confirm_order_id');
    }

    public function deliveries()
    {
        return $this->hasMany(WholesaleOrderDelivery::class, 'confirmed_order_id');
    }

    /**
     * Spatie Activity Log Options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('wholesale_confirm_order')
            ->logOnly([
                'status',
                'delivery_status',
                'payment_status',
                'confirmed_at',
                'confirm_order_no',
                'invoice_no',

            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Track who performed the activity (admin/customer)
     */
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
    public function getImageFullUrlAttribute(): array
    {
        $value = $this->image;
        if (count($this->storage) > 0) {
            $storage = $this->storage->where('key', 'attachments')->first();
        }
        return $this->storageLink('wholesale_attachment', $value, $storage['value'] ?? 'public');
    }
    protected $appends = ['image_full_url'];
}
