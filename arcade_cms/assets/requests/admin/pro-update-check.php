<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');
ini_set('display_errors', '0');

$root = dirname(__DIR__, 3);
require_once $root . '/assets/includes/core.php';

function gps_pro_update_reply(int $status, array $payload): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    gps_pro_update_reply(405, ['ok' => false, 'message' => 'Use POST.']);
}
if (!is_logged() || !is_admin()) {
    gps_pro_update_reply(401, ['ok' => false, 'message' => 'Administrator login required.']);
}

require_once $root . '/assets/includes/license/bootstrap.php';
require_once $root . '/assets/includes/license/feature-catalog.php';

function gps_pro_quarantine_installed_markers(string $reason): int
{
    $markerDirectory = rtrim(ABSPATH, '/\\') . '/json/pro-features';
    $markers = is_dir($markerDirectory) ? (glob($markerDirectory . '/*.json') ?: []) : [];
    if ($markers === []) {
        return 0;
    }

    $suffix = gmdate('Ymd-His') . '-' . bin2hex(random_bytes(3));
    $quarantine = rtrim(ABSPATH, '/\\') . '/json/pro-feature-quarantine/' . $suffix;
    if (!mkdir($quarantine, 0750, true) && !is_dir($quarantine)) {
        throw new RuntimeException('The unlicensed PRO features could not be disabled safely.');
    }

    $moved = 0;
    foreach ($markers as $marker) {
        $name = basename($marker);
        if (!preg_match('/^[a-z][a-z0-9_]{1,63}\.json$/', $name)) {
            continue;
        }
        $destination = $quarantine . '/' . $name;
        if (@rename($marker, $destination) || (@copy($marker, $destination) && @unlink($marker))) {
            $moved++;
        }
    }

    @file_put_contents($quarantine . '/reason.json', json_encode([
        'reason' => $reason,
        'domain' => function_exists('gps_license_exact_hostname') ? gps_license_exact_hostname() : '',
        'quarantined_at' => gmdate('c'),
        'markers' => $moved,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    return $moved;
}

$validation = gps_license_validate_online('manual');
if (empty($validation['ok'])) {
    $code = strtolower(trim((string)($validation['code'] ?? '')));
    $authoritativeCloneCodes = [
        'license_not_found',
        'domain_not_activated',
        'domain_deactivated',
        'installation_mismatch',
        'installation_secret_invalid',
        'license_revoked',
        'license_suspended',
        'license_disabled',
        'license_inactive',
    ];

    if (in_array($code, $authoritativeCloneCodes, true)) {
        try {
            $disabled = gps_pro_quarantine_installed_markers($code);
        } catch (Throwable $error) {
            gps_pro_update_reply(500, ['ok' => false, 'message' => $error->getMessage(), 'code' => $code]);
        }
        gps_pro_update_reply(403, [
            'ok' => false,
            'code' => $code,
            'disabled_features' => $disabled,
            'message' => 'This exact domain is not licensed for PRO. Installed PRO features were disabled. Activate or buy PRO to continue.',
            'buy_url' => 'https://gameportalscript.com/pro.php',
        ]);
    }

    if ($code === 'license_expired') {
        gps_pro_update_reply(403, [
            'ok' => false,
            'code' => $code,
            'message' => 'PRO has expired. Installed features remain available; renew PRO to receive new updates.',
            'buy_url' => 'https://gameportalscript.com/pro.php',
        ]);
    }

    $message = trim((string)($validation['message'] ?? ''));
    if ($message === 'License is not activated.') {
        try {
            $disabled = gps_pro_quarantine_installed_markers('license_not_activated');
        } catch (Throwable $error) {
            gps_pro_update_reply(500, ['ok' => false, 'message' => $error->getMessage()]);
        }
        $message = 'This domain does not have an active PRO license. Activate or buy PRO to check for updates.';
        if ($disabled > 0) {
            $message .= ' Copied PRO features were disabled.';
        }
    }
    gps_pro_update_reply(403, [
        'ok' => false,
        'code' => $code !== '' ? $code : null,
        'message' => $message !== '' ? $message : 'The license could not be verified. No installed feature was changed.',
        'retry_after' => max(0, (int)($validation['retry_after'] ?? 0)),
        'buy_url' => 'https://gameportalscript.com/pro.php',
    ]);
}

$catalog = gps_pro_feature_catalog_load(true, 0);
$updates = [];
foreach ($catalog as $feature) {
    $slug = strtolower(trim((string)($feature['slug'] ?? '')));
    $remoteVersion = trim((string)($feature['version'] ?? ''));
    if ($slug === '' || $remoteVersion === '') {
        continue;
    }
    $markerPath = rtrim(ABSPATH, '/\\') . '/json/pro-features/' . $slug . '.json';
    $marker = is_file($markerPath) ? json_decode((string)@file_get_contents($markerPath), true) : null;
    $installedVersion = is_array($marker) ? trim((string)($marker['version'] ?? '0.0.0')) : '';
    if ($installedVersion !== '' && version_compare($remoteVersion, $installedVersion, '>')) {
        $updates[] = ['slug' => $slug, 'installed' => $installedVersion, 'available' => $remoteVersion];
    }
}

gps_pro_update_reply(200, [
    'ok' => true,
    'updates' => $updates,
    'message' => $updates === []
        ? 'PRO verified for this domain. All installed features are current.'
        : count($updates) . ' PRO feature update(s) are available.',
]);
