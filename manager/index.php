<?php
require_once __DIR__ . '/../_session.php';
app_require_role('manager');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Manager | WeGoodCoffee</title>
</head>
<body>
    <header class="site-header">
        <a class="brand" href="../index.php" aria-label="WeGoodCoffee home"><span class="brand-name">WeGood<strong>Coffee</strong></span></a>
        <form class="ms-auto" action="../logout.php" method="post">
            <button class="btn btn-sm btn-light" type="submit">Log out</button>
        </form>
    </header>
    <main class="admin-stage container-fluid">
        <div class="container">
            <section class="admin-content" aria-labelledby="manager-title">
                <h1 id="manager-title">Manager</h1>
                <p class="text-muted">Manage the cafe menu.</p>
                <a class="btn admin-add-button" href="menuItems.php">Menu Items</a>
            </section>
        </div>
    </main>
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
