<nav class="sidebar" id="sidebar">

    {{-- Sidebar Header --}}
    <div class="sidebar-header">

        <button
            type="button"
            class="sidebar-toggle"
            id="sidebarToggle"
            aria-label="Toggle sidebar"
            title="Toggle sidebar">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        {{-- Brand --}}
        <a href="{{ route('dashboard') }}" class="sidebar-brand">

            <img
                src="{{ asset('images/logo/brewing-bar-logo.png') }}"
                alt="The Brewing Bar"
                class="brewing-logo">

            <div class="sidebar-brand-text">
                <div class="sidebar-title">
                    The Brewing Bar
                </div>

                <div class="sidebar-subtitle">
                    POS & Inventory System
                </div>
            </div>

        </a>

    </div>


    {{-- Navigation Links --}}
    <div class="sidebar-links">

        {{-- Dashboard --}}
        <a
            href="{{ route('dashboard') }}"
            class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
            title="Dashboard">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M3 11.5 12 4l9 7.5" />
                <path d="M5 10.5V20h14v-9.5" />
                <path d="M9 20v-6h6v6" />
            </svg>

            <span class="sidebar-link-text">
                Dashboard
            </span>
        </a>


        {{-- POS --}}
        <a
            href="/pos"
            class="sidebar-link {{ request()->is('pos') ? 'active' : '' }}"
            title="POS">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M6 3h12v18H6z" />
                <path d="M9 7h6" />
                <path d="M9 11h2M13 11h2" />
                <path d="M9 15h2M13 15h2" />
                <path d="M9 19h6" />
            </svg>

            <span class="sidebar-link-text">
                POS
            </span>
        </a>


        {{-- Kitchen --}}
        <a
            href="/kitchen"
            class="sidebar-link {{ request()->is('kitchen*') ? 'active' : '' }}"
            title="Kitchen">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4 10h16v10H4z" />
                <path d="M7 10V7a5 5 0 0 1 10 0v3" />
                <path d="M8 14h8" />
                <path d="M8 17h5" />
            </svg>

            <span class="sidebar-link-text">
                Kitchen
            </span>
        </a>


        {{-- Inventory --}}
        <a
            href="/inventory"
            class="sidebar-link {{ request()->is('inventory*') ? 'active' : '' }}"
            title="Inventory">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4 7h16v13H4z" />
                <path d="M7 7V4h10v3" />
                <path d="M8 11h8" />
                <path d="M8 15h5" />
            </svg>

            <span class="sidebar-link-text">
                Inventory
            </span>
        </a>


        {{-- Finance Report --}}
        <a
            href="{{ route('finance-report.index') }}"
            class="sidebar-link {{ request()->routeIs('finance-report.index') ? 'active' : '' }}"
            title="Finance Report">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4 19V5" />
                <path d="M4 19h16" />
                <path d="m7 15 4-4 3 2 5-6" />
            </svg>

            <span class="sidebar-link-text">
                Finance Report
            </span>
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
                class="sidebar-link logout-button"
                title="Logout">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M10 4H5v16h5" />
                    <path d="M14 8l4 4-4 4" />
                    <path d="M18 12H9" />
                </svg>

                <span class="sidebar-link-text">
                    Logout
                </span>
            </button>

        </form>

    </div>

</nav>


<script>
    document.addEventListener('DOMContentLoaded', function() {

        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');

        if (!sidebar || !sidebarToggle) {
            return;
        }

        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('expanded');
        });

    });
</script>