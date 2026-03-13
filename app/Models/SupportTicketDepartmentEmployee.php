<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTicketDepartmentEmployee extends Model
{
    use HasFactory;

    protected $table = 'support_ticket_department_employee';

    protected $fillable = [
        'ticket_id',
        'department_id',
        'employee_id',
        'status_id',
        'status_type_id',
        'created_by',
        'status'
    ];

    /**
     * Get the ticket associated with this record.
     */
    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    /**
     * Get the department associated with this record.
     */
    public function department()
    {
        return $this->belongsTo(Departments::class, 'department_id');
    }

    /**
     * Get the employee (admin) associated with this record.
     */
    public function employee()
    {
        return $this->belongsTo(Admin::class, 'employee_id');
    }

    public function status()
    {
        return $this->belongsTo(SupportTicketStatusMaster::class, 'status_id');
    }

    public function status_type()
    {
        return $this->belongsTo(SupportTicketStatusMaster::class, 'status_type_id');
    }
}