<?php
declare(strict_types=1);

/**
 * Decode Base64URL data.
 *
 * @return string|false
 */
function gps_license_base64url_decode(string $value)
{
    $padding = strlen($value) % 4;
    if ($padding !== 0) {
        $value .= str_repeat('=', 4 - $padding);
    }

    return base64_decode(strtr($value, '-_', '+/'), true);
}

/**
 * Load sodium_compat when it is already bundled with the CMS or installed by Composer.
 */
function gps_license_load_sodium_compat(): void
{
    $packageAutoloaders = [
        __DIR__ . '/vendor/paragonie/sodium_compat/autoload.php',
        dirname(__DIR__, 3) . '/vendor/paragonie/sodium_compat/autoload.php',
    ];

    foreach ($packageAutoloaders as $candidate) {
        if (is_file($candidate)) {
            $packageRoot = dirname($candidate);

            // The hosting has a legacy Composer loader registered first. Its
            // PSR mapping throws before sodium_compat's normal loader runs.
            // Register this package's mappings at the front of SPL so both
            // namespaced and legacy class names resolve from this bundle.
            spl_autoload_register(static function ($class) use ($packageRoot): bool {
                $class = ltrim((string) $class, '\\');
                if (strpos($class, 'ParagonIE\\Sodium\\') === 0) {
                    $relative = substr($class, strlen('ParagonIE\\Sodium\\'));
                    $file = $packageRoot . '/namespaced/' . str_replace('\\', '/', $relative) . '.php';
                } elseif (strpos($class, 'ParagonIE_Sodium_') === 0) {
                    $relative = substr($class, strlen('ParagonIE_Sodium_'));
                    $file = $packageRoot . '/src/' . str_replace('_', '/', $relative) . '.php';
                } else {
                    return false;
                }

                if (!is_file($file)) {
                    return false;
                }

                require_once $file;
                return true;
            }, true, true);
            require_once $candidate;

            if (class_exists('ParagonIE_Sodium_Core_Util', true)) {
                return;
            }
        }
    }

    foreach ([dirname(__DIR__, 3) . '/vendor/autoload.php', dirname(__DIR__, 4) . '/vendor/autoload.php'] as $candidate) {
        if (is_file($candidate)) {
            require_once $candidate;
            if (class_exists('ParagonIE_Sodium_Core_Util', true)) {
                return;
            }
        }
    }
}

function gps_license_public_key(): string
{
    $encoded = trim((string) @file_get_contents(GPS_LICENSE_PUBLIC_KEY_FILE));
    $decoded = base64_decode($encoded, true);

    // An Ed25519 public key is always exactly 32 bytes.
    if (!is_string($decoded) || strlen($decoded) !== 32) {
        throw new RuntimeException('License public key is missing or invalid.');
    }

    return $decoded;
}

/**
 * Convert a raw 32-byte Ed25519 public key to SubjectPublicKeyInfo PEM.
 */
function gps_license_ed25519_public_key_pem(string $rawPublicKey): string
{
    // RFC 8410 SubjectPublicKeyInfo prefix for Ed25519 (OID 1.3.101.112).
    $der = hex2bin('302a300506032b6570032100') . $rawPublicKey;

    return "-----BEGIN PUBLIC KEY-----\n"
        . chunk_split(base64_encode($der), 64, "\n")
        . "-----END PUBLIC KEY-----\n";
}

/**
 * Verify an Ed25519 signature using the best cryptographic backend available.
 */
function gps_license_verify_ed25519(string $signature, string $message, string $publicKey): bool
{
    // Fast native backend available in PHP 7.2+ when ext-sodium is enabled.
    if (function_exists('sodium_crypto_sign_verify_detached')) {
        return sodium_crypto_sign_verify_detached($signature, $message, $publicKey);
    }

    // Prefer OpenSSL: it is independent of any CMS Composer autoloader and
    // supports Ed25519 on the PHP 8.3 hosting stack. Algorithm 0 selects the
    // key's intrinsic Ed25519 algorithm without an external digest.
    if (function_exists('openssl_pkey_get_public') && function_exists('openssl_verify')) {
        $key = @openssl_pkey_get_public(gps_license_ed25519_public_key_pem($publicKey));
        if ($key !== false) {
            $result = @openssl_verify($message, $signature, $key, 0);
            if (is_resource($key) && function_exists('openssl_free_key')) {
                @openssl_free_key($key);
            }
            if ($result === 1) {
                return true;
            }
            if ($result === 0) {
                return false;
            }
        }
    }

    // Composer/bundled pure-PHP compatibility backend, when available.
    gps_license_load_sodium_compat();
    if (class_exists('ParagonIE_Sodium_Compat', false)) {
        return ParagonIE_Sodium_Compat::crypto_sign_verify_detached(
            $signature,
            $message,
            $publicKey
        );
    }

    throw new RuntimeException(
        'Ed25519 verification is unavailable. The hosting requires Sodium, sodium_compat, or OpenSSL with Ed25519 support.'
    );
}

function gps_license_verify_signed_token(array $token): array
{
    if (($token['alg'] ?? '') !== 'Ed25519') {
        throw new RuntimeException('Unsupported signed token algorithm.');
    }

    $payload = (string) ($token['payload'] ?? '');
    $signature = gps_license_base64url_decode((string) ($token['signature'] ?? ''));
    if ($payload === '' || !is_string($signature)) {
        throw new RuntimeException('Signed token data is incomplete.');
    }

    if (!gps_license_verify_ed25519($signature, $payload, gps_license_public_key())) {
        throw new RuntimeException('Signed token signature is invalid.');
    }

    $json = gps_license_base64url_decode($payload);
    $claims = is_string($json) ? json_decode($json, true) : null;
    if (!is_array($claims)) {
        throw new RuntimeException('Signed token payload is invalid.');
    }
    return $claims;
}

function gps_license_verify_entitlement(array $entitlement, string $domain, string $installationId): array
{
    $claims = gps_license_verify_signed_token($entitlement);
    if (($claims['aud'] ?? '') !== 'gameportalscript-cms' || (int) ($claims['v'] ?? 0) !== 2) {
        throw new RuntimeException('Entitlement audience or version is invalid.');
    }
    if (!gps_license_domains_equivalent((string) ($claims['domain'] ?? ''), $domain)) {
        throw new RuntimeException('Entitlement domain does not match this website.');
    }
    if (!hash_equals(
        (string) ($claims['installation_id_hash'] ?? ''),
        hash('sha256', strtolower(trim($installationId)))
    )) {
        throw new RuntimeException('Entitlement installation does not match this CMS.');
    }
    if (($claims['status'] ?? '') !== 'active') {
        throw new RuntimeException('License is not active.');
    }

    $licenseExpiresAt = trim((string)($claims['license_expires_at'] ?? ''));
    $licenseExpiresTimestamp = $licenseExpiresAt !== '' ? strtotime($licenseExpiresAt . ' UTC') : false;
    if ($licenseExpiresTimestamp !== false && $licenseExpiresTimestamp < time()) {
        throw new RuntimeException('License subscription has expired.');
    }

    $graceUntil = (int) ($claims['grace_until'] ?? 0);
    if ($graceUntil < time()) {
        throw new RuntimeException('Entitlement and offline grace have expired.');
    }

    return $claims;
}

function gps_license_verify_update_manifest(array $manifest, string $domain, string $installationId): array
{
    $claims = gps_license_verify_signed_token($manifest);
    if (($claims['aud'] ?? '') !== 'gameportalscript-cms-update'
        || ($claims['type'] ?? '') !== 'cms_update'
        || (int)($claims['v'] ?? 0) !== 1) {
        throw new RuntimeException('Update manifest audience, type or version is invalid.');
    }
    if (!gps_license_domains_equivalent((string)($claims['domain'] ?? ''), $domain)) {
        throw new RuntimeException('Update manifest domain does not match this website.');
    }
    if (!hash_equals(
        (string)($claims['installation_id_hash'] ?? ''),
        hash('sha256', strtolower(trim($installationId)))
    )) {
        throw new RuntimeException('Update manifest installation does not match this CMS.');
    }
    if ((int)($claims['exp'] ?? 0) < time()) {
        throw new RuntimeException('Update manifest has expired. Check for updates again.');
    }
    if (!preg_match('/^[a-f0-9]{64}$/', strtolower((string)($claims['sha256'] ?? '')))) {
        throw new RuntimeException('Update package SHA-256 is invalid.');
    }
    $downloadUrl = (string)($claims['download_url'] ?? '');
    $parts = parse_url($downloadUrl);
    if (!is_array($parts)
        || strtolower((string)($parts['scheme'] ?? '')) !== 'https'
        || strtolower((string)($parts['host'] ?? '')) !== 'api.gameportalscript.com') {
        throw new RuntimeException('Update download URL is not trusted.');
    }
    if (!empty($claims['sql_url'])) {
        $sqlParts = parse_url((string)$claims['sql_url']);
        if (!is_array($sqlParts)
            || strtolower((string)($sqlParts['scheme'] ?? '')) !== 'https'
            || strtolower((string)($sqlParts['host'] ?? '')) !== 'api.gameportalscript.com'
            || !preg_match('/^[a-f0-9]{64}$/', strtolower((string)($claims['sql_sha256'] ?? '')))) {
            throw new RuntimeException('Update SQL download data is invalid.');
        }
    }
    if (trim((string)($claims['version'] ?? '')) === '') {
        throw new RuntimeException('Update version is missing.');
    }
    return $claims;
}

