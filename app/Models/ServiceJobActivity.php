<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceJobActivity extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'service_job_activities';

    protected $fillable = [
        'job_id',
        'activity_type',
        'description',
        'created_by',
        'attachments',
        'part_id',
        'labor_hours',
        'gps_coordinates',
        'odometer_reading',
    ];

    protected $casts = [
        'attachments' => 'array',
        'labor_hours' => 'float',
    ];

    public function job()
    {
        return $this->belongsTo(ServiceJob::class, 'job_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }
}
