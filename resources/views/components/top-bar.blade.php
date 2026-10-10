@php
$user = Auth::user();
$roleName = $user->role->name ?? 'User';

// Initials for the avatar fallback
$initials = collect(explode(' ', trim($user->name ?? 'User')))
->filter()
->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
->take(2)
->implode('');

// Friendly page title from the current route
$pageTitle = match (true) {
request()->routeIs('dashboard') => 'Dashboard',
request()->routeIs('inventory.*') => 'Inventory',
request()->is('kitchen*') => 'Kitchen',
request()->routeIs('finance-report.*') => 'Finance Report',
request()->routeIs('purchases.*') => 'Purchase Management',
request()->routeIs('suppliers.*') => 'Suppliers',
request()->routeIs('users.*') => 'Users',
request()->routeIs('profile.*') => 'Profile',
default => 'The Brewing Bar',
};
@endphp

<header class="top-bar">

    {{-- Left: page title --}}
    <div class="top-bar-left">
        <h2 class="top-bar-page-title">{{ $pageTitle }}</h2>
        <span class="top-bar-date">{{ now()->format('l, F j, Y') }}</span>
    </div>

    {{-- Right: account + profile --}}
    <div class="top-bar-right">

        {{-- Optional: subtle indicator dot --}}
        <span class="top-bar-status" aria-hidden="true"></span>

        {{-- User dropdown --}}
        <div class="top-bar-user" id="topBarUser" tabindex="0">
            <button
                type="button"
                class="top-bar-user-button"
                id="topBarUserButton"
                aria-haspopup="true"
                aria-expanded="false">

                <span class="top-bar-avatar" aria-hidden="true">
                    @if (!empty($user->profile_photo_url))
                    <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}">
                    @else
                    <span class="top-bar-avatar-initials">{{ $initials ?: 'U' }}</span>
                    @endif
                </span>

                <span class="top-bar-user-meta">
                    <span class="top-bar-user-name">{{ $user->name ?? 'User' }}</span>
                    <span class="top-bar-user-role">{{ $roleName }}</span>
                </span>

                <svg class="top-bar-caret" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="m6 9 6 6 6-6" />
                </svg>

            </button>

            {{-- Dropdown --}}
            <div class="top-bar-dropdown" role="menu" aria-hidden="true">
                <a href="{{ route('profile.edit') }}" class="top-bar-dropdown-item" role="menuitem">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8z" />
                        <path d="M4 20a8 8 0 0 1 16 0" />
                    </svg>
                    <span>My Profile</span>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="top-bar-dropdown-item top-bar-dropdown-danger" role="menuitem">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M10 4H5v16h5" />
                            <path d="M14 8l4 4-4 4" />
                            <path d="M18 12H9" />
                        </svg>
                        <span>Log Out</span>
                    </button>
                </form>
            </div>
        </div>

    </div>

</header>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const wrap = document.getElementById('topBarUser');
        const button = document.getElementById('topBarUserButton');
        const menu = wrap?.querySelector('.top-bar-dropdown');

        if (!wrap || !button || !menu) return;

        function open() {
            wrap.classList.add('is-open');
            button.setAttribute('aria-expanded', 'true');
            menu.setAttribute('aria-hidden', 'false');
        }

        function close() {
            wrap.classList.remove('is-open');
            button.setAttribute('aria-expanded', 'false');
            menu.setAttribute('aria-hidden', 'true');
        }

        button.addEventListener('click', function(e) {
            e.stopPropagation();
            wrap.classList.contains('is-open') ? close() : open();
        });

        document.addEventListener('click', function(e) {
            if (!wrap.contains(e.target)) close();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') close();
        });
    });
</script>