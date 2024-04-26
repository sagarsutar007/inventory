<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',
        'name',
        'email',
        'phone',
        'gender',
        'password',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function role()
    {
        return $this->belongsTo(UserRole::class, 'id', 'user_id');
    }

    public function permissions()
    {
        return $this->hasMany(UserPermissions::class, 'user_id');
    }

    /**
     * Define a method to check if the user has a specific permission.
     *
     * @param string $permissionName The name of the permission to check.
     * @return bool
     */
    public function hasPermission($permissionName)
    {
        $permission = Permission::where('name', $permissionName)->first();

        if ($permission) {
            $permissionId = $permission->id;
            return $this->permissions->contains('permission_id', $permissionId);
        }

        return false;
    }
}
