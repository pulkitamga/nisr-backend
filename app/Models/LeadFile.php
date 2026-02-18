<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadFile extends Model
{
    protected $table = 'lead_file';

    protected $fillable = [
        'lead_id',
        'employee_id',
        'file',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function employee()
    {
        return $this->belongsTo(Admin::class, 'employee_id');
    }
}