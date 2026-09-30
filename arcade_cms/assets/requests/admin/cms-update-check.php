<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');

$root = dirname(__DIR__, 3);
require_once $root . '/assets/includes/core.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Use POST.']);
    exit;
}
if (!is_logged() || !is_admin()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Administrator login required.']);
    exit;
}
require_once $root . '/assets/includes/license/bootstrap.php';
$result = gps_license_check_cms_update();
http_response_code(!empty($result['ok']) ? 200 : 400);
echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
