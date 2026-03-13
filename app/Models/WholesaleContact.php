<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Utils\Helpers;

class WholesaleContact extends Model
{
    use SoftDeletes, LogsActivity;

    protected $dates = ['deleted_at', 'last_contacted_at'];

    protected $fillable = [
        'first_name',
        'last_name',
        'job_title',
        'email',
        'phone_number',
        'mobile_number_1',
        'mobile_number_2',
        'company_id',
        'preferred_contact_method',
        'address',
        'city',
        'state',
        'country',
        'notes',
        'tags',
        'is_active',
        'last_contacted_at',
    ];

    public function company()
    {
        return $this->belongsTo(WholeSalerBusiness::class, 'company_id');
    }

    /**
     * Spatie Activity Log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('wholesale_contact')
            ->logOnly([
                'first_name',
                'last_name',
                'job_title',
                'email',
                'phone_number',
                'mobile_number_1',
                'mobile_number_2',
                'preferred_contact_method',
                'address',
                'city',
                'state',
                'country',
                'notes',
                'tags',
                'is_active',
                'last_contacted_at'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Store who performed the activity
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
    }
}
