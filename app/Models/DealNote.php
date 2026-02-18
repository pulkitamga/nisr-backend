<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DealNote extends Model
{
    use HasFactory;

      protected $fillable = [
        'deal_id',
        'employee_id',
        'note',
        'noted_at',
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
