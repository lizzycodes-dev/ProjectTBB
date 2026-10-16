                    @php
                    $categoryIcons = [
                    'Coffee' => [
                    '
                    <path d="M4 8h12v5a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4V8z" />',
                    '
                    <path d="M16 9h2a2 2 0 0 1 0 4h-2" />',
                    '
                    <path d="M8 3v2M12 3v2" />',
                    ],
                    'Non Coffee' => [
                    '
                    <path d="M5 8h11v6a5 5 0 0 1-5 5H10a5 5 0 0 1-5-5V8z" />',
                    '
                    <path d="M16 10h2a2 2 0 0 1 0 4h-2" />',
                    '
                    <circle cx="8" cy="4" r="1" />',
                    '
                    <circle cx="12" cy="4" r="1" />',
                    ],
                    'Frappe Ice Cream on Top' => [
                    '
                    <path d="M6 8h12l-1.5 12h-9L6 8z" />',
                    '
                    <path d="M8 8a4 4 0 0 1 8 0" />',
                    '
                    <circle cx="12" cy="3" r="1" />',
                    ],
                    'Smoothies Ice Cream on Top' => [
                    '
                    <path d="M6 9h12l-1.5 11h-9L6 9z" />',
                    '
                    <path d="M9 9V7a3 3 0 0 1 6 0v2" />',
                    '
                    <path d="M12 4v3" />',
                    ],
                    'Popping Boba Pearls' => [
                    '
                    <path d="M6 8h12l-1.5 12h-9L6 8z" />',
                    '
                    <path d="M10 4l3-1" />',
                    '
                    <circle cx="10" cy="13" r="1" />',
                    '
                    <circle cx="14" cy="13" r="1" />',
                    '
                    <circle cx="12" cy="17" r="1" />',
                    ],
                    'Oreo Milk Series' => [
                    '
                    <circle cx="12" cy="12" r="8" />',
                    '
                    <circle cx="12" cy="12" r="3" />',
                    '
                    <path d="M9 9l6 6M15 9l-6 6" />',
                    ],
                    'Fizzy Coolers' => [
                    '
                    <path d="M8 3h8l-1 18H9L8 3z" />',
                    '
                    <path d="M8 8h8" />',
                    '
                    <circle cx="11" cy="13" r="1" />',
                    '
                    <circle cx="13" cy="17" r="1" />',
                    ],
                    'Buy 1 Take 1 Smoothies' => [
                    '
                    <path d="M6 8h10l-1 12H7L6 8z" />',
                    '
                    <path d="M16 8h3l-1 12h-1" />',
                    '
                    <path d="M9 5V3M13 5V3" />',
                    ],
                    'Soda Fruit Jelly Buy 1 Take 1' => [
                    '
                    <path d="M8 3h8l-1 18H9L8 3z" />',
                    '
                    <circle cx="11" cy="12" r="1" />',
                    '
                    <circle cx="13" cy="16" r="1" />',
                    '
                    <path d="M15 6l3-3" />',
                    ],
                    'Fruit Juice Pitcher' => [
                    '
                    <path d="M8 5h8l-2 14H10L8 5z" />',
                    '
                    <path d="M6 9l3-1M15 9l3-1" />',
                    '
                    <path d="M9 9h6" />',
                    ],
                    'Rice Meals' => [
                    '
                    <path d="M4 13h16a8 8 0 0 1-16 0z" />',
                    '
                    <path d="M9 6c0-1 1-1 1-2M13 6c0-1 1-1 1-2" />',
                    ],
                    'Rice Toppings' => [
                    '
                    <path d="M6 13h12a6 6 0 0 1-12 0z" />',
                    '
                    <path d="M9 8h6M11 5h2" />',
                    ],
                    'Snack Meals' => [
                    '
                    <path d="M5 12h14a3 3 0 0 1-3 3H8a3 3 0 0 1-3-3z" />',
                    '
                    <path d="M7 12l1-4h8l1 4" />',
                    '
                    <path d="M12 8V5" />',
                    ],
                    ];

                    $categoryFallbackIcon = [
                    '
                    <path d="M4 6h16" />',
                    '
                    <path d="M4 12h16" />',
                    '
                    <path d="M4 18h16" />',
                    ];
                    @endphp

                    @php
                    use App\Models\Category;

                    $posCategoryNames = [
                    'Coffee',
                    'Non Coffee',
                    'Frappe Ice Cream on Top',
                    'Smoothies Ice Cream on Top',
                    'Popping Boba Pearls',
                    'Oreo Milk Series',
                    'Fizzy Coolers',
                    'Buy 1 Take 1 Smoothies',
                    'Soda Fruit Jelly Buy 1 Take 1',
                    'Fruit Juice Pitcher',
                    'Rice Meals',
                    'Rice Toppings',
                    'Snack Meals',
                    ];

                    $posCategories = Category::whereIn('name', $posCategoryNames)
                    ->orderByRaw("FIELD(name, '" . implode("','", $posCategoryNames) . "')")
                    ->get(['id', 'name']);
                    @endphp

                    {{-- Mobile hamburger --}}
                    <button
                        type="button"
                        class="pos-mobile-toggle"
                        id="posMobileToggle"
                        aria-label="Open menu">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    {{-- Mobile backdrop --}}
                    <div class="pos-mobile-backdrop" id="posMobileBackdrop"></div>

                    {{-- Sidebar --}}
                    <nav class="pos-sidebar" id="posSidebar">

                        <div class="pos-sidebar-header">
                            <a href="/pos" class="pos-sidebar-brand">
                                <img
                                    src="{{ asset('images/logo/brewing-bar-logo.png') }}"
                                    alt="The Brewing Bar"
                                    class="pos-sidebar-logo">

                                <div class="pos-sidebar-brand-text">
                                    <div class="pos-sidebar-title">
                                        The Brewing Bar
                                    </div>
                                    <div class="pos-sidebar-subtitle">
                                        Point of Sale
                                    </div>
                                </div>
                            </a>
                        </div>

                        {{-- Back to Inventory (hidden for cashiers) --}}
                        @if (Auth::user()->role_id !== 2)
                        <div class="pos-sidebar-back-wrap">
                            <a
                                href="{{ route('dashboard') }}"
                                class="pos-sidebar-link"
                                title="Back to Inventory">

                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M15 18l-6-6 6-6" />
                                </svg>

                                <span class="pos-sidebar-link-text">
                                    Back
                                </span>
                            </a>
                        </div>
                        @endif

                        {{-- POS Categories --}}
                        <div class="pos-sidebar-links">
                            <button
                                type="button"
                                class="pos-sidebar-link pos-category-link active"
                                data-category="All"
                                onclick="filterCategory(this.dataset.category, this)"
                                title="All">

                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M4 4h7v7H4z" />
                                    <path d="M13 4h7v7h-7z" />
                                    <path d="M4 13h7v7H4z" />
                                    <path d="M13 13h7v7h-7z" />
                                </svg>

                                <span class="pos-sidebar-link-text">
                                    All
                                </span>
                            </button>

                            @foreach ($posCategories as $category)
                            @php
                            $paths = $categoryIcons[$category->name] ?? $fallbackIcon;
                            @endphp

                            <button
                                type="button"
                                class="pos-sidebar-link pos-category-link"
                                data-category="{{ $category->name }}"
                                onclick="filterCategory(this.dataset.category, this)"
                                title="{{ $category->name }}">

                                @php
                                $paths = $categoryIcons[$category->name] ?? $categoryFallbackIcon;
                                @endphp

                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    @foreach ($paths as $path)
                                    {!! $path !!}
                                    @endforeach
                                </svg>

                                <span class="pos-sidebar-link-text">
                                    {{ $category->name }}
                                </span>
                            </button>
                            @endforeach
                        </div>

                        {{-- Logout --}}
                        <div class="pos-sidebar-logout">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="pos-sidebar-link pos-sidebar-logout-button"
                                    title="Logout">

                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M10 4H5v16h5" />
                                        <path d="M14 8l4 4-4 4" />
                                        <path d="M18 12H9" />
                                    </svg>

                                    <span class="pos-sidebar-link-text">
                                        Logout
                                    </span>
                                </button>
                            </form>
                        </div>

                    </nav>

                    <script>
                        (function() {
                            // Runs as soon as the browser hits it, but safe either way.
                            function init() {
                                const sidebar = document.querySelector('.pos-sidebar');
                                const toggle = document.getElementById('posMobileToggle');
                                const backdrop = document.getElementById('posMobileBackdrop');

                                if (!sidebar || !toggle) {
                                    console.warn('[POS] sidebar or toggle not found');
                                    return;
                                }

                                function openSidebar() {
                                    sidebar.classList.add('is-open');
                                    if (backdrop) backdrop.classList.add('is-open');
                                }

                                function closeSidebar() {
                                    sidebar.classList.remove('is-open');
                                    if (backdrop) backdrop.classList.remove('is-open');
                                }

                                toggle.addEventListener('click', function(e) {
                                    e.stopPropagation();
                                    if (sidebar.classList.contains('is-open')) {
                                        closeSidebar();
                                    } else {
                                        openSidebar();
                                    }
                                });

                                if (backdrop) {
                                    backdrop.addEventListener('click', closeSidebar);
                                }

                                sidebar.querySelectorAll('.pos-sidebar-link').forEach(function(link) {
                                    link.addEventListener('click', closeSidebar);
                                });

                                document.addEventListener('keydown', function(e) {
                                    if (e.key === 'Escape') closeSidebar();
                                });

                                // Debug helper — check the console
                                console.log('[POS] mobile toggle ready');
                            }

                            if (document.readyState === 'loading') {
                                document.addEventListener('DOMContentLoaded', init);
                            } else {
                                init();
                            }
                        })();
                    </script>