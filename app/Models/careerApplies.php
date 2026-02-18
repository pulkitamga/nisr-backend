<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class careerApplies extends Model
{
    use HasFactory, SoftDeletes;


    protected $fillable = [
        'job_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'gender',
        'country',
        'state',
        'city',
        'area',
        'notice_period',
        'last_ctc',
        'resume',
    ];


    public function job()
    {
        return $this->belongsTo(CareerJob::class);
    }
}
