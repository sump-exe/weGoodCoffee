<?php
if (!defined('POS_PAGE')) {
    http_response_code(404);
    exit;
}

function pos_load_menu(mysqli $conn): array
{
    $categories = ['Drinks' => [], 'Food' => []];
    $error = '';

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

    return ['categories' => $categories, 'error' => $error];
}

function pos_render_menu(array $categories): void
{
?>
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
<?php
}
?>
