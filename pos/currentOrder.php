<?php
if (!defined('POS_PAGE')) {
    http_response_code(404);
    exit;
}

function pos_process_order(mysqli $conn): array
{
    $error = '';
    $submittedCart = [];

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

    return [
        'error' => $error,
        'initialCart' => $initialCart,
        'orderId' => filter_var($_GET['order'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]),
    ];
}

function pos_render_current_order(): void
{
?>
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
<?php
}
?>
