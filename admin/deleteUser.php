<?php
require_once __DIR__ . '/../_conn.php';

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
            $statement = $conn->prepare('DELETE FROM users WHERE id = ?');
            $statement->bind_param('i', $userId);
            $statement->execute();
            $deleted = $statement->affected_rows;
            $statement->close();

            if ($deleted === 1) {
                header('Location: index.php?status=deleted');
                exit;
            }

            $error = 'This user was not deleted. Refresh the user list and try again.';
        }
    } catch (mysqli_sql_exception $exception) {
        error_log($exception->getMessage());
        $error = 'Unable to delete this user. Please try again.';
    }
}
?>
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') ?>" method="post">
                <input type="hidden" name="id" value="<?= htmlspecialchars((string) $userId, ENT_QUOTES, 'UTF-8') ?>">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="userModalLabel">Delete User</h1>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                    <?php if ($user !== null): ?>
                        <p>Are you sure you want to delete <strong><?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></strong>? This action cannot be undone.</p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                    <?php if ($user !== null): ?>
                        <button class="btn admin-delete-button" type="submit">Delete User</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>
