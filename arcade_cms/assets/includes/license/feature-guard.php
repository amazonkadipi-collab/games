<?php
declare(strict_types=1);

function gps_license_cached_claims(): array
{
    $state = gps_license_state_read();
    $entitlement = $state['entitlement'] ?? null;
    $installationId = (string)($state['installation_id'] ?? '');
    $domain = gps_license_exact_hostname();

    if (!is_array($entitlement) || $installationId === '' || $domain === '') {
        return [];
    }

    try {
        return gps_license_verify_entitlement($entitlement, $domain, $installationId);
    } catch (Throwable $e) {
        return [];
    }
}

function gps_license_cached_bound_claims(): array
{
    $state = gps_license_state_read();
    $entitlement = $state['entitlement'] ?? null;
    $installationId = (string)($state['installation_id'] ?? '');
    $domain = gps_license_exact_hostname();

    if (!is_array($entitlement) || $installationId === '' || $domain === '') {
        return [];
    }

    try {
        $claims = gps_license_verify_signed_token($entitlement);
        if (($claims['aud'] ?? '') !== 'gameportalscript-cms'
            || (int)($claims['v'] ?? 0) !== 2
            || !gps_license_domains_equivalent((string)($claims['domain'] ?? ''), $domain)
            || !hash_equals(
                (string)($claims['installation_id_hash'] ?? ''),
                hash('sha256', strtolower(trim($installationId)))
            )) {
            return [];
        }
        return $claims;
    } catch (Throwable $e) {
        return [];
    }
}

function gps_license_claims_allow_feature(array $claims, string $feature): bool
{
    $features = is_array($claims['features'] ?? null) ? $claims['features'] : [];
    return in_array($feature, $features, true)
        || in_array('all_pro_features', $features, true);
}

function gps_license_entitlement_fingerprint($entitlement): string
{
    if (!is_array($entitlement)) {
        return '';
    }

    $payload = trim((string)($entitlement['payload'] ?? ''));
    $signature = trim((string)($entitlement['signature'] ?? ''));
    if ($payload === '' || $signature === '') {
        return '';
    }

    return hash('sha256', $payload . "\0" . $signature);
}

function gps_license_is_pro_mutation_request(): bool
{
    if (strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
        return false;
    }

    $path = strtolower((string)parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH));
    return str_contains($path, '/assets/requests/admin/')
        || (strtolower((string)($_GET['t'] ?? '')) === 'admin');
}

function gps_license_validate_pro_mutation(string $feature): array
{
    static $onlineResult = null;

    if (!preg_match('/^[a-z][a-z0-9_]{1,63}$/', $feature)) {
        return ['ok' => false, 'message' => 'Unknown PRO feature.'];
    }
    if ($onlineResult === null) {
        $onlineResult = gps_license_validate_online('manual');
    }
    if (empty($onlineResult['ok'])) {
        return [
            'ok' => false,
            'message' => (string)($onlineResult['message'] ?? 'Renew and reactivate PRO before changing plugin settings.'),
            'code' => $onlineResult['code'] ?? null,
			'status' => (int)($onlineResult['status'] ?? 0),
			'retry_after' => max(0, (int)($onlineResult['retry_after'] ?? 0)),
        ];
    }

    $claims = is_array($onlineResult['claims'] ?? null) ? $onlineResult['claims'] : [];
    $status = strtolower(trim((string)($claims['status'] ?? 'active')));
    $expiryRaw = trim((string)($claims['license_expires_at'] ?? ''));
    $expiry = $expiryRaw !== '' ? strtotime($expiryRaw . ' UTC') : false;
    if ($status !== 'active' || ($expiry !== false && $expiry < time())) {
        return ['ok' => false, 'message' => 'Your paid PRO term has ended. Renew and reactivate before changing any PRO plugin.'];
    }
    if (!gps_license_claims_allow_feature($claims, $feature)) {
        return ['ok' => false, 'message' => 'This active license does not include the requested PRO feature.'];
    }

    return ['ok' => true, 'message' => 'PRO license verified online.', 'claims' => $claims];
}

