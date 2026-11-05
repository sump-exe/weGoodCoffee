<?php
require_once __DIR__ . '/_session.php';
app_session_start();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}

$expired = ($_GET['expired'] ?? '') === '1';
app_end_session();
header('Location: ' . app_base_path() . '/index.php' . ($expired ? '?expired=1' : '?logged_out=1'));
exit;
