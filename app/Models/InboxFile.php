<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InboxFile extends Model
{
    use HasFactory;

      protected $fillable = [
        'massage_id',
        'employee_id',
        'file',
    ];

   public function massage()
    {
        return $this->belongsTo(InboxMessage::class, 'massage_id');
    }

    public function employee()
    {
        return $this->belongsTo(Admin::class, 'employee_id');
    }
}
