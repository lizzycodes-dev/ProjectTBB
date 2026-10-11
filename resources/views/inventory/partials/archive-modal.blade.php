{{-- ============================================================
     ARCHIVE MODAL
     ============================================================ --}}

<div id="archiveModal" class="inventory-modal">

    {{-- Background overlay --}}
    <div class="inventory-modal-backdrop"></div>

    <div class="inventory-modal-content archive-modal">

        {{-- Header --}}
        <div class="inventory-modal-header">
            <div>
                <h2>Archived Inventory</h2>
                <p>View and restore inactive inventory items.</p>
            </div>

            <button
                type="button"
                class="inventory-modal-close"
                id="closeArchiveModal">
                &times;
            </button>
        </div>

        {{-- Body --}}
        <div class="inventory-modal-body">

            {{-- Search and Date Filters --}}
            <div class="archive-filters" id="archiveFilterForm">

                <div class="archive-search">
                    <input
                        type="search"
                        id="archiveSearch"
                        placeholder="Search archived items...">
                </div>

                <div class="archive-date-filters">
                    <input type="date" id="archiveFrom" aria-label="Updated from">
                    <span>to</span>
                    <input type="date" id="archiveTo" aria-label="Updated to">
                </div>

                <button
                    type="button"
                    class="archive-filter-button"
                    id="applyArchiveFilters">
                    Filter
                </button>

                <button
                    type="button"
                    class="archive-reset-button"
                    id="resetArchiveFilters">
                    Reset
                </button>

            </div>

            @if ($archivedItems->isEmpty())

            <div class="archive-empty-state" id="archiveEmptyState">
                <p>No archived inventory items.</p>
            </div>

            @else

            <div class="archive-table-wrapper">

                <table class="archive-table">

                    <thead>
                        <tr>
                            <th>ITEM NAME</th>
                            <th>CATEGORY</th>
                            <th>LOCATION</th>
                            <th>UNIT</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>

                    <tbody id="archivedItemsTable">

                        @foreach ($archivedItems as $item)

                        <tr
                            class="archived-item-row"
                            data-name="{{ strtolower($item->name) }}"
                            data-updated-at="{{ $item->updated_at?->format('Y-m-d') }}">

                            <td>{{ $item->name }}</td>

                            <td>
                                {{ $item->category->name ?? '—' }}
                            </td>

                            <td>
                                {{ $item->inventoryLocation->name ?? '—' }}
                            </td>

                            <td>
                                {{ $item->unit->name ?? '—' }}
                            </td>

                            <td>
                                <form
                                    method="POST"
                                    action="{{ route('inventory.toggle-active', $item->id) }}">

                                    @csrf

                                    <button
                                        type="submit"
                                        class="archive-restore-button">
                                        Restore
                                    </button>

                                </form>
                            </td>

                        </tr>

                        @endforeach

                    </tbody>

                </table>

                <p id="archiveNoResults" hidden>
                    No archived items match your filters.
                </p>

            </div>

            @endif

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('archiveSearch');
        const fromInput = document.getElementById('archiveFrom');
        const toInput = document.getElementById('archiveTo');
        const filterButton = document.getElementById('applyArchiveFilters');
        const resetButton = document.getElementById('resetArchiveFilters');
        const noResults = document.getElementById('archiveNoResults');

        const rows = document.querySelectorAll('.archived-item-row');

        if (!searchInput || !fromInput || !toInput ||
            !filterButton || !resetButton) {
            return;
        }

        function filterArchivedItems() {
            const search = searchInput.value.trim().toLowerCase();
            const from = fromInput.value;
            const to = toInput.value;

            let visibleCount = 0;

            rows.forEach(function(row) {
                const name = row.dataset.name || '';
                const updatedAt = row.dataset.updatedAt || '';

                const matchesSearch = name.includes(search);
                const matchesFrom = !from || (updatedAt !== '' && updatedAt >= from);
                const matchesTo = !to || (updatedAt !== '' && updatedAt <= to);

                const matches = matchesSearch && matchesFrom && matchesTo;

                row.hidden = !matches;

                if (matches) {
                    visibleCount++;
                }
            });

            if (noResults) {
                noResults.hidden = visibleCount !== 0;
            }
        }

        filterButton.addEventListener('click', filterArchivedItems);

        resetButton.addEventListener('click', function() {
            searchInput.value = '';
            fromInput.value = '';
            toInput.value = '';

            filterArchivedItems();
        });
    });
</script>