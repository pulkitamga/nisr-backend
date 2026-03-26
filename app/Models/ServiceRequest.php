<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ServiceRequest extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'service_id',
        'customer_id',
        'service_option',
        'country',
        'state',
        'city',
        'area',
        'address',
        'latitude',
        'longitude',
        'vehicle_type',
        'vehicle_make',
        'vehicle_model',
        'vehicle_year',
        'vehicle_mileage',
        'vin',
        'problem_description',
    ];

    // Relations (optional)
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
