<x-app-layout>

    <div class="kitchen-page">

        {{-- Header --}}
        <div class="kitchen-header">

            <div>
                <h1>Kitchen / Bar Orders</h1>
                <p>Manage active orders and preparation status.</p>
            </div>

        </div>


        {{-- Messages --}}
        @if (session('success'))
        <div class="kitchen-message success">
            {{ session('success') }}
        </div>
        @endif

        @if (session('error'))
        <div class="kitchen-message error">
            {{ session('error') }}
        </div>
        @endif


        {{-- Order Board --}}
        <div class="kitchen-board">

            {{-- =========================
                 PENDING
            ========================= --}}
            <div class="kitchen-column">

                <div class="column-title pending-title">
                    Pending
                </div>

                <div class="column-orders">

                    @php
                    $pendingOrders = $orders->filter(
                    fn ($orderItems) =>
                    $orderItems->contains(
                    fn ($item) => $item->status === 'Pending'
                    )
                    );
                    @endphp

                    @forelse ($pendingOrders as $orderItems)

                    @php
                    $firstKitchenOrder = $orderItems->first();
                    $order = $firstKitchenOrder->orderItem->order;
                    @endphp

                    <div class="kitchen-card">

                        <div class="order-top">

                            <strong class="queue-badge" title="{{ $order->order_number }}">
                                Queue {{ $order->queue_label }}
                            </strong>

                            <span>
                                {{ $order->order_type }}
                            </span>

                        </div>


                        @foreach ($orderItems as $kitchenOrder)

                        @if ($kitchenOrder->status === 'Pending')

                        <div class="kitchen-item">

                            <div class="item-main">
                                {{ $kitchenOrder->orderItem->quantity }}x
                                {{ $kitchenOrder->orderItem->InventoryItem->name }}
                            </div>


                            @if ($kitchenOrder->orderItem->notes)

                            <div class="item-notes">
                                Note:
                                {{ $kitchenOrder->orderItem->notes }}
                            </div>

                            @endif


                            @if (in_array(Auth::user()->role_id, [1, 3]))

                            <form
                                method="POST"
                                action="/kitchen/{{ $kitchenOrder->id }}/start">
                                @csrf

                                <button
                                    type="submit"
                                    class="kitchen-button">
                                    Start Preparing
                                </button>

                            </form>

                            @endif

                        </div>

                        @endif

                        @endforeach

                    </div>

                    @empty

                    <div class="empty-column">
                        No pending orders.
                    </div>

                    @endforelse

                </div>

            </div>


            {{-- =========================
                 PREPARING
            ========================= --}}
            <div class="kitchen-column">

                <div class="column-title preparing-title">
                    Preparing
                </div>

                <div class="column-orders">

                    @php
                    $preparingOrders = $orders->filter(
                    fn ($orderItems) =>
                    $orderItems->contains(
                    fn ($item) => $item->status === 'Preparing'
                    )
                    );
                    @endphp

                    @forelse ($preparingOrders as $orderItems)

                    @php
                    $firstKitchenOrder = $orderItems->first();
                    $order = $firstKitchenOrder->orderItem->order;
                    @endphp

                    <div class="kitchen-card">

                        <div class="order-top">

                            <strong class="queue-badge" title="{{ $order->order_number }}">
                                Queue {{ $order->queue_label }}
                            </strong>

                            <span>
                                {{ $order->order_type }}
                            </span>

                        </div>


                        @foreach ($orderItems as $kitchenOrder)

                        @if ($kitchenOrder->status === 'Preparing')

                        <div class="kitchen-item">

                            <div class="item-main">
                                {{ $kitchenOrder->orderItem->quantity }}x
                                {{ $kitchenOrder->orderItem->InventoryItem->name }}
                            </div>


                            @if ($kitchenOrder->orderItem->options?->count())

                            <div class="item-options">

                                @foreach ($kitchenOrder->orderItem->options as $option)

                                {{ $option->optionValue->name }}

                                @if (!$loop->last)
                                •
                                @endif

                                @endforeach

                            </div>

                            @endif


                            @if ($kitchenOrder->orderItem->notes)

                            <div class="item-notes">
                                Note:
                                {{ $kitchenOrder->orderItem->notes }}
                            </div>

                            @endif


                            @if ($kitchenOrder->preparedBy)

                            <div class="prepared-by">
                                By {{ $kitchenOrder->preparedBy->name }}
                            </div>

                            @endif


                            @if (in_array(Auth::user()->role_id, [1, 3]))

                            <form
                                method="POST"
                                action="/kitchen/{{ $kitchenOrder->id }}/complete">
                                @csrf

                                <button
                                    type="submit"
                                    class="kitchen-button">
                                    Mark as Ready
                                </button>

                            </form>

                            @endif

                        </div>

                        @endif

                        @endforeach

                    </div>

                    @empty

                    <div class="empty-column">
                        No orders being prepared.
                    </div>

                    @endforelse

                </div>

            </div>


            {{-- =========================
                 READY
            ========================= --}}
            <div class="kitchen-column">

                <div class="column-title ready-title">
                    Ready
                </div>

                <div class="column-orders">

                    @php
                    $readyOrders = $orders->filter(
                    fn ($orderItems) =>
                    $orderItems->every(
                    fn ($item) => $item->status === 'Ready'
                    )
                    );
                    @endphp

                    @forelse ($readyOrders as $orderItems)

                    @php
                    $firstKitchenOrder = $orderItems->first();
                    $order = $firstKitchenOrder->orderItem->order;
                    @endphp

                    <div class="kitchen-card">

                        <div class="order-top">

                            <strong class="queue-badge" title="{{ $order->order_number }}">
                                Queue {{ $order->queue_label }}
                            </strong>

                            <span>
                                {{ $order->order_type }}
                            </span>

                        </div>


                        @foreach ($orderItems as $kitchenOrder)

                        <div class="kitchen-item">

                            <div class="item-main">
                                {{ $kitchenOrder->orderItem->quantity }}x
                                {{ $kitchenOrder->orderItem->InventoryItem->name }}
                            </div>


                            @if ($kitchenOrder->orderItem->options?->count())

                            <div class="item-options">

                                @foreach ($kitchenOrder->orderItem->options as $option)

                                {{ $option->optionValue->name }}

                                @if (!$loop->last)
                                •
                                @endif

                                @endforeach

                            </div>

                            @endif

                        </div>

                        @endforeach


                        @if (in_array(Auth::user()->role_id, [1, 3]))

                        <form
                            method="POST"
                            action="/orders/{{ $order->id }}/complete">
                            @csrf

                            <button
                                type="submit"
                                class="complete-button">
                                Complete Order
                            </button>

                        </form>

                        @endif

                    </div>

                    @empty

                    <div class="empty-column">
                        No ready orders.
                    </div>

                    @endforelse

                </div>

            </div>

        </div>


        {{-- =========================
             COMPLETED ORDERS
        ========================= --}}

        <div class="completed-section" id="completed">

            <div class="completed-header">
                Completed Orders
            </div>

            <div class="completed-orders">

                @forelse ($completedOrders as $orderItems)

                @php
                $firstKitchenOrder = $orderItems->first();
                $order = $firstKitchenOrder->orderItem->order;
                @endphp

                <div class="completed-card">

                    <div>
                        <strong title="{{ $order->order_number }}">
                            Queue {{ $order->queue_label }}
                        </strong>

                        <span>
                            {{ $order->order_type }}
                        </span>
                    </div>

                    <div class="completed-items">

                        @foreach ($orderItems as $kitchenOrder)

                        {{ $kitchenOrder->orderItem->quantity }}x
                        {{ $kitchenOrder->orderItem->InventoryItem->name }}

                        @if (!$loop->last)
                        •
                        @endif

                        @endforeach

                    </div>

                    @if ($order->completed_at)

                    <small>
                        Completed:
                        {{ $order->completed_at }}
                    </small>

                    @endif

                </div>

                @empty

                <div class="empty-completed">
                    No completed orders.
                </div>

                @endforelse

            </div>

            @if ($completedOrders->hasPages())
            <div class="pagination-wrap">
                {{ $completedOrders->fragment('completed')->links() }}
            </div>
            @endif

        </div>

    </div>

</x-app-layout>