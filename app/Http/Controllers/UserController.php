<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\ProfileRequest;
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
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Role or Archived status filter
        if ($filter === 'archived') {
            $query->onlyTrashed();
        } elseif ($filter) {
            $query->whereHas('role', function ($q) use ($filter) {
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
        $managerCount = User::whereHas('role', fn ($q) => $q->where('name', 'Manager'))->count();
        $cashierCount = User::whereHas('role', fn ($q) => $q->where('name', 'Cashier'))->count();
        $cookCount    = User::whereHas('role', fn ($q) => $q->whereIn('name', ['Cook', 'Kitchen Staff']))->count();

        // Pending profile change requests
        $pendingRequests = ProfileRequest::with('user')
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->get();

        $pendingCount = $pendingRequests->count();

        return view('User-management.index', compact(
            'users',
            'roles',
            'managerCount',
            'cashierCount',
            'cookCount',
            'pendingRequests',
            'pendingCount'
        ));
    }

    // 2. Show the "Create User" form
    public function create()
    {
        $roles = Role::all();
        return view('User-management.create', compact('roles'));
    }

    // 3. Save a brand new user
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role_id'  => 'required|exists:roles,id',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('users.index')
            ->with('success', 'User account created successfully.');
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
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role_id'  => 'required|exists:roles,id',
            'password' => 'nullable|string|min:8',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.index')
            ->with('success', 'User account updated successfully.');
    }

    // 6. Archive the user (Soft Delete)
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot archive your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User account archived successfully.');
    }

    public function restore($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return redirect()->route('users.index')
            ->with('success', 'User account reactivated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Profile Change Requests
    |--------------------------------------------------------------------------
    */

    /**
     * Approve a pending profile change request.
     * Applies the requested name/email to the user, then marks it approved.
     */
    public function approveProfileRequest(ProfileRequest $profileRequest)
    {
        if (! $profileRequest->isPending()) {
            return back()->with('error', 'This request has already been reviewed.');
        }

        $user = $profileRequest->user;

        if (! $user) {
            return back()->with('error', 'The user for this request no longer exists.');
        }

        /* =========================================================
        | Password reset request
        | ========================================================= */
        if ($profileRequest->isPasswordRequest()) {

            if (empty($profileRequest->password)) {
                return back()->with('error', 'This password request is missing the new password.');
            }

            $user->update([
                'password' => $profileRequest->password, // already hashed
            ]);

            $profileRequest->update([
                'status'      => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            return back()->with(
                'success',
                'Password reset approved. ' . $user->name . "'s password has been updated."
            );
        }

        /* =========================================================
        | Profile change request
        | ========================================================= */
        $conflict = User::where('email', $profileRequest->email)
            ->where('id', '!=', $user->id)
            ->exists();

        if ($conflict) {
            return back()->with(
                'error',
                'Cannot approve: the email "' . $profileRequest->email . '" is already in use by another user.'
            );
        }

        $user->update([
            'name'  => $profileRequest->name,
            'email' => $profileRequest->email,
        ]);

        $profileRequest->update([
            'status'      => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with(
            'success',
            'Profile request approved. ' . $user->name . "'s details have been updated."
        );
    }

    /**
     * Deny a pending profile change request.
     */
    public function denyProfileRequest(Request $request, ProfileRequest $profileRequest)
    {
        if (! $profileRequest->isPending()) {
            return back()->with('error', 'This request has already been reviewed.');
        }

        $validated = $request->validate([
            'review_note' => ['nullable', 'string', 'max:500'],
        ]);

        $profileRequest->update([
            'status'      => 'denied',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_note' => $validated['review_note'] ?? null,
        ]);

        return back()->with('success', 'Profile request denied.');
    }
}