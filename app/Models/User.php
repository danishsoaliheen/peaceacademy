<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Role Helpers
    |--------------------------------------------------------------------------
    */

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isAccountant(): bool
    {
        return in_array($this->role, ['admin', 'accountant']);
    }

    public function hasRole(string|array $roles): bool
    {
        return in_array($this->role, (array) $roles);
    }

    /*
    |--------------------------------------------------------------------------
    | Permission Relationship
    |--------------------------------------------------------------------------
    */

    public function rolePermissions(): HasMany
    {
        return $this->hasMany(
            RolePermission::class,
            'role',
            'role'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Permission Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Check whether the user has a specific permission.
     *
     * Administrators automatically have every permission.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->rolePermissions()
            ->whereHas('permission', function ($query) use ($permission) {
                $query->where('name', $permission);
            })
            ->exists();
    }

    /**
     * Check whether the user has at least one of the supplied permissions.
     */
    public function hasAnyPermission(array|string $permissions): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        foreach ((array) $permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check whether the user has all supplied permissions.
     */
    public function hasAllPermissions(array|string $permissions): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        foreach ((array) $permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }
}