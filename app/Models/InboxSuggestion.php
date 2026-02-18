<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InboxSuggestion extends Model
{
    protected $fillable = ['inbox_message_id', 'user_id', 'status'];

    public function inboxMessage()
    {
        return $this->belongsTo(InboxMessage::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
