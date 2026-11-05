<?php

const APP_IDLE_TIMEOUT_SECONDS = 300;
const APP_IDLE_TIMEOUT_ROLES = ['admin', 'manager', 'inventory_staff'];

function app_base_path(): string
{
    $scriptDirectory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    if (in_array(basename($scriptDirectory), ['admin', 'pos', 'manager', 'invStaff'], true)) {
        $scriptDirectory = dirname($scriptDirectory);
    }

    return rtrim($scriptDirectory, '/');
}

function app_session_start(): void
{
    static $started = false;
    if ($started) {
        return;
    }

    if (session_status() !== PHP_SESSION_ACTIVE) {
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');
        session_name('WGCSESSID');

        $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && strtolower($_SERVER['HTTPS']) !== 'off';
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => app_base_path() . '/',
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
    }

    $started = true;
    header('Cache-Control: no-store, private, max-age=0');
    header('Pragma: no-cache');

    if (isset($_SESSION['user_id'])) {
        $role = $_SESSION['role'] ?? '';
        $lastActivity = $_SESSION['last_activity'] ?? null;

        if (
            in_array($role, APP_IDLE_TIMEOUT_ROLES, true)
            && is_int($lastActivity)
            && time() - $lastActivity >= APP_IDLE_TIMEOUT_SECONDS
        ) {
            app_end_session();
            header('Location: ' . app_base_path() . '/index.php?expired=1');
            exit;
        }

        $_SESSION['last_activity'] = time();
    }
}

function app_end_session(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $cookie = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $cookie['path'],
            'domain' => $cookie['domain'],
            'secure' => $cookie['secure'],
            'httponly' => $cookie['httponly'],
            'samesite' => $cookie['samesite'] ?? 'Lax',
        ]);
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}

function app_role_home(string $role): ?string
{
    $destinationByRole = [
        'admin' => '/admin/index.php',
        'cashier' => '/pos/index.php',
        'manager' => '/manager/index.php',
        'inventory_staff' => '/invStaff/index.php',
    ];

    return isset($destinationByRole[$role])
        ? app_base_path() . $destinationByRole[$role]
        : null;
}

function app_require_role(string ...$allowedRoles): void
{
    app_session_start();

    $role = $_SESSION['role'] ?? null;
    if (!isset($_SESSION['user_id'], $_SESSION['username']) || !is_string($role)) {
        header('Location: ' . app_base_path() . '/index.php');
        exit;
    }

    if (!in_array($role, $allowedRoles, true)) {
        http_response_code(403);
        echo 'You do not have permission to access this page.';
        exit;
    }
}
