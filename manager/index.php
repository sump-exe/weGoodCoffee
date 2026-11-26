<?php
require_once __DIR__ . '/../_session.php';
app_require_role('manager');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles.css?v=5">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Manager | WeGoodCoffee</title>
</head>
<body class="manager-page">
    <header class="site-header">
        <a class="brand" href="index.php" aria-label="WeGoodCoffee manager home"><img class="brand-logo" src="../img/logo.png" alt=""></a>
        <div class="header-account d-flex align-items-center gap-3">
            <span class="manager-signed-in">Signed in as manager</span>
            <form action="../logout.php" method="post">
                <button class="btn btn-sm btn-light" type="submit">Log out</button>
            </form>
        </div>
    </header>
    <main class="manager-stage">
        <div class="manager-content">
            <section aria-labelledby="manager-title">
                <h1 id="manager-title">Manager</h1>
                <p class="manager-subtitle">Manage the cafe menu.</p>
                <div class="manager-cards">
                    <article class="manager-card manager-card-menu">
                        <h2>Menu Items</h2>
                        <p>Add, edit and remove the drinks and food that appear on the POS.</p>
                        <a class="manager-card-button" href="menuItems.php">Open menu items</a>
                    </article>
                    <article class="manager-card manager-card-reports">
                        <h2>Sales Reports</h2>
                        <p>Suggested next feature: daily sales and best-selling items.</p>
                        <span class="manager-coming-soon">Coming soon</span>
                    </article>
                </div>
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
