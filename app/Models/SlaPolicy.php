<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlaPolicy extends Model
{
    use HasFactory;

    protected $fillable = ['entity_type', 'priority', 'response_time_minutes', 'resolution_time_minutes', 'is_active'];
}
