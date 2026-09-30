<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');
ini_set('display_errors', '0');

function gps_pro_update_notify_reply(int $status, array $payload): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    gps_pro_update_notify_reply(405, ['ok' => false, 'message' => 'Use POST.']);
}

$root = dirname(__DIR__, 3);
require_once $root . '/assets/includes/core.php';
require_once $root . '/assets/includes/license/bootstrap.php';
require_once $root . '/assets/includes/license/feature-catalog.php';

try {
    $raw = (string)file_get_contents('php://input');
    if ($raw === '' || strlen($raw) > 16384) {
        throw new RuntimeException('Notification body is invalid.');
    }
    $body = json_decode($raw, true, 16, JSON_THROW_ON_ERROR);
    $token = is_array($body) ? (array)($body['token'] ?? []) : [];
    $claims = gps_license_verify_signed_token($token);

    if (($claims['aud'] ?? '') !== 'gameportalscript-cms-feature-notify'
        || ($claims['type'] ?? '') !== 'pro_feature_update'
        || (int)($claims['v'] ?? 0) !== 1) {
        throw new RuntimeException('Notification audience, type or version is invalid.');
    }
    $now = time();
    if ((int)($claims['iat'] ?? 0) > $now + 60 || (int)($claims['exp'] ?? 0) < $now) {
        throw new RuntimeException('Notification has expired.');
    }
    if (!gps_license_domains_equivalent((string)($claims['domain'] ?? ''), gps_license_exact_hostname())) {
        throw new RuntimeException('Notification domain does not match this website.');
    }

    $state = gps_license_state_read();
    $installationId = strtolower(trim((string)($state['installation_id'] ?? '')));
    $installationHash = (string)($claims['installation_id_hash'] ?? '');
    if ($installationId === '' || !hash_equals($installationHash, hash('sha256', $installationId))) {
        throw new RuntimeException('Notification installation does not match this CMS.');
    }

    $slug = strtolower(trim((string)($claims['feature'] ?? '')));
    $version = trim((string)($claims['version'] ?? ''));
    gps_pro_feature_update_notice_record($slug, $version, (string)($claims['published_at'] ?? ''));
    gps_pro_update_notify_reply(200, ['ok' => true, 'feature' => $slug, 'version' => $version]);
} catch (Throwable $exception) {
    gps_pro_update_notify_reply(403, ['ok' => false, 'message' => $exception->getMessage()]);
}
