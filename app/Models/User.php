<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'is_active',
        'all_branches',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'all_branches' => 'boolean',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'branch_user');
    }

    public function isSuperAdmin(): bool
    {
        return $this->roles->contains('slug', 'super_admin');
    }

    public function hasRole(string|array $roles): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $roleList = (array) $roles;

        return $this->roles->whereIn('slug', $roleList)->isNotEmpty();
    }

    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        foreach ($this->roles as $role) {
            if ($role->permissions->contains('slug', $permissionSlug)) {
                return true;
            }
        }

        return false;
    }

    public function canAccessBranch(int|Branch $branch): bool
    {
        if ($this->isSuperAdmin() || $this->all_branches) {
            return true;
        }

        $branchId = $branch instanceof Branch ? $branch->id : $branch;

        return $this->branches->contains('id', $branchId);
    }

    /**
     * Return accessible branch IDs for scoping queries.
     * Returns null if user has access to all branches.
     *
     * @return array<int>|null
     */
    public function accessibleBranchIds(): ?array
    {
        if ($this->isSuperAdmin() || $this->all_branches) {
            return null;
        }

        return $this->branches->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    /**
     * Determine initial dashboard target route after login based on user roles.
     */
    public function preferredDashboardRoute(): string
    {
        if ($this->isSuperAdmin()) {
            return route('admin.dashboard');
        }

        if ($this->hasPermission('franchises.view') && ! $this->hasPermission('branch_menu.manage') && ! $this->hasPermission('products.manage')) {
            return route('admin.franchises.index');
        }

        if ($this->hasPermission('feedbacks.view') && ! $this->hasPermission('branch_menu.manage') && ! $this->hasPermission('products.manage')) {
            return route('admin.feedbacks.index');
        }

        if ($this->hasPermission('prices.bulk_update') && ! $this->hasPermission('products.manage')) {
            return route('admin.pricing.index');
        }

        return route('admin.dashboard');
    }
}
