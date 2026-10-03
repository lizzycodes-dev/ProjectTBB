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


    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #ead8c4;
        }


        .login-page {
            min-height: 100vh;
            display: flex;
            background: #ead8c4;
        }


        /* =========================
           LEFT SIDE
        ========================= */

        .login-brand {
            width: 56%;
            min-height: 100vh;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #ead8c4;
            padding: 40px;
        }


        .logo-container {
            width: 78%;
            max-width: 650px;

            display: flex;
            align-items: center;
            justify-content: center;
        }


        .brewing-logo {
            width: 100%;
            max-width: 560px;
            height: auto;
            display: block;
        }


        /* =========================
           RIGHT SIDE
        ========================= */

        .login-form-section {
            width: 44%;
            min-height: 100vh;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #d9c6b3;

            padding: 40px;
        }


        .login-form-container {
            width: 100%;
            max-width: 360px;
        }


        .login-form-container h1 {
            margin: 0;

            text-align: center;

            color: #754936;

            font-family: Georgia, serif;
            font-size: 25px;
            font-weight: bold;
        }


        .login-subtitle {
            margin: 12px 0 45px;

            text-align: center;

            color: #754936;

            font-family: Georgia, serif;
            font-size: 14px;
            font-weight: bold;
            font-style: italic;
        }


        /* =========================
           FORM
        ========================= */

        .form-group {
            margin-bottom: 20px;
        }


        .login-label {
            display: block;

            margin-bottom: 7px;

            color: #754936;

            font-family: Georgia, serif;
            font-size: 13px;
            font-weight: bold;
            font-style: italic;
        }


        .login-input {
            width: 100%;

            height: 43px;

            padding: 0 12px;

            border: none;
            border-radius: 6px;

            background: #ffffff;

            color: #5d4030;

            font-size: 13px;

            box-shadow: none;
        }


        .login-input:focus {
            border: 2px solid #a97958;

            outline: none;

            box-shadow: 0 0 0 2px rgba(139, 94, 60, 0.12);
        }


        .login-input::placeholder {
            color: #b18b72;
        }


        .login-error {
            margin-top: 5px;

            color: #9b3d2f;

            font-size: 12px;
        }


        /* =========================
           REMEMBER ME
        ========================= */

        .remember-container {
            margin-top: 4px;
            margin-bottom: 18px;
        }


        .remember-container label {
            display: flex;
            align-items: center;

            gap: 7px;

            color: #754936;

            font-size: 12px;
        }


        .remember-container input {
            accent-color: #8b5e3c;
        }


        /* =========================
           LOGIN BUTTON
        ========================= */

        .login-button {
            width: 100%;

            height: 43px;

            border: none;
            border-radius: 6px;

            background: #b18463;

            color: #754936;

            font-family: Georgia, serif;
            font-size: 13px;
            font-weight: bold;

            cursor: pointer;

            transition: 0.2s;
        }


        .login-button:hover {
            background: #8b5e3c;
            color: white;
        }


        .login-button:active {
            transform: translateY(1px);
        }


        /* =========================
           STATUS
        ========================= */

        .login-status {
            margin-bottom: 15px;
            color: #754936;
            font-size: 13px;
        }


        /* =========================
           MOBILE
        ========================= */

        @media (max-width: 800px) {

            .login-page {
                flex-direction: column;
            }

            .login-brand {
                width: 100%;
                min-height: 40vh;
                padding: 30px;
            }

            .logo-container {
                width: 70%;
            }

            .login-form-section {
                width: 100%;
                min-height: 60vh;
                padding: 35px 25px;
            }

        }
    </style>

</x-guest-layout>