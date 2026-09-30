<?php
declare(strict_types=1);

function gps_license_state_default(): array
{
    return [
        'installation_id' => '',
        'installation_secret' => '',
        'license_key' => '',
        'entitlement' => null,
        'claims' => null,
        'license_status' => '',
        'license_expires_at' => null,
        'license_verified_at' => null,
        'checked_at' => null,
        'login_validation_at' => null,
        'login_validation_attempt_at' => null,
        'login_validation_domain' => '',
        'last_error' => null,
    ];
}

function gps_license_state_read(): array
{
    $file = GPS_LICENSE_STATE_FILE;
    if (!is_file($file)) {
        return gps_license_state_default();
    }

    $raw = @file_get_contents($file);
    if (!is_string($raw)) {
        return gps_license_state_default();
    }

    $prefix = "<?php exit; ?>\n";
    if (strncmp($raw, $prefix, strlen($prefix)) !== 0) {
        return gps_license_state_default();
    }

    $decoded = json_decode(substr($raw, strlen($prefix)), true);
    return is_array($decoded) ? array_merge(gps_license_state_default(), $decoded) : gps_license_state_default();
}

function gps_license_state_write(array $state): bool
{
    $file = GPS_LICENSE_STATE_FILE;
    $directory = dirname($file);

    if (!is_dir($directory) && !@mkdir($directory, 0755, true) && !is_dir($directory)) {
        return false;
    }

    $payload = "<?php exit; ?>\n" . json_encode($state, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    $tmp = $file . '.tmp';

    if (@file_put_contents($tmp, $payload, LOCK_EX) === false) {
        return false;
    }

    @chmod($tmp, 0640);
    if (!@rename($tmp, $file)) {
        @unlink($tmp);
        return false;
    }

    @chmod($file, 0640);
    return true;
}

function gps_license_generate_installation_id(): string
{
    $bytes = random_bytes(16);
    $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
    $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
    $hex = bin2hex($bytes);

    return sprintf('%s-%s-%s-%s-%s',
        substr($hex, 0, 8),
        substr($hex, 8, 4),
        substr($hex, 12, 4),
        substr($hex, 16, 4),
        substr($hex, 20, 12)
    );
}

function gps_license_installation_id(): string
{
    $state = gps_license_state_read();
    $current = trim((string)($state['installation_id'] ?? ''));
    if ($current !== '') {
        return $current;
    }

    $state['installation_id'] = gps_license_generate_installation_id();
    if (!gps_license_state_write($state)) {
        throw new RuntimeException('License storage is not writable.');
    }

    return $state['installation_id'];
}
