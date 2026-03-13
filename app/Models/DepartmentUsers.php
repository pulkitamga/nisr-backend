<?php

namespace App\Models;

use App\Support\AdminPermissionRegistry;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class DepartmentUsers extends Model
{
    use HasFactory, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'department_id',
        'status'
    ];

    protected $guard_name = 'admin';

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the department associated with the user.
     */
    public function departments()
    {
        return $this->belongsTo(Departments::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeInDepartment(Builder $query, int|string|null $departmentId): Builder
    {
        if (empty($departmentId)) {
            return $query;
        }

        return $query->where('department_id', (int)$departmentId);
    }

    public function scopeWithRole(Builder $query, string|array|null $roleName): Builder
    {
        if (empty($roleName)) {
            return $query;
        }

        $roles = is_array($roleName) ? array_values(array_filter($roleName)) : [$roleName];
        if (count($roles) === 0) {
            return $query;
        }

        return $query->whereHas('roles', function ($roleQuery) use ($roles) {
            $roleQuery
                ->where('guard_name', AdminPermissionRegistry::guard())
                ->whereIn('name', $roles);
        });
    }
}
