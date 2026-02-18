<?php

namespace App\Models;

use App\Models\WholeSalerBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Deal extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'po_id',
        'related_party_type',
        'related_party_id',
        'contact_id',
        'stage',
        'owner_id',
        'department_id',
        'value',
        'priority',
        'source_id',
        'status',
        'quotation_id',
        'quotation_status',
        'employee_id',
        'order_id',
        'payment_status',
        'fulfillment_status',
        'response_due',
        'resolution_due',
        'first_response_at',
        'escalation_level',
        'escalated_at',
        'escalated_by',
        'sla_paused_at',
    ];

    protected $casts = [
        'quotation_status' => 'string',
    ];

    public function relatedParty(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'related_party_type', 'related_party_id');
    }

    public function relatedUser()
    {
        return $this->hasOneThrough(
            User::class,
            WholeSalerBusiness::class,
            'id',
            'id',
            'related_party_id',
            'wholesaler_id'
        );
    }


    public function department()
    {
        return $this->belongsTo(Departments::class, 'department_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'contact_id');
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(WholesalePurchaseOrder::class, 'po_id');
    }

    public function employee()
    {
        return $this->belongsTo(Admin::class, 'employee_id'); // Link to admins table
    }

    public function owner()
    {
        return $this->belongsTo(Admin::class, 'owner_id');
    }
    public function activities()
    {
        return $this->hasMany(DealActivity::class, 'deal_id');
    }

    public function notes()
    {
        return $this->hasMany(DealNote::class, 'deal_id');
    }

    public function tasks()
    {
        return $this->hasMany(DealTask::class, 'deal_id');
    }

    public function calls()
    {
        return $this->hasMany(DealCall::class, 'deal_id');
    }

    public function files()
    {
        return $this->hasMany(DealFile::class, 'deal_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
