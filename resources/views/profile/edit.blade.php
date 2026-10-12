<x-app-layout>

    <div class="profile-page">

        <header class="profile-header">
            <div>
                <h1>My Profile</h1>
                <p>View your account details and submit change requests to your manager.</p>
            </div>
        </header>

        <div class="profile-grid">

            {{-- CURRENT DETAILS --}}
            <section class="profile-card">
                <div class="profile-card-head">
                    <div>
                        <h2>Account Information</h2>
                        <p>These are your current details on file.</p>
                    </div>
                </div>

                <div class="profile-card-body">
                    <div class="profile-form">

                        <div class="profile-field">
                            <label>Name</label>
                            <div class="profile-static-value">{{ $user->name }}</div>
                        </div>

                        <div class="profile-field">
                            <label>Email</label>
                            <div class="profile-static-value">{{ $user->email }}</div>
                        </div>

                        <div class="profile-field">
                            <label>Role</label>
                            <div class="profile-static-value">
                                {{ $user->role->name ?? '—' }}
                            </div>
                        </div>

                        <div class="profile-note">
                            Name, email, and password changes must be requested and approved by a manager.
                        </div>

                    </div>
                </div>
            </section>


            {{-- PROFILE CHANGE REQUEST --}}
            <section class="profile-card">
                <div class="profile-card-head">
                    <div>
                        <h2>Request a Profile Change</h2>
                        <p>Submit the changes you want. A manager will review them.</p>
                    </div>
                </div>

                <div class="profile-card-body">

                    @if (session('status') === 'request-submitted')
                        <div class="profile-success-message">
                            Your profile change request has been submitted and is pending manager review.
                        </div>
                    @endif

                    @if ($errors->has('request'))
                        <div class="profile-error-message">
                            {{ $errors->first('request') }}
                        </div>
                    @endif

                    @if ($pendingRequest)

                        <div class="profile-pending-banner">
                            <strong>You have a pending request.</strong>
                            <p>
                                Submitted on {{ $pendingRequest->created_at->format('M d, Y h:i A') }}
                                ({{ $pendingRequest->typeLabel() }}).
                                Please wait for the manager to review it before submitting another.
                            </p>
                        </div>

                    @else

                        <form method="post" action="{{ route('profile.update') }}" class="profile-form">
                            @csrf
                            @method('patch')

                            <div class="profile-field">
                                <label for="name">New Name</label>
                                <input
                                    id="name"
                                    name="name"
                                    type="text"
                                    class="profile-input"
                                    value="{{ old('name', $user->name) }}"
                                    required
                                    autocomplete="name">
                                @error('name')
                                    <p class="profile-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="profile-field">
                                <label for="email">New Email</label>
                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    class="profile-input"
                                    value="{{ old('email', $user->email) }}"
                                    required
                                    autocomplete="email">
                                @error('email')
                                    <p class="profile-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="profile-field">
                                <label for="reason">Reason <span>(optional)</span></label>
                                <textarea
                                    id="reason"
                                    name="reason"
                                    class="profile-input"
                                    rows="3"
                                    maxlength="500"
                                    placeholder="Explain briefly why you need this change.">{{ old('reason') }}</textarea>
                                @error('reason')
                                    <p class="profile-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="profile-actions">
                                <button type="submit" class="profile-primary-button">
                                    Submit Profile Request
                                </button>
                            </div>
                        </form>

                    @endif

                </div>
            </section>


            {{-- PASSWORD CHANGE REQUEST --}}
            <section class="profile-card">
                <div class="profile-card-head">
                    <div>
                        <h2>Request a Password Change</h2>
                        <p>Set a new password. A manager will review it and apply it.</p>
                    </div>
                </div>

                <div class="profile-card-body">

                    @if (session('status') === 'password-request-submitted')
                        <div class="profile-success-message">
                            Your password change request has been submitted and is pending manager review.
                        </div>
                    @endif

                    @if ($errors->has('password_request'))
                        <div class="profile-error-message">
                            {{ $errors->first('password_request') }}
                        </div>
                    @endif

                    @if ($pendingRequest)

                        <div class="profile-pending-banner">
                            <strong>You already have a pending request.</strong>
                            <p>
                                Wait for the manager to review it before submitting another.
                            </p>
                        </div>

                    @else

                        <form method="post" action="{{ route('profile.request-password') }}" class="profile-form">
                            @csrf
                            @method('patch')

                            <div class="profile-field">
                                <label for="new_password">New Password</label>
                                <input
                                    id="new_password"
                                    name="new_password"
                                    type="password"
                                    class="profile-input"
                                    required
                                    autocomplete="new-password"
                                    minlength="8">
                                @error('new_password')
                                    <p class="profile-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="profile-field">
                                <label for="new_password_confirmation">Confirm New Password</label>
                                <input
                                    id="new_password_confirmation"
                                    name="new_password_confirmation"
                                    type="password"
                                    class="profile-input"
                                    required
                                    autocomplete="new-password"
                                    minlength="8">
                            </div>

                            <div class="profile-field">
                                <label for="password_reason">Reason <span>(optional)</span></label>
                                <textarea
                                    id="password_reason"
                                    name="reason"
                                    class="profile-input"
                                    rows="3"
                                    maxlength="500"
                                    placeholder="Explain briefly why you need a password change.">{{ old('reason') }}</textarea>
                                @error('reason')
                                    <p class="profile-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="profile-actions">
                                <button type="submit" class="profile-primary-button">
                                    Submit Password Request
                                </button>
                            </div>
                        </form>

                    @endif

                </div>
            </section>


            {{-- REQUEST HISTORY --}}
            <section class="profile-card">
                <div class="profile-card-head">
                    <div>
                        <h2>Request History</h2>
                        <p>Your last 20 requests and their statuses.</p>
                    </div>
                </div>

                <div class="profile-card-body">

                    @if ($requests->isEmpty())
                        <p class="profile-empty">You haven't submitted any requests yet.</p>
                    @else
                        <div class="profile-requests-list">
                            @foreach ($requests as $req)
                                <div class="profile-request-row status-{{ $req->status }}">

                                    <div class="profile-request-main">
                                        <div>
                                            <span class="profile-request-type">
                                                {{ $req->typeLabel() }}
                                            </span>
                                        </div>

                                        @if ($req->isProfileRequest())
                                            <div>
                                                <span class="profile-request-label">Name:</span>
                                                {{ $req->name }}
                                            </div>
                                            <div>
                                                <span class="profile-request-label">Email:</span>
                                                {{ $req->email }}
                                            </div>
                                        @else
                                            <div>
                                                <span class="profile-request-label">Password:</span>
                                                ••••••••
                                            </div>
                                        @endif

                                        <div class="profile-request-meta">
                                            Submitted {{ $req->created_at->format('M d, Y h:i A') }}
                                        </div>

                                        @if ($req->review_note)
                                            <div class="profile-request-meta">
                                                Manager note: {{ $req->review_note }}
                                            </div>
                                        @endif
                                    </div>

                                    <span class="profile-request-status status-{{ $req->status }}">
                                        {{ ucfirst($req->status) }}
                                    </span>

                                </div>
                            @endforeach
                        </div>
                    @endif

                </div>
            </section>

        </div>

    </div>

</x-app-layout>