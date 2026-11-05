<?php
require_once __DIR__ . '/_session.php';
app_require_role(...APP_IDLE_TIMEOUT_ROLES);

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['ok' => true]);
