<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DealTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'deal_id',
        'employee_id',
        'department_id',
        'name',
        'description',
        'due_date',
        'status',
    ];

    public function deal()
    {
        return $this->belongsTo(Deal::class, 'deal_id');
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
