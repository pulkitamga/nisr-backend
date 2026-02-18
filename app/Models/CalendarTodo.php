<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarTodo extends Model
{
    use HasFactory;

    protected $fillable = ['employee_id', 'date', 'note'];

    public function employee()
    {
        return $this->belongsTo(Admin::class, 'employee_id');
    }
}
