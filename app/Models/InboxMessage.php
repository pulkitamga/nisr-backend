<?php

namespace App\Models;

use App\Services\Crm\ContactResolver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * InboxMessage Model - Represents a CRM inbox message
 *
 * @property int $id
 * @property string $subject
 * @property string $body
 * @property int $contact_id
 * @property string $sender_name
 * @property string $sender_email
 * @property string $sender_phone
 * @property string $pipeline
 * @property string $message_type
 * @property int $source_id
 * @property int $related_lead_id
 * @property int $related_ticket_id
 * @property int $related_warranty_id
 * @property string $status
 * @property float $spam_score
 * @property int $owner_id
 * @property int $department_id
 * @property int $employee_id
 * @property string $priority
 * @property string $attachment
 * @property string $reply
 * @property \Carbon\Carbon $follow_up_date
 * @property string $convert_type
 * @property string $convert_sub_type
 * @property string $message
 * @property \Carbon\Carbon $response_due
 * @property \Carbon\Carbon $resolution_due
 * @property \Carbon\Carbon $first_response_at
 * @property string $escalation_level
 * @property \Carbon\Carbon $escalated_at
 * @property int $escalated_by
 * @property \Carbon\Carbon $sla_paused_at
 */
class InboxMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'subject',
        'body',
        'contact_id',
        'sender_name',
        'sender_email',
        'sender_phone',
        'pipeline',
        'message_type',
        'source_id',
        'related_lead_id',
        'related_ticket_id',
        'related_warranty_id',
        'details',
        'status',
        'spam_score',
        'owner_id',
        'department_id',
        'employee_id',
        'priority',
        'attachment',
        'reply',
        'follow_up_date',
        'convert_type',
        'convert_sub_type',
        'message',
        'response_due',
        'resolution_due',
        'first_response_at',
        'escalation_level',
        'escalated_at',
        'escalated_by',
        'sla_paused_at',
    ];

    protected $casts = [
        'details' => 'array',
        'spam_score' => 'float',
        'response_due' => 'datetime',
        'resolution_due' => 'datetime',
        'first_response_at' => 'datetime',
        'escalated_at' => 'datetime',
        'sla_paused_at' => 'datetime',
        'follow_up_date' => 'datetime',
    ];

    /**
     * Boot the model.
     * Uses ContactResolver service to resolve contact_id automatically.
     */
    protected static function booted()
    {
        static::saving(function ($message) {
            if (empty($message->contact_id)) {
                $resolver = app(ContactResolver::class);
                $resolver->setContactOnMessage($message);
            }
        });
    }

    // Relationships

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'related_lead_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'contact_id');
    }

    public function department()
    {
        return $this->belongsTo(Departments::class, 'department_id');
    }

    public function employee()
    {
        return $this->belongsTo(Admin::class, 'employee_id');
    }

    public function owner()
    {
        return $this->belongsTo(Admin::class, 'owner_id');
    }

    public function activities()
    {
        return $this->hasMany(InboxActivities::class, 'message_id');
    }

    public function notes()
    {
        return $this->hasMany(InboxNote::class, 'message_id');
    }

    public function tasks()
    {
        return $this->hasMany(InboxTask::class, 'message_id');
    }

    public function calls()
    {
        return $this->hasMany(InboxCall::class, 'message_id');
    }

    public function files()
    {
        return $this->hasMany(InboxFile::class, 'message_id');
    }

    // Scopes

    /**
     * Scope to filter messages by status.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get messages for a specific owner.
     */
    public function scopeForOwner($query, int $ownerId)
    {
        return $query->where('owner_id', $ownerId);
    }

    /**
     * Scope to get messages for a specific department.
     */
    public function scopeForDepartment($query, int $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Scope to get messages by message type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('message_type', $type);
    }

    /**
     * Scope to get unread messages.
     */
    public function scopeUnread($query)
    {
        return $query->where('status', 'new');
    }

    /**
     * Scope to get high priority messages.
     */
    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['high', 'urgent']);
    }

    // Accessors

    /**
     * Check if message is unread.
     */
    public function getIsUnreadAttribute(): bool
    {
        return $this->status === 'new';
    }

    /**
     * Check if message is high priority.
     */
    public function getIsHighPriorityAttribute(): bool
    {
        return in_array($this->priority, ['high', 'urgent']);
    }

    /**
     * Check if message is overdue for response.
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->response_due?->isPast() ?? false
            && is_null($this->first_response_at);
    }

    /**
     * Get the sender's display name.
     */
    public function getSenderDisplayNameAttribute(): string
    {
        if (!empty($this->sender_name)) {
            return $this->sender_name;
        }

        if (!empty($this->sender_email)) {
            return $this->sender_email;
        }

        return $this->user?->name ?? 'Unknown';
    }

    /**
     * Get related entity (lead, ticket, or warranty).
     */
    public function getRelatedEntityAttribute(): ?Model
    {
        if ($this->related_lead_id) {
            return $this->lead;
        }

        if ($this->related_ticket_id) {
            return $this->ticket()->first();
        }

        if ($this->related_warranty_id) {
            return $this->warranty()->first();
        }

        return null;
    }

    /**
     * Relationship to support ticket (if any).
     */
    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'related_ticket_id');
    }

    /**
     * Relationship to warranty (if any).
     */
    public function warranty()
    {
        return $this->belongsTo(Warranty::class, 'related_warranty_id');
    }
}
