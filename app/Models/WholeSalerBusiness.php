<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Database\Eloquent\SoftDeletes;


class WholeSalerBusiness extends Model
{
    use HasFactory, LogsActivity ,SoftDeletes;

    protected $table = 'wholesaler_businesses';

    protected $fillable = [
        'wholesaler_id',
        'company_name',
        'trade_name',
        'registration_number',
        'register_copy',
        'tax_id',
        'tax_card_copy',
        'vat_number',
        'vat_register_copy'
    ];


        protected $dates = ['deleted_at'];

    public function wholesaler()
    {
        return $this->belongsTo(User::class, 'wholesaler_id', 'id');
    }

    public function contacts()
    {
        return $this->hasMany(WholesaleContact::class, 'company_id');
    }

    /**
     * Required by spatie activitylog (v4+)
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('wholesaler_business')
            ->logOnly([
                'wholesaler_id',
                'company_name',
                'trade_name',
                'registration_number',
                'tax_id',
                'vat_number',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Set causer dynamically using booted() model hook
     */
    protected static function booted()
    {
        // Note: The LogsActivity trait with getActivitylogOptions() already handles logging.
        // Manual logging removed to prevent duplicate entries in activity log.
    }
}
