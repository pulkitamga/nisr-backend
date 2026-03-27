<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * CareerJob Model - Represents a job posting
 *
 * @property int $id
 * @property string $title
 * @property string $location
 * @property string $experience
 * @property string $skills
 * @property string $job_description
 * @property bool $is_active
 */
class CareerJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'location',
        'experience',
        'skills',
        'job_description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'skills' => 'array', // Stored as JSON, can be decoded to array
    ];

    /**
     * Get all applications for this job
     */
    public function applications(): HasMany
    {
        return $this->hasMany(careerApplies::class, 'job_id');
    }

    /**
     * Get translations for this job
     */
    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    /**
     * Scope to filter only active jobs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get skills as array
     */
    public function getSkillsList(): array
    {
        // Skills may be stored as comma-separated or JSON
        if (is_array($this->skills)) {
            return $this->skills;
        }

        return array_map('trim', explode(',', $this->skills ?? ''));
    }

    /**
     * Count of applications for this job
     */
    public function getApplicationCountAttribute(): int
    {
        return $this->applications()->count();
    }
}
