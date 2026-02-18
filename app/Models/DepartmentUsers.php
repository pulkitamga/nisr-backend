<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentUsers extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'department_id',
        'status'
    ];

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
    /**
     * Get the department associated with the user.
     */
    public function user_role()
    {
        return $this->belongsTo(AdminRole::class, 'user_type');
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin()
    {
        return $this->user_type === 'admin';
    }
}
