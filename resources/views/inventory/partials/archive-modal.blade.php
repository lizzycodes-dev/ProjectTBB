{{-- ============================================================
     ARCHIVE MODAL
     ============================================================ --}}

<div
    id="archiveModal"
    class="inventory-modal">

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

            @if ($archivedItems->isEmpty())

            <div class="archive-empty-state">
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

                    <tbody>

                        @foreach ($archivedItems as $item)

                        <tr>

                            <td>
                                {{ $item->name }}
                            </td>

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

            </div>

            @endif

        </div>

    </div>

</div>