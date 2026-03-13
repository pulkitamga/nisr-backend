<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DealCall extends Model
{
    use HasFactory;

        protected $fillable = [
        'deal_id',
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
