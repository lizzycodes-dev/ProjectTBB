<nav class="sidebar">

    {{-- Logo / Brand --}}
    <div class="sidebar-brand">

        <a href="{{ route('dashboard') }}">
            <div class="sidebar-logo">
                <img
                    src="{{ asset('images/logo/brewing-bar-logo.png') }}"
                    alt="The Brewing Bar"
                    class="brewing-logo">
            </div>

            <div class="sidebar-title">
                The Brewing Bar
            </div>

            <div class="sidebar-subtitle">
                POS & Inventory System
            </div>
        </a>

    </div>


    {{-- User --}}
    <div class="sidebar-user">

        <div class="sidebar-welcome">
            Welcome!
        </div>

        <div class="sidebar-username">
            {{ Auth::user()->name }}
        </div>

    </div>


    {{-- Navigation --}}
    <div class="sidebar-links">

        <a
            href="{{ route('dashboard') }}"
            class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <span>→</span>
            <span>Dashboard</span>
        </a>


        <a
            href="/pos"
            class="sidebar-link {{ request()->is('pos') ? 'active' : '' }}">
            <span>→</span>
            <span>POS</span>
        </a>


        <a
            href="/kitchen"
            class="sidebar-link {{ request()->is('kitchen*') ? 'active' : '' }}">
            <span>→</span>
            <span>Kitchen</span>
        </a>


        <a
            href="/inventory"
            class="sidebar-link {{ request()->is('inventory*') ? 'active' : '' }}">
            <span>→</span>
            <span>Inventory</span>
        </a>

        <a
            href="{{ route('finance-report.index') }}"
            class="sidebar-link {{ request()->routeIs('finance-report.index') ? 'active' : '' }}">
            <span>→</span>
            <span>Finance Report</span>
        </a>


    </div>


    {{-- Logout --}}
    <div class="sidebar-logout">

        <form
            method="POST"
            action="{{ route('logout') }}">

            @csrf

            <button
                type="submit"
                class="sidebar-link logout-button">

                <span>→</span>
                <span>Logout</span>

            </button>

        </form>

    </div>

</nav>


<style>
    .brewing-logo {
        height: 80px;
        width: auto;
        display: block;
        margin: 0 auto;
    }

    .sidebar {
        width: 285px;
        min-width: 285px;
        min-height: 100vh;
        background: #ead8c4;
        border-right: 2px solid #8b5e3c;
        padding: 20px 20px;
        display: flex;
        flex-direction: column;
        color: #6b4328;
    }


    /* Brand */

    .sidebar-brand {
        text-align: center;
        padding-bottom: 15px;
    }

    .sidebar-brand a {
        text-decoration: none;
        color: inherit;
    }

    .sidebar-logo {
        font-size: 48px;
        margin-bottom: 5px;
    }

    .sidebar-title {
        font-family: Georgia, serif;
        font-size: 27px;
        font-weight: bold;
        font-style: italic;
        color: #6b4328;
    }

    .sidebar-subtitle {
        margin-top: 8px;
        font-size: 14px;
        font-weight: bold;
        color: #76543c;
    }


    /* User */

    .sidebar-user {
        border-top: 1px dashed #9b7658;
        border-bottom: 1px dashed #9b7658;
        padding: 20px 5px;
        text-align: center;
    }

    .sidebar-welcome {
        font-size: 16px;
        font-weight: bold;
    }

    .sidebar-username {
        margin-top: 5px;
        font-size: 17px;
        font-weight: bold;
    }


    /* Navigation */

    .sidebar-links {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: 25px;
    }

    .sidebar-link {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 18px;
        border-radius: 8px;
        border: 1px solid #9b7658;
        background: transparent;
        color: #6b4328;
        text-decoration: none;
        font-size: 15px;
        font-weight: bold;
        cursor: pointer;
        transition: 0.2s;
    }

    .sidebar-link:hover {
        background: #d8b99a;
    }

    .sidebar-link.active {
        background: #8b5e3c;
        color: white;
        border-color: #8b5e3c;
    }

    .sidebar-link.disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }

    .sidebar-link.disabled:hover {
        background: transparent;
    }


    /* Logout */

    .sidebar-logout {
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px dashed #9b7658;
    }

    .sidebar-logout form {
        width: 100%;
    }

    .logout-button {
        font-family: inherit;
        text-align: left;
    }

    .logout-button:hover {
        background: #8b5e3c;
        color: white;
    }
</style>