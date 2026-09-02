<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Business;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['role', 'business', 'businesses'])->latest()->get();
        return view('users.index', compact('users'));
    }

    // public function create()
    // {
    //     $businesses = Business::all();
    //     $roles = Role::all();

    //     return view('users.create', compact('businesses', 'roles'));
    // }

   


    public function create()
    {
        $roles = Role::all();
        $businesses = Business::all();
        return view('users.create', compact(
            'roles',
            'businesses'
        ));
    }
   
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|exists:roles,id',
            'business_ids' => 'nullable|array',
            'business_ids.*' => 'integer|distinct|exists:businesses,id',
        ]);

        DB::transaction(function () use ($validated): void {
            $businessIds = array_values($validated['business_ids'] ?? []);
            $role = Role::findOrFail($validated['role']);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role_id' => $role->id,
                // Retained temporarily for old modules that still read business_id.
                'business_id' => $businessIds[0] ?? null,
            ]);

            $user->businesses()->sync(
                $this->businessAssignments($businessIds, $role->name)
            );
        });

        return redirect()->route('users.index')
            ->with('success', 'User created successfully');
    }

    public function edit($id)
    {
        $user = User::with('businesses')->findOrFail($id);
        $businesses = Business::all();
        $roles = Role::all();

        return view('users.edit', compact('user', 'businesses', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|exists:roles,id',
            'password' => 'nullable|string|min:6',
            'business_ids' => 'nullable|array',
            'business_ids.*' => 'integer|distinct|exists:businesses,id',
        ]);

        DB::transaction(function () use ($user, $validated): void {
            $businessIds = array_values($validated['business_ids'] ?? []);
            $role = Role::findOrFail($validated['role']);

            $attributes = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role_id' => $role->id,
                'business_id' => $businessIds[0] ?? null,
            ];

            if (! empty($validated['password'])) {
                $attributes['password'] = Hash::make($validated['password']);
            }

            $user->update($attributes);
            $user->businesses()->sync(
                $this->businessAssignments($businessIds, $role->name)
            );
        });

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully');
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully');
    }

    private function businessAssignments(array $businessIds, string $roleName): array
    {
        $accessLevel = in_array($roleName, ['admin', 'manager'], true)
            ? $roleName
            : 'employee';

        $assignments = [];

        foreach ($businessIds as $index => $businessId) {
            $assignments[$businessId] = [
                'access_level' => $accessLevel,
                'is_primary' => $index === 0,
                'is_active' => true,
            ];
        }

        return $assignments;
    }
}
