<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Lead extends Model
{
    use HasFactory;
    protected $table = 'leads';
    protected $fillable = [
        'party_type',
        'po_id',
        'company_id',
        'contact_id',
        'department_id',
        'employee_id',
        'owner_id',
        'source_id',
        'utm_source',
        'utm_campaign',
        'utm_medium',
        'utm_term',
        'utm_content',
        'status',
        'priority',
        'response_due',
        'resolution_due',
        'first_response_at',
        'escalation_level',
        'escalated_at',
        'escalated_by',
        'sla_paused_at',
    ];

    protected $casts = [
        'converted_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(WholeSalerBusiness::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function owner()
    {
        return $this->belongsTo(Admin::class, 'owner_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'contact_id');
    }

    public function inboxMessages()
    {
        return $this->hasMany(InboxMessage::class, 'related_lead_id');
    }

    public function latestInboxMessage()
    {
        return $this->hasOne(InboxMessage::class, 'related_lead_id')
            ->latestOfMany();
    }


    public function department()
    {
        return $this->belongsTo(Departments::class, 'department_id');
    }

    public function employee()
    {
        return $this->belongsTo(Admin::class, 'employee_id', 'id');
    }


    public function deals()
    {
        return $this->hasMany(Deal::class, 'lead_id');
    }

    // New Relationships for lead_ tables
    public function activities()
    {
        return $this->hasMany(LeadActivity::class, 'lead_id');
    }

    public function notes()
    {
        return $this->hasMany(LeadNote::class, 'lead_id');
    }

    public function tasks()
    {
        return $this->hasMany(LeadTask::class, 'lead_id');
    }

    public function calls()
    {
        return $this->hasMany(LeadCall::class, 'lead_id');
    }

    public function files()
    {
        return $this->hasMany(LeadFile::class, 'lead_id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(WholesalePurchaseOrder::class, 'po_id');
    }

    public function escalations(): MorphMany
    {
        return $this->morphMany(Escalation::class, 'escalatable')->latest('id');
    }
}
