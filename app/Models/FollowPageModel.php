<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowPageModel extends Model
{
    use HasFactory;


    protected $fillable = [
        'platform',
        'username',
        'link',
        'is_active',
    ];
}
