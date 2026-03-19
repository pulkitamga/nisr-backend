<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * HasActivityLog Trait
 *
 * Provides standard activity logging relationships for models that track
 * activities, notes, tasks, calls, and files.
 *
 * Usage:
 * 1. Add use HasActivityLog; to your model
 * 2. Define getActivityLogConfig() method returning:
 *    [
 *        'prefix' => 'lead',  // Used for model names: LeadActivity, LeadNote, etc.
 *        'foreign_key' => 'lead_id'  // Foreign key column name
 *    ]
 */
trait HasActivityLog
{
    /**
     * Get the configuration for activity logging relationships.
     * Must be implemented in the using model.
     *
     * @return array{prefix: string, foreign_key: string}
     */
    abstract protected function getActivityLogConfig(): array;

    /**
     * Get the prefix for activity model names (e.g., 'lead', 'deal').
     */
    protected function getActivityLogPrefix(): string
    {
        return $this->getActivityLogConfig()['prefix'];
    }

    /**
     * Get the foreign key for activity relationships.
     */
    protected function getActivityLogForeignKey(): string
    {
        return $this->getActivityLogConfig()['foreign_key'];
    }

    /**
     * Get the fully qualified class name for an activity model.
     */
    protected function getActivityModelClass(string $type): string
    {
        $prefix = $this->getActivityLogPrefix();
        $className = match($type) {
            'activity' => ucfirst($prefix) . 'Activity',
            'note' => ucfirst($prefix) . 'Note',
            'task' => ucfirst($prefix) . 'Task',
            'call' => ucfirst($prefix) . 'Call',
            'file' => ucfirst($prefix) . 'File',
            default => throw new \InvalidArgumentException("Unknown activity type: {$type}"),
        };

        return "App\\Models\\{$className}";
    }

    /**
     * Get all activities for this entity.
     */
    public function activities(): HasMany
    {
        $class = $this->getActivityModelClass('activity');
        return $this->hasMany($class, $this->getActivityLogForeignKey());
    }

    /**
     * Get all notes for this entity.
     */
    public function notes(): HasMany
    {
        $class = $this->getActivityModelClass('note');
        return $this->hasMany($class, $this->getActivityLogForeignKey());
    }

    /**
     * Get all tasks for this entity.
     */
    public function tasks(): HasMany
    {
        $class = $this->getActivityModelClass('task');
        return $this->hasMany($class, $this->getActivityLogForeignKey());
    }

    /**
     * Get all calls for this entity.
     */
    public function calls(): HasMany
    {
        $class = $this->getActivityModelClass('call');
        return $this->hasMany($class, $this->getActivityLogForeignKey());
    }

    /**
     * Get all files for this entity.
     */
    public function files(): HasMany
    {
        $class = $this->getActivityModelClass('file');
        return $this->hasMany($class, $this->getActivityLogForeignKey());
    }

    /**
     * Get recent activities for this entity.
     */
    public function recentActivities(int $limit = 10): HasMany
    {
        return $this->activities()->latest()->limit($limit);
    }

    /**
     * Get pending tasks for this entity.
     */
    public function pendingTasks(): HasMany
    {
        return $this->tasks()->where('status', 'pending');
    }

    /**
     * Get completed tasks for this entity.
     */
    public function completedTasks(): HasMany
    {
        return $this->tasks()->where('status', 'completed');
    }
}
