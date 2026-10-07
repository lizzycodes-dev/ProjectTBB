<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filter = $request->input('filter');

        $query = User::with('role');

        // Search query filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Role or Archived status filter
        if ($filter === 'archived') {
            $query->onlyTrashed();
        } elseif ($filter) {
            $query->whereHas('role', function($q) use ($filter) {
                if ($filter === 'Cook') {
                    $q->whereIn('name', ['Cook', 'Kitchen Staff']);
                } else {
                    $q->where('name', $filter);
                }
            });
        }

        $users = $query->paginate(10)->withQueryString();
        $roles = Role::all();

        // Summary counts (keeping active counts)
        $managerCount = User::whereHas('role', fn($q) => $q->where('name', 'Manager'))->count();
        $cashierCount = User::whereHas('role', fn($q) => $q->where('name', 'Cashier'))->count();
        $cookCount = User::whereHas('role', fn($q) => $q->whereIn('name', ['Cook', 'Kitchen Staff']))->count();

        return view('User-management.index', compact('users', 'roles', 'managerCount', 'cashierCount', 'cookCount'));
    }

    // 2. Show the "Create User" form
    public function create()
    {
        $roles = Role::all(); // Get all positions (Manager, Cashier, etc.) to populate the dropdown
        return view('User-management.create', compact('roles'));
    }

    // 3. Save a brand new user
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
        ]);

        // Securely hash the password before saving
        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('users.index')->with('success', 'User account created successfully.');
    }

    // 4. Show the "Edit User" form
    public function edit(User $user)
    {
        $roles = Role::all();
        return view('User-management.edit', compact('user', 'roles'));
    }

    // 5. Save updates to an existing user
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // Allow the user to keep their current email without throwing a "Unique" error
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role_id' => 'required|exists:roles,id',
            'password' => 'nullable|string|min:8', // Password is optional on update
        ]);

        // Only hash and update the password if they actually typed a new one
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            // If they left it blank, remove it from the array so it doesn't overwrite the old one
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'User account updated successfully.');
    }

    // 6. Archive the user (Soft Delete)
    public function destroy(User $user)
    {
        // Because we added SoftDeletes to the model, this won't actually erase them!
        // It will just stamp the 'deleted_at' column and hide them from the app.
        if ($user->id === auth()->id()) {
    return redirect()->route('users.index')
        ->with('error', 'You cannot archive your own account.');
}

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User account archived successfully.');
    }

    public function restore($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return redirect()->route('users.index')->with('success', 'User account reactivated successfully.');
    }
}