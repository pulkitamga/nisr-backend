<?php

namespace App\Models;

use App\Traits\StorageTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class WarrantyClaim extends Model
{
    use HasFactory, StorageTrait, SoftDeletes;

    protected $fillable = [
        'warranty_id',
        'serial_number',
        'claim_number',
        'status',
        'description',
        'submitted_at',
        'response_due',
        'activated_by_name',
        'rma_number',
        'rma_deadline',
        'received_at',
        'branch_id',
        'technician_id',
        'diagnosis_notes',
        'repair_or_replace',
        'is_admin_override',
        'override_reason',
        'override_by_user_id',
        'reopen_count',
        'inspection_fee_due',
        'is_fee_waived',
        'inspection_fee_amount',
        'tamper_detected',
        'replacement_mode',
        'tracking_number',
        'dispatched_at',
        'qc_passed_at',
        'resolved_at',
        'first_response_at',
        'sla_paused_at',
        'escalated_by',
        'escalated_at',
        'escalation_level',
        'resolution_due',
        'priority',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'rma_deadline' => 'datetime',
        'received_at' => 'datetime',
        'is_admin_override' => 'boolean',
        'is_fee_waived' => 'boolean',
        'inspection_fee_amount' => 'float',
        'reopen_count' => 'integer',
        'tamper_detected' => 'boolean',
        'dispatched_at' => 'datetime',
        'qc_passed_at' => 'datetime',
        'resolved_at' => 'datetime',
        'response_due' => 'datetime',
        'resolution_due' => 'datetime',
    ];

    protected $appends = ['attachments_full_url'];

    public function warranty(): BelongsTo
    {
        return $this->belongsTo(Warranty::class);
    }

    public function charges()
    {
        return $this->hasMany(WarrantyClaimCharge::class);
    }
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function overrideBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'override_by_user_id');
    }

    public function workOrder(): HasOne
    {
        return $this->hasOne(WorkOrder::class, 'warranty_claim_id');
    }

    // In WarrantyClaim.php
    public function attachments(): HasMany
    {
        return $this->hasMany(WarrantyClaimAttachment::class, 'warranty_claim_id');
    }

    public function timelineEvents(): HasMany
    {
        return $this->hasMany(WarrantyTimelineEvent::class, 'warranty_claim_id');
    }

    public function isWithinSLA(string $slaType): bool
    {
        $dueField = $slaType . '_due';
        return isset($this->{$dueField}) && Carbon::now()->lt($this->{$dueField});
    }

    public function getAttachmentsFullUrlAttribute(): array
    {
        $urls = [];
        foreach ($this->attachments as $attachment) {
            $urls[] = $this->storageLink('warranty/attachments', $attachment->file_path, 'public');
        }
        return $urls;
    }

    public function scopeOpen($query)
    {
        return $query->whereNotIn('status', ['closed', 'rejected']);
    }
     public function product()
    {
        return $this->hasOneThrough(
            Product::class,
            Warranty::class,
            'id',       
            'id',        
            'product_id'  
        );
    }
}
