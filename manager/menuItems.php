<?php
require_once __DIR__ . '/../_session.php';
app_require_role('manager');
require_once __DIR__ . '/../_conn.php';

$subCategories = [
    'Drinks' => [
        'Coffee',
        'Milk-Based Beverages',
        'Matcha Series',
        'Ice-Blended / Frappe',
        'Hot Drinks',
        'Fruit Sodas',
        'Refreshers',
    ],
    'Food' => [
        'Add Ons',
        'Rice Bowl',
        'Pasta',
        'Sandwiches',
        'Dessert',
        'Snacks',
    ],
];
$categories = ['Drinks', 'Food'];
$itemsByCategory = ['Drinks' => [], 'Food' => []];
$error = '';
$activeModal = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = $_POST['action'] ?? '';
    $itemId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if ($itemId === false || $itemId === null) {
        $error = 'A valid menu item ID is required.';
    } elseif ($action === 'edit') {
        $activeModal = 'editModal-' . $itemId;
        $nameInput = $_POST['name'] ?? null;
        $price = filter_var($_POST['price'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        $category = $_POST['category'] ?? '';
        $subCategory = $_POST['sub_category'] ?? '';

        if (!is_string($nameInput) || trim($nameInput) === '' || strlen(trim($nameInput)) > 100) {
            $error = 'Enter a name of up to 100 characters.';
        } elseif ($price === false || $price === null) {
            $error = 'Enter a valid price in PHP.';
        } elseif (!is_string($category) || !is_string($subCategory) || !isset($subCategories[$category]) || !in_array($subCategory, $subCategories[$category], true)) {
            $error = 'Choose a valid category and subcategory.';
        } else {
            try {
                $name = trim($nameInput);
                $statement = $conn->prepare('UPDATE menu_items SET name = ?, price = ?, category = ?, sub_category = ? WHERE id = ?');
                $statement->bind_param('sissi', $name, $price, $category, $subCategory, $itemId);
                $statement->execute();
                $statement->close();
                header('Location: menuItems.php?status=updated');
                exit;
            } catch (mysqli_sql_exception $exception) {
                error_log($exception->getMessage());
                $error = 'Unable to update this menu item. Please try again.';
            }
        }
    } elseif ($action === 'delete') {
        $activeModal = 'deleteModal-' . $itemId;

        try {
            $statement = $conn->prepare('DELETE FROM menu_items WHERE id = ?');
            $statement->bind_param('i', $itemId);
            $statement->execute();
            $deleted = $statement->affected_rows;
            $statement->close();

            if ($deleted !== 1) {
                $error = 'This menu item could not be found.';
            } else {
                header('Location: menuItems.php?status=deleted');
                exit;
            }
        } catch (mysqli_sql_exception $exception) {
            error_log($exception->getMessage());
            $error = 'Unable to delete this menu item. Please try again.';
        }
    } else {
        $error = 'Choose a valid menu item action.';
    }
}

try {
    $result = $conn->query('SELECT id, name, price, category, sub_category FROM menu_items ORDER BY category, sub_category, name, id');
    while ($item = $result->fetch_assoc()) {
        if (isset($itemsByCategory[$item['category']])) {
            $itemsByCategory[$item['category']][] = $item;
        }
    }
    $result->free();
} catch (mysqli_sql_exception $exception) {
    error_log($exception->getMessage());
    $error = 'Unable to load menu items. Please try again.';
}

$status = $_GET['status'] ?? '';
$statusMessage = $status === 'updated'
    ? 'Menu item updated successfully.'
    : ($status === 'deleted' ? 'Menu item deleted successfully.' : '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Manager | Menu Items</title>
</head>
<body>
    <header class="site-header">
        <a class="brand" href="index.php" aria-label="WeGoodCoffee manager home"><span class="brand-name">WeGood<strong>Coffee</strong></span></a>
        <form class="ms-auto" action="../logout.php" method="post">
            <button class="btn btn-sm btn-light" type="submit">Log out</button>
        </form>
    </header>

    <main class="admin-stage container-fluid">
        <div class="container">
            <div class="admin-heading d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h1>Menu Items</h1>
                    <p class="text-muted mb-0">Manage drinks and food on the menu.</p>
                </div>
                <a class="btn btn-outline-secondary" href="index.php">Back to Manager</a>
            </div>

            <?php if ($statusMessage !== ''): ?>
                <div class="alert alert-success" role="status"><?= htmlspecialchars($statusMessage, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <?php if ($error !== ''): ?>
                <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <?php foreach ($categories as $category): ?>
                <section class="admin-content mb-4" aria-labelledby="<?= strtolower($category) ?>Heading">
                    <div class="admin-heading">
                        <h2 class="h3" id="<?= strtolower($category) ?>Heading"><?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?></h2>
                    </div>
                    <div class="admin-table-wrap">
                        <table class="table admin-table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Price (PHP)</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <?php if ($itemsByCategory[$category] === []): ?>
                                <tbody>
                                    <tr>
                                        <td class="text-center text-muted py-4" colspan="4">No <?= htmlspecialchars(strtolower($category), ENT_QUOTES, 'UTF-8') ?> found.</td>
                                    </tr>
                                </tbody>
                            <?php else: ?>
                                <?php
                                $groupedItems = [];
                                foreach ($itemsByCategory[$category] as $item) {
                                    $groupedItems[$item['sub_category']][] = $item;
                                }
                                ?>
                                <?php foreach ($groupedItems as $subCategory => $items): ?>
                                    <tbody>
                                        <tr class="table-light">
                                            <th class="text-uppercase small" colspan="4" scope="rowgroup"><?= htmlspecialchars($subCategory, ENT_QUOTES, 'UTF-8') ?></th>
                                        </tr>
                                        <?php foreach ($items as $item): ?>
                                            <tr>
                                                <td><?= htmlspecialchars((string) $item['id'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars(number_format((int) $item['price']), ENT_QUOTES, 'UTF-8') ?></td>
                                                <td>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <button class="btn btn-sm admin-edit-button" type="button" data-bs-toggle="modal" data-bs-target="#editModal-<?= (int) $item['id'] ?>">Edit</button>
                                                        <button class="btn btn-sm admin-delete-button" type="button" data-bs-toggle="modal" data-bs-target="#deleteModal-<?= (int) $item['id'] ?>">Delete</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </table>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>
    </main>

    <?php foreach ($itemsByCategory as $items): ?>
        <?php foreach ($items as $item): ?>
            <div class="modal fade" id="editModal-<?= (int) $item['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel-<?= (int) $item['id'] ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="post">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                            <div class="modal-header">
                                <h2 class="modal-title fs-5" id="editModalLabel-<?= (int) $item['id'] ?>">Edit Menu Item</h2>
                                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <?php if ($error !== '' && $activeModal === 'editModal-' . $item['id']): ?>
                                    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                                <?php endif; ?>
                                <div class="mb-3">
                                    <label class="form-label" for="name-<?= (int) $item['id'] ?>">Name</label>
                                    <input class="form-control" id="name-<?= (int) $item['id'] ?>" name="name" type="text" maxlength="100" value="<?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="price-<?= (int) $item['id'] ?>">Price (PHP)</label>
                                    <input class="form-control" id="price-<?= (int) $item['id'] ?>" name="price" type="number" min="0" step="1" value="<?= (int) $item['price'] ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="category-<?= (int) $item['id'] ?>">Category</label>
                                    <select class="form-select" id="category-<?= (int) $item['id'] ?>" name="category" required>
                                        <?php foreach ($categories as $availableCategory): ?>
                                            <option value="<?= htmlspecialchars($availableCategory, ENT_QUOTES, 'UTF-8') ?>" <?= $item['category'] === $availableCategory ? 'selected' : '' ?>><?= htmlspecialchars($availableCategory, ENT_QUOTES, 'UTF-8') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label" for="sub-category-<?= (int) $item['id'] ?>">Subcategory</label>
                                    <select class="form-select" id="sub-category-<?= (int) $item['id'] ?>" name="sub_category" required>
                                        <?php foreach ($subCategories as $parentCategory => $availableSubCategories): ?>
                                            <?php foreach ($availableSubCategories as $availableSubCategory): ?>
                                                <option value="<?= htmlspecialchars($availableSubCategory, ENT_QUOTES, 'UTF-8') ?>" data-category="<?= htmlspecialchars($parentCategory, ENT_QUOTES, 'UTF-8') ?>" <?= $item['sub_category'] === $availableSubCategory ? 'selected' : '' ?>><?= htmlspecialchars($availableSubCategory, ENT_QUOTES, 'UTF-8') ?></option>
                                            <?php endforeach; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn admin-edit-button" type="submit">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="deleteModal-<?= (int) $item['id'] ?>" tabindex="-1" aria-labelledby="deleteModalLabel-<?= (int) $item['id'] ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="post">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                            <div class="modal-header">
                                <h2 class="modal-title fs-5" id="deleteModalLabel-<?= (int) $item['id'] ?>">Delete Menu Item</h2>
                                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <?php if ($error !== '' && $activeModal === 'deleteModal-' . $item['id']): ?>
                                    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                                <?php endif; ?>
                                <p class="mb-0">Are you sure you want to delete <strong><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></strong>?</p>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn btn-danger" type="submit">Delete Item</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('[id^="category-"]').forEach(function (categorySelect) {
            var itemId = categorySelect.id.replace('category-', '');
            var subCategorySelect = document.getElementById('sub-category-' + itemId);

            function updateSubCategories() {
                var matchingOptions = [];
                Array.from(subCategorySelect.options).forEach(function (option) {
                    var isAvailable = option.dataset.category === categorySelect.value;
                    option.hidden = !isAvailable;
                    if (isAvailable) {
                        matchingOptions.push(option);
                    }
                });

                if (!matchingOptions.some(function (option) { return option.value === subCategorySelect.value; })) {
                    subCategorySelect.value = matchingOptions[0].value;
                }
            }

            categorySelect.addEventListener('change', updateSubCategories);
            updateSubCategories();
        });

        <?php if ($activeModal !== ''): ?>
            var activeModal = document.getElementById(<?= json_encode($activeModal) ?>);
            if (activeModal) {
                new bootstrap.Modal(activeModal).show();
            }
        <?php endif; ?>
    </script>
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
