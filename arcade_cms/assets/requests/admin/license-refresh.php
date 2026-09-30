<?php
require_once __DIR__ . '/../../../assets/includes/core.php';

if (!isset($GLOBALS['access']) || $GLOBALS['access'] !== true || empty($GLOBALS['userData']['admin'])) {
    $data = ['ok' => false, 'message' => 'Access denied.'];
    return;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    $data = ['ok' => false, 'message' => 'Use POST.'];
    return;
}

require_once __DIR__ . '/../../../assets/includes/license/bootstrap.php';

$state = gps_license_state_read();
$checkedAtTimestamp = !empty($state['checked_at']) ? strtotime((string)$state['checked_at']) : false;
$retryAfter = $checkedAtTimestamp !== false ? max(0, ($checkedAtTimestamp + 10800) - time()) : 0;

if ($retryAfter > 0) {
    http_response_code(429);
    $data = [
        'ok' => false,
        'code' => 'refresh_rate_limited',
        'message' => 'License refresh is available once every 3 hours.',
        'retry_after' => $retryAfter,
    ];
    return;
}

$result = gps_license_validate_online();
$data = [
    'ok' => !empty($result['ok']),
    'message' => (string)($result['message'] ?? 'License refresh failed.'),
    'code' => $result['code'] ?? null,
    'retry_after' => max(0, (int)($result['retry_after'] ?? 0)),
];
