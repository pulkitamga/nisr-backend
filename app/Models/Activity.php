<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = ['deal_id', 'type', 'subject', 'due_date', 'assigned_to', 'status'];

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }
}
