<?php

namespace App\Models;

use App\Traits\StorageTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Branch Model - Represents a physical branch/location
 *
 * @property int $id
 * @property string $branch_name
 * @property string $branch_country
 * @property string $branch_state
 * @property string $phone
 * @property string $email
 * @property string $status
 * @property string $branch_address
 * @property string $branch_zipcode
 * @property string $branch_hours_from
 * @property string $branch_hours_to
 * @property string $sun_branch_hours_from
 * @property string $sun_branch_hours_to
 * @property string $mon_branch_hours_from
 * @property string $mon_branch_hours_to
 * @property string $tue_branch_hours_from
 * @property string $tue_branch_hours_to
 * @property string $wed_branch_hours_from
 * @property string $wed_branch_hours_to
 * @property string $thu_branch_hours_from
 * @property string $thu_branch_hours_to
 * @property string $fri_branch_hours_from
 * @property string $fri_branch_hours_to
 * @property string $sat_branch_hours_from
 * @property string $sat_branch_hours_to
 * @property string $shipping_method_city
 * @property string $shipping_methods_area @deprecated Use pivot table instead
 * @property string $delivery_restriction @deprecated Use pivot table instead
 * @property string $branch_latitude
 * @property string $branch_longitude
 * @property int $manager_id
 * @property float $sales_commission_percentage
 * @property float $gst
 * @property string $pos_status
 * @property float $minimum_order_amount
 * @property string $free_delivery_status
 * @property float $free_delivery_over_amount
 * @property string $app_language
 */
class Branch extends Model
{
    use StorageTrait, SoftDeletes;

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
        'branch_latitude',
        'branch_longitude',
        'manager_id',
    ];

    protected $casts = [
        'id' => 'integer',
        'manager_id' => 'integer',
        'sales_commission_percentage' => 'float',
        'gst' => 'float',
        'minimum_order_amount' => 'float',
        'free_delivery_over_amount' => 'float',
    ];

    protected $dates = ['deleted_at'];

    // Relationships

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'manager_id');
    }

    /**
     * Relationship to the seller/vendor
     * Note: Uses vendor_id in database but method named seller() for consistency
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'vendor_id');
    }

    /**
     * Branch shipping areas via pivot table
     * Replaces comma-separated storage in shipping_methods_area column
     */
    public function shippingAreas(): BelongsToMany
    {
        return $this->belongsToMany(
            ShippingMethodArea::class,
            'branch_shipping_method_areas',
            'branch_id',
            'shipping_method_area_id'
        );
    }

    /**
     * Branch delivery restrictions via pivot table
     * Replaces comma-separated storage in delivery_restriction column
     */
    public function deliveryRestrictions(): BelongsToMany
    {
        return $this->belongsToMany(
            DeliveryArea::class,
            'branch_delivery_restrictions',
            'branch_id',
            'delivery_area_id'
        );
    }

    /**
     * Legacy method for backward compatibility during migration
     * @deprecated Use shippingAreas() relationship instead
     */
    public function getShippingMethodsAreas(): string
    {
        $areas = $this->relationLoaded('shippingAreas')
            ? $this->shippingAreas->pluck('area')->toArray()
            : $this->shippingAreas()->pluck('area')->toArray();
        return implode(', ', $areas);
    }

    /**
     * Legacy method for backward compatibility during migration
     * @deprecated Use deliveryRestrictions() relationship instead
     */
    public function getDeliveryRestriction(): string
    {
        $areas = $this->relationLoaded('deliveryRestrictions')
            ? $this->deliveryRestrictions->pluck('area')->toArray()
            : $this->deliveryRestrictions()->pluck('area')->toArray();
        return implode(', ', $areas);
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    /**
     * Check if branch is currently open based on day of week and operating hours
     */
    public function isOpenNow(): bool
    {
        $now = now();
        $day = strtolower($now->format('D')); // sun, mon, tue, wed...

        $fromField = "{$day}_branch_hours_from";
        $toField = "{$day}_branch_hours_to";

        $from = $this->{$fromField};
        $to = $this->{$toField};

        if (!$from || !$to) {
            return false;
        }

        $fromTime = Carbon::parse($from);
        $toTime = Carbon::parse($to);

        return $now->between($fromTime, $toTime);
    }
}
