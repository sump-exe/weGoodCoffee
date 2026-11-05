<?php
require_once __DIR__ . '/../_session.php';
app_require_role('cashier');
require_once __DIR__ . '/../_conn.php';

$categories = ['Drinks' => [], 'Food' => []];
$error = '';
$submittedCart = [];
$orderId = null;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $cartInput = $_POST['cart'] ?? '';
    $decodedCart = is_string($cartInput) ? json_decode($cartInput, true) : null;

    if (!is_array($decodedCart) || $decodedCart === []) {
        $error = 'Add at least one menu item to the order.';
    } else {
        $submittedCart = $decodedCart;
        $validatedCart = [];

        foreach ($decodedCart as $cartEntry) {
            if (
                !is_array($cartEntry)
                || !isset($cartEntry['id'], $cartEntry['quantity'])
                || filter_var($cartEntry['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false
                || filter_var($cartEntry['quantity'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 99]]) === false
            ) {
                $error = 'The order contains an invalid item or quantity.';
                break;
            }

            $itemId = (int) $cartEntry['id'];
            $quantity = (int) $cartEntry['quantity'];
            $validatedCart[$itemId] = ($validatedCart[$itemId] ?? 0) + $quantity;

            if ($validatedCart[$itemId] > 99) {
                $error = 'An item quantity cannot exceed 99.';
                break;
            }
        }

        if ($error === '' && $validatedCart === []) {
            $error = 'Add at least one menu item to the order.';
        }

        if ($error === '') {
            $transactionStarted = false;
            try {
                $conn->begin_transaction();
                $transactionStarted = true;

                $orderStatement = $conn->prepare('INSERT INTO orders () VALUES ()');
                $orderStatement->execute();
                $orderId = $conn->insert_id;
                $orderStatement->close();

                $menuStatement = $conn->prepare('SELECT price FROM menu_items WHERE id = ?');
                $itemStatement = $conn->prepare('INSERT INTO order_items (order_id, menu_item_id, quantity, unit_price) VALUES (?, ?, ?, ?)');

                foreach ($validatedCart as $itemId => $quantity) {
                    $menuStatement->bind_param('i', $itemId);
                    $menuStatement->execute();
                    $menuResult = $menuStatement->get_result();
                    $menuItem = $menuResult->fetch_assoc();
                    $menuResult->free();

                    if ($menuItem === null) {
                        $error = 'One or more selected items no longer exist. Please review the order.';
                        break;
                    }

                    $unitPrice = (int) $menuItem['price'];
                    $itemStatement->bind_param('iiii', $orderId, $itemId, $quantity, $unitPrice);
                    $itemStatement->execute();
                }

                $menuStatement->close();
                $itemStatement->close();

                if ($error !== '') {
                    $conn->rollback();
                    $transactionStarted = false;
                } else {
                    $conn->commit();
                    header('Location: index.php?order=' . (int) $orderId);
                    exit;
                }
            } catch (mysqli_sql_exception $exception) {
                if ($transactionStarted) {
                    $conn->rollback();
                }
                error_log($exception->getMessage());
                $error = 'Unable to save the order. Please review the cart and try again.';
            }
        }
    }
}

try {
    $result = $conn->query('SELECT id, name, price, category, sub_category FROM menu_items ORDER BY category, sub_category, name, id');
    while ($item = $result->fetch_assoc()) {
        if (isset($categories[$item['category']])) {
            $categories[$item['category']][$item['sub_category']][] = $item;
        }
    }
    $result->free();
} catch (mysqli_sql_exception $exception) {
    error_log($exception->getMessage());
    $error = 'Unable to load the menu. Please try again.';
}

