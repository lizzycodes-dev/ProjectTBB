<form method="post" action="{{ route('profile.update') }}" class="profile-form">
    @csrf
    @method('patch')

    <div class="profile-field">
        <label for="name">Name</label>
        <input
            id="name"
            name="name"
            type="text"
            class="profile-input"
            value="{{ old('name', $user->name) }}"
            required
            autofocus
            autocomplete="name">
        @error('name')
        <p class="profile-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="profile-field">
        <label for="email">Email</label>
        <input
            id="email"
            name="email"
            type="email"
            class="profile-input"
            value="{{ old('email', $user->email) }}"
            required
            autocomplete="username">
        @error('email')
        <p class="profile-error">{{ $message }}</p>
        @enderror

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
        <div class="profile-note">
            Your email address is unverified.

            <button form="send-verification" class="profile-link-button">
                Click here to re-send the verification email.
            </button>

            @if (session('status') === 'verification-link-sent')
            <p class="profile-success">
                A new verification link has been sent to your email address.
            </p>
            @endif
        </div>
        @endif
    </div>

    <div class="profile-actions">
        <button type="submit" class="profile-primary-button">Save</button>

        @if (session('status') === 'profile-updated')
        <span class="profile-save-note">Saved.</span>
        @endif
    </div>
</form>

<form id="send-verification" method="post" action="{{ route('verification.send') }}" class="hidden">
    @csrf
</form>