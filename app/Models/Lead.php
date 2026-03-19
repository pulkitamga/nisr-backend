<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Lead Model - Represents a sales lead
 *
 * @property int $id
 * @property string $party_type
 * @property int $company_id
 * @property int $contact_id
 * @property int $department_id
 * @property int $employee_id
 * @property int $owner_id
 * @property int $source_id
 * @property string $status
 * @property string $priority
 * @property \Carbon\Carbon $response_due
 * @property \Carbon\Carbon $resolution_due
 */
class Lead extends Model
{
    use HasActivityLog, HasFactory, SoftDeletes;

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
        'response_due' => 'datetime',
        'resolution_due' => 'datetime',
        'first_response_at' => 'datetime',
        'escalated_at' => 'datetime',
        'sla_paused_at' => 'datetime',
    ];

    /**
     * Configure activity logging for Lead model.
     * Used by HasActivityLog trait.
     */
    protected function getActivityLogConfig(): array
    {
        return [
            'prefix' => 'lead',
            'foreign_key' => 'lead_id',
        ];
    }

    // Relationships

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
        return $this->belongsTo(Departments::class, 'department_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(Admin::class, 'employee_id', 'id');
    }

    public function deals()
    {
        return $this->hasMany(Deal::class, 'lead_id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(WholesalePurchaseOrder::class, 'po_id');
    }

    public function escalations(): MorphMany
    {
        return $this->morphMany(Escalation::class, 'escalatable')->latest('id');
    }

    // Scopes

    /**
     * Scope to filter leads by status.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get leads assigned to a specific owner.
     */
    public function scopeAssignedTo($query, int $ownerId)
    {
        return $query->where('owner_id', $ownerId);
    }

    /**
     * Scope to get leads for a specific department.
     */
    public function scopeForDepartment($query, int $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Scope to get leads due for response.
     */
    public function scopeDueForResponse($query)
    {
        return $query->where('response_due', '<=', now())
            ->whereNull('first_response_at');
    }

    // Accessors

    /**
     * Get the lead's full name from contact.
     */
    public function getFullNameAttribute(): string
    {
        return $this->contact?->name ?? '';
    }

    /**
     * Check if lead is overdue for response.
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->response_due?->isPast() ?? false
            && is_null($this->first_response_at);
    }
}
