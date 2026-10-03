@if ($paginator->hasPages())
<nav class="inventory-pagination" role="navigation" aria-label="{{ __('Pagination Navigation') }}">

    {{-- Previous --}}
    @if ($paginator->onFirstPage())
    <span
        class="pagination-button pagination-arrow disabled"
        aria-disabled="true">
        ‹
    </span>
    @else
    <a
        href="{{ $paginator->previousPageUrl() }}"
        class="pagination-button pagination-arrow"
        rel="prev"
        aria-label="{{ __('pagination.previous') }}">
        ‹
    </a>
    @endif

    {{-- Page Numbers --}}
    @foreach ($elements as $element)

    {{-- Three dots --}}
    @if (is_string($element))
    <span class="pagination-dots">
        {{ $element }}
    </span>
    @endif

    {{-- Page links --}}
    @if (is_array($element))
    @foreach ($element as $page => $url)

    @if ($page == $paginator->currentPage())
    <span
        class="pagination-button active"
        aria-current="page">
        {{ $page }}
    </span>
    @else
    <a
        href="{{ $url }}"
        class="pagination-button"
        aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
        {{ $page }}
    </a>
    @endif

    @endforeach
    @endif

    @endforeach

    {{-- Next --}}
    @if ($paginator->hasMorePages())
    <a
        href="{{ $paginator->nextPageUrl() }}"
        class="pagination-button pagination-arrow"
        rel="next"
        aria-label="{{ __('pagination.next') }}">
        ›
    </a>
    @else
    <span
        class="pagination-button pagination-arrow disabled"
        aria-disabled="true">
        ›
    </span>
    @endif

</nav>
@endif