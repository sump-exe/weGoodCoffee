<?php
define('POS_PAGE', true);
require_once __DIR__ . '/../_session.php';
app_require_role('cashier');
require_once __DIR__ . '/../_conn.php';
require_once __DIR__ . '/menu.php';
require_once __DIR__ . '/currentOrder.php';

$orderState = pos_process_order($conn);
$menuState = pos_load_menu($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles.css?v=4">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Point of Sale | WeGoodCoffee</title>
    <style>
        .pos-page { min-height: calc(100vh - 54px); padding: 28px 20px; }
        .pos-panel { min-width: 0; padding: 22px; border: 1px solid var(--color-line); border-radius: 16px; background: var(--color-surface); box-shadow: 0 8px 24px rgb(83 40 59 / 8%); }
        .pos-category { height: 100%; }
        .pos-subcategory + .pos-subcategory { margin-top: 20px; }
        .pos-subcategory-title { margin-bottom: 10px; color: var(--color-teal); font-size: 15px; font-weight: 700; }
        .pos-item { display: flex; width: 100%; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 8px; padding: 10px 12px; border: 1px solid var(--color-line); border-radius: 9px; background: #fffdf7; color: var(--color-ink); text-align: left; }
        .pos-item:hover, .pos-item:focus-visible { border-color: var(--color-orange); background: var(--color-surface-alt); }
        .pos-item-price { flex: 0 0 auto; color: var(--color-orange-deep); font-weight: 700; }
        .pos-cart { position: sticky; top: 16px; }
        .pos-cart-list { max-height: 48vh; overflow-y: auto; }
        .pos-cart-item { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 6px 12px; padding: 12px 0; border-bottom: 1px solid #f3e5d5; }
        .pos-cart-controls { display: flex; align-items: center; gap: 8px; }
        .pos-cart-controls button { min-width: 30px; }
        @media (max-width: 767.98px) { .pos-cart { position: static; } }
    </style>
</head>
<body>
    <header class="site-header">
        <a class="brand" href="../index.php" aria-label="WeGoodCoffee home"><img class="brand-logo" src="../img/logo.png" alt=""></a>
        <div class="d-flex align-items-center gap-3 ms-auto">
            <?php app_render_user_profile(); ?>
            <form action="../logout.php" method="post">
                <button class="btn btn-sm btn-light" type="submit">Log out</button>
            </form>
        </div>
    </header>

    <main class="pos-page container-fluid">
        <div class="container-fluid">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <div>
                    <h1>Point of Sale</h1>
                    <p class="text-muted mb-0">Choose items to add them to the order.</p>
                </div>
            </div>

            <?php if ($orderState['orderId'] !== false && $orderState['orderId'] !== null): ?>
                <div class="alert alert-success" role="status">Order #<?= (int) $orderState['orderId'] ?> saved successfully.</div>
            <?php endif; ?>
            <?php foreach ([$orderState['error'], $menuState['error']] as $error): ?>
                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            <?php endforeach; ?>

            <div class="row g-4 align-items-start">
                <?php pos_render_menu($menuState['categories']); ?>
                <?php pos_render_current_order(); ?>
            </div>
        </div>
    </main>

    <script>
        var menuItems = {};
        document.querySelectorAll('.pos-item').forEach(function (button) {
            menuItems[button.dataset.itemId] = {
                id: Number(button.dataset.itemId),
                name: button.dataset.itemName,
                price: Number(button.dataset.itemPrice)
            };
        });

        var cart = new Map();
        var initialCart = <?= json_encode($orderState['initialCart'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        initialCart.forEach(function (entry) {
            if (menuItems[entry.id]) {
                cart.set(entry.id, entry.quantity);
            }
        });

        var cartItems = document.getElementById('cartItems');
        var cartTotal = document.getElementById('cartTotal');
        var cartInput = document.getElementById('cartInput');
        var checkoutButton = document.getElementById('checkoutButton');
        var clearCartButton = document.getElementById('clearCartButton');

        function formatPrice(price) {
            return '₱' + new Intl.NumberFormat('en-PH', { maximumFractionDigits: 0 }).format(price);
        }

        document.getElementById('menuSearch').addEventListener('input', function (event) {
            var query = event.target.value.trim().toLocaleLowerCase();
            var matchingItems = 0;

            document.querySelectorAll('.pos-category-column').forEach(function (column) {
                var columnMatches = 0;

                column.querySelectorAll('.pos-subcategory').forEach(function (subcategory) {
                    var subcategoryMatches = 0;
                    subcategory.querySelectorAll('.pos-item').forEach(function (button) {
                        var matches = button.dataset.searchText.toLocaleLowerCase().includes(query);
                        button.hidden = !matches;
                        if (matches) {
                            subcategoryMatches++;
                            columnMatches++;
                        }
                    });
                    subcategory.hidden = query !== '' && subcategoryMatches === 0;
                });

                column.hidden = query !== '' && columnMatches === 0;
                matchingItems += columnMatches;
            });

            document.getElementById('menuSearchEmpty').hidden = query === '' || matchingItems > 0;
        });

        function renderCart() {
            cartItems.replaceChildren();
            var total = 0;

            cart.forEach(function (quantity, itemId) {
                var item = menuItems[itemId];
                if (!item) {
                    return;
                }

                total += item.price * quantity;
                var row = document.createElement('div');
                row.className = 'pos-cart-item';

                var name = document.createElement('strong');
                name.textContent = item.name;
                var lineTotal = document.createElement('span');
                lineTotal.className = 'text-end';
                lineTotal.textContent = formatPrice(item.price * quantity);
                var controls = document.createElement('div');
                controls.className = 'pos-cart-controls';

                var decrease = document.createElement('button');
                decrease.className = 'btn btn-sm btn-outline-secondary';
                decrease.type = 'button';
                decrease.textContent = '−';
                decrease.setAttribute('aria-label', 'Decrease ' + item.name);
                decrease.addEventListener('click', function () {
                    if (quantity <= 1) {
                        cart.delete(itemId);
                    } else {
                        cart.set(itemId, quantity - 1);
                    }
                    renderCart();
                });

                var quantityLabel = document.createElement('span');
                quantityLabel.textContent = String(quantity);

                var increase = document.createElement('button');
                increase.className = 'btn btn-sm btn-outline-secondary';
                increase.type = 'button';
                increase.textContent = '+';
                increase.setAttribute('aria-label', 'Increase ' + item.name);
                increase.disabled = quantity >= 99;
                increase.addEventListener('click', function () {
                    if (quantity < 99) {
                        cart.set(itemId, quantity + 1);
                        renderCart();
                    }
                });

                controls.append(decrease, quantityLabel, increase);
                row.append(name, lineTotal, controls);
                cartItems.append(row);
            });

            if (cart.size === 0) {
                var emptyMessage = document.createElement('p');
                emptyMessage.className = 'text-muted mb-0';
                emptyMessage.textContent = 'No items added yet.';
                cartItems.append(emptyMessage);
            }

            cartTotal.textContent = formatPrice(total);
            checkoutButton.disabled = cart.size === 0;
            clearCartButton.disabled = cart.size === 0;
            cartInput.value = JSON.stringify(Array.from(cart, function (entry) {
                return { id: entry[0], quantity: entry[1] };
            }));
        }

        document.querySelectorAll('.pos-item').forEach(function (button) {
            button.addEventListener('click', function () {
                var itemId = Number(button.dataset.itemId);
                var quantity = cart.get(itemId) || 0;
                if (quantity < 99) {
                    cart.set(itemId, quantity + 1);
                    renderCart();
                }
            });
        });

        clearCartButton.addEventListener('click', function () {
            cart.clear();
            renderCart();
        });

        document.getElementById('checkoutForm').addEventListener('submit', function (event) {
            if (cart.size === 0) {
                event.preventDefault();
                return;
            }
            cartInput.value = JSON.stringify(Array.from(cart, function (entry) {
                return { id: entry[0], quantity: entry[1] };
            }));
        });

        renderCart();
    </script>
</body>
</html>
