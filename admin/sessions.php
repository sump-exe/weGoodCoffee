<?php
require_once __DIR__ . '/../_session.php';
app_require_role('admin');
require_once __DIR__ . '/../_conn.php';

$sessions = [];
$loadError = '';

try {
    $result = $conn->query(
        'SELECT id, username, role, time_in, time_out FROM sessions ORDER BY time_in DESC, id DESC'
    );
    $sessions = $result->fetch_all(MYSQLI_ASSOC);
    $result->free();
} catch (mysqli_sql_exception $exception) {
    error_log($exception->getMessage());
    $loadError = 'Unable to load sessions. Check the database connection and sessions table.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles.css?v=4">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Sessions | WeGoodCoffee</title>
</head>
<body>
    <header class="site-header">
        <a class="brand" aria-label="WeGoodCoffee home"><span class="brand-name">WeGood<strong>Coffee</strong></span></a>
        <div class="dropdown ms-auto me-3">
            <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                Admin Menu
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="index.php">Users</a></li>
                <li><a class="dropdown-item active" href="sessions.php" aria-current="page">View Sessions</a></li>
            </ul>
        </div>
        <div class="d-flex align-items-center gap-3">
            <?php app_render_user_profile(); ?>
            <form action="../logout.php" method="post">
                <button class="btn btn-sm btn-light" type="submit">Log out</button>
            </form>
        </div>
    </header>

    <main class="admin-stage container-fluid">
        <div class="row justify-content-center">
            <section class="col-12 col-xl-10 admin-content" aria-labelledby="sessions-title">
                <div class="admin-heading">
                    <h1 id="sessions-title">Sessions</h1>
                    <p class="text-muted mb-0">Login history for all users.</p>
                </div>

                <?php if ($loadError !== ''): ?>
                    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($loadError, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <div class="admin-table-wrap">
                    <table class="table admin-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Username</th>
                                <th scope="col">Role</th>
                                <th scope="col">Time In</th>
                                <th scope="col">Time Out</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($sessions === [] && $loadError === ''): ?>
                                <tr>
                                    <td class="text-center text-muted py-4" colspan="6">No sessions found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($sessions as $session): ?>
                                    <tr>
                                        <th scope="row"><?= htmlspecialchars((string) $session['id'], ENT_QUOTES, 'UTF-8') ?></th>
                                        <td><?= htmlspecialchars($session['username'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><span class="role-badge"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $session['role'])), ENT_QUOTES, 'UTF-8') ?></span></td>
                                        <td><?= htmlspecialchars($session['time_in'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($session['time_out'] ?? 'Not available', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td>
                                            <?php if ($session['time_out'] === null): ?>
                                                <span class="badge text-bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge text-bg-secondary">Ended</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
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
