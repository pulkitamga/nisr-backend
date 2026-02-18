<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Utils\Helpers;

class WholesaleTier extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = ['name', 'is_active', 'rank'];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'rank' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get all translations for this tier.
     */
    public function translations()
    {
        return $this->morphMany(\App\Models\Translation::class, 'translationable');
    }

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

    public function getTranslation(string $key, string $locale)
    {
        return $this->translations->firstWhere(
            fn($t) =>
            $t->locale === $locale && $t->key === $key
        )?->value;
    }
}
