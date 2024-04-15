<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\UserPermissions;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('role.role')->where('type', 'user')->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $context = [
            'roles' => Role::all()
        ];
        
        return view('users.create', $context);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'gender' => 'required|in:male,female',
            'phone' => 'required|numeric',
            'empid' => 'required|string|unique:users,employee_id',
            'password' => 'required|string',
            'role' => 'required|string|exists:roles,id',
        ]);

        DB::beginTransaction();

        try {
            $user = User::create([
                'employee_id' => $request->input('empid'),
                'name' => $request->input('name'),
                'gender' => $request->input('gender'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'password' => $request->input('password'),
            ]);
            
            $userRole = UserRole::create([
                'user_id' => $user->id,
                'role_id' => $request->input('role'),
            ]);
            
            $permissions = PermissionRole::where('role_id', $userRole->role_id)->pluck('permission_id');

            $userPermissions = [];
            foreach ($permissions as $permission) {
                $userPermissions[] = [
                    'up_id' => Str::uuid(),
                    'user_id' => $user->id,
                    'permission_id' => $permission,
                ];
            }
            UserPermissions::insert($userPermissions);

            DB::commit();

            return redirect()->route('users')->with('success', 'User created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
        }
    }


    public function edit(User $user)
    {
        $context = [
            'roles' => Role::all(),
            'user' => $user,
        ];
        return view('users.edit', $context);
    }
    
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8',
            'gender' => 'required|in:male,female,other',
            'phone' => 'required|numeric',
            'empid' => 'required|string|unique:users,employee_id,' . $user->id,
            'role' => 'required|string|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->filled('password') ? bcrypt($request->password) : $user->password,
                'gender' => $request->gender,
                'phone' => $request->phone,
                'employee_id' => $request->empid,
            ]);

            // Update user role
            $user->roles()->sync([$request->role]);

            // Update user permissions
            $permissions = PermissionRole::where('role_id', $request->role)->pluck('permission_id');
            $userPermissions = [];
            foreach ($permissions as $permission) {
                $userPermissions[] = [
                    'up_id' => Str::uuid(),
                    'user_id' => $user->id,
                    'permission_id' => $permission,
                ];
            }
            UserPermissions::where('user_id', $user->id)->delete();
            UserPermissions::insert($userPermissions);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
        }

        return redirect()->route('users')->with('success', 'User updated successfully.');
    }


    public function permission(Request $request, User $user)
    {
        $context = [
            'permissions' => Permission::all(),
            'user' => $user,
            'userPermissions' => $user->permissions
        ];
        
        return view('users.permissions', $context);
    }

    public function setPermission(Request $request, User $user)
    {
        $request->validate([
            'permissions' => 'array',
        ]);

        $permissionIds = $request->input('permissions', []);

        try {
            $user->permissions()->delete();
            foreach ($permissionIds as $permissionId) {
                UserPermissions::create([
                    'up_id' => Str::uuid(),
                    'user_id' => $user->id,
                    'permission_id' => $permissionId,
                ]);
            }
            
            return redirect()->back()->with('success', 'Permissions updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update permissions. Please try again.');
        }
    }

    public function destroy(User $user)
    {
        try {
            $user->permissions()->delete();
            $user->roles()->detach();
            $user->delete();
            return redirect()->route('users')->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

}
