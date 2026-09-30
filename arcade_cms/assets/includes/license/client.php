<?php
declare(strict_types=1);

function gps_license_activate(string $licenseKey): array
{
    $licenseKey = strtoupper(trim($licenseKey));
    $domain = gps_license_exact_hostname();
    $installationId = gps_license_installation_id();
    $current = gps_license_state_read();

    if ($licenseKey === '' || $domain === '') {
        return ['ok' => false, 'message' => 'License key or hostname is missing.'];
    }

    $request = [
        'license_key' => $licenseKey,
        'domain' => $domain,
        'installation_id' => $installationId,
    ];

    if (!empty($current['installation_secret'])) {
        $request['installation_secret'] = (string)$current['installation_secret'];
    }

    $response = gps_license_api_post('activate.php', $request);
    $data = is_array($response['data'] ?? null) ? $response['data'] : [];

    if (empty($response['ok']) || empty($data['valid']) || !is_array($data['entitlement'] ?? null)) {
        $current['last_error'] = (string)($data['message'] ?? $response['error'] ?? 'Activation failed.');
        $current['checked_at'] = gmdate('c');
        gps_license_state_write($current);
        return [
            'ok' => false,
            'message' => $current['last_error'],
            'code' => $data['code'] ?? null,
            'status' => (int)($response['status'] ?? 0),
            'retry_after' => max(0, (int)($data['retry_after'] ?? 0)),
        ];
    }

    try {
        $claims = gps_license_verify_entitlement($data['entitlement'], $domain, $installationId);
    } catch (Throwable $e) {
        return ['ok' => false, 'message' => $e->getMessage()];
    }

    $state = array_merge($current, [
        'installation_id' => $installationId,
        'installation_secret' => (string)($data['installation_secret'] ?? $current['installation_secret'] ?? ''),
        'license_key' => $licenseKey,
        'entitlement' => $data['entitlement'],
        'claims' => $claims,
        'license_status' => (string)($claims['status'] ?? 'active'),
        'license_expires_at' => $claims['license_expires_at'] ?? null,
        'license_verified_at' => gmdate('c'),
        'checked_at' => gmdate('c'),
        'last_error' => null,
    ]);

    if ($state['installation_secret'] === '' || !gps_license_state_write($state)) {
        return ['ok' => false, 'message' => 'Could not securely save the installation license state.'];
    }

    return ['ok' => true, 'message' => 'License activated.', 'claims' => $claims];
}

function gps_license_validate_online(string $context = 'manual'): array
{
    $state = gps_license_state_read();
    $domain = gps_license_exact_hostname();
    $installationId = (string)($state['installation_id'] ?? '');

    if ($domain === '' || $installationId === '' || empty($state['license_key']) || empty($state['installation_secret'])) {
        return ['ok' => false, 'message' => 'License is not activated.'];
    }

    $response = gps_license_api_post('validate.php', [
        'license_key' => (string)$state['license_key'],
        'domain' => $domain,
        'installation_id' => $installationId,
        'installation_secret' => (string)$state['installation_secret'],
        'context' => $context === 'admin_login' ? 'admin_login' : 'manual',
    ]);
    $data = is_array($response['data'] ?? null) ? $response['data'] : [];

    if (empty($response['ok']) || empty($data['valid']) || !is_array($data['entitlement'] ?? null)) {
        $state['last_error'] = (string)($data['message'] ?? $response['error'] ?? 'Validation failed.');
        $state['checked_at'] = gmdate('c');
        $status = (int)($response['status'] ?? 0);
        if ($status >= 400 && $status < 500 && $status !== 429) {
            $license = is_array($data['license'] ?? null) ? $data['license'] : [];
            $state['license_status'] = (string)($license['status'] ?? (($data['code'] ?? '') === 'license_expired' ? 'expired' : 'inactive'));
            $state['license_expires_at'] = $license['expires_at'] ?? $state['license_expires_at'] ?? null;
            if (($data['code'] ?? '') !== 'license_expired') {
                $state['entitlement'] = null;
                $state['claims'] = null;
            }
        }
        gps_license_state_write($state);
        return [
            'ok' => false,
            'message' => $state['last_error'],
            'code' => $data['code'] ?? null,
            'status' => $status,
            'retry_after' => max(0, (int)($data['retry_after'] ?? 0)),
        ];
    }

    try {
        $claims = gps_license_verify_entitlement($data['entitlement'], $domain, $installationId);
    } catch (Throwable $e) {
        return ['ok' => false, 'message' => $e->getMessage()];
    }

    $state['entitlement'] = $data['entitlement'];
    $state['claims'] = $claims;
    $state['license_status'] = (string)($claims['status'] ?? 'active');
    $state['license_expires_at'] = $claims['license_expires_at'] ?? null;
    $state['license_verified_at'] = gmdate('c');
    $state['checked_at'] = gmdate('c');
    $state['last_error'] = null;
    gps_license_state_write($state);

    return ['ok' => true, 'message' => 'License validated.', 'claims' => $claims];
}

