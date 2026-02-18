<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadNote extends Model
{
    protected $table = 'lead_note';

    protected $fillable = [
        'lead_id',
        'employee_id',
        'note',
        'noted_at',
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