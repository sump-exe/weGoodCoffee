<?php
require_once __DIR__ . '/../_conn.php';

$users = [];
$loadError = '';
$notices = [
    'added' => 'User added successfully.',
    'updated' => 'User updated successfully.',
    'deleted' => 'User deleted successfully.',
];
$status = $_GET['status'] ?? '';
$notice = is_string($status) ? ($notices[$status] ?? '') : '';

try {
    $result = $conn->query('SELECT id, username, role FROM users ORDER BY id');
    $users = $result->fetch_all(MYSQLI_ASSOC);
    $result->free();
} catch (mysqli_sql_exception $exception) {
    error_log($exception->getMessage());
    $loadError = 'Unable to load users. Check the database connection and users table.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Admin | WeGoodCoffee</title>
</head>
<body>
    <header class="site-header">
        <a class="brand" aria-label="WeGoodCoffee home"><span class="brand-name">WeGood<strong>Coffee</strong></span>
        </a>
    </header>

    <main class="admin-stage container-fluid">
        <div class="row justify-content-center">
            <section class="col-12 col-xl-10 admin-content" aria-labelledby="users-title">
                <div class="admin-heading row align-items-center g-3">
                    <div class="col">
                        <h1 id="users-title">Users</h1>
                    </div>
                    <div class="col-auto">
                        <a class="btn admin-add-button" href="addUser.php">Add User</a>
                    </div>
                </div>

                <?php if ($notice !== ''): ?>
                    <div class="alert alert-success" role="status"><?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <?php if ($loadError !== ''): ?>
                    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($loadError, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-12">
                        <div class="admin-table-wrap">
                            <table class="table admin-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Username</th>
                                        <th scope="col">Role</th>
                                        <th scope="col" class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($users === []): ?>
                                        <tr>
                                            <td class="text-center text-muted py-4" colspan="4">No users found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <th scope="row"><?= htmlspecialchars((string) $user['id'], ENT_QUOTES, 'UTF-8') ?></th>
                                                <td><?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><span class="role-badge"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $user['role'])), ENT_QUOTES, 'UTF-8') ?></span></td>
                                                <td>
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <a class="btn btn-sm admin-edit-button" href="editUser.php?id=<?= rawurlencode((string) $user['id']) ?>">Edit</a>
                                                        <a class="btn btn-sm admin-delete-button" href="deleteUser.php?id=<?= rawurlencode((string) $user['id']) ?>">Delete</a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
    <div id="user-modal-container"></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const modalContainer = document.getElementById('user-modal-container');

        async function loadUserModal(url, options = {}) {
            const response = await fetch(url, {
                ...options,
                headers: {
                    ...options.headers,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.redirected) {
                window.location.assign(response.url);
                return;
            }

            const markup = await response.text();
            const currentModal = modalContainer.querySelector('.modal.show');
            if (currentModal) {
                await new Promise(resolve => {
                    currentModal.addEventListener('hidden.bs.modal', resolve, { once: true });
                    bootstrap.Modal.getInstance(currentModal).hide();
                });
            }

            modalContainer.innerHTML = markup;
            const modalElement = modalContainer.querySelector('.modal');

            if (!modalElement) {
                throw new Error('Unable to load the user form. Please try again.');
            }

            bootstrap.Modal.getOrCreateInstance(modalElement).show();
        }

        document.addEventListener('click', event => {
            const link = event.target.closest('a');
            if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }

            const url = new URL(link.href);
            if (!['addUser.php', 'editUser.php', 'deleteUser.php'].includes(url.pathname.split('/').pop())) {
                return;
            }

            event.preventDefault();
            loadUserModal(url.href).catch(error => {
                window.alert(error.message);
            });
        });

        modalContainer.addEventListener('submit', event => {
            const form = event.target.closest('form');
            if (!form) {
                return;
            }

            event.preventDefault();
            loadUserModal(form.action || window.location.href, {
                method: form.method || 'POST',
                body: new FormData(form)
            }).catch(error => {
                window.alert(error.message);
            });
        });

        modalContainer.addEventListener('hidden.bs.modal', event => {
            if (event.target === modalContainer.querySelector('.modal')) {
                modalContainer.replaceChildren();
            }
        });
    </script>
</body>
</html>
