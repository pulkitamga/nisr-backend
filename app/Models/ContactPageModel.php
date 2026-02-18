<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactPageModel extends Model
{
    use HasFactory;


    protected $table = 'contact_us';  // Explicitly set table name

    protected $fillable = [
        'phone',
        'email',
        'location',
        'is_active',
    ];
}
