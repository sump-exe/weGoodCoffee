<?php
require_once __DIR__ . '/../_conn.php';

$inventoryItems = [];
$inventoryError = '';

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
<section class="admin-content" aria-labelledby="inventory-list-title">
    <h2 class="h3 mb-3" id="inventory-list-title">Inventory</h2>
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
                        <tr>
                            <td class="text-center text-muted py-4" colspan="3">No inventory items found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($inventoryItems as $inventoryItem): ?>
                            <tr>
                                <td><?= htmlspecialchars($inventoryItem['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) $inventoryItem['quantity'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-secondary" href="?inventory_id=<?= (int) $inventoryItem['id'] ?>">Select</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
