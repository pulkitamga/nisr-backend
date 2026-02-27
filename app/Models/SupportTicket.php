<?php

namespace App\Models;

use App\Traits\StorageTrait;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

class SupportTicket extends Model
{
    use StorageTrait;

    protected $fillable = [
        'request_type',
        'customer_id',
        'company_id',
        'source_id',
        'owner_id',
        'subject',
        'type',
        'sub_type',
        'priority',
        'description',
        'reply',
        'status',
        'department_id',
        'escalation_level',
        'escalated_at',
        'escalated_by',
        'reopen_count',
        'created_at',
        'updated_at',
        'attachment',
        'sla_hours',
        'sla_paused_at',
        'closed_at',
        'response_due',
        'resolution_due',
        'first_response_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'customer_id' => 'integer',
        'company_id' => 'integer',
        'source_id' => 'integer',
        'owner_id' => 'integer',
        'priority' => 'string',
        'status' => 'string',
        'escalation_level' => 'string',
        'escalated_at' => 'datetime',
        'escalated_by' => 'integer',
        'reopen_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'attachment' => 'array',
        'sla_hours' => 'integer',
        'sla_paused_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    protected $appends = ['attachment_full_url'];

    public function conversations(): HasMany
    {
        return $this->hasMany(SupportTicketConv::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(WholesalerBusiness::class, 'company_id');
    }

    public function relatedInboxMessages(): HasMany
    {
        return $this->hasMany(InboxMessage::class, 'related_ticket_id');
    }
    public function relatedInboxMessage(): hasOne
    {
        return $this->hasOne(InboxMessage::class, 'related_ticket_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'owner_id');
    }

    public function escalatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'escalated_by');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Departments::class, 'department_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'employee_id');
    }

    public function status_details(): BelongsTo
    {
        return $this->belongsTo(SupportTicketStatusMaster::class, 'status');
    }

    public function serviceJobs(): HasMany
    {
        return $this->hasMany(ServiceJob::class, 'ticket_id');
    }

    public function supportActivities(): HasMany
    {
        return $this->hasMany(SupportTicketActivity::class, 'support_ticket_id')->orderBy('noted_at', 'desc');
    }

    public function latestServiceJob(): HasOne
    {
        return $this->hasOne(ServiceJob::class, 'ticket_id')->latestOfMany();
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(ServiceInvoice::class, 'ticket_id');
    }

    public function estimates(): HasMany
    {
        return $this->hasMany(ServiceEstimate::class, 'ticket_id');
    }

    public function changeOrders(): HasMany
    {
        return $this->hasMany(ServiceChangeOrder::class, 'ticket_id');
    }

    public function cancellations(): HasMany
    {
        return $this->hasMany(ServiceCancellation::class, 'ticket_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ServiceJobActivity::class, 'job_id');
    }

    public function careerInterviews()
    {
        return $this->hasMany(CareerInterview::class, 'ticket_id');
    }

    public function careerActivities()
    {
        return $this->hasMany(CareerActivity::class, 'ticket_id');
    }

    public function careerOffers()
    {
        return $this->hasMany(CareerOffer::class, 'ticket_id');
    }

    public function careerRejections()
    {
        return $this->hasMany(CareerRejection::class, 'ticket_id');
    }

    // In SupportTicket model
    public function careerTalentPool()
    {
        return $this->hasOne(CareerTalentPool::class, 'ticket_id');
    }

    public function escalations(): MorphMany
    {
        return $this->morphMany(Escalation::class, 'escalatable')->latest('id');
    }

    // 🔹 Accessors
    public function getAttachmentFullUrlAttribute(): ?array
    {
        $images = [];
        $value = $this->attachment;
        if ($value) {
            foreach ($value as $item) {
                $item = isset($item['file_name'])
                    ? (array) $item
                    : ['file_name' => $item, 'storage' => 'public'];

                $images[] = $this->storageLink(
                    'support-ticket',
                    $item['file_name'],
                    $item['storage'] ?? 'public'
                );
            }
        }
        return $images;
    }
}
