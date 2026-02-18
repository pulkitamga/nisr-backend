<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DealActivity extends Model
{
    use HasFactory;

    protected $table = 'deal_activities';

    protected $fillable = [
        'deal_id',
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

    public function deal()
    {
        return $this->belongsTo(Deal::class, 'deal_id');
    }

    public function employee()
    {
        return $this->belongsTo(Admin::class, 'employee_id');
    }
}
