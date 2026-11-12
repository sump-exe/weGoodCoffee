<?php
require_once __DIR__ . '/../_conn.php';

$menuInventoryId = filter_var($_GET['inventory_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$menuInventoryName = '';
$menuItems = [];
$menuError = '';

if ($menuInventoryId !== false && $menuInventoryId !== null) {
    try {
        $inventoryStatement = $conn->prepare('SELECT name FROM inventory WHERE id = ?');
        $inventoryStatement->bind_param('i', $menuInventoryId);
        $inventoryStatement->execute();
        $inventoryResult = $inventoryStatement->get_result();
        $selectedInventory = $inventoryResult->fetch_assoc();
        $inventoryStatement->close();

        if ($selectedInventory !== null) {
            $menuInventoryName = $selectedInventory['name'];
            $menuStatement = $conn->prepare(
                'SELECT menu_items.id, menu_items.name
                FROM inv_items
                INNER JOIN menu_items ON menu_items.id = inv_items.menu_item_id
                WHERE inv_items.inventory_id = ?
                ORDER BY menu_items.name, menu_items.id'
            );
            $menuStatement->bind_param('i', $menuInventoryId);
            $menuStatement->execute();
            $menuResult = $menuStatement->get_result();
            while ($menuItem = $menuResult->fetch_assoc()) {
                $menuItems[] = $menuItem;
            }
            $menuStatement->close();
        }
    } catch (mysqli_sql_exception $exception) {
        error_log($exception->getMessage());
        $menuError = 'Unable to load menu items. Please try again.';
    }
}
?>
<section class="admin-content" aria-labelledby="menu-item-list-title">
    <h2 class="h3 mb-3" id="menu-item-list-title">Menu Items</h2>
    <?php if ($menuError !== ''): ?>
        <div class="alert alert-danger mb-0" role="alert"><?= htmlspecialchars($menuError, ENT_QUOTES, 'UTF-8') ?></div>
    <?php elseif ($menuInventoryName === ''): ?>
        <p class="text-muted mb-0">Select a stock item.</p>
    <?php else: ?>
        <p class="mb-3">For <strong><?= htmlspecialchars($menuInventoryName, ENT_QUOTES, 'UTF-8') ?></strong></p>
        <div class="admin-table-wrap">
            <table class="table admin-table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Menu Item</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($menuItems === []): ?>
                        <tr>
                            <td class="text-center text-muted py-4">No menu items linked to this stock item.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($menuItems as $menuItem): ?>
                            <tr>
                                <td><?= htmlspecialchars($menuItem['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
