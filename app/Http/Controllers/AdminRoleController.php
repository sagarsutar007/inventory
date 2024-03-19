<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;


class AdminRoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::all();
        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create($request->only('name'));

        $role->permissions()->attach($request->input('permissions', []));

        return redirect()->route('roles')
            ->with('success', 'Role created successfully');
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return redirect()->route('roles')->with('success', 'Role deleted successfully');
    }

    public function edit($roleId)
    {
        $role = Role::with('permissions')->find($roleId);

        if (!$role) {
            return response()->json(['role' => null]);
        }

        $permissions = Permission::all();

        return response()->json(['role' => $role, 'permissions' => $permissions]);
    }

    public function update(Request $request, $roleId)
    {
        $request->validate([
            'role_name' => 'required|unique:roles,name,'.$roleId,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::findOrFail($roleId);

        $role->name = $request->input('role_name');
        $role->save();

        $role->permissions()->sync($request->input('permissions', []));

        return response()->json(['message' => 'Role updated successfully']);
    }
}
