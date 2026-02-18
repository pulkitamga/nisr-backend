<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Utils\Helpers;

class WholesalePriceTier extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'subcategory_id',
        'tier',
        'moq',
        'discount_percent',
    ];

    public function subcategory()
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('wholesale_price_tier')
            ->logOnly(['subcategory_id', 'tier', 'moq', 'discount_percent'])
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
    }
}
