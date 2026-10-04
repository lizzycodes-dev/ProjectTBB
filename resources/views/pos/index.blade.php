<x-app-layout>

        <div class="kitchen-page">

    <div class="kitchen-header">
        <div>
            <h1>Point of Sale</h1>
            <p>Create and process customer orders</p>
        </div>
    </div>

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
                <!-- #made the menu-card not clickable if its out of stock -->
                <div class="menu-card {{ $menuItem->stock_status === 'out' ? 'out-of-stock' : '' }}"
                    data-search="{{ strtolower($menuItem->name) }}"
                    data-category="{{ $menuItem->category->name ?? 'Menu Item' }}">

                    <div class="menu-card-photo">
                        <img src="{{ asset('images/menu/sample.png') }}"
                            alt="{{ $menuItem->name }}">
                    </div>

                    <h3>{{ $menuItem->name }}</h3>

                    <div class="menu-card-category">
                        {{ $menuItem->category->name ?? 'Menu Item' }}
                    </div>

                    <div class="price">
                        ₱{{ number_format($menuItem->price, 2) }}
                    </div>

                    <div class="menu-stock-status">
                        @if (in_array($menuItem->category->name, ['Coffee', 'Non Coffee', 'Juice']))

                        {{-- Invisible badge to keep alignment --}}
                        <span class="stock-badge stock-placeholder">
                            &nbsp;
                        </span>

                        @elseif ($menuItem->stock_status === 'out')

                        <span class="stock-badge stock-out">
                            Out of Stock
                        </span>

                        @elseif ($menuItem->stock_status === 'low')

                        <span class="stock-badge stock-low">
                            Low Stock · {{ number_format($menuItem->current_stock, 0) }} left
                        </span>

                        @else

                        <span class="stock-badge stock-in">
                            In Stock · {{ number_format($menuItem->current_stock, 0) }} left
                        </span>

                        @endif
                    </div>
                    <button type="button"
                        class="add-to-cart {{ $menuItem->stock_status === 'out' ? 'disabled' : '' }}"
                        data-id="{{ $menuItem->id }}"
                        data-name="{{ $menuItem->name }}"
                        data-price="{{ $menuItem->price }}"
                        data-options='@json($menuItem->optionGroups)'
                        @if ($menuItem->stock_status === 'out') disabled @endif>
                        {{ $menuItem->stock_status === 'out' ? 'Out of Stock' : 'Add to Order' }}
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

                /*
                 * Update POS stock display after successful order
                 */
                if (result.stock_updates) {
                    result.stock_updates.forEach(update => {
                        const button = document.querySelector(
                            `.add-to-cart[data-id="${update.menu_item_id}"]`
                        );

                        if (!button) {
                            return;
                        }

                        const card = button.closest('.menu-card');

                        if (!card) {
                            return;
                        }

                        const stockStatus = card.querySelector('.menu-stock-status');

                        if (update.stock <= 0) {
                            // Out of stock
                            card.classList.add('out-of-stock');

                            if (stockStatus) {
                                stockStatus.innerHTML = `
                    <span class="stock-badge stock-out">
                        Out of Stock
                    </span>
                `;
                            }

                            button.textContent = 'Out of Stock';
                            button.disabled = true;
                            button.classList.add('disabled');
                        } else {
                            // Still has stock
                            if (stockStatus) {
                                stockStatus.innerHTML = `
                    <span class="stock-badge stock-in">
                        In Stock · ${Number(update.stock).toLocaleString()} left
                    </span>
                `;
                            }
                        }
                    });
                }

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