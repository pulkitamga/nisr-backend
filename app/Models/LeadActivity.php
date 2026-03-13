<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadActivity extends Model
{
    protected $table = 'lead_activity';

    protected $fillable = [
        'lead_id',
        'employee_id',
        'activity_type',
        'details',
        'title',
        'subject',
        'note_date',
    ];

    protected $casts = [
        'details' => 'array', 
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
