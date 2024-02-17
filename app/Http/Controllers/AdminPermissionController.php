<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class AdminPermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::orderBy('created_at', 'desc')->get();
        return view('admin.permissions.index', compact('permissions'));
    }

    public function create()
    {
        return view('admin.permissions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'required|string',
        ],[
            'permissions.*.required' => 'Enter Permission name or remove the blank field.',
        ]);

        $permissions = $request->input('permissions');
        
        foreach ($permissions as $permissionName) {
            Permission::create([
                'name' => $permissionName,
            ]);
        }

        return redirect()->route('permissions')
            ->with('success', 'One or more permissions created successfully');
    }

    public function edit(Permission $permission)
    {
        if (!$permission) {
            return response()->json(['error' => 'Permission not found'], Response::HTTP_NOT_FOUND);
        }
        return response()->json(['permission' => $permission]);
    }

    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'permission_name' => 'required|string|max:255',
        ]);

        $permission->update([
            'name' => $request->input('permission_name'),
        ]);

        return response()->json(['message' => 'Commodity updated successfully']);
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        return redirect()->route('permissions')->with('success', 'Permission deleted successfully');
    }
}
