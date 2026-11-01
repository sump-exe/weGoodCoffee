<?php
require_once __DIR__ . '/../_conn.php';

$roles = ['admin', 'cashier', 'manager', 'inventory_staff'];
$username = '';
$role = 'cashier';
$error = '';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($requestMethod === 'POST') {
    $usernameInput = $_POST['username'] ?? null;
    $password = $_POST['password'] ?? null;
    $role = $_POST['role'] ?? null;

    if (!is_string($usernameInput)) {
        $error = 'Enter a valid username.';
    } elseif (($username = trim($usernameInput)) === '' || strlen($username) > 100) {
        $error = 'Enter a username of up to 100 characters.';
    } elseif (!is_string($password) || $password === '') {
        $error = 'Enter a password.';
    } elseif (!in_array($role, $roles, true)) {
        $error = 'Choose a valid role.';
    } else {
        try {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $statement = $conn->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
            $statement->bind_param('sss', $username, $passwordHash, $role);
            $statement->execute();
            $statement->close();

            header('Location: index.php?status=added');
            exit;
        } catch (mysqli_sql_exception $exception) {
            error_log($exception->getMessage());
            $error = $exception->getCode() === 1062
                ? 'This username is already in use.'
                : 'Unable to add this user. Please try again.';
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
    <title>Add User | WeGoodCoffee</title>
</head>
<body>
    <header class="site-header">
        <a class="brand" href="index.php" aria-label="WeGoodCoffee admin home"><span class="brand-name">WeGood<strong>Coffee</strong></span></a>
    </header>

    <main class="admin-stage container-fluid">
        <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="post">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="userModalLabel">Add User</h1>
                            <a class="btn-close" href="index.php" aria-label="Close"></a>
                        </div>
                        <div class="modal-body">
                            <?php if ($error !== ''): ?>
                                <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                            <?php endif; ?>
                            <div class="mb-3">
                                <label class="form-label" for="username">Username</label>
                                <input class="form-control" id="username" name="username" type="text" maxlength="100" value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="password">Password</label>
                                <input class="form-control" id="password" name="password" type="password" autocomplete="new-password" required>
                            </div>
                            <div>
                                <label class="form-label" for="role">Role</label>
                                <select class="form-select" id="role" name="role" required>
                                    <?php foreach ($roles as $availableRole): ?>
                                        <option value="<?= htmlspecialchars($availableRole, ENT_QUOTES, 'UTF-8') ?>" <?= $role === $availableRole ? 'selected' : '' ?>><?= htmlspecialchars(ucwords(str_replace('_', ' ', $availableRole)), ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <a class="btn btn-secondary" href="index.php">Cancel</a>
                            <button class="btn admin-add-button" type="submit">Add User</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>new bootstrap.Modal(document.getElementById('userModal')).show();</script>
</body>
</html>
