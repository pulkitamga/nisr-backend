<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InboxTask extends Model
{
    use HasFactory;

      protected $fillable = [
        'message_id',
        'employee_id',
        'department_id',
        'name',
        'description',
        'due_date',
        'status',
    ];

    public function message()
    {
        return $this->belongsTo(InboxMessage::class, 'message_id');
    }
    public function employee()
    {
        return $this->belongsTo(Admin::class, 'employee_id');
    }

    public function department()
    {
        return $this->belongsTo(Departments::class, 'department_id');
    }
}

