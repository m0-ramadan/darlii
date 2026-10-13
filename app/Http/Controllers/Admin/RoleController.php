<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminPanel;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Support\PermissionCatalog;
use App\Services\Admin\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    use InteractsWithAdminPanel;

    public function index()
    {
        $this->authorizePermission('roles.manage');

        $roles = Role::query()
            ->with('permissions')
            ->withCount('users')
            ->orderByDesc('level')
            ->get();

        $permissions = Permission::query()
            ->orderBy('group')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('group');

        return view('admin.roles.index', [
            'roles' => $roles,
            'permissionGroups' => $permissions,
            'groupLabels' => PermissionCatalog::groups(),
            'title' => 'الأدوار والصلاحيات',
            'subtitle' => 'تحكم كامل في صلاحيات كل دور — الأدمن له كل الصلاحيات، والمسؤول صلاحيات محدودة، والسمسار أقلها',
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizePermission('roles.manage');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'label_ar' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:100', 'alpha_dash', Rule::unique('roles', 'slug')],
            'level' => ['nullable', 'integer', 'min:1', 'max:99'],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role = Role::query()->create([
            'name' => $data['name'],
            'slug' => $data['slug'] ?: Str::slug($data['name'], '-'),
            'label_ar' => $data['label_ar'],
            'description' => $data['description'] ?? null,
            'level' => $data['level'] ?? 20,
            'is_system' => false,
        ]);

        $role->permissions()->sync($data['permissions'] ?? []);
        AuditLogger::log('role.created', $role, [], ['slug' => $role->slug, 'permissions' => count($data['permissions'] ?? [])]);

        return back()->with('success', 'تم إنشاء الدور "' . $role->label_ar . '" بنجاح.');
    }

    public function show(Role $role)
    {
        $this->authorizePermission('roles.manage');

        return view('admin.roles.show', [
            'role' => $role->load(['permissions', 'users.roles']),
            'permissionGroups' => Permission::query()->orderBy('group')->orderBy('sort_order')->get()->groupBy('group'),
            'groupLabels' => PermissionCatalog::groups(),
            'title' => 'الدور: ' . $role->label_ar,
            'subtitle' => $role->description,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $this->authorizePermission('roles.manage');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'label_ar' => ['required', 'string', 'max:100'],
            'level' => ['nullable', 'integer', 'min:1', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        if ($role->slug === 'super-admin') {
            $data['level'] = 100;
        }

        $role->update($data);
        AuditLogger::log('role.updated', $role, [], $data);

        return back()->with('success', 'تم تحديث الدور.');
    }

    public function destroy(Role $role)
    {
        $this->authorizePermission('roles.manage');

        abort_if($role->is_system, 403, 'لا يمكن حذف أدوار النظام الأساسية.');
        abort_if($role->users()->exists(), 422, 'لا يمكن حذف دور مرتبط بمستخدمين. انقل المستخدمين لدور آخر أولاً.');

        AuditLogger::log('role.deleted', $role, ['slug' => $role->slug]);
        $role->permissions()->detach();
        $role->delete();

        return back()->with('success', 'تم حذف الدور.');
    }

    /** تحديث مصفوفة صلاحيات دور واحد. */
    public function syncPermissions(Request $request, Role $role)
    {
        $this->authorizePermission('roles.manage');

        $data = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        // حماية: لا يمكن تقليص صلاحيات السوبر أدمن أو الأدمن.
        if (in_array($role->slug, ['super-admin', 'admin'], true) && count($data['permissions'] ?? []) < Permission::count()) {
            return back()->with('error', 'لا يمكن تقليص صلاحيات ' . $role->label_ar . ' — هذا الدور يملك كل الصلاحيات بحكم التصميم.');
        }

        $role->permissions()->sync($data['permissions'] ?? []);
        AuditLogger::log('role.permissions_synced', $role, [], ['count' => count($data['permissions'] ?? [])]);

        return back()->with('success', 'تم تحديث صلاحيات "' . $role->label_ar . '" (' . count($data['permissions'] ?? []) . ' صلاحية).');
    }
}
