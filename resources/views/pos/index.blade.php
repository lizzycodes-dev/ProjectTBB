<x-app-layout>

    <x-slot name="header">
        <div>
            <h2 style="
                margin: 0;
                color: #6b4328;
                font-size: 24px;
                font-weight: bold;
            ">
                Point of Sale
            </h2>

            <p style="
                margin: 5px 0 0;
                color: #76543c;
                font-size: 14px;
            ">
                Create and process customer orders
            </p>
        </div>
    </x-slot>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3e4d2;
            color: #6b4328;
        }

        /* =========================
       POS MAIN LAYOUT
    ========================= */

        .pos-container {
            display: flex;
            height: calc(100vh - 85px);
            overflow: hidden;
            background: #f3e4d2;
        }

        /* =========================
       CARD STUFF
    ========================= */
        .cart-item {
            display: flex;
            align-items: center;
            gap: 8px;

            padding: 8px;

            margin-bottom: 6px;

            background: #b99173;

            border-radius: 6px;

            color: #6b4328;
        }

        .cart-item-image {
            width: 38px;
            height: 38px;

            flex-shrink: 0;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #a97856;

            border-radius: 4px;

            color: white;

            font-family: Georgia, serif;
            font-size: 9px;
            font-weight: bold;
        }

        .cart-item-info {
            flex: 1;
            min-width: 0;
        }

        .cart-item-name {
            font-size: 11px;
            font-weight: bold;

            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .cart-item-options {
            margin-top: 2px;

            font-size: 9px;
            color: #76543c;

            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .cart-item-price {
            margin-top: 2px;

            font-size: 9px;
            font-weight: bold;
        }

        .cart-quantity {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .cart-quantity button {
            width: 24px;
            height: 24px;

            border: none;
            border-radius: 5px;

            background: #a97856;
            color: white;

            font-size: 14px;
            font-weight: bold;

            cursor: pointer;
        }

        .cart-quantity button:hover {
            background: #8b5e3c;
        }

        .cart-quantity span {
            min-width: 16px;

            text-align: center;

            font-size: 11px;
            font-weight: bold;
        }

        .cart-item-total {
            min-width: 45px;

            text-align: right;

            font-size: 10px;
            font-weight: bold;
        }

        /* =========================
       MENU SECTION
    ========================= */

        .menu-section {
            width: 65%;
            padding: 16px 26px 20px;
            overflow-y: auto;
            min-height: 0;
            background: #f8f1e8;
        }

        /* Hide old POS heading */
        .header {
            display: none;
        }

        /* =========================
       CATEGORY TABS
    ========================= */

        .category-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 14px;
            padding: 9px;
            background: #c9a98e;
            border-radius: 7px;
        }

        .category-tab {
            flex: 1;
            padding: 8px 12px;

            border: 1px solid #a97856;
            border-radius: 6px;

            background: transparent;
            color: #6b4328;

            font-family: Georgia, serif;
            font-size: 12px;
            font-weight: bold;

            cursor: pointer;
        }

        .category-tab:hover {
            background: #d8b99a;
        }

        .category-tab.active {
            background: #b48765;
            color: white;
            border-color: #b48765;
        }

        /* =========================
       MENU GRID
    ========================= */

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        /* =========================
       MENU CARD
    ========================= */

        .menu-card {
            background: #d0ad91;
            border: 1px solid #b99173;
            border-radius: 9px;

            padding: 12px;

            cursor: pointer;

            box-shadow: 0 2px 4px rgba(107, 67, 40, 0.18);

            transition: 0.15s;
        }

        .menu-card:hover {
            transform: translateY(-1px);
            border-color: #8b5e3c;
            box-shadow: 0 4px 8px rgba(107, 67, 40, 0.22);
        }

        /* =========================
       PRODUCT PHOTO
    ========================= */

        .menu-card-photo {
            height: 90px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #b99173;

            border-radius: 6px;

            margin-bottom: 7px;

            color: white;

            font-family: Georgia, serif;
            font-size: 12px;
            font-weight: bold;
        }

        /* =========================
       PRODUCT INFO
    ========================= */

        .menu-card h3 {
            margin: 0;

            color: #6b4328;

            font-family: Georgia, serif;
            font-size: 13px;
            font-weight: bold;
        }

        .menu-card-category {
            margin-top: 2px;

            color: #76543c;

            font-family: Georgia, serif;
            font-size: 10px;
            font-style: italic;
        }

        .menu-card .price {
            margin-top: 5px;

            color: #6b4328;

            font-size: 12px;
            font-weight: bold;
        }

        /* Hide Add to Order button */
        .menu-card .add-to-cart {
            display: block;
            width: 100%;
            margin-top: 12px;
            padding: 10px 14px;

            background: #6f4e37;
            color: #ffffff;

            border: none;
            border-radius: 8px;

            font-size: 14px;
            font-weight: 600;

            cursor: pointer;
            transition: background 0.2s ease, transform 0.2s ease;
        }

        .menu-card .add-to-cart:hover {
            background: #563b2a;
            transform: translateY(-1px);
        }

        .menu-card .add-to-cart:active {
            transform: translateY(0);
        }

        .menu-card .add-to-cart:disabled {
            background: #b9aaa0;
            color: #f7f3ef;
            cursor: not-allowed;
            transform: none;
        }

        /* =========================
       CART
    ========================= */

        .cart-section {
            width: 35%;

            background: #c9a98e;

            border-left: 1px solid #b99173;

            padding: 14px 12px;

            display: flex;
            flex-direction: column;

            min-height: 0;
            overflow: hidden;

            box-shadow: -3px 0 8px rgba(107, 67, 40, 0.08);
        }

        .cart-section h2 {
            margin: 0 0 10px;

            padding: 8px 10px;

            background: #a97856;
            color: white;

            border-radius: 6px 6px 0 0;

            font-family: Georgia, serif;
            font-size: 21px;
        }

        /* =========================
       CART ITEMS
    ========================= */

        .cart-items {
            flex: 1;
            overflow-y: auto;
            min-height: 0;
        }

        .empty-cart {
            text-align: center;

            margin-top: 30px;

            color: #76543c;

            font-size: 13px;
        }

        /* =========================
       CART SUMMARY
    ========================= */

        .cart-summary {
            border-top: 1px solid #a97856;

            padding-top: 10px;
            margin-top: 8px;
        }

        .cart-item-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 10px;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .quantity-controls button {
            width: 30px;
            height: 30px;
            border: 1px solid #c9aa8c;
            background: #fffaf4;
            color: #6b4328;
            border-radius: 5px;
            cursor: pointer;
        }

        .remove-item {
            border: none;
            background: none;
            color: #8b5e3c;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .remove-item:hover {
            text-decoration: underline;
        }

        .summary-row {
            display: flex;

            justify-content: space-between;

            margin-bottom: 7px;

            color: #6b4328;

            font-size: 13px;
            font-weight: bold;
        }

        .discount-buttons {
            display: flex;
            gap: 8px;

            margin: 8px 0;
        }

        .discount-button {
            flex: 1;

            padding: 8px;

            border: 1px solid #a97856;
            border-radius: 6px;

            background: #d8b99a;

            color: #6b4328;

            font-family: Georgia, serif;
            font-size: 11px;
            font-weight: bold;

            cursor: pointer;
        }

        .discount-button:hover {
            background: #b99173;
        }

        .discount-button.active {
            background: #a97856;

            border-color: #a97856;

            color: white;
        }

        .total {
            border-top: 1px solid #a97856;

            padding-top: 8px;
            margin-top: 8px;

            font-size: 17px;
        }

        .checkout-button {
            width: 100%;

            padding: 9px;

            margin-top: 8px;

            border: 1px solid #a97856;
            border-radius: 6px;

            background: #d8b99a;

            color: #6b4328;

            font-family: Georgia, serif;
            font-size: 12px;
            font-weight: bold;

            cursor: pointer;
        }

        .checkout-button:hover {
            background: #b99173;
            color: white;
        }

        /* =========================
       OPTION MODAL
    ========================= */

        .option-modal {
            display: none;

            position: fixed;
            inset: 0;

            background: rgba(60, 35, 20, 0.55);

            align-items: center;
            justify-content: center;

            z-index: 1000;
        }

        .option-modal-content {
            background: #fffaf4;

            width: 420px;
            max-width: 90%;

            padding: 24px;

            border-radius: 10px;

            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .option-modal-content h2 {
            margin-top: 0;

            color: #6b4328;

            font-family: Georgia, serif;
        }

        .option-group {
            margin-bottom: 18px;
        }

        .option-group h3 {
            margin-bottom: 9px;

            color: #6b4328;

            font-size: 15px;
        }

        .option-choice {
            display: block;

            margin-bottom: 8px;

            color: #76543c;

            font-size: 14px;
        }

        .option-modal-actions {
            display: flex;
            gap: 10px;

            margin-top: 20px;
        }

        .option-modal-actions button {
            flex: 1;

            padding: 10px;

            border: none;
            border-radius: 6px;

            cursor: pointer;
        }

        .option-modal-actions button:last-child {
            background: #8b5e3c;
            color: white;
        }

        /* =========================
       RESPONSIVE
    ========================= */

        @media (max-width: 900px) {

            .pos-container {
                flex-direction: column;
                height: auto;
            }

            .menu-section,
            .cart-section {
                width: 100%;
            }

            .cart-section {
                min-height: 450px;
            }

            .menu-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .menu-card-photo {
            width: 100%;
            height: 150px;
            overflow: hidden;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .menu-card-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }






        .menu-stock-status {
            display: inline-block;
            margin-top: 7px;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: bold;
        }

        .menu-stock-status.available {
            background: #eaf5ed;
            color: #287344;
        }

        .menu-stock-status.low {
            background: #fff0d6;
            color: #96600b;
        }

        .menu-stock-status.out {
            background: #fce8e6;
            color: #b3261e;
        }

        .menu-stock-status.not-set {
            background: #eee9e4;
            color: #75645a;
        }

        .payment-fields {
            margin-top: 8px;
        }

        .payment-fields label {
            display: block;
            margin-bottom: 4px;
            font-size: 12px;
            font-weight: bold;
            color: #6b4328;
        }

        .payment-fields input {
            width: 100%;
            padding: 8px 10px;
            box-sizing: border-box;
            border: 1px solid #a97856;
            border-radius: 6px;
            background: #fff8f0;
            color: #4a2f1c;
            font-size: 14px;
        }

        .payment-fields .summary-row {
            margin-top: 6px;
        }

        .place-order {
            padding: 11px;
            font-size: 14px;
            background: #8b5e3c;
            color: #fff;
        }

        .place-order:hover {
            background: #6b4328;
        }

        .receipt-head {
            text-align: center;
            margin-bottom: 10px;
        }

        .receipt-head h2 {
            margin: 0;
        }

        .receipt-head p {
            margin: 2px 0 8px;
            font-size: 12px;
            color: #8b6a50;
        }

        .receipt-meta {
            margin-bottom: 8px;
            text-align: center;
            font-size: 12px;
            color: #8b6a50;
        }

        .receipt-line {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin: 4px 0;
            font-size: 13px;
        }

        .receipt-totals {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #a97856;
        }

        .receipt-total {
            font-size: 16px;
        }

        .receipt-thanks {
            margin: 12px 0 0;
            text-align: center;
            font-size: 12px;
            color: #8b6a50;
        }

        .menu-card.out-of-stock {
            opacity: 0.55;
            filter: grayscale(1);
            cursor: not-allowed;
        }

        .menu-card.out-of-stock:hover {
            transform: none;
            border-color: #b99173;
            box-shadow: 0 2px 4px rgba(107, 67, 40, 0.18);
        }

        .pos-search {
            margin-bottom: 16px;
        }

        #posSearch {
            width: 100%;
            max-width: 420px;
            padding: 10px 14px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
        }

        .menu-card.search-hidden {
            display: none !important;
        }
    </style>

    <div class="pos-container">

        <!-- MENU -->
        <section class="menu-section">
            <div class="category-tabs">
                <button
                    type="button"
                    class="category-tab active"
                    data-category="All"
                    onclick="filterCategory(this.dataset.category, this)">
                    ALL
                </button>

                @foreach ($categories as $category)
                <button
                    type="button"
                    class="category-tab"
                    data-category="{{ $category->name }}"
                    onclick="filterCategory(this.dataset.category, this)">
                    {{ strtoupper($category->name) }}
                </button>
                @endforeach
            </div>
            <div class="pos-search">
                <input
                    type="search"
                    id="posSearch"
                    placeholder="Search menu items..."
                    autocomplete="off"
                    aria-label="Search menu items">
            </div>
            <div class="menu-grid">
                @foreach ($menuItems as $menuItem)
                <div
                    class="menu-card"
                    data-search="{{ strtolower($menuItem->name) }}"
                    data-category="{{ $menuItem->category->name ?? 'Menu Item' }}">

                    <div class="menu-card-photo">
                        <img
                            src="{{ asset('images/menu/sample.png') }}"
                            alt="{{ $menuItem->name }}">
                    </div>

                    <h3>{{ $menuItem->name }}</h3>

                    <div class="menu-card-category">
                        {{ $menuItem->category->name ?? 'Menu Item' }}
                    </div>

                    <div class="price">
                        ₱{{ number_format($menuItem->price, 2) }}
                    </div>

                    <button
                        type="button"
                        class="add-to-cart"
                        data-id="{{ $menuItem->id }}"
                        data-name="{{ $menuItem->name }}"
                        data-price="{{ $menuItem->price }}"
                        data-options='@json($menuItem->optionGroups)'>
                        Add to Order
                    </button>
                </div>
                @endforeach
            </div>
        </section>

        <!-- CART -->
        <section class="cart-section">
            <h2>Current Order</h2>

            <div class="discount-buttons">
                <button
                    type="button"
                    id="dineInButton"
                    class="discount-button active"
                    onclick="selectOrderType('Dine-in')">
                    Dine-in
                </button>

                <button
                    type="button"
                    id="takeoutButton"
                    class="discount-button"
                    onclick="selectOrderType('Takeout')">
                    Takeout
                </button>
            </div>

            <div class="cart-items" id="cartItems">
                <div class="empty-cart">
                    No items added.
                </div>
            </div>

            <div class="cart-summary">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span id="subtotal">₱0.00</span>
                </div>

                <div class="summary-row">
                    <span>Discount</span>
                    <span id="discount">₱0.00</span>
                </div>

                <div class="discount-buttons">
                    <button
                        type="button"
                        id="noDiscountButton"
                        class="discount-button active"
                        onclick="selectDiscount('None')">
                        No Discount
                    </button>

                    <button
                        type="button"
                        id="seniorButton"
                        class="discount-button"
                        onclick="selectDiscount('Senior')">
                        Senior
                    </button>

                    <button
                        type="button"
                        id="pwdButton"
                        class="discount-button"
                        onclick="selectDiscount('PWD')">
                        PWD
                    </button>
                </div>

                <div class="summary-row total">
                    <span>Total</span>
                    <span id="total">₱0.00</span>
                </div>

                <div class="discount-buttons">
                    <button
                        type="button"
                        id="cashButton"
                        class="discount-button active"
                        onclick="selectPayment('Cash')">
                        Cash
                    </button>

                    <button
                        type="button"
                        id="gcashButton"
                        class="discount-button"
                        onclick="selectPayment('GCash')">
                        GCash
                    </button>
                </div>

                <div id="cashPaymentFields" class="payment-fields">
                    <label for="amountReceived">Amount Received</label>
                    <input
                        type="number"
                        id="amountReceived"
                        min="0"
                        step="0.01"
                        placeholder="₱0.00">

                    <div class="summary-row">
                        <span>Change</span>
                        <span id="changeAmount">₱0.00</span>
                    </div>
                </div>

                <div
                    id="gcashPaymentFields"
                    class="payment-fields"
                    style="display: none;">
                    <p>Payment method: GCash</p>
                </div>

                <button
                    type="button"
                    class="checkout-button place-order"
                    id="placeOrderButton"
                    onclick="placeOrder()">
                    Place Order
                </button>
            </div>
        </section>
    </div>

    <!-- OPTIONS MODAL -->
    <div id="optionModal" class="option-modal">
        <div class="option-modal-content">
            <h2 id="optionMenuName"></h2>

            <div id="optionGroups"></div>

            <div class="option-modal-actions">
                <button type="button" onclick="closeOptionModal()">
                    Cancel
                </button>

                <button type="button" onclick="confirmOptions()">
                    Add to Order
                </button>
            </div>
        </div>
    </div>

    <!-- RECEIPT MODAL -->
    <div id="receiptModal" class="option-modal">
        <div class="option-modal-content">
            <div id="receiptBody"></div>

            <div class="option-modal-actions">
                <button type="button" onclick="closeReceipt()">
                    Done
                </button>
            </div>
        </div>
    </div>

    <script>
        const posSearch = document.getElementById('posSearch');

        posSearch.addEventListener('input', function() {
            const searchTerm = this.value.trim().toLowerCase();

            document.querySelectorAll('.menu-card').forEach(card => {
                const itemName = card.dataset.search || '';
                card.classList.toggle(
                    'search-hidden',
                    !itemName.includes(searchTerm)
                );
            });
        });
        let cart = [];
        let selectedMenuItem = null;

        let discountType = 'None';
        let orderType = 'Dine-in';
        let paymentMethod = 'Cash';

        const DISCOUNT_RATE = 0.20;

        /*
         * Convert the inventory-item option-group pivot data into
         * the simpler structure used by the option modal.
         *
         * Expected relation structure:
         * optionGroups -> optionGroup -> optionValues
         */
        function normalizeOptionGroups(rawGroups) {
            return (rawGroups || [])
                .map(itemGroup => {
                    const group = itemGroup.option_group;

                    if (!group) {
                        return null;
                    }

                    return {
                        id: Number(group.id),
                        name: group.name,
                        is_required: Boolean(itemGroup.is_required),
                        values: (group.option_values || []).map(value => ({
                            id: Number(value.id),
                            name: value.name,
                            price_adjustment: Number(value.price_adjustment || 0)
                        }))
                    };
                })
                .filter(group => group !== null);
        }

        /*
         * Add-to-cart buttons
         */
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function() {
                const id = Number(this.dataset.id);
                const name = this.dataset.name;
                const price = Number(this.dataset.price);

                let rawOptions = [];

                try {
                    rawOptions = JSON.parse(this.dataset.options || '[]');
                } catch (error) {
                    console.error('Could not read item options:', error);
                    alert('Could not load options for this menu item.');
                    return;
                }

                const options = normalizeOptionGroups(rawOptions);

                const menuItem = {
                    id: id,
                    name: name,
                    price: price,
                    options: options
                };

                if (options.length > 0) {
                    openOptionModal(menuItem);
                } else {
                    addToCart(id, name, price, []);
                }
            });
        });

        /*
         * Add item to cart.
         * The item ID is the inventory_items.id.
         */
        function addToCart(id, name, price, options = []) {
            const optionIds = options
                .map(option => Number(option.id))
                .sort((a, b) => a - b);

            const existingItem = cart.find(item => {
                const existingOptionIds = (item.options || [])
                    .map(option => Number(option.id))
                    .sort((a, b) => a - b);

                return item.id === id &&
                    JSON.stringify(existingOptionIds) === JSON.stringify(optionIds);
            });

            if (existingItem) {
                existingItem.quantity++;
            } else {
                cart.push({
                    id: id,
                    name: name,
                    price: price,
                    quantity: 1,
                    options: options
                });
            }

            renderCart();
        }

        /*
         * Calculate totals
         */
        function getTotals() {
            const subtotal = cart.reduce(
                (sum, item) => sum + (item.price * item.quantity),
                0
            );

            const discountAmount = discountType === 'Senior' || discountType === 'PWD' ?
                Math.round(subtotal * DISCOUNT_RATE * 100) / 100 :
                0;

            const total = Math.max(
                0,
                Math.round((subtotal - discountAmount) * 100) / 100
            );

            return {
                subtotal: subtotal,
                discountAmount: discountAmount,
                total: total
            };
        }

        /*
         * Render cart and update the summary
         */
        function renderCart() {
            const cartItems = document.getElementById('cartItems');

            if (cart.length === 0) {
                cartItems.innerHTML = `
                <div class="empty-cart">
                    No items added.
                </div>
            `;

                document.getElementById('subtotal').textContent = '₱0.00';
                document.getElementById('discount').textContent = '₱0.00';
                document.getElementById('total').textContent = '₱0.00';

                updateChange();
                return;
            }

            cartItems.innerHTML = '';

            cart.forEach((item, index) => {
                const itemSubtotal = item.price * item.quantity;

                const optionText = (item.options || [])
                    .map(option => option.name)
                    .join(' • ');

                const itemElement = document.createElement('div');
                itemElement.className = 'cart-item';

                itemElement.innerHTML = `
                <div class="cart-item-image">
                    IMAGE
                </div>

                <div class="cart-item-info">
                    <div class="cart-item-name">
                        ${esc(item.name)}
                    </div>

                    ${
                        optionText
                            ? `<div class="cart-item-options">${esc(optionText)}</div>`
                            : ''
                    }

                    <div class="cart-item-price">
                        ₱${item.price.toFixed(2)} each
                    </div>
                </div>

                <div class="cart-quantity">
                    <button
                        type="button"
                        onclick="decreaseQuantity(${index})">
                        −
                    </button>

                    <span>${item.quantity}</span>

                    <button
                        type="button"
                        onclick="increaseQuantity(${index})">
                        +
                    </button>
                </div>

                <button
                    type="button"
                    class="remove-item"
                    onclick="removeCartItem(${index})">
                    Remove
                </button>

                <div class="cart-item-total">
                    ₱${itemSubtotal.toFixed(2)}
                </div>
            `;

                cartItems.appendChild(itemElement);
            });

            const totals = getTotals();

            document.getElementById('subtotal').textContent =
                `₱${totals.subtotal.toFixed(2)}`;

            document.getElementById('discount').textContent =
                `₱${totals.discountAmount.toFixed(2)}`;

            document.getElementById('total').textContent =
                `₱${totals.total.toFixed(2)}`;

            updateChange();
        }

        function increaseQuantity(index) {
            if (!cart[index]) return;

            cart[index].quantity++;
            renderCart();
        }

        function decreaseQuantity(index) {
            if (!cart[index]) return;

            if (cart[index].quantity > 1) {
                cart[index].quantity--;
            } else {
                cart.splice(index, 1);
            }

            renderCart();
        }

        function removeCartItem(index) {
            cart.splice(index, 1);
            renderCart();
        }

        /*
         * Options modal
         */
        function openOptionModal(menuItem) {
            selectedMenuItem = menuItem;

            document.getElementById('optionMenuName').textContent =
                menuItem.name;

            const optionGroupsElement =
                document.getElementById('optionGroups');

            optionGroupsElement.innerHTML = '';

            menuItem.options.forEach(group => {
                const groupElement = document.createElement('div');
                groupElement.className = 'option-group';

                const heading = document.createElement('h3');
                heading.textContent =
                    group.name + (group.is_required ? ' *' : '');

                groupElement.appendChild(heading);

                group.values.forEach(value => {
                    const label = document.createElement('label');
                    label.className = 'option-choice';

                    const radio = document.createElement('input');
                    radio.type = 'radio';
                    radio.name = `option_group_${group.id}`;
                    radio.value = value.id;

                    const labelText = document.createElement('span');
                    const adjustmentText = value.price_adjustment > 0 ?
                        ` (+₱${value.price_adjustment.toFixed(2)})` :
                        '';

                    labelText.textContent = value.name + adjustmentText;

                    label.appendChild(radio);
                    label.appendChild(labelText);
                    groupElement.appendChild(label);
                });

                optionGroupsElement.appendChild(groupElement);
            });

            document.getElementById('optionModal').style.display = 'flex';
        }

        function closeOptionModal() {
            document.getElementById('optionModal').style.display = 'none';
            selectedMenuItem = null;
        }

        function confirmOptions() {
            if (!selectedMenuItem) {
                return;
            }

            const selectedOptions = [];

            for (const group of selectedMenuItem.options) {
                const selected = document.querySelector(
                    `input[name="option_group_${group.id}"]:checked`
                );

                if (group.is_required && !selected) {
                    alert(`Please select a ${group.name}.`);
                    return;
                }

                if (selected) {
                    const value = group.values.find(
                        option => option.id === Number(selected.value)
                    );

                    if (value) {
                        selectedOptions.push({
                            id: value.id,
                            name: value.name,
                            price_adjustment: value.price_adjustment
                        });
                    }
                }
            }

            const optionPriceAdjustment = selectedOptions.reduce(
                (sum, option) => sum + option.price_adjustment,
                0
            );

            const finalPrice =
                selectedMenuItem.price + optionPriceAdjustment;

            addToCart(
                selectedMenuItem.id,
                selectedMenuItem.name,
                finalPrice,
                selectedOptions
            );

            closeOptionModal();
        }

        /*
         * Order type and payment controls
         */
        function selectOrderType(type) {
            orderType = type;

            document.getElementById('dineInButton')
                .classList.toggle('active', type === 'Dine-in');

            document.getElementById('takeoutButton')
                .classList.toggle('active', type === 'Takeout');
        }

        function selectPayment(method) {
            paymentMethod = method;

            document.getElementById('cashButton')
                .classList.toggle('active', method === 'Cash');

            document.getElementById('gcashButton')
                .classList.toggle('active', method === 'GCash');

            document.getElementById('cashPaymentFields').style.display =
                method === 'Cash' ? 'block' : 'none';

            document.getElementById('gcashPaymentFields').style.display =
                method === 'GCash' ? 'block' : 'none';

            updateChange();
        }

        function selectDiscount(type) {
            discountType = type;

            document.getElementById('noDiscountButton')
                .classList.toggle('active', type === 'None');

            document.getElementById('seniorButton')
                .classList.toggle('active', type === 'Senior');

            document.getElementById('pwdButton')
                .classList.toggle('active', type === 'PWD');

            renderCart();
        }

        /*
         * Cash change calculation
         */
        function updateChange() {
            const received =
                parseFloat(document.getElementById('amountReceived').value) || 0;

            const change = Math.max(received - getTotals().total, 0);

            document.getElementById('changeAmount').textContent =
                `₱${change.toFixed(2)}`;
        }

        document.getElementById('amountReceived')
            .addEventListener('input', updateChange);

        /*
         * Escape text before inserting it into HTML
         */
        function esc(value) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };

            return String(value).replace(/[&<>"']/g, character => map[character]);
        }

        /*
         * Receipt
         */
        function showReceipt(receipt) {
            const money = number => '₱' + Number(number).toFixed(2);

            const lines = receipt.items.map(item => `
            <div class="receipt-line">
                <span>
                    ${item.quantity}× ${esc(item.name)}
                    ${item.options ? ' (' + esc(item.options) + ')' : ''}
                </span>
                <span>${money(item.price * item.quantity)}</span>
            </div>
        `).join('');

            const discountLine = receipt.discount > 0 ?
                `
                <div class="receipt-line">
                    <span>Discount (${esc(receipt.discountType)})</span>
                    <span>−${money(receipt.discount)}</span>
                </div>
            ` :
                '';

            const cashLines = receipt.payment === 'Cash' ?
                `
                <div class="receipt-line">
                    <span>Cash</span>
                    <span>${money(receipt.received)}</span>
                </div>
                <div class="receipt-line">
                    <span>Change</span>
                    <span>${money(receipt.received - receipt.total)}</span>
                </div>
            ` :
                '';

            document.getElementById('receiptBody').innerHTML = `
            <div class="receipt-head">
                <h2>The Brewing Bar</h2>
                <p>Gravahan, New Matina, Davao City</p>

                <div class="receipt-line">
                    <strong>Queue #${esc(receipt.queue)}</strong>
                    <strong>${esc(receipt.orderType)}</strong>
                </div>
            </div>

            <div class="receipt-meta">
                ${esc(receipt.orderNumber)} · ${new Date().toLocaleString('en-PH')}
            </div>

            ${lines}

            <div class="receipt-totals">
                <div class="receipt-line">
                    <span>Subtotal</span>
                    <span>${money(receipt.subtotal)}</span>
                </div>

                ${discountLine}

                <div class="receipt-line receipt-total">
                    <strong>TOTAL</strong>
                    <strong>${money(receipt.total)}</strong>
                </div>

                <div class="receipt-line">
                    <span>Payment</span>
                    <span>${esc(receipt.payment)}</span>
                </div>

                ${cashLines}
            </div>

            <p class="receipt-thanks">
                Thank you for visiting!
            </p>
        `;

            document.getElementById('receiptModal').style.display = 'flex';
        }

        function closeReceipt() {
            document.getElementById('receiptModal').style.display = 'none';
        }

        /*
         * Submit the cart to the Laravel store method.
         *
         * Important: items[*].inventory_item_id must match the
         * validation key in the controller.
         */
        async function placeOrder() {
            if (cart.length === 0) {
                alert('Please add an item to the order.');
                return;
            }

            const totals = getTotals();
            let amountTendered = totals.total;

            if (paymentMethod === 'Cash') {
                amountTendered =
                    parseFloat(document.getElementById('amountReceived').value) || 0;

                if (amountTendered < totals.total) {
                    alert('Amount received is not enough.');
                    return;
                }
            }

            const receiptItems = cart.map(item => ({
                name: item.name,
                quantity: item.quantity,
                price: item.price,
                options: (item.options || [])
                    .map(option => option.name)
                    .join(' • ')
            }));

            const data = {
                order_type: orderType,

                items: cart.map(item => ({
                    inventory_item_id: item.id,
                    quantity: item.quantity,
                    notes: null,
                    options: (item.options || []).map(option => option.id)
                })),

                discount_type: discountType,
                payment_method: paymentMethod,
                amount_tendered: amountTendered
            };

            const placeOrderButton =
                document.getElementById('placeOrderButton');

            placeOrderButton.disabled = true;
            placeOrderButton.textContent = 'Processing...';

            try {
                const response = await fetch('/orders', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (!response.ok) {
                    console.error('Order submission failed:', result);

                    const validationMessages = result.errors ?
                        Object.values(result.errors).flat().join('\n') :
                        '';

                    alert(
                        validationMessages ||
                        result.message ||
                        'Failed to create order.'
                    );

                    return;
                }

                showReceipt({
                    queue: result.queue_number ?? '-',
                    orderNumber: result.order?.order_number ?? '-',
                    orderType: orderType,
                    items: receiptItems,
                    subtotal: totals.subtotal,
                    discount: totals.discountAmount,
                    discountType: discountType,
                    total: totals.total,
                    payment: paymentMethod,
                    received: amountTendered
                });

                cart = [];

                document.getElementById('amountReceived').value = '';

                renderCart();
            } catch (error) {
                console.error(error);
                alert('Something went wrong while creating the order.');
            } finally {
                placeOrderButton.disabled = false;
                placeOrderButton.textContent = 'Place Order';
            }
        }

        /*
         * Filter menu cards by category
         */
        function filterCategory(category, button) {
            document.querySelectorAll('.category-tab').forEach(tab => {
                tab.classList.remove('active');
            });

            button.classList.add('active');

            document.querySelectorAll('.menu-card').forEach(card => {
                const cardCategory = card.dataset.category;

                card.style.display =
                    category === 'All' || cardCategory === category ?
                    '' :
                    'none';
            });
        }
    </script>
</x-app-layout>