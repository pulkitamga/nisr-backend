<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory;

    protected $table = 'states';

    // Mass assignable attributes
    protected $fillable = [
        'name',
        'country',
    ];


    public function cities()
    {
        return $this->hasMany(City::class);
    }
}
