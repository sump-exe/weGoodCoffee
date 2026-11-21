<?php
require_once __DIR__ . '/../_conn.php';

$inventoryItems = [];
$inventoryError = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $name = trim((string) ($_POST['name'] ?? ''));
    $quantity = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

    if ($name === '' || strlen($name) > 100) {
        $inventoryError = 'Enter a stock name of up to 100 characters.';
    } elseif ($quantity === false || $quantity === null) {
        $inventoryError = 'Enter a valid quantity.';
    } else {
        try {
            $statement = $conn->prepare('INSERT INTO inventory (name, quantity) VALUES (?, ?)');
            $statement->bind_param('si', $name, $quantity);
            $statement->execute();
            $statement->close();
            header('Location: index.php');
            exit;
        } catch (mysqli_sql_exception $exception) {
            error_log($exception->getMessage());
            $inventoryError = 'Unable to add this stock item. Please try again.';
        }
    }
}

try {
    $inventoryResult = $conn->query('SELECT id, name, quantity FROM inventory ORDER BY name, id');
    while ($inventoryRow = $inventoryResult->fetch_assoc()) {
        $inventoryItems[] = $inventoryRow;
    }
    $inventoryResult->free();
} catch (mysqli_sql_exception $exception) {
    error_log($exception->getMessage());
    $inventoryError = 'Unable to load inventory items. Please try again.';
}
?>
<section class="inventory-card" aria-labelledby="inventory-list-title">
    <div class="inventory-card-heading">
        <h2 id="inventory-list-title">Inventory</h2>
        <div class="inventory-tools">
            <input id="stockSearch" type="search" placeholder="Search stock" aria-label="Search stock" autocomplete="off">
            <button class="inventory-add-button" type="button" data-bs-toggle="modal" data-bs-target="#addStockModal">Add stock item</button>
        </div>
    </div>
    <?php if ($inventoryError !== ''): ?>
        <div class="alert alert-danger mb-0" role="alert"><?= htmlspecialchars($inventoryError, ENT_QUOTES, 'UTF-8') ?></div>
    <?php else: ?>
        <div class="admin-table-wrap">
            <table class="table admin-table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Quantity</th>
                        <th scope="col"><span class="visually-hidden">Selection</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($inventoryItems === []): ?>
                        <tr class="inventory-empty-row">
                            <td colspan="3">
                                <div class="inventory-empty">
                                    <strong>No inventory items found.</strong>
                                    <span>Add your first stock item to start tracking quantities.</span>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($inventoryItems as $inventoryItem): ?>
                            <tr>
                                <td><?= htmlspecialchars($inventoryItem['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) $inventoryItem['quantity'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-end">
                                    <a class="inventory-select-button" href="?inventory_id=<?= (int) $inventoryItem['id'] ?>">Select</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<div class="modal fade" id="addStockModal" tabindex="-1" aria-labelledby="addStockModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content inventory-modal">
            <form method="post">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="addStockModalTitle">Add stock item</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if ($inventoryError !== '' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'): ?>
                        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($inventoryError, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label" for="stockName">Name</label>
                        <input class="form-control" id="stockName" name="name" type="text" maxlength="100" required>
                    </div>
                    <div>
                        <label class="form-label" for="stockQuantity">Quantity</label>
                        <input class="form-control" id="stockQuantity" name="quantity" type="number" min="0" step="1" value="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="inventory-add-button">Add stock item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('stockSearch').addEventListener('input', function (event) {
        var query = event.target.value.trim().toLocaleLowerCase();
        var table = document.querySelector('[aria-labelledby="inventory-list-title"] table');
        if (!table) {
            return;
        }
        table.querySelectorAll('tbody tr:not(.inventory-empty-row)').forEach(function (row) {
            row.hidden = !row.textContent.toLocaleLowerCase().includes(query);
        });
    });

    <?php if ($inventoryError !== '' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'): ?>
    if (window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('addStockModal')).show();
    }
    <?php endif; ?>
</script>
