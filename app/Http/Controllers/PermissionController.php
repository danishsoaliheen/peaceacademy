<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\RolePermission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Editable roles
    |--------------------------------------------------------------------------
    |
    | "admin" is deliberately excluded — App\Models\User::isAdmin() already
    | bypasses every permission check (see AppServiceProvider's Gate::before
    | hook), so an admin's access can't be reduced or changed from this
    | screen. Only the roles that actually consult the role_permissions
    | table are editable here.
    |--------------------------------------------------------------------------
    */
    private const EDITABLE_ROLES = [
        'accountant' => 'Accountant',
        'viewer'     => 'Viewer',
    ];

    /*
    |--------------------------------------------------------------------------
    | VIEW — permission matrix
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $permissions = Permission::orderBy('group')
            ->orderBy('name')
            ->get()
            ->groupBy('group');

        $roles = self::EDITABLE_ROLES;

        // role => [permission_id, permission_id, ...]
        $assignedMap = RolePermission::whereIn('role', array_keys($roles))
            ->get()
            ->groupBy('role')
            ->map(fn ($rows) => $rows->pluck('permission_id')->all());

        // Make sure every role key exists even if it has zero permissions yet
        foreach (array_keys($roles) as $roleKey) {
            $assignedMap[$roleKey] = $assignedMap[$roleKey] ?? [];
        }

        return view('permissions.index', compact('permissions', 'roles', 'assignedMap'));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE — save the full matrix in one go
    |--------------------------------------------------------------------------
    |
    | Expects: roles[accountant][] = permission_id, roles[viewer][] = permission_id
    | Any role present in EDITABLE_ROLES but missing/empty from the submitted
    | payload is treated as "no permissions selected" (an unchecked checkbox
    | sends nothing), not "leave untouched" — this matches how the matrix
    | checkboxes behave on screen.
    |--------------------------------------------------------------------------
    */
    public function update(Request $request)
    {
        $request->validate([
            'roles'                  => 'nullable|array',
            'roles.*'                => 'array',
            'roles.*.*'              => 'integer|exists:permissions,id',
        ]);

        $submitted = $request->input('roles', []);

        foreach (self::EDITABLE_ROLES as $roleKey => $label) {

            $selectedIds = array_map('intval', $submitted[$roleKey] ?? []);

            // Remove anything no longer selected for this role
            RolePermission::where('role', $roleKey)
                ->when(
                    !empty($selectedIds),
                    fn ($q) => $q->whereNotIn('permission_id', $selectedIds),
                    fn ($q) => $q // no selections at all — delete everything for this role
                )
                ->delete();

            // Add anything newly selected
            foreach ($selectedIds as $permissionId) {
                RolePermission::firstOrCreate([
                    'role'          => $roleKey,
                    'permission_id' => $permissionId,
                ]);
            }
        }

        return redirect()
            ->route('permissions.index')
            ->with('success', 'Role permissions updated successfully.');
    }
}