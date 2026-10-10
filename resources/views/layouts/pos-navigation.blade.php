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

<nav class="pos-sidebar" id="posSidebar">

    {{-- Sidebar Header --}}
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
            href="{{ route('inventory.index') }}"
            class="pos-sidebar-link"
            title="Back to Inventory">

            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M15 18l-6-6 6-6" />
            </svg>

            <span class="pos-sidebar-link-text">
                Back to Inventory
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
        <button
            type="button"
            class="pos-sidebar-link pos-category-link"
            data-category="{{ $category->name }}"
            onclick="filterCategory(this.dataset.category, this)"
            title="{{ $category->name }}">

            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4 6h16" />
                <path d="M4 12h16" />
                <path d="M4 18h16" />
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