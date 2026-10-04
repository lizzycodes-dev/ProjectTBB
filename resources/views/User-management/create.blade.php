<x-app-layout>

    <div class="user-page user-create-page">

        {{-- HEADER --}}
        <div class="user-header">
            <div>
                <h1>Add New User</h1>
                <p>
                    Create a new employee account and assign their position.
                </p>
            </div>

            <a
                href="{{ route('users.index') }}"
                class="user-back-button">
                &larr; Back to Users
            </a>
        </div>

        {{-- VALIDATION ERRORS --}}
        @if ($errors->any())
        <div class="user-error-message">
            <strong>Please check the form:</strong>

            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- CREATE USER CARD --}}
        <div class="user-create-card">

            <div class="user-create-card-header">
                <div>
                    <h2>Employee Account</h2>
                    <p>
                        Enter the employee's account information below.
                    </p>
                </div>
            </div>

            <div class="user-create-card-body">

                <form
                    action="{{ route('users.store') }}"
                    method="POST">

                    @csrf

                    <div class="user-create-form">

                        {{-- FULL NAME --}}
                        <div class="user-form-group">
                            <label for="name">
                                Full Name
                            </label>

                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
                                required
                                autocomplete="name"
                                placeholder="Enter full name">
                        </div>

                        {{-- EMAIL --}}
                        <div class="user-form-group">
                            <label for="email">
                                Email Address
                            </label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autocomplete="email"
                                placeholder="Enter email address">
                        </div>

                        {{-- PASSWORD --}}
                        <div class="user-form-group">
                            <label for="password">
                                Initial Password
                            </label>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                required
                                minlength="8"
                                autocomplete="new-password"
                                placeholder="Enter initial password">

                            <small>
                                Password must be at least 8 characters.
                            </small>
                        </div>

                        {{-- POSITION --}}
                        <div class="user-form-group">
                            <label for="role_id">
                                Assigned Position
                            </label>

                            <select
                                id="role_id"
                                name="role_id"
                                required>

                                <option value="" disabled
                                    @selected(old('role_id')===null)>
                                    Select a position
                                </option>

                                @foreach($roles as $role)
                                <option
                                    value="{{ $role->id }}"
                                    @selected(old('role_id')==$role->id)>
                                    {{ $role->name }}
                                </option>
                                @endforeach

                            </select>
                        </div>

                    </div>

                    {{-- FORM FOOTER --}}
                    <div class="user-create-footer">

                        <a
                            href="{{ route('users.index') }}"
                            class="user-cancel-button">
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="user-save-button">
                            Create User Account
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</x-app-layout>