<?php

namespace App\Http\Controllers;

use App\Models\ProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();

        $requests = ProfileRequest::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $pendingRequest = $requests->firstWhere('status', 'pending');

        return view('profile.edit', [
            'user' => $user,
            'requests' => $requests,
            'pendingRequest' => $pendingRequest,
        ]);
    }

    /**
     * Submit a profile update request (name / email).
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (ProfileRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists()) {
            return back()->withErrors([
                'request' => 'You already have a pending request. Wait for the manager to review it.',
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        ProfileRequest::create([
            'user_id' => $user->id,
            'type'    => 'profile',
            'name'    => $validated['name'],
            'email'   => $validated['email'],
            'reason'  => $validated['reason'] ?? null,
            'status'  => 'pending',
        ]);

        return redirect()
            ->route('profile.edit')
            ->with('status', 'request-submitted');
    }

    /**
     * Submit a password reset request.
     */
    public function requestPassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (ProfileRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists()) {
            return back()->withErrors([
                'password_request' => 'You already have a pending request. Wait for the manager to review it.',
            ]);
        }

        $validated = $request->validate([
            'new_password' => ['required', 'string', Password::defaults(), 'confirmed'],
            'reason'       => ['nullable', 'string', 'max:500'],
        ]);

        ProfileRequest::create([
            'user_id'  => $user->id,
            'type'     => 'password',
            'password' => Hash::make($validated['new_password']),
            'reason'   => $validated['reason'] ?? null,
            'status'   => 'pending',
        ]);

        return redirect()
            ->route('profile.edit')
            ->with('status', 'password-request-submitted');
    }

    /**
     * Account deletion is disabled — see User Management.
     */
    public function destroy(Request $request): RedirectResponse
    {
        return redirect()
            ->route('profile.edit')
            ->withErrors([
                'account' => 'Account deletion is disabled. Contact a manager if you need your account removed.',
            ]);
    }
}