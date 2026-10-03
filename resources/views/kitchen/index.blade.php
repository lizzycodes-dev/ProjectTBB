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

                            <strong>
                                {{ $order->order_number }}
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
                                {{ $kitchenOrder->orderItem->menuItem->name }}
                            </div>


                            @if ($kitchenOrder->orderItem->options->count())

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


                            @if (Auth::user()->role_id === 3)

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

                            <strong>
                                {{ $order->order_number }}
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
                                {{ $kitchenOrder->orderItem->menuItem->name }}
                            </div>


                            @if ($kitchenOrder->orderItem->options->count())

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


                            @if (Auth::user()->role_id === 3)

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

                            <strong>
                                {{ $order->order_number }}
                            </strong>

                            <span>
                                {{ $order->order_type }}
                            </span>

                        </div>


                        @foreach ($orderItems as $kitchenOrder)

                        <div class="kitchen-item">

                            <div class="item-main">
                                {{ $kitchenOrder->orderItem->quantity }}x
                                {{ $kitchenOrder->orderItem->menuItem->name }}
                            </div>


                            @if ($kitchenOrder->orderItem->options->count())

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


                        @if (Auth::user()->role_id === 3)

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

        <div class="completed-section">

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
                        <strong>
                            {{ $order->order_number }}
                        </strong>

                        <span>
                            {{ $order->order_type }}
                        </span>
                    </div>

                    <div class="completed-items">

                        @foreach ($orderItems as $kitchenOrder)

                        {{ $kitchenOrder->orderItem->quantity }}x
                        {{ $kitchenOrder->orderItem->menuItem->name }}

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

        </div>

    </div>


    <style>
        /* =========================
           PAGE
        ========================= */

        .kitchen-page {
            min-height: calc(100vh - 70px);

            padding: 28px 30px;

            background: #f8f1e8;

            color: #6b4328;
        }


        /* =========================
           HEADER
        ========================= */

        .kitchen-header {
            margin-bottom: 22px;
        }


        .kitchen-header h1 {
            margin: 0;

            font-family: Georgia, serif;

            font-size: 25px;

            font-weight: bold;

            color: #6b4328;
        }


        .kitchen-header p {
            margin: 4px 0 0;

            color: #8b6a50;

            font-size: 13px;
        }


        /* =========================
           MESSAGES
        ========================= */

        .kitchen-message {
            padding: 10px 14px;

            margin-bottom: 18px;

            border-radius: 6px;

            font-size: 13px;
        }


        .kitchen-message.success {
            background: #e4eadc;
            color: #536345;
        }


        .kitchen-message.error {
            background: #ead5d0;
            color: #8a4035;
        }


        /* =========================
           BOARD
        ========================= */

        .kitchen-board {
            display: grid;

            grid-template-columns:
                repeat(3, minmax(0, 1fr));

            gap: 18px;

            align-items: start;
        }


        .kitchen-column {
            min-width: 0;
        }


        .column-title {
            margin-bottom: 10px;

            font-family: Georgia, serif;

            font-size: 19px;

            font-weight: bold;

            font-style: italic;
        }


        .pending-title {
            color: #c45a54;
        }


        .preparing-title {
            color: #76543c;
        }


        .ready-title {
            color: #4e9b61;
        }


        .column-orders {
            display: flex;

            flex-direction: column;

            gap: 12px;
        }


        /* =========================
           ORDER CARD
        ========================= */

        .kitchen-card {
            padding: 0 12px 12px;

            border-radius: 7px;

            background: #d5b99f;

            box-shadow:
                0 2px 5px rgba(107, 67, 40, 0.10);

            overflow: hidden;
        }


        .order-top {
            display: flex;

            align-items: center;

            justify-content: space-between;

            margin: 0 -12px 8px;

            padding: 5px 10px;

            background: #b18463;

            color: white;

            font-size: 11px;
        }


        .order-top strong {
            font-family: Georgia, serif;
        }


        .order-top span {
            font-size: 10px;
        }


        .kitchen-item {
            padding: 9px 0;

            border-bottom: 1px solid #b9987d;
        }


        .kitchen-item:last-child {
            border-bottom: none;
        }


        .item-main {
            color: #76543c;

            font-size: 12px;

            font-weight: bold;
        }


        .item-options {
            margin-top: 4px;

            color: #8b6a50;

            font-size: 11px;
        }


        .item-notes {
            margin-top: 5px;

            color: #8a4035;

            font-size: 11px;
        }


        .prepared-by {
            margin-top: 5px;

            color: #8b6a50;

            font-size: 10px;
        }


        /* =========================
           BUTTONS
        ========================= */

        .kitchen-button,
        .complete-button {
            width: 100%;

            margin-top: 9px;

            padding: 7px 10px;

            border: 1px solid #a97e5f;

            border-radius: 6px;

            background: transparent;

            color: #76543c;

            font-family: Georgia, serif;

            font-size: 11px;

            font-weight: bold;

            cursor: pointer;
        }


        .kitchen-button:hover {
            background: #b18463;

            color: white;
        }


        .complete-button {
            background: #4e9b61;

            border-color: #4e9b61;

            color: white;
        }


        .complete-button:hover {
            background: #3d7f4d;
        }


        /* =========================
           EMPTY
        ========================= */

        .empty-column {
            padding: 25px 12px;

            text-align: center;

            border: 1px dashed #b9987d;

            border-radius: 7px;

            color: #9b7658;

            font-size: 12px;
        }


        /* =========================
           COMPLETED
        ========================= */

        .completed-section {
            margin-top: 30px;
        }


        .completed-header {
            margin-bottom: 12px;

            color: #6b4328;

            font-family: Georgia, serif;

            font-size: 19px;

            font-weight: bold;

            font-style: italic;
        }


        .completed-orders {
            display: grid;

            grid-template-columns:
                repeat(auto-fill, minmax(260px, 1fr));

            gap: 12px;
        }


        .completed-card {
            padding: 12px;

            border: 1px solid #d8c2aa;

            border-radius: 7px;

            background: #fffaf4;

            color: #76543c;
        }


        .completed-card>div:first-child {
            display: flex;

            justify-content: space-between;

            font-size: 12px;
        }


        .completed-items {
            margin-top: 7px;

            font-size: 11px;
        }


        .completed-card small {
            display: block;

            margin-top: 7px;

            color: #9b7658;

            font-size: 10px;
        }


        .empty-completed {
            color: #9b7658;

            font-size: 12px;
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 900px) {

            .kitchen-board {
                grid-template-columns: 1fr;
            }

        }
    </style>

</x-app-layout>