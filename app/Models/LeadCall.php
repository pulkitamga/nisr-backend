<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadCall extends Model
{
    protected $table = 'lead_call';

    protected $fillable = [
        'lead_id',
        'employee_id',
        'department_id',
        'title',
        'from',
        'to',
        'guests',
        'location',
        'description',
    ];


    protected $casts = [
        'from' => 'datetime',
        'to' => 'datetime',
    ];
    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
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
