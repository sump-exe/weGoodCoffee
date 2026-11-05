<?php
require_once __DIR__ . '/_session.php';
app_session_start();

$error = '';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$roleHome = isset($_SESSION['role']) && is_string($_SESSION['role'])
    ? app_role_home($_SESSION['role'])
    : null;

if (isset($_SESSION['user_id']) && $roleHome !== null) {
    header('Location: ' . $roleHome);
    exit;
}

if ($requestMethod === 'POST') {
    require_once __DIR__ . '/_conn.php';

    $username = $_POST['username'] ?? null;
    $password = $_POST['password'] ?? null;

    if (!is_string($username) || !is_string($password) || trim($username) === '' || $password === '') {
        $error = 'Enter your username and password.';
    } else {
        try {
            $statement = $conn->prepare('SELECT id, username, password, role FROM users WHERE username = ?');
            $username = trim($username);
            $statement->bind_param('s', $username);
            $statement->execute();
            $result = $statement->get_result();
            $user = $result->fetch_assoc();
            $statement->close();

            $roleHome = $user !== null ? app_role_home($user['role']) : null;
            if ($user === null || !password_verify($password, $user['password']) || $roleHome === null) {
                $error = 'Invalid username or password.';
            } else {
                session_regenerate_id(true);
                $_SESSION = [];
                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_activity'] = time();

                header('Location: ' . $roleHome);
                exit;
            }
        } catch (mysqli_sql_exception $exception) {
            error_log($exception->getMessage());
            $error = 'Unable to log in right now. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#ad7548">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

</head>
<body>
    <header class="site-header">
        <a class="brand" aria-label="WeGoodCoffee home"><span class="brand-name">WeGood<strong>Coffee</strong></span>
        </a>
    </header>

    <main class="login-stage">
        <section class="login-card" aria-labelledby="login-title">
            <h1 id="login-title">Welcome Back!<br><br></h1>

            <?php if (($_GET['expired'] ?? '') === '1'): ?>
                <div class="alert alert-warning" role="status">Your session ended after 5 minutes of inactivity. Please log in again.</div>
            <?php elseif (($_GET['logged_out'] ?? '') === '1'): ?>
                <div class="alert alert-success" role="status">You have been logged out.</div>
            <?php endif; ?>
            <?php if ($error !== ''): ?>
                <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form class="login-form" action="" method="post">
                <label for="username">Username</label>
                <input id="username" name="username" type="text" placeholder="Enter your username" autocomplete="username" required>

                <div class="password-label">
                    <label for="password">Password</label>
                </div>
                <input id="password" name="password" type="password" placeholder="Enter your password" autocomplete="current-password" required>

                <button type="submit">Log in</button>
            </form>
        </section>
    </main>
</body>
</html>