function gps_license_validate_on_admin_login(int $intervalSeconds = 86400): array
{
    $state = gps_license_state_read();
    $domain = gps_license_exact_hostname();
    if ($domain === '' || empty($state['license_key']) || empty($state['installation_secret'])) {
        return ['ok' => true, 'skipped' => true, 'message' => 'No activated license to validate.'];
    }

    $lastDomain = strtolower(trim((string)($state['login_validation_domain'] ?? '')));
    $lastCheck = strtotime((string)($state['login_validation_at'] ?? '')) ?: 0;
    $lastAttempt = strtotime((string)($state['login_validation_attempt_at'] ?? '')) ?: 0;
    $lastVerified = strtotime((string)($state['license_verified_at'] ?? '')) ?: 0;
    $now = time();
    $domainChanged = $lastDomain === '' || !hash_equals($lastDomain, $domain);
    $due = $domainChanged || $lastVerified === 0 || $lastCheck === 0 || ($now - $lastCheck) >= $intervalSeconds;

    if (!$due) {
        return ['ok' => true, 'skipped' => true, 'message' => 'Daily license validation is not due.'];
    }
    if (!$domainChanged && $lastAttempt > 0 && ($now - $lastAttempt) < 10800) {
        return ['ok' => true, 'skipped' => true, 'message' => 'License validation retry is cooling down.'];
    }

    $state['login_validation_attempt_at'] = gmdate('c');
    $state['login_validation_domain'] = $domain;
    gps_license_state_write($state);

    $result = gps_license_validate_online('admin_login');
    $state = gps_license_state_read();
    $state['login_validation_domain'] = $domain;
    if (!empty($result['ok']) || (int)($result['status'] ?? 0) > 0) {
        $state['login_validation_at'] = gmdate('c');
    }
    gps_license_state_write($state);

    return $result;
}

function gps_license_current_cms_version(): string
{
    $file = dirname(__DIR__, 3) . '/json/cms-version.json';
    if (!is_file($file)) {
        return '9.0.0';
    }
    $data = json_decode((string)@file_get_contents($file), true);
    $version = is_array($data) ? trim((string)($data['version'] ?? '')) : '';
    return $version !== '' ? $version : '9.0.0';
}

