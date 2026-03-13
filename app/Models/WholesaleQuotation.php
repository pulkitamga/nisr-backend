<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Utils\Helpers;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphMany;



class WholesaleQuotation extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'order_id',
        'purchase_order_no',
        'quotation_no',
        'wholeseller_id',
        'wholeseller_tier',
        'status',
        'final_price',
        'wholesaler_discount_amount',
        'terms_and_conditions',
        'note',
    ];

        protected $dates = ['deleted_at'];


    /**
     * Relationships
     */
    public function metas()
    {
        return $this->hasMany(QuotationMeta::class, 'wholesale_quotation_id');
    }

    public function wholeseller()
    {
        return $this->belongsTo(User::class, 'wholeseller_id');
    }


    public function items()
    {
        return $this->hasMany(WholesaleQuotationItem::class);
    }

    /**
     * Spatie Activity Log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('wholesale_quotation')
            ->logOnly([
                'status',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Automatically set causer (admin/customer)
     */
    public function tapActivity(\Spatie\Activitylog\Models\Activity $activity, string $eventName)
    {
        $activity->causer()->associate(Helpers::getLoggedInUser());

        $user = Helpers::getLoggedInUser();
        if ($user) {
            $activity->properties = $activity->properties->merge([
                'causer_type_name' => class_basename(get_class($user)), 
                'causer_name' => $user->name ?? null,
            ]);
        }
            $activity->order_id = $this->order_id ?? null;

    }

      public function translations(): MorphMany
    {
        return $this->morphMany('App\Models\Translation', 'translationable');
    }
}