function gps_license_validate_pro_mutation_cached(string $feature, string $scope, int $ttlSeconds = 10800): array
{
    if (!preg_match('/^[a-z][a-z0-9_]{1,63}$/', $feature)
        || !preg_match('/^[a-z][a-z0-9_]{1,63}$/', $scope)) {
        return ['ok' => false, 'message' => 'Unknown PRO license verification scope.'];
    }

    $ttlSeconds = max(60, min(10800, $ttlSeconds));
    $state = gps_license_state_read();
    $domain = gps_license_exact_hostname();
    $installationId = strtolower(trim((string)($state['installation_id'] ?? '')));
    $installationHash = $installationId !== '' ? hash('sha256', $installationId) : '';
    $entitlementFingerprint = gps_license_entitlement_fingerprint($state['entitlement'] ?? null);
    $approvals = is_array($state['pro_mutation_approvals'] ?? null)
        ? $state['pro_mutation_approvals']
        : [];
    $approval = is_array($approvals[$scope] ?? null) ? $approvals[$scope] : [];
    $verifiedAt = strtotime((string)($approval['verified_at'] ?? '')) ?: 0;
    $approvalDomain = strtolower(trim((string)($approval['domain'] ?? '')));
    $approvalFeature = strtolower(trim((string)($approval['feature'] ?? '')));
    $approvalInstallationHash = strtolower(trim((string)($approval['installation_id_hash'] ?? '')));
    $approvalEntitlementFingerprint = strtolower(trim((string)($approval['entitlement_fingerprint'] ?? '')));
    $licenseVerifiedAt = strtotime((string)($state['license_verified_at'] ?? '')) ?: 0;
    $freshScopedApproval = $verifiedAt > 0
        && (time() - $verifiedAt) < $ttlSeconds
        && hash_equals($approvalDomain, strtolower($domain))
        && hash_equals($approvalFeature, $feature)
        && $installationHash !== ''
        && hash_equals($approvalInstallationHash, $installationHash)
        && $entitlementFingerprint !== ''
        && hash_equals($approvalEntitlementFingerprint, $entitlementFingerprint);
    $sharedVerifiedAt = $freshScopedApproval ? $verifiedAt : 0;
    if (!$freshScopedApproval) {
        foreach ($approvals as $candidateApproval) {
            if (!is_array($candidateApproval)) {
                continue;
            }
            $candidateVerifiedAt = strtotime((string)($candidateApproval['verified_at'] ?? '')) ?: 0;
            if ($candidateVerifiedAt <= 0 || (time() - $candidateVerifiedAt) >= $ttlSeconds) {
                continue;
            }
            if (hash_equals(strtolower(trim((string)($candidateApproval['domain'] ?? ''))), strtolower($domain))
                && hash_equals(strtolower(trim((string)($candidateApproval['feature'] ?? ''))), $feature)
                && $installationHash !== ''
                && hash_equals(strtolower(trim((string)($candidateApproval['installation_id_hash'] ?? ''))), $installationHash)
                && $entitlementFingerprint !== ''
                && hash_equals(strtolower(trim((string)($candidateApproval['entitlement_fingerprint'] ?? ''))), $entitlementFingerprint)) {
                $freshScopedApproval = true;
                $sharedVerifiedAt = max($sharedVerifiedAt, $candidateVerifiedAt);
            }
        }
    }
    $freshApiApproval = $licenseVerifiedAt > 0
        && (time() - $licenseVerifiedAt) < $ttlSeconds;
    $status = strtolower(trim((string)($state['license_status'] ?? '')));

    if (($freshScopedApproval || $freshApiApproval)
        && $domain !== ''
        && !in_array($status, ['inactive', 'revoked', 'suspended', 'disabled', 'expired'], true)) {
        $claims = gps_license_cached_bound_claims();
        $expiryRaw = trim((string)($claims['license_expires_at'] ?? ''));
        $expiry = $expiryRaw !== '' ? strtotime($expiryRaw . ' UTC') : false;
        $tokenExpiry = (int)($claims['exp'] ?? 0);
        $claimStatus = strtolower(trim((string)($claims['status'] ?? 'active')));
        if ($claims !== []
            && $claimStatus === 'active'
            && $tokenExpiry >= time()
            && ($expiry === false || $expiry >= time())
            && gps_license_claims_allow_feature($claims, $feature)) {
            return [
                'ok' => true,
                'cached' => true,
                'message' => 'A recent signed PRO license approval is still valid.',
                'claims' => $claims,
                'valid_for' => max(0, $ttlSeconds - (time() - ($freshScopedApproval ? $sharedVerifiedAt : $licenseVerifiedAt))),
            ];
        }
    }

    $result = gps_license_validate_pro_mutation($feature);
    $state = gps_license_state_read();
    $domain = gps_license_exact_hostname();
    $installationId = strtolower(trim((string)($state['installation_id'] ?? '')));
    $installationHash = $installationId !== '' ? hash('sha256', $installationId) : '';
    $entitlementFingerprint = gps_license_entitlement_fingerprint($state['entitlement'] ?? null);
    $approvals = is_array($state['pro_mutation_approvals'] ?? null)
        ? $state['pro_mutation_approvals']
        : [];
    if (!empty($result['ok']) && $domain !== '' && $installationHash !== '' && $entitlementFingerprint !== '') {
        $approvals[$scope] = [
            'feature' => $feature,
            'domain' => strtolower($domain),
            'installation_id_hash' => $installationHash,
            'entitlement_fingerprint' => $entitlementFingerprint,
            'verified_at' => gmdate('c'),
        ];
    } else {
        unset($approvals[$scope]);
    }
    $state['pro_mutation_approvals'] = $approvals;
    gps_license_state_write($state);
    $result['cached'] = false;
    $result['valid_for'] = !empty($result['ok']) ? $ttlSeconds : 0;
    return $result;
}

