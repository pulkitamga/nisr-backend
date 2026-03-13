<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;


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

    protected $dates = ['deleted_at'];

    protected $casts = [
        'details' => 'array',
    ];
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
        return $this->hasMany(InboxActivities::class, 'massage_id');
    }

    public function notes()
    {
        return $this->hasMany(InboxNote::class, 'massage_id');
    }

    public function tasks()
    {
        return $this->hasMany(InboxTask::class, 'massage_id');
    }

    public function calls()
    {
        return $this->hasMany(InboxCall::class, 'massage_id');
    }

    public function files()
    {
        return $this->hasMany(InboxFile::class, 'massage_id');
    }

    protected static function booted()
    {
        static::saving(function ($message) {
            if (empty($message->contact_id)) {
                if (Auth::guard('customer')->check()) {
                    $message->contact_id = Auth::guard('customer')->id();
                } elseif (Auth::guard('seller')->check()) {
                    $message->contact_id = Auth::guard('seller')->id();
                }
                if (empty($message->contact_id)) {
                    $user = null;

                    if (!empty($message->sender_email)) {
                        $user = \App\User::where('email', $message->sender_email)->first();
                    }

                    if (!$user && !empty($message->sender_phone)) {
                        $user = \App\User::where('phone', $message->sender_phone)->first();
                    }

                    if ($user) {
                        $message->contact_id = $user->id;
                    }
                }
            }
        });
    }
}
