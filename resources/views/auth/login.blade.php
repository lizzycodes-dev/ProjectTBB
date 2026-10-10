<x-guest-layout>

    <div class="login-page">

        {{-- =========================================================
             LEFT SIDE — BRAND
             ========================================================= --}}
        <div class="login-brand">

            <div class="login-brand-inner">

                <div class="login-brand-logo-frame">
                    <img
                        src="{{ asset('images/logo/brewing-bar-logo.png') }}"
                        alt="The Brewing Bar"
                        class="brewing-logo">
                </div>

                <div class="login-brand-tagline">
                    <span class="login-brand-line"></span>
                    <span class="login-brand-text">
                        Brewed fresh. Served warm.
                    </span>
                    <span class="login-brand-line"></span>
                </div>

            </div>

        </div>


        {{-- =========================================================
             RIGHT SIDE — LOGIN CARD
             ========================================================= --}}
        <div class="login-form-section">

            <div class="login-form-container">

                <div class="login-card">

                    <header class="login-card-header">
                        <h1>Welcome back</h1>
                        <p class="login-subtitle">
                            Sign in to The Brewing Bar POS &amp; Inventory
                        </p>
                    </header>


                    {{-- Session Status --}}
                    <x-auth-session-status
                        class="login-status"
                        :status="session('status')" />


                    <form method="POST" action="{{ route('login') }}" class="login-form">
                        @csrf

                        {{-- Username / Email --}}
                        <div class="form-group">

                            <x-input-label
                                for="email"
                                :value="__('Username')"
                                class="login-label" />

                            <div class="login-input-wrap">

                                <span class="login-input-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24">
                                        <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8z" />
                                        <path d="M4 20a8 8 0 0 1 16 0" />
                                    </svg>
                                </span>

                                <x-text-input
                                    id="email"
                                    class="login-input"
                                    type="email"
                                    name="email"
                                    :value="old('email')"
                                    required
                                    autofocus
                                    autocomplete="username"
                                    placeholder="you@brewingbar.ph" />

                            </div>

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

                            <div class="login-input-wrap">

                                <span class="login-input-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24">
                                        <path d="M6 11V8a6 6 0 0 1 12 0v3" />
                                        <rect x="4" y="11" width="16" height="9" rx="2" />
                                        <path d="M12 15v2" />
                                    </svg>
                                </span>

                                <x-text-input
                                    id="password"
                                    class="login-input"
                                    type="password"
                                    name="password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="Enter your password" />

                            </div>

                            <x-input-error
                                :messages="$errors->get('password')"
                                class="login-error" />

                        </div>


                        {{-- Remember + Forgot --}}
                        <div class="login-row">

                            <div class="remember-container">
                                <label for="remember_me">
                                    <input
                                        id="remember_me"
                                        type="checkbox"
                                        name="remember">

                                    <span>Remember me</span>
                                </label>
                            </div>

                            @if (Route::has('password.request'))
                            <a
                                href="{{ route('password.request') }}"
                                class="login-forgot">
                                Forgot password?
                            </a>
                            @endif

                        </div>


                        {{-- Login Button --}}
                        <button
                            type="submit"
                            class="login-button">

                            <span class="login-button-text">Sign in</span>

                            <span class="login-button-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M5 12h14" />
                                    <path d="m13 6 6 6-6 6" />
                                </svg>
                            </span>

                        </button>

                    </form>


                    <footer class="login-card-footer">
                        <span>Protected by session authentication</span>
                    </footer>

                </div>

            </div>

        </div>

    </div>

</x-guest-layout>