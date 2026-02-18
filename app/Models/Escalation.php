<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Escalation extends Model
{
    use HasFactory;

    protected $fillable = [
        'escalatable_id',
        'escalatable_type',
        'escalated_by',
        'reason',
        'status',
    ];

    /**
     * Get the parent escalatable model (SupportTicket, Lead, Deal, etc.).
     */
    public function escalatable()
    {
        return $this->morphTo();
    }

    /**
     * Get the employee who escalated.
     */
    public function escalatedBy()
    {
        return $this->belongsTo(Admin::class, 'escalated_by');
    }
}