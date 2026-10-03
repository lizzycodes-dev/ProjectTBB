<x-guest-layout>

    <div class="login-page">

        {{-- Left Side --}}
        <div class="login-brand">

            <div class="logo-container">
                <img
                    src="{{ asset('images/logo/brewing-bar-logo.png') }}"
                    alt="The Brewing Bar"
                    class="brewing-logo">
            </div>

        </div>

        {{-- Right Side --}}
        <div class="login-form-section">

            <div class="login-form-container">

                <h1>WELCOME!</h1>

                <p class="login-subtitle">
                    POS & Inventory Management System
                </p>

                {{-- Session Status --}}
                <x-auth-session-status
                    class="login-status"
                    :status="session('status')" />

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    {{-- Username / Email --}}
                    <div class="form-group">

                        <x-input-label
                            for="email"
                            :value="__('Username')"
                            class="login-label" />

                        <x-text-input
                            id="email"
                            class="login-input"
                            type="email"
                            name="email"
                            :value="old('email')"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="Username" />

                        <x-input-error
                            :messages="$errors->get('email')"
                            class="login-error" />

                    </div>

                    {{-- Password --}}
                    <div class="form-group">

                        <x-input-label
                            for="password"
                            :value="__('Password')"
                            class="login-label" />

                        <x-text-input
                            id="password"
                            class="login-input"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="Password" />

                        <x-input-error
                            :messages="$errors->get('password')"
                            class="login-error" />

                    </div>

                    {{-- Remember Me --}}
                    <div class="remember-container">

                        <label for="remember_me">

                            <input
                                id="remember_me"
                                type="checkbox"
                                name="remember">

                            <span>
                                Remember me
                            </span>

                        </label>

                    </div>

                    {{-- Login Button --}}
                    <button
                        type="submit"
                        class="login-button">
                        ⇥ LOGIN
                    </button>

                </form>

            </div>

        </div>

    </div>

</x-guest-layout>