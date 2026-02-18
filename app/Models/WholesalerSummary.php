<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Utils\Helpers;

class WholesalerSummary extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'wholesaler_summary'; 

    protected $fillable = [
        'wholesaler_id',
        'company_name',
        'trade_name',
        'registration_number',
        'tax_id',
        'tax_card_copy',
        'vat_number',
        'vat_register_copy',
        'product_name',
        'category_name',
        'subcategory_name',
        'min_qty',
        'max_qty',
        'price_per_piece',
        'minimum_order_amount',
        'wholesaler_status',
    ];

    // Relationships to other models
    public function wholesaler()
    {
        return $this->belongsTo(User::class, 'wholesaler_id');
    }

    public function business()
    {
        return $this->belongsTo(WholeSalerBusiness::class, 'wholesaler_id', 'wholesaler_id');
    }

    public function product()
    {
        return $this->belongsTo(WholeSaleProducts::class, 'wholesaler_id', 'wholesaler_id')->withTrashed();
    }

    public function priceRange()
    {
        return $this->hasMany(WholesaleProductPriceRange::class, 'wholesale_id', 'wholesaler_id');
    }

    // Spatie Activity Log options
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('wholesaler_summary')
            ->logOnly([
                'company_name',
                'trade_name',
                'registration_number',
                'tax_id',
                'vat_number',
                'product_name',
                'category_name',
                'subcategory_name',
                'min_qty',
                'max_qty',
                'price_per_piece',
                'minimum_order_amount',
                'wholesaler_status',
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
                'causer_type' => class_basename(get_class($user)),
                'causer_name' => $user->name ?? ($user->f_name ?? null),
            ]);
        }
    }
}
