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
        <a class="brand" href="#" aria-label="WeGoodCoffee home"><span class="brand-name">WeGood<strong>Coffee</strong></span>
        </a>
    </header>

    <a href="admin.php">For testing purposes | Admin page</a>

    <main class="login-stage">
        <section class="login-card" aria-labelledby="login-title">
            </div>
            <h1 id="login-title">Welcome Back!<br><br></h1>

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