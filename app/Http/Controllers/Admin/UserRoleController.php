<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\TurkishSlug;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class UserRoleController extends Controller
{
    public function usersIndex(): View
    {
        $users = User::with(['roles', 'branches'])->orderBy('id')->get();
        $roles = Role::orderBy('id')->get();
        $branches = Branch::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles', 'branches'));
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', PasswordRule::min(8)],
            'role_ids' => ['required', 'array', 'min:1'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
            'branch_ids' => ['nullable', 'array'],
            'branch_ids.*' => ['integer', 'exists:branches,id'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'is_active' => $request->boolean('is_active', true),
            'all_branches' => $request->boolean('all_branches', false),
        ]);

        $user->roles()->sync($validated['role_ids']);
        $user->branches()->sync($validated['branch_ids'] ?? []);

        AuditLogger::record(
            actionType: 'user.created',
            description: "Yeni kullanıcı oluşturuldu: {$user->name} ({$user->email})",
            auditable: $user,
            newValues: [
                'email' => $user->email,
                'role_ids' => $validated['role_ids'],
                'branch_ids' => $validated['branch_ids'] ?? [],
                'all_branches' => $user->all_branches,
            ]
        );

        return back()->with('status', "{$user->name} kullanıcısı başarıyla oluşturuldu.");
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', PasswordRule::min(8)],
            'role_ids' => ['required', 'array', 'min:1'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
            'branch_ids' => ['nullable', 'array'],
            'branch_ids.*' => ['integer', 'exists:branches,id'],
        ]);

        $oldValues = [
            'name' => $user->name,
            'email' => $user->email,
            'is_active' => $user->is_active,
            'all_branches' => $user->all_branches,
            'role_ids' => $user->roles->pluck('id')->all(),
            'branch_ids' => $user->branches->pluck('id')->all(),
        ];

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;
        $user->is_active = $request->boolean('is_active');
        $user->all_branches = $request->boolean('all_branches');

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();
        $user->roles()->sync($validated['role_ids']);
        $user->branches()->sync($validated['branch_ids'] ?? []);

        AuditLogger::record(
            actionType: 'user.updated',
            description: "{$user->name} ({$user->email}) kullanıcı yetki/şube bilgileri güncellendi.",
            auditable: $user,
            oldValues: $oldValues,
            newValues: [
                'name' => $user->name,
                'email' => $user->email,
                'is_active' => $user->is_active,
                'all_branches' => $user->all_branches,
                'role_ids' => $validated['role_ids'],
                'branch_ids' => $validated['branch_ids'] ?? [],
            ]
        );

        return back()->with('status', "{$user->name} bilgileri güncellendi.");
    }

    public function rolesIndex(): View
    {
        $roles = Role::with('permissions')->withCount('users')->orderBy('id')->get();
        $permissionGroups = Permission::orderBy('group_name')->orderBy('id')->get()->groupBy('group_name');

        return view('admin.roles.index', compact('roles', 'permissionGroups'));
    }

    public function storeRole(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        $slug = TurkishSlug::make($validated['name']);
        if (Role::where('slug', $slug)->exists()) {
            $slug .= '-'.rand(10, 99);
        }

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => str_replace('-', '_', $slug),
            'description' => $validated['description'] ?? null,
            'is_system' => false,
        ]);

        $role->permissions()->sync($validated['permission_ids'] ?? []);

        AuditLogger::record(
            actionType: 'role.created',
            description: "Yeni rol tanımlandı: {$role->name}",
            auditable: $role,
            newValues: ['permissions' => $validated['permission_ids'] ?? []]
        );

        return back()->with('status', "{$role->name} rolü ve izinleri oluşturuldu.");
    }

    public function updateRole(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        $oldPerms = $role->permissions->pluck('slug')->all();

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        if ($role->slug !== 'super_admin') {
            $role->permissions()->sync($validated['permission_ids'] ?? []);
        }

        AuditLogger::record(
            actionType: 'role.updated',
            description: "{$role->name} rolünün izin matrisi güncellendi.",
            auditable: $role,
            oldValues: ['permissions' => $oldPerms],
            newValues: ['permission_ids' => $validated['permission_ids'] ?? []]
        );

        return back()->with('status', "{$role->name} rolü izinleri güncellendi.");
    }
}
