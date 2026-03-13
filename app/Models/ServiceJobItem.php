<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceJobItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'service_job_items';

    protected $fillable = [
        'job_id',
        'item_type',
        'item_id',
        'item_name',
        'quantity',
        'rate',
        'total',
    ];

    protected $casts = [
        'quantity' => 'float',
        'rate' => 'float',
        'total' => 'float',
    ];

    public function job()
    {
        return $this->belongsTo(ServiceJob::class, 'job_id');
    }
}