function gps_license_verify_feature_manifest(array $manifest, string $domain, string $installationId, string $feature): array
{
    $claims = gps_license_verify_signed_token($manifest);
    if (($claims['aud'] ?? '') !== 'gameportalscript-cms-feature'
        || ($claims['type'] ?? '') !== 'pro_feature'
        || (int)($claims['v'] ?? 0) !== 1) {
        throw new RuntimeException('Feature manifest audience, type or version is invalid.');
    }
    if (!hash_equals($feature, (string)($claims['feature'] ?? ''))) {
        throw new RuntimeException('Feature manifest does not match the requested feature.');
    }
    if (!hash_equals($feature, (string)($claims['slug'] ?? ''))
        || !preg_match('/^[a-z][a-z0-9_]{1,63}$/', (string)($claims['slug'] ?? ''))) {
        throw new RuntimeException('Feature manifest slug is invalid.');
    }
    if (!gps_license_domains_equivalent((string)($claims['domain'] ?? ''), $domain)) {
        throw new RuntimeException('Feature manifest domain does not match this website.');
    }
    if (!hash_equals(
        (string)($claims['installation_id_hash'] ?? ''),
        hash('sha256', strtolower(trim($installationId)))
    )) {
        throw new RuntimeException('Feature manifest installation does not match this CMS.');
    }
    if ((int)($claims['exp'] ?? 0) < time()) {
        throw new RuntimeException('Feature manifest has expired. Start the installation again.');
    }
    if (!preg_match('/^[a-f0-9]{64}$/', strtolower((string)($claims['sha256'] ?? '')))) {
        throw new RuntimeException('Feature package SHA-256 is invalid.');
    }
    if (!hash_equals(strtolower((string)$claims['sha256']), strtolower((string)($claims['checksum'] ?? '')))) {
        throw new RuntimeException('Feature package checksum does not match its SHA-256.');
    }
    if (!preg_match('/^[a-f0-9]{64}$/', strtolower((string)($claims['content_checksum'] ?? '')))) {
        throw new RuntimeException('Feature content checksum is invalid.');
    }
    if (!preg_match('/^[0-9]+(?:\.[0-9]+){1,3}(?:[-+][0-9A-Za-z.-]+)?$/', (string)($claims['minimum_cms_version'] ?? ''))) {
        throw new RuntimeException('Feature minimum CMS version is invalid.');
    }
    if (!is_array($claims['files'] ?? null) || $claims['files'] === [] || count($claims['files']) > 250) {
        throw new RuntimeException('Feature signed file list is invalid.');
    }
    if ((int)($claims['installation_size'] ?? 0) < 1 || (int)$claims['installation_size'] > 52428800) {
        throw new RuntimeException('Feature installation size is invalid.');
    }
    if (($claims['database_migration'] ?? null) !== null) {
        $migrationPath = str_replace('\\', '/', (string)$claims['database_migration']);
        if (!preg_match('/^[A-Za-z0-9_.\/-]+\.sql$/', $migrationPath)
            || str_starts_with($migrationPath, '/') || preg_match('#(^|/)\.\.(/|$)#', $migrationPath)) {
            throw new RuntimeException('Feature database migration is invalid.');
        }
    }
    $tutorial = parse_url((string)($claims['tutorial_url'] ?? ''));
    if (!is_array($tutorial)
        || strtolower((string)($tutorial['scheme'] ?? '')) !== 'https'
        || !in_array(strtolower((string)($tutorial['host'] ?? '')), ['gameportalscript.com', 'www.gameportalscript.com'], true)) {
        throw new RuntimeException('Feature tutorial URL is not trusted.');
    }
    $downloadUrl = (string)($claims['download_url'] ?? '');
    $parts = parse_url($downloadUrl);
    if (!is_array($parts)
        || strtolower((string)($parts['scheme'] ?? '')) !== 'https'
        || strtolower((string)($parts['host'] ?? '')) !== 'api.gameportalscript.com') {
        throw new RuntimeException('Feature download URL is not trusted.');
    }
    if (trim((string)($claims['version'] ?? '')) === '') {
        throw new RuntimeException('Feature version is missing.');
    }
    return $claims;
}
