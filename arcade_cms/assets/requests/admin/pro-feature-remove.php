<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');

$root = dirname(__DIR__, 3);
require_once $root . '/assets/includes/core.php';
if (!is_logged() || !is_admin()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Administrator login required.']);
    exit;
}
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Use POST.']);
    exit;
}
require_once $root . '/assets/includes/license/bootstrap.php';

function gps_feature_remove_reply(int $status, array $data): never
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function gps_feature_remove_safe_path(string $path): string
{
    $normalized = str_replace('\\', '/', trim($path));
    $lower = strtolower($normalized);
    if ($normalized === '' || str_contains($normalized, "\0") || str_starts_with($normalized, '/')
        || preg_match('/^[A-Za-z]:\//', $normalized) || preg_match('#(^|/)\.\.(/|$)#', $normalized)
        || in_array($lower, ['assets/includes/config.php', 'assets/includes/license/license-state.php', 'assets/includes/license/public.key'], true)
        || str_contains($lower, 'private.key') || str_contains($lower, '/.git/')) {
        throw new RuntimeException('The installed feature marker contains an unsafe path.');
    }
    return trim($normalized, '/');
}

function gps_feature_remove_owned_pro(): bool
{
    $claims = gps_license_cached_bound_claims();
    $state = gps_license_state_read();
    $plan = strtolower(trim((string)($claims['plan'] ?? '')));
    $status = strtolower(trim((string)($claims['status'] ?? $state['license_status'] ?? '')));
    $expiryRaw = trim((string)($claims['license_expires_at'] ?? $state['license_expires_at'] ?? ''));
    $expiry = $expiryRaw !== '' ? strtotime($expiryRaw . ' UTC') : false;
    $hasStoredLicense = trim((string)($state['license_key'] ?? '')) !== '';
    $expired = $status === 'expired' || ($expiry !== false && $expiry < time());
    $active = ($status === '' || $status === 'active') && ($expiry === false || $expiry >= time());
    return $hasStoredLicense && $plan !== 'free' && ($active || $expired);
}

$feature = strtolower(trim((string)($_POST['feature'] ?? '')));
if (!preg_match('/^[a-z][a-z0-9_]{1,63}$/', $feature)) {
    gps_feature_remove_reply(422, ['ok' => false, 'message' => 'Unknown PRO feature.']);
}
if (!gps_feature_remove_owned_pro()) {
    gps_feature_remove_reply(403, ['ok' => false, 'message' => 'A current or expired PRO license is required to remove installed features.']);
}
$mutationLicense = gps_license_validate_pro_mutation($feature);
if (empty($mutationLicense['ok'])) {
    gps_feature_remove_reply(403, ['ok' => false, 'message' => (string)$mutationLicense['message']]);
}

$markerDirectory = $root . '/json/pro-features';
$markerPath = $markerDirectory . '/' . $feature . '.json';
$marker = is_file($markerPath) ? json_decode((string)file_get_contents($markerPath), true) : null;
if (!is_array($marker) || !hash_equals($feature, (string)($marker['feature'] ?? '')) || !is_array($marker['files'] ?? null)) {
    gps_feature_remove_reply(404, ['ok' => false, 'message' => 'This PRO feature is not installed.']);
}

try {
    $declared = [];
    foreach ($marker['files'] as $file) {
        $safe = gps_feature_remove_safe_path((string)$file);
        $declared[$safe] = true;
    }

    $ownedByOtherFeature = [];
    foreach (glob($markerDirectory . '/*.json') ?: [] as $otherMarkerPath) {
        if ($otherMarkerPath === $markerPath) {
            continue;
        }
        $other = json_decode((string)@file_get_contents($otherMarkerPath), true);
        if (!is_array($other) || !is_array($other['files'] ?? null)) {
            continue;
        }
        foreach ($other['files'] as $otherFile) {
            try {
                $ownedByOtherFeature[gps_feature_remove_safe_path((string)$otherFile)] = true;
            } catch (Throwable $ignored) {
            }
        }
    }

    $backupRoot = $root . '/json/pro-feature-backups/' . $feature . '-removed-' . gmdate('Ymd-His');
    if (!mkdir($backupRoot, 0750, true) && !is_dir($backupRoot)) {
        throw new RuntimeException('Could not create the feature removal backup.');
    }
    if (!copy($markerPath, $backupRoot . '/marker.json')) {
        throw new RuntimeException('Could not back up the installed-feature marker.');
    }

    $originalFiles = is_array($marker['original_files'] ?? null) ? $marker['original_files'] : [];
    $legacyBackupRoots = glob($root . '/json/pro-feature-backups/' . $feature . '-*', GLOB_ONLYDIR) ?: [];
    $legacyBackupRoots = array_values(array_filter($legacyBackupRoots, static fn(string $path): bool => !str_contains(basename($path), '-removed-')));
    sort($legacyBackupRoots, SORT_STRING);
    $removeFiles = [];
    foreach (array_keys($declared) as $relative) {
        if (isset($ownedByOtherFeature[$relative])) {
            continue;
        }
        $source = $root . '/' . $relative;
        if (!is_file($source)) {
            continue;
        }
        $backup = $backupRoot . '/files/' . $relative;
        if (!is_dir(dirname($backup)) && !mkdir(dirname($backup), 0750, true) && !is_dir(dirname($backup))) {
            throw new RuntimeException('Could not create a feature file backup directory.');
        }
        if (!copy($source, $backup)) {
            throw new RuntimeException('Could not back up a feature file.');
        }

        $original = null;
        $delete = false;
        if (array_key_exists($relative, $originalFiles)) {
            $savedOriginal = $originalFiles[$relative];
            if ($savedOriginal === null || $savedOriginal === '') {
                $delete = true;
            } else {
                $savedOriginal = gps_feature_remove_safe_path((string)$savedOriginal);
                if (!str_starts_with($savedOriginal, 'json/pro-feature-backups/')) {
                    throw new RuntimeException('The original backup path is outside protected storage.');
                }
                $original = $root . '/' . $savedOriginal;
            }
        } else {
            foreach ($legacyBackupRoots as $legacyRoot) {
                foreach ([$legacyRoot . '/' . $relative, $legacyRoot . '/files/' . $relative] as $candidate) {
                    if (is_file($candidate)) {
                        $original = $candidate;
                        break 2;
                    }
                }
            }
            if ($original === null) {
                $proOwned = str_starts_with($relative, 'assets/pro/')
                    || preg_match('#^templates/(?:crazygames|y8|kizi|poki)-pro/#', $relative) === 1;
                if (!$proOwned) {
                    throw new RuntimeException('Original backup is unavailable for shared file: ' . $relative);
                }
                $delete = true;
            }
        }
        if (!$delete && ($original === null || !is_file($original))) {
            throw new RuntimeException('Original backup is missing for: ' . $relative);
        }
        $removeFiles[$relative] = ['source' => $source, 'backup' => $backup, 'original' => $original, 'delete' => $delete];
    }

    $removed = [];
    $restoredCount = 0;
    try {
        foreach ($removeFiles as $relative => $paths) {
            if (!empty($paths['delete'])) {
                if (!unlink($paths['source'])) {
                    throw new RuntimeException('Could not remove feature file: ' . $relative);
                }
            } else {
                $staged = $paths['source'] . '.gps-original-' . bin2hex(random_bytes(4));
                if (!copy((string)$paths['original'], $staged) || !rename($staged, $paths['source'])) {
                    @unlink($staged);
                    throw new RuntimeException('Could not restore original file: ' . $relative);
                }
                @chmod($paths['source'], 0644);
                $restoredCount++;
            }
            $removed[$relative] = $paths;
        }
        if (!unlink($markerPath)) {
            throw new RuntimeException('Could not remove the installed-feature marker.');
        }
    } catch (Throwable $error) {
        foreach ($removed as $paths) {
            if (is_file($paths['backup'])) {
                @copy($paths['backup'], $paths['source']);
            }
        }
        throw $error;
    }

    gps_feature_remove_reply(200, [
        'ok' => true,
        'feature' => $feature,
        'removed_files' => count($removed),
        'restored_files' => $restoredCount,
        'message' => ucwords(str_replace('_', ' ', $feature)) . ' removed and original files restored. It can be installed again at any time while PRO is active.',
    ]);
} catch (Throwable $error) {
    gps_feature_remove_reply(500, ['ok' => false, 'message' => $error->getMessage()]);
}