$orderIdFromQuery = filter_var($_GET['order'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$initialCart = [];
foreach ($submittedCart as $cartEntry) {
    if (is_array($cartEntry) && isset($cartEntry['id'], $cartEntry['quantity'])) {
        $itemId = filter_var($cartEntry['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $quantity = filter_var($cartEntry['quantity'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 99]]);
        if ($itemId !== false && $quantity !== false) {
            $initialCart[] = ['id' => $itemId, 'quantity' => $quantity];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Point of Sale | WeGoodCoffee</title>
    <style>
        .pos-page { min-height: calc(100vh - 54px); padding: 28px 20px; }
        .pos-panel { min-width: 0; padding: 22px; border: 1px solid #eadbc9; border-radius: 16px; background: #fffaf3; box-shadow: 0 8px 24px rgb(112 76 43 / 6%); }
        .pos-category { height: 100%; }
        .pos-subcategory + .pos-subcategory { margin-top: 20px; }
        .pos-subcategory-title { margin-bottom: 10px; color: #754b2d; font-size: 15px; font-weight: 700; }
        .pos-item { display: flex; width: 100%; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 8px; padding: 10px 12px; border: 1px solid #eadbc9; border-radius: 9px; background: #fff; color: #493c31; text-align: left; }
        .pos-item:hover, .pos-item:focus-visible { border-color: #ad7548; background: #fff5e7; }
        .pos-item-price { flex: 0 0 auto; color: #754b2d; font-weight: 700; }
        .pos-cart { position: sticky; top: 16px; }
        .pos-cart-list { max-height: 48vh; overflow-y: auto; }
        .pos-cart-item { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 6px 12px; padding: 12px 0; border-bottom: 1px solid #f0e7dd; }
        .pos-cart-controls { display: flex; align-items: center; gap: 8px; }
        .pos-cart-controls button { min-width: 30px; }
        @media (max-width: 767.98px) { .pos-cart { position: static; } }
    </style>
</head>
<body>
    <header class="site-header">
        <a class="brand" href="../index.php" aria-label="WeGoodCoffee home"><span class="brand-name">WeGood<strong>Coffee</strong></span></a>
        <form class="ms-auto" action="../logout.php" method="post">
            <button class="btn btn-sm btn-light" type="submit">Log out</button>
        </form>
    </header>

    <main class="pos-page container-fluid">
        <div class="container-fluid">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <div>
                    <h1>Point of Sale</h1>
                    <p class="text-muted mb-0">Choose items to add them to the order.</p>
                </div>
            </div>

            <?php if ($orderIdFromQuery !== false && $orderIdFromQuery !== null): ?>
                <div class="alert alert-success" role="status">Order #<?= (int) $orderIdFromQuery ?> saved successfully.</div>
            <?php endif; ?>
            <?php if ($error !== ''): ?>
                <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <div class="row g-4 align-items-start">
                <div class="col-12 col-xl-9">
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="menuSearch">Search menu items</label>
                        <input
                            class="form-control"
                            type="search"
                            id="menuSearch"
                            placeholder="Search by item, category, or subcategory"
                            autocomplete="off"
                        >
                    </div>
                    <p class="text-muted" id="menuSearchEmpty" role="status" hidden>No menu items match your search.</p>
                    <div class="row g-4">
                        <?php foreach (['Drinks', 'Food'] as $category): ?>
                            <div class="col-12 col-md-6 pos-category-column">
                                <section class="pos-panel pos-category" aria-labelledby="<?= strtolower($category) ?>Heading">
                                    <h2 class="h3 mb-4" id="<?= strtolower($category) ?>Heading"><?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?></h2>
                                    <?php if ($categories[$category] === []): ?>
                                        <p class="text-muted mb-0">No <?= htmlspecialchars(strtolower($category), ENT_QUOTES, 'UTF-8') ?> available.</p>
                                    <?php else: ?>
                                        <?php foreach ($categories[$category] as $subCategory => $items): ?>
                                            <section class="pos-subcategory" aria-label="<?= htmlspecialchars($subCategory, ENT_QUOTES, 'UTF-8') ?>">
                                                <h3 class="pos-subcategory-title"><?= htmlspecialchars($subCategory, ENT_QUOTES, 'UTF-8') ?></h3>
                                                <?php foreach ($items as $item): ?>
                                                    <button
                                                        class="pos-item"
                                                        type="button"
                                                        data-item-id="<?= (int) $item['id'] ?>"
                                                        data-item-name="<?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>"
                                                        data-item-price="<?= (int) $item['price'] ?>"
                                                        data-search-text="<?= htmlspecialchars($category . ' ' . $subCategory . ' ' . $item['name'], ENT_QUOTES, 'UTF-8') ?>"
                                                    >
                                                        <span><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></span>
                                                        <span class="pos-item-price">₱<?= number_format((int) $item['price']) ?></span>
                                                    </button>
                                                <?php endforeach; ?>
                                            </section>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </section>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <aside class="col-12 col-xl-3" aria-labelledby="cartHeading">
                    <section class="pos-panel pos-cart">
                        <h2 class="h4 mb-3" id="cartHeading">Current Order</h2>
                        <div class="pos-cart-list" id="cartItems" aria-live="polite">
                            <p class="text-muted mb-0" id="emptyCart">No items added yet.</p>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                            <strong>Total</strong>
                            <strong id="cartTotal">₱0</strong>
                        </div>
                        <form method="post" id="checkoutForm" class="mt-3">
                            <input type="hidden" name="cart" id="cartInput" value="">
                            <button class="btn admin-add-button w-100" type="submit" id="checkoutButton" disabled>Place Order</button>
                        </form>
                        <button class="btn btn-outline-secondary w-100 mt-2" type="button" id="clearCartButton" disabled>Clear Order</button>
                    </section>
                </aside>
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
        var initialCart = <?= json_encode($initialCart, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
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
