<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'from_user_id',
        'type',
        'title',
        'message',
        'related_id',
        'status',
        'is_active',
    ];

    public function user()
    {
        return $this->belongsTo(Admin::class, 'user_id');
    }

    public function fromUser()
    {
        return $this->belongsTo(Admin::class, 'from_user_id');
    }
}
