<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    // Define the table name if needed
    protected $table = 'cities';

    // Mass assignable attributes
    protected $fillable = [
        'name',
        'state_id',
    ];

    /**
     * A city belongs to a state.
     */
    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function areas()
    {
        return $this->hasMany(Area::class, 'city_id');
    }
}