function gps_license_check_cms_update(): array
{
    $state = gps_license_state_read();
    $domain = gps_license_exact_hostname();
    $installationId = (string)($state['installation_id'] ?? '');
    if ($domain === '' || $installationId === '' || empty($state['license_key']) || empty($state['installation_secret'])) {
        return ['ok' => false, 'message' => 'Activate CMS PRO before checking for updates.'];
    }

    $response = gps_license_api_post('update-manifest.php', [
        'license_key' => (string)$state['license_key'],
        'domain' => $domain,
        'installation_id' => $installationId,
        'installation_secret' => (string)$state['installation_secret'],
        'current_version' => gps_license_current_cms_version(),
    ]);
    $data = is_array($response['data'] ?? null) ? $response['data'] : [];
    if (empty($response['ok']) || empty($data['ok'])) {
        return ['ok' => false, 'message' => (string)($data['message'] ?? $response['error'] ?? 'Update check failed.'), 'code' => $data['code'] ?? null];
    }

    $noticeFile = dirname(__DIR__, 3) . '/json/cms-update-notice.json';
    if (empty($data['update_available'])) {
        @file_put_contents($noticeFile, json_encode([
            'available' => false,
            'checked_at' => gmdate('c'),
            'latest_version' => (string)($data['latest_version'] ?? gps_license_current_cms_version()),
        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT), LOCK_EX);
        return [
            'ok' => true,
            'update_available' => false,
            'message' => 'CMS PRO is up to date.',
            'current_version' => gps_license_current_cms_version(),
            'latest_version' => (string)($data['latest_version'] ?? gps_license_current_cms_version()),
        ];
    }
    if (!is_array($data['manifest'] ?? null)) {
        return ['ok' => false, 'message' => 'Update API did not return a signed manifest.'];
    }

    try {
        $claims = gps_license_verify_update_manifest($data['manifest'], $domain, $installationId);
    } catch (Throwable $error) {
        return ['ok' => false, 'message' => $error->getMessage()];
    }
    $notice = [
        'available' => true,
        'version' => (string)$claims['version'],
        'message' => (string)($claims['message'] ?? 'A new protected CMS update is ready.'),
        'zip_url' => (string)$claims['download_url'],
        'sha256' => strtolower((string)$claims['sha256']),
        'sql_url' => (string)($claims['sql_url'] ?? ''),
        'sql_sha256' => strtolower((string)($claims['sql_sha256'] ?? '')),
        'expires_at' => (int)$claims['exp'],
        'watermark_id' => (string)($claims['watermark_id'] ?? ''),
        'manifest' => $data['manifest'],
        'checked_at' => gmdate('c'),
    ];
    if (@file_put_contents($noticeFile, json_encode($notice, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT), LOCK_EX) === false) {
        return ['ok' => false, 'message' => 'Could not save the signed update notice.'];
    }
    return [
        'ok' => true,
        'update_available' => true,
        'message' => 'Protected CMS update ' . $notice['version'] . ' is ready.',
        'version' => $notice['version'],
        'current_version' => gps_license_current_cms_version(),
        'latest_version' => $notice['version'],
    ];
}

function gps_license_get_feature_manifest(string $feature): array
{
    $state = gps_license_state_read();
    $domain = gps_license_exact_hostname();
    $installationId = (string)($state['installation_id'] ?? '');
    if ($domain === '' || $installationId === '' || empty($state['license_key']) || empty($state['installation_secret'])) {
        return ['ok' => false, 'message' => 'Activate CMS PRO before installing features.'];
    }

    $response = gps_license_api_post('feature-manifest.php', [
        'license_key' => (string)$state['license_key'],
        'domain' => $domain,
        'installation_id' => $installationId,
        'installation_secret' => (string)$state['installation_secret'],
        'feature' => $feature,
    ]);
    $data = is_array($response['data'] ?? null) ? $response['data'] : [];
    if (empty($response['ok']) || empty($data['ok']) || !is_array($data['manifest'] ?? null)) {
        return ['ok' => false, 'message' => (string)($data['message'] ?? $response['error'] ?? 'Feature package request failed.'), 'code' => $data['code'] ?? null];
    }

    try {
        $claims = gps_license_verify_feature_manifest($data['manifest'], $domain, $installationId, $feature);
    } catch (Throwable $error) {
        return ['ok' => false, 'message' => $error->getMessage()];
    }
    return ['ok' => true, 'claims' => $claims, 'manifest' => $data['manifest']];
}
