<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * careerApplies Model - Represents a job application
 *
 * @property int $id
 * @property int $job_id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $phone
 * @property string $gender
 * @property string $country
 * @property string $state
 * @property string $city
 * @property string $area
 * @property string $notice_period
 * @property string $last_ctc
 * @property string $resume
 */
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

    protected $casts = [
        'job_id' => 'integer',
    ];

    /**
     * The job that this application belongs to
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(CareerJob::class, 'job_id');
    }

    /**
     * Get the applicant's full name
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Scope to filter applications by job
     */
    public function scopeForJob($query, $jobId)
    {
        return $query->where('job_id', $jobId);
    }

    /**
     * Scope to search by email
     */
    public function scopeByEmail($query, $email)
    {
        return $query->where('email', $email);
    }
}
