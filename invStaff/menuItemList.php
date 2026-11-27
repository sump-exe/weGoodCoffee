<?php
require_once __DIR__ . '/../_conn.php';

$menuInventoryId = $inventoryEditError !== '' && $inventoryEditId !== null
    ? $inventoryEditId
    : filter_var($_GET['inventory_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$menuInventoryName = '';
$menuItems = [];
$allMenuItems = [];
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
            $allMenuItemResult = $conn->query(
                'SELECT id, name, category, sub_category
                FROM menu_items
                ORDER BY category, sub_category, name, id'
            );
            while ($menuItem = $allMenuItemResult->fetch_assoc()) {
                $allMenuItems[] = $menuItem;
            }
            $allMenuItemResult->free();

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
        } else {
            $menuInventoryId = null;
        }
    } catch (mysqli_sql_exception $exception) {
        error_log($exception->getMessage());
        $menuError = 'Unable to load menu items. Please try again.';
    }
}
?>
<section class="admin-content" aria-labelledby="menu-item-list-title">
    <div class="menu-items-heading">
        <h2 class="h3" id="menu-item-list-title">Menu Items</h2>
        <?php if ($menuInventoryName !== ''): ?>
            <button class="inventory-select-button" type="button" data-bs-toggle="modal" data-bs-target="#editStockModal">Edit</button>
        <?php endif; ?>
    </div>
    <?php if ($menuError !== ''): ?>
        <div class="alert alert-danger mb-0" role="alert"><?= htmlspecialchars($menuError, ENT_QUOTES, 'UTF-8') ?></div>
    <?php elseif ($menuInventoryName === ''): ?>
        <p class="text-muted mb-0">Select a stock item.</p>
    <?php else: ?>
        <?php if (($_GET['status'] ?? '') === 'updated'): ?>
            <div class="alert alert-success" role="status">Stock item updated successfully.</div>
        <?php endif; ?>
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

<?php if ($menuInventoryName !== ''): ?>
    <?php
    $linkedMenuItemIds = array_map(static function (array $menuItem): int {
        return (int) $menuItem['id'];
    }, $menuItems);
    $selectedMenuItemIds = $inventoryEditError !== '' && $inventoryEditId === $menuInventoryId
        ? $submittedMenuItemIds
        : $linkedMenuItemIds;
    $editName = $inventoryEditError !== '' && $inventoryEditId === $menuInventoryId
        ? $inventoryEditName
        : $menuInventoryName;
    ?>
    <div class="modal fade" id="editStockModal" tabindex="-1" aria-labelledby="editStockModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content inventory-modal">
                <form method="post" action="index.php?inventory_id=<?= (int) $menuInventoryId ?>">
                    <input type="hidden" name="action" value="edit_inventory">
                    <input type="hidden" name="inventory_id" value="<?= (int) $menuInventoryId ?>">
                    <div class="modal-header">
                        <h2 class="modal-title fs-5" id="editStockModalTitle">Edit stock item</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php if ($inventoryEditError !== '' && $inventoryEditId === $menuInventoryId): ?>
                            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($inventoryEditError, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                        <div class="mb-3">
                            <label class="form-label" for="editStockName">Name</label>
                            <input class="form-control" id="editStockName" name="name" type="text" maxlength="100" value="<?= htmlspecialchars($editName, ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>
                        <fieldset>
                            <legend class="form-label">Associated menu items</legend>
                            <div class="inventory-menu-options">
                                <?php foreach ($allMenuItems as $menuItem): ?>
                                    <?php $menuItemId = (int) $menuItem['id']; ?>
                                    <label class="inventory-menu-option" for="stockMenuItem<?= $menuItemId ?>">
                                        <input id="stockMenuItem<?= $menuItemId ?>" name="menu_item_ids[]" type="checkbox" value="<?= $menuItemId ?>"<?= in_array($menuItemId, $selectedMenuItemIds, true) ? ' checked' : '' ?>>
                                        <span><?= htmlspecialchars($menuItem['name'], ENT_QUOTES, 'UTF-8') ?><small><?= htmlspecialchars($menuItem['category'] . ' · ' . $menuItem['sub_category'], ENT_QUOTES, 'UTF-8') ?></small></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </fieldset>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="inventory-add-button">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>
