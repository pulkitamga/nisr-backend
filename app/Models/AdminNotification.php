<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class AdminNotification extends Model
{
    use HasFactory;

    protected $table = 'admin_notifications';

    protected $fillable = [
        'notifiable_id',
        'notifiable_type',
        'notification_for', // 1=employee, 2=department, 3=customer
        'employee_id',
        'department_id',
        'customer_id',
        'title',
        'message',
        'link',
        'status',      // 0=unread, 1=read
        'is_active',
    ];

    protected $casts = [
        'status' => 'integer',
        'is_active' => 'boolean',
    ];

    /* -------------------------------------------------
     *  Relationships
     * ------------------------------------------------*/
    public function notifiable()
    {
        return $this->morphTo();
    }

    /** Employee (admin) */
    public function employee()
    {
        return $this->belongsTo(Admin::class, 'employee_id');
    }

    /** Department */
    public function department()
    {
        return $this->belongsTo(Departments::class, 'department_id');
    }

    /** Customer */
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id'); 
    }

    /* -------------------------------------------------
     *  Scopes
     * ------------------------------------------------*/
    public function scopeForEmployee(Builder $query, int $employeeId)
    {
        return $query->where('notification_for', 1)->where('employee_id', $employeeId);
    }

    public function scopeForDepartment(Builder $query, int $departmentId)
    {
        return $query->where('notification_for', 2)->where('department_id', $departmentId);
    }

    public function scopeForCustomer(Builder $query, int $customerId)
    {
        return $query->where('notification_for', 3)->where('customer_id', $customerId);
    }

    public function scopeUnread(Builder $query)
    {
        return $query->where('status', 0);
    }
}