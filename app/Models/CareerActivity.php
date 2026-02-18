<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CareerActivity extends Model
{
    use SoftDeletes;

    protected $table = 'career_activities';

    protected $fillable = [
        'ticket_id',
        'activity_type',
        'description',
        'attachments',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'attachments' => 'array',
    ];

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }
}