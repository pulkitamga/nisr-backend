<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleYear extends Model
{
    use HasFactory;
    protected $table = 'vehicle_years';
    protected $fillable = ['year'];
}
