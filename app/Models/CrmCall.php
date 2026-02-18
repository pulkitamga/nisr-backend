<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmCall extends Model
{
    use HasFactory;

     protected $table = 'crm_calls';

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'call_id',
        'customer_id',
        'agent_id',
        'call_date',
        'call_duration',
        'call_notes',
        'direction',
        'status',
    ];

    /**
     * Relationship: Call belongs to a Customer (CRM Contact)
     */
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Relationship: Call belongs to an Agent (Admin)
     */
    public function agent()
    {
        return $this->belongsTo(Admin::class, 'agent_id');
    }

    public function getFormattedCallDateAttribute()
    {
        return $this->call_date ? $this->call_date->format('d M Y, h:i A') : null;
    }

    public function setCallNotesAttribute($value)
    {
        $this->attributes['call_notes'] = trim($value);
    }
}
