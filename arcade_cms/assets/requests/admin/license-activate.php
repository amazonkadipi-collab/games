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

$key = trim((string)($_POST['license_key'] ?? ''));
$data = gps_license_activate($key);
