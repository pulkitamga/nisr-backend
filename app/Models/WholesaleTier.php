<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Utils\Helpers;

class WholesaleTier extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, HasTranslations;

    protected $fillable = ['name', 'is_active', 'rank'];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'rank' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Spatie Activity Log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('wholesale_tier')
            ->logOnly(['name', 'is_active', 'rank'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Override tapActivity to set causer dynamically
     */
    public function tapActivity(\Spatie\Activitylog\Models\Activity $activity, string $eventName)
    {
        $activity->causer()->associate(Helpers::getLoggedInUser());
    }

}
