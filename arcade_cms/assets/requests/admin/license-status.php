<?php
require_once __DIR__ . '/../../../assets/includes/core.php';

if (!isset($GLOBALS['access']) || $GLOBALS['access'] !== true || empty($GLOBALS['userData']['admin'])) {
    $data = ['ok' => false, 'message' => 'Access denied.'];
    return;
}

require_once __DIR__ . '/../../../assets/includes/license/bootstrap.php';

// The admin session can remain open for a long time, so checking only during
// password login is insufficient. This call contacts GamePortalScript at most
// once per 24 hours, but immediately when the exact hostname changes.
gps_license_validate_on_admin_login();

$state = gps_license_state_read();
$claims = gps_license_cached_bound_claims();
$storedStatus = strtolower(trim((string)($state['license_status'] ?? '')));
$licenseExpiresAt = trim((string)($claims['license_expires_at'] ?? $state['license_expires_at'] ?? ''));
$licenseExpiresTimestamp = $licenseExpiresAt !== '' ? strtotime($licenseExpiresAt . ' UTC') : false;
$licenseExpired = strtolower((string)($claims['status'] ?? $storedStatus)) === 'expired'
    || ($licenseExpiresTimestamp !== false && $licenseExpiresTimestamp < time());
$checkedAtTimestamp = !empty($state['checked_at']) ? strtotime((string)$state['checked_at']) : false;
$refreshAvailableAt = $checkedAtTimestamp !== false ? $checkedAtTimestamp + 10800 : 0;

$data = [
    'ok' => $claims !== [] && !$licenseExpired,
    'state' => $licenseExpired ? 'expired' : ($claims === [] ? 'inactive' : 'active'),
    'domain' => gps_license_exact_hostname(),
    'installation_id' => (string)($state['installation_id'] ?? ''),
    'plan' => (string)($claims['plan'] ?? ''),
    'features' => is_array($claims['features'] ?? null) ? $claims['features'] : [],
    'expires_at' => $licenseExpiresAt,
    'entitlement_refresh_at' => (int)($claims['exp'] ?? 0),
    'grace_until' => (int)($claims['grace_until'] ?? 0),
    'refresh_available_at' => $refreshAvailableAt,
    'refresh_retry_after' => max(0, $refreshAvailableAt - time()),
    'last_error' => $state['last_error'] ?? null,
];
