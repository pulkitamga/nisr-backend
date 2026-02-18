<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlaBreach extends Model
{
    use HasFactory;

    protected $fillable = ['entity_type', 'entity_id', 'breach_type', 'occurred_at', 'notified', 'escalation_level'];
}
