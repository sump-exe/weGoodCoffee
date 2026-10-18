<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#ad7548">
    <title>Log in | WeGoodCoffee</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="site-header">
        <a class="brand" href="#" aria-label="WeGoodCoffee home">
            <!--<span class="brand-mark" aria-hidden="true">
                <svg viewBox="0 0 32 32" role="presentation">
                    <path d="M8 13h14v7a7 7 0 0 1-7 7h-1a6 6 0 0 1-6-6v-8Z" />
                    <path d="M22 15h2a3 3 0 0 1 0 6h-3M11 9c0-2 2-2 2-4m4 4c0-2 2-2 2-4M7 29h18" />
                </svg>
            </span>-->
            <span class="brand-name">WeGood<strong>Coffee</strong></span>
        </a>
    </header>

    <main class="login-stage">
        <section class="login-card" aria-labelledby="login-title">
            <!--<div class="coffee-icon" aria-hidden="true">
                <svg viewBox="0 0 32 32" role="presentation">
                    <path d="M7 13h16v7a7 7 0 0 1-7 7h-2a7 7 0 0 1-7-7v-7Zm16 2h2a3 3 0 0 1 0 6h-3M11 9c0-2 2-2 2-4m4 4c0-2 2-2 2-4M5 29h22" />
                </svg>-->
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