function gps_license_installed_feature_allowed(string $feature): bool
{
    if (!preg_match('/^[a-z][a-z0-9_]{1,63}$/', $feature)) {
        return false;
    }

    $markerFile = dirname(__DIR__, 3) . '/json/pro-features/' . $feature . '.json';
    $marker = is_file($markerFile)
        ? json_decode((string)@file_get_contents($markerFile), true)
        : null;
    if (!is_array($marker) || !hash_equals($feature, (string)($marker['feature'] ?? ''))) {
        return false;
    }

    // Public/runtime access remains available after the paid term. Any Admin
    // POST that changes a PRO plugin must pass a recent, signed API approval.
    // The proof is rechecked on every mutation and refreshed online at most
    // once per three hours, preventing a busy Admin session from spamming API.
    if (gps_license_is_pro_mutation_request()) {
        if (empty(gps_license_validate_pro_mutation_cached($feature, 'admin_pro_settings', 10800)['ok'])) {
            return false;
        }
    }

    $state = gps_license_state_read();
    $status = strtolower(trim((string)($state['license_status'] ?? '')));
    if (in_array($status, ['inactive', 'revoked', 'suspended', 'disabled'], true)) {
        return false;
    }

    $domain = gps_license_exact_hostname();
    $installationId = trim((string)($state['installation_id'] ?? ''));
    if ($domain === '' || $installationId === '') {
        return false;
    }

    $markerBound = false;
    $signedManifest = $marker['signed_manifest'] ?? null;
    if (is_array($signedManifest)) {
        try {
            $claims = gps_license_verify_signed_token($signedManifest);
            $markerBound = ($claims['aud'] ?? '') === 'gameportalscript-cms-feature'
                && ($claims['type'] ?? '') === 'pro_feature'
                && (int)($claims['v'] ?? 0) === 1
                && hash_equals($feature, (string)($claims['feature'] ?? ''))
                && hash_equals($feature, (string)($claims['slug'] ?? ''))
                && gps_license_domains_equivalent((string)($claims['domain'] ?? ''), $domain)
                && hash_equals(
                    (string)($claims['installation_id_hash'] ?? ''),
                    hash('sha256', strtolower($installationId))
                );
        } catch (Throwable $e) {
            return false;
        }
    }

    // Markers created by an older installer did not retain the signed feature
    // manifest. They remain usable only while the saved entitlement proves
    // that this exact domain and installation were licensed for the feature.
    $boundClaims = gps_license_cached_bound_claims();
    if (!is_array($signedManifest)) {
        $markerBound = $boundClaims !== [] && gps_license_claims_allow_feature($boundClaims, $feature);
    }
    if (!$markerBound || $boundClaims === []) {
        return false;
    }

    $licenseExpiryRaw = trim((string)($boundClaims['license_expires_at'] ?? ''));
    $licenseExpiry = $licenseExpiryRaw !== '' ? strtotime($licenseExpiryRaw . ' UTC') : false;
    $signedStatus = strtolower(trim((string)($boundClaims['status'] ?? 'active')));

    // A completed paid term keeps the already delivered version on its
    // original installation. Only new packages and updates remain locked.
    if ($signedStatus === 'expired' || ($licenseExpiry !== false && $licenseExpiry < time())) {
        return true;
    }
    if ($signedStatus !== 'active') {
        return false;
    }

    // While the subscription is active, require a recent successful response
    // signed by GamePortalScript. A network failure never refreshes this time.
    $verifiedAt = strtotime((string)($state['license_verified_at'] ?? '')) ?: 0;
    if ($verifiedAt === 0) {
        $verifiedAt = (int)($boundClaims['iat'] ?? 0);
    }
    return $verifiedAt > 0 && (time() - $verifiedAt) <= 259200;
}

function gps_license_feature_allowed_online(string $feature): bool
{
    $claims = gps_license_cached_claims();
    if ($claims !== [] && gps_license_claims_allow_feature($claims, $feature)) {
        return true;
    }

    $online = gps_license_validate_online();
    return !empty($online['ok'])
        && gps_license_claims_allow_feature(
            is_array($online['claims'] ?? null) ? $online['claims'] : [],
            $feature
        );
}

function gps_license_feature_allowed(string $feature, bool $refreshWhenExpired = true): bool
{
    $state = gps_license_state_read();
    $boundClaims = gps_license_cached_bound_claims();
    $boundExpiryRaw = trim((string)($boundClaims['license_expires_at'] ?? $state['license_expires_at'] ?? ''));
    $boundExpiry = $boundExpiryRaw !== '' ? strtotime($boundExpiryRaw . ' UTC') : false;
    $subscriptionExpired = strtolower((string)($state['license_status'] ?? '')) === 'expired'
        || ($boundExpiry !== false && $boundExpiry < time());

    // Expiry stops new protected releases, not features already delivered to
    // this cryptographically bound domain and installation. Revoked,
    // suspended, moved or cloned installations do not use this path.
    if ($subscriptionExpired && $boundClaims !== []) {
        return gps_license_claims_allow_feature($boundClaims, $feature);
    }

    $claims = gps_license_cached_claims();
    $now = time();

    if ($claims !== []) {
        if ((int)($claims['exp'] ?? 0) >= $now) {
            return gps_license_claims_allow_feature($claims, $feature);
        }
        if ((int)($claims['grace_until'] ?? 0) >= $now) {
            if ($refreshWhenExpired) {
                $online = gps_license_validate_online();
                if (!empty($online['ok'])) {
                    return gps_license_claims_allow_feature(
                        is_array($online['claims'] ?? null) ? $online['claims'] : [],
                        $feature
                    );
                }
            }
            return gps_license_claims_allow_feature($claims, $feature);
        }
    }

    if ($refreshWhenExpired) {
        $online = gps_license_validate_online();
        if (!empty($online['ok'])) {
            return gps_license_claims_allow_feature(
                is_array($online['claims'] ?? null) ? $online['claims'] : [],
                $feature
            );
        }
    }

    return false;
}

function gps_require_pro_feature(string $feature): void
{
    if (gps_license_installed_feature_allowed($feature)) {
        return;
    }

    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-store, max-age=0');
    exit('This PRO feature requires a valid GamePortalScript license. The public website remains online.');
}
