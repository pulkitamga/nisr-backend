<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Deal Model - Represents a sales deal/opportunity
 *
 * @property int $id
 * @property int $lead_id
 * @property string $related_party_type
 * @property int $related_party_id
 * @property string $stage
 * @property int $owner_id
 * @property int $department_id
 * @property float $value
 * @property string $priority
 * @property string $status
 * @property string $quotation_id
 * @property string $quotation_status
 * @property string $order_id
 * @property string $payment_status
 * @property string $fulfillment_status
 */
class Deal extends Model
{
    use HasActivityLog, HasFactory, SoftDeletes;

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
        'value' => 'decimal:2',
        'response_due' => 'datetime',
        'resolution_due' => 'datetime',
        'first_response_at' => 'datetime',
        'escalated_at' => 'datetime',
        'sla_paused_at' => 'datetime',
    ];

    /**
     * Configure activity logging for Deal model.
     * Used by HasActivityLog trait.
     */
    protected function getActivityLogConfig(): array
    {
        return [
            'prefix' => 'deal',
            'foreign_key' => 'deal_id',
        ];
    }

    // Relationships

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
        return $this->belongsTo(Admin::class, 'employee_id');
    }

    public function owner()
    {
        return $this->belongsTo(Admin::class, 'owner_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function escalations(): MorphMany
    {
        return $this->morphMany(Escalation::class, 'escalatable')->latest('id');
    }

    // Scopes

    /**
     * Scope to filter deals by stage.
     */
    public function scopeInStage($query, string $stage)
    {
        return $query->where('stage', $stage);
    }

    /**
     * Scope to get deals assigned to a specific owner.
     */
    public function scopeAssignedTo($query, int $ownerId)
    {
        return $query->where('owner_id', $ownerId);
    }

    /**
     * Scope to get open deals (not closed).
     */
    public function scopeOpen($query)
    {
        return $query->where('stage', '!=', 'closed');
    }

    /**
     * Scope to get won deals.
     */
    public function scopeWon($query)
    {
        return $query->where('stage', 'closed')
            ->where('status', 'won');
    }

    /**
     * Scope to get lost deals.
     */
    public function scopeLost($query)
    {
        return $query->where('stage', 'closed')
            ->where('status', 'lost');
    }

    // Accessors

    /**
     * Get the deal's value formatted as currency.
     */
    public function getFormattedValueAttribute(): string
    {
        return number_format($this->value, 2);
    }

    /**
     * Check if deal is in negotiation stage.
     */
    public function getIsInNegotiationAttribute(): bool
    {
        return $this->stage === 'negotiation';
    }

    /**
     * Check if deal is closed.
     */
    public function getIsClosedAttribute(): bool
    {
        return $this->stage === 'closed';
    }
}
