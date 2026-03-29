<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory, HasTranslations;

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
