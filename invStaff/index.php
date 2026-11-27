<?php
require_once __DIR__ . '/../_session.php';
app_require_role('inventory_staff');

$inventoryEditError = '';
$inventoryEditId = null;
$inventoryEditName = '';
$submittedMenuItemIds = [];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['action'] ?? '') === 'edit_inventory') {
    require_once __DIR__ . '/../_conn.php';

    $inventoryEditId = filter_var($_POST['inventory_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $inventoryEditNameInput = $_POST['name'] ?? null;
    $inventoryEditName = is_string($inventoryEditNameInput) ? trim($inventoryEditNameInput) : '';
    $menuItemIdsInput = $_POST['menu_item_ids'] ?? [];

    if ($inventoryEditId === false || $inventoryEditId === null) {
        $inventoryEditId = null;
        $inventoryEditError = 'Select a valid stock item to edit.';
    } elseif ($inventoryEditName === '' || strlen($inventoryEditName) > 100) {
        $inventoryEditError = 'Enter a stock name of up to 100 characters.';
    }

    if (!is_array($menuItemIdsInput)) {
        $inventoryEditError = 'Choose valid menu items.';
    } else {
        foreach ($menuItemIdsInput as $menuItemIdInput) {
            $menuItemId = filter_var($menuItemIdInput, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($menuItemId === false || $menuItemId === null) {
                $inventoryEditError = 'Choose valid menu items.';
                continue;
            }
            $submittedMenuItemIds[] = $menuItemId;
        }
        $submittedMenuItemIds = array_values(array_unique($submittedMenuItemIds));
    }

    if ($inventoryEditError === '') {
        $transactionStarted = false;
        try {
            $menuItemResult = $conn->query('SELECT id FROM menu_items');
            $availableMenuItemIds = [];
            while ($menuItem = $menuItemResult->fetch_assoc()) {
                $availableMenuItemIds[(int) $menuItem['id']] = true;
            }
            $menuItemResult->free();

            foreach ($submittedMenuItemIds as $menuItemId) {
                if (!isset($availableMenuItemIds[$menuItemId])) {
                    $inventoryEditError = 'One or more selected menu items are unavailable.';
                    break;
                }
            }

            $inventoryCheck = $conn->prepare('SELECT id FROM inventory WHERE id = ?');
            $inventoryCheck->bind_param('i', $inventoryEditId);
            $inventoryCheck->execute();
            $inventoryResult = $inventoryCheck->get_result();
            $inventoryExists = $inventoryResult->fetch_assoc() !== null;
            $inventoryCheck->close();

            if (!$inventoryExists) {
                $inventoryEditError = 'This stock item could not be found.';
            }

            if ($inventoryEditError === '') {
                $conn->begin_transaction();
                $transactionStarted = true;

                $updateStatement = $conn->prepare('UPDATE inventory SET name = ? WHERE id = ?');
                $updateStatement->bind_param('si', $inventoryEditName, $inventoryEditId);
                $updateStatement->execute();
                $updateStatement->close();

                $deleteLinks = $conn->prepare('DELETE FROM inv_items WHERE inventory_id = ?');
                $deleteLinks->bind_param('i', $inventoryEditId);
                $deleteLinks->execute();
                $deleteLinks->close();

                $insertLink = $conn->prepare('INSERT INTO inv_items (inventory_id, menu_item_id) VALUES (?, ?)');
                foreach ($submittedMenuItemIds as $menuItemId) {
                    $insertLink->bind_param('ii', $inventoryEditId, $menuItemId);
                    $insertLink->execute();
                }
                $insertLink->close();

                $conn->commit();
                header('Location: index.php?inventory_id=' . $inventoryEditId . '&status=updated');
                exit;
            }
        } catch (mysqli_sql_exception $exception) {
            if ($transactionStarted) {
                $conn->rollback();
            }
            error_log($exception->getMessage());
            $inventoryEditError = 'Unable to update this stock item. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles.css?v=13">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Inventory Staff | WeGoodCoffee</title>
</head>
<body class="inventory-page">
    <header class="site-header">
        <a class="brand" href="../index.php" aria-label="WeGoodCoffee home"><img class="brand-logo" src="../img/logo.png" alt=""><span class="brand-name">WeGoodCoffee</span></a>
        <div class="d-flex align-items-center gap-3 ms-auto">
            <?php app_render_user_profile(); ?>
            <form action="../logout.php" method="post">
                <button class="btn btn-sm btn-light" type="submit">Log out</button>
            </form>
        </div>
    </header>
    <main class="admin-stage container-fluid">
        <div class="container">
            <section class="inventory-heading mb-4" aria-labelledby="inventory-title">
                <h1 id="inventory-title">Inventory Staff</h1>
                <p>Track stock and see which menu items use it.</p>
            </section>
            <?php if ($inventoryEditError !== '' && $inventoryEditId === null): ?>
                <div class="alert alert-danger" role="alert"><?= htmlspecialchars($inventoryEditError, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <div class="row g-4 align-items-start">
                <div class="col-lg-8">
                    <?php require __DIR__ . '/inventoryList.php'; ?>
                </div>
                <div class="col-lg-4">
                    <?php require __DIR__ . '/menuItemList.php'; ?>
                </div>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <?php if ($inventoryEditError !== '' && $inventoryEditId !== null): ?>
        <script>
            bootstrap.Modal.getOrCreateInstance(document.getElementById('editStockModal')).show();
        </script>
    <?php endif; ?>
    <script
        src="<?= htmlspecialchars(app_base_path() . '/session.js', ENT_QUOTES, 'UTF-8') ?>"
        data-logout-url="<?= htmlspecialchars(app_base_path() . '/logout.php', ENT_QUOTES, 'UTF-8') ?>"
        data-ping-url="<?= htmlspecialchars(app_base_path() . '/session_ping.php', ENT_QUOTES, 'UTF-8') ?>"
        data-login-url="<?= htmlspecialchars(app_base_path() . '/index.php', ENT_QUOTES, 'UTF-8') ?>"
        data-timeout-ms="<?= APP_IDLE_TIMEOUT_SECONDS * 1000 ?>"
        defer
    ></script>
</body>
</html>
