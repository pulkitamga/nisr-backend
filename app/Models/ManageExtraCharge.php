<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManageExtraCharge extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'manage_extra_charges';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'type',
        'category_id',
        'charges',
        'status'
    ];

    /**
     * Relationship with the Category model.
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
