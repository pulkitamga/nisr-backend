<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmCall extends Model
{
    use HasFactory;

     protected $table = 'crm_calls';

    protected $casts = [
        'call_date' => 'datetime',
        'started_at' => 'datetime',
        'answered_at' => 'datetime',
        'ended_at' => 'datetime',
        'call_duration' => 'integer',
        'raw_payload' => 'array',
    ];

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'call_id',
        'ucm_channel',
        'ucm_peer_channel',
        'ucm_uniqueid',
        'ucm_bridge_id',
        'src_number',
        'dst_number',
        'customer_id',
        'agent_id',
        'call_date',
        'started_at',
        'answered_at',
        'ended_at',
        'call_duration',
        'call_notes',
        'raw_payload',
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
        $this->attributes['call_notes'] = is_null($value) ? null : trim((string)$value);
    }
}
