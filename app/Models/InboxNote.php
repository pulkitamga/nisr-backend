<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InboxNote extends Model
{
    use HasFactory;

     protected $fillable = [
        'message_id',
        'employee_id',
        'note',
        'noted_at',
    ];

   public function message()
    {
        return $this->belongsTo(InboxMessage::class, 'message_id');
    }

    public function employee()
    {
        return $this->belongsTo(Admin::class, 'employee_id');
    }
}
