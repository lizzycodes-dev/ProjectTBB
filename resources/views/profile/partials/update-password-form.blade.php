<form method="post" action="{{ route('password.update') }}" class="profile-form">
    @csrf
    @method('put')

    <div class="profile-field">
        <label for="update_password_current_password">Current Password</label>
        <input
            id="update_password_current_password"
            name="current_password"
            type="password"
            class="profile-input"
            autocomplete="current-password">
        @error('current_password', 'updatePassword')
        <p class="profile-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="profile-field">
        <label for="update_password_password">New Password</label>
        <input
            id="update_password_password"
            name="password"
            type="password"
            class="profile-input"
            autocomplete="new-password">
        @error('password', 'updatePassword')
        <p class="profile-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="profile-field">
        <label for="update_password_password_confirmation">Confirm Password</label>
        <input
            id="update_password_password_confirmation"
            name="password_confirmation"
            type="password"
            class="profile-input"
            autocomplete="new-password">
        @error('password_confirmation', 'updatePassword')
        <p class="profile-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="profile-actions">
        <button type="submit" class="profile-primary-button">Save</button>

        @if (session('status') === 'password-updated')
        <span class="profile-save-note">Saved.</span>
        @endif
    </div>
</form>