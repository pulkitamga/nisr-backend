<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Departments extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'head_id',
        'status',
    ];

    /**
     * Get the users associated with the department.
     */
    public function users()
    {
        return $this->hasMany(DepartmentUsers::class, 'department_id', 'id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Admin::class,'head_id');
    }
      public function inboxMessages()
    {
        return $this->hasMany(InboxMessage::class, 'department_id');
    }
}
