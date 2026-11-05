<?php
require_once __DIR__ . '/../_conn.php';

$roles = ['admin', 'cashier', 'manager', 'inventory_staff'];
$userId = filter_var($_POST['id'] ?? $_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$user = null;
$error = '';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($userId === false || $userId === null) {
    http_response_code(400);
    $error = 'A valid user ID is required.';
} else {
    try {
        $statement = $conn->prepare('SELECT id, username, role FROM users WHERE id = ?');
        $statement->bind_param('i', $userId);
        $statement->execute();
        $result = $statement->get_result();
        $user = $result->fetch_assoc();
        $statement->close();

        if ($user === null) {
            http_response_code(404);
            $error = 'This user could not be found.';
        } elseif ($requestMethod === 'POST') {
            $usernameInput = $_POST['username'] ?? null;
            $role = $_POST['role'] ?? '';
            $password = $_POST['password'] ?? '';

            if (!is_string($usernameInput)) {
                $error = 'Enter a valid username.';
            } elseif (($username = trim($usernameInput)) === '' || strlen($username) > 100) {
                $error = 'Enter a username of up to 100 characters.';
            } elseif (!in_array($role, $roles, true)) {
                $error = 'Choose a valid role.';
            } elseif (!is_string($password)) {
                $error = 'Enter a valid password or leave it blank.';
            } else {
                if ($password === '') {
                    $statement = $conn->prepare('UPDATE users SET username = ?, role = ? WHERE id = ?');
                    $statement->bind_param('ssi', $username, $role, $userId);
                } else {
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    $statement = $conn->prepare('UPDATE users SET username = ?, role = ?, password = ? WHERE id = ?');
                    $statement->bind_param('sssi', $username, $role, $passwordHash, $userId);
                }

                $statement->execute();
                $statement->close();
                header('Location: index.php?status=updated');
                exit;
            }
        }
    } catch (mysqli_sql_exception $exception) {
        error_log($exception->getMessage());
        $error = $exception->getCode() === 1062
            ? 'This username is already in use.'
            : 'Unable to save these changes. Please try again.';
    }
}

$usernameInput = $_POST['username'] ?? null;
$roleInput = $_POST['role'] ?? null;
$username = $requestMethod === 'POST' && is_string($usernameInput)
    ? trim($usernameInput)
    : ($user['username'] ?? '');
$role = $requestMethod === 'POST' && is_string($roleInput)
    ? $roleInput
    : ($user['role'] ?? '');
?>
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') ?>" method="post">
                <input type="hidden" name="id" value="<?= htmlspecialchars((string) $userId, ENT_QUOTES, 'UTF-8') ?>">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="userModalLabel">Edit User</h1>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                    <?php if ($user !== null): ?>
                        <div class="mb-3">
                            <label class="form-label" for="username">Username</label>
                            <input class="form-control" id="username" name="username" type="text" maxlength="100" value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="role">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <?php foreach ($roles as $availableRole): ?>
                                    <option value="<?= htmlspecialchars($availableRole, ENT_QUOTES, 'UTF-8') ?>" <?= $role === $availableRole ? 'selected' : '' ?>><?= htmlspecialchars(ucwords(str_replace('_', ' ', $availableRole)), ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="password">New password <span class="text-muted">(leave blank to keep current password)</span></label>
                            <input class="form-control" id="password" name="password" type="password" autocomplete="new-password">
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                    <?php if ($user !== null): ?>
                        <button class="btn admin-edit-button" type="submit">Save Changes</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>
