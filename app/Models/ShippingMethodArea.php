<?php

namespace App\Models;

use App\Models\Seller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class ShippingMethod
 *
 * @property int $id
 * @property int|null $creator_id
 * @property string $creator_type
 * @property string|null $country
 * @property string|null $state
 * @property string|null $city
 * @property string|null $area
 * @property float $cost
 * @property string|null $duration
 * @property bool $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 */
class ShippingMethodArea extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'creator_id',
        'creator_type',
        'country',
        'state_id',
        'city_id',
        'area',
        'cost',
        'coordinates',
        'duration',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status' => 'boolean',
        'cost' => 'float:2',
    ];

    public function seller():BelongsTo
    {
        return $this->belongsTo(Seller::class,'creator_id');
    }

    public function state():BelongsTo
    {
        return $this->belongsTo(State::class,'state_id');
    }

    public function city():BelongsTo
    {
        return $this->belongsTo(City::class,'city_id');
    }
}
