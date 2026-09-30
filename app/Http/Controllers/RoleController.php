<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with(['permissions', 'users'])->orderBy('id', 'asc')->get();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::all()->groupBy('category');
        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:100|unique:roles,display_name',
            'description' => 'nullable|string',
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ], [
            'display_name.required' => 'يرجى إدخال المسمى العربي للدور الوظيفي.',
            'display_name.unique' => 'هذا الدور الوظيفي مسجل مسبقاً.',
        ]);

        $systemName = Str::slug($validated['display_name'], '_');
        if (Role::where('name', $systemName)->exists()) {
            $systemName .= '_' . time();
        }

        $role = Role::create([
            'name' => $systemName,
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
            'is_system' => false,
        ]);

        if (!empty($validated['permission_ids'])) {
            $role->permissions()->sync($validated['permission_ids']);
        }

        ActivityLogger::log(
            action: 'create',
            module: 'roles',
            description: "تم إنشاء دور وظيفي جديد: '{$role->display_name}' وتحديد صلاحياته",
            recordId: $role->id
        );

        return redirect()->route('roles.index')
            ->with('success', "تم إنشاء الدور الوظيفي '{$role->display_name}' بنجاح.");
    }

    public function edit(Role $role)
    {
        $role->load('permissions');
        $permissions = Permission::all()->groupBy('category');
        $rolePermissionIds = $role->permissions->pluck('id')->toArray();

        return view('roles.edit', compact('role', 'permissions', 'rolePermissionIds'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:100', Rule::unique('roles')->ignore($role->id)],
            'description' => 'nullable|string',
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ], [
            'display_name.required' => 'يرجى إدخال المسمى العربي للدور.',
            'display_name.unique' => 'هذا الدور الوظيفي مسجل مسبقاً.',
        ]);

        $role->update([
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
        ]);

        // Sync permissions
        $permissions = $validated['permission_ids'] ?? [];
        // If admin role, ensure all permissions stay attached
        if ($role->name === 'admin') {
            $permissions = Permission::pluck('id')->toArray();
        }
        $role->permissions()->sync($permissions);

        ActivityLogger::log(
            action: 'update',
            module: 'roles',
            description: "تم تحديث مصفوفة صلاحيات الدور الوظيفي: '{$role->display_name}'",
            recordId: $role->id
        );

        return redirect()->route('roles.index')
            ->with('success', "تم تحديث صلاحيات دور '{$role->display_name}' بنجاح.");
    }

    public function destroy(Role $role)
    {
        if ($role->is_system || $role->name === 'admin') {
            return back()->withErrors(['error' => 'لا يمكن حذف أدوار النظام الأساسية.']);
        }

        if ($role->users()->count() > 0) {
            return back()->withErrors(['error' => 'لا يمكن حذف هذا الدور لوجود موظفين مرتبطين به حالياً. يرجى نقل الموظفين أولاً.']);
        }

        $name = $role->display_name;
        $role->permissions()->detach();
        $role->delete();

        ActivityLogger::log(
            action: 'delete',
            module: 'roles',
            description: "تم حذف الدور الوظيفي: '{$name}' من النظام",
            recordId: $role->id
        );

        return redirect()->route('roles.index')
            ->with('success', "تم حذف الدور الوظيفي '{$name}' بنجاح.");
    }
}
