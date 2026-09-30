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
require_once $root . '/assets/includes/license/feature-migration.php';

function gps_feature_reply(int $status, array $data): never
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function gps_feature_delete_directory(string $directory): void
{
    if (!is_dir($directory)) {
        return;
    }
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($iterator as $item) {
        $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
    }
    @rmdir($directory);
}

function gps_feature_download(string $url, string $target): void
{
    $handle = fopen($target, 'wb');
    if ($handle === false) {
        throw new RuntimeException('Could not create the temporary package file.');
    }
    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_FILE => $handle,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_CONNECTTIMEOUT => 20,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_USERAGENT => 'GamePortalScript-CMS-PRO-Feature/1.0',
    ]);
    $ok = curl_exec($curl);
    $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    curl_close($curl);
    fclose($handle);
    if (!$ok || $status < 200 || $status >= 300 || !is_file($target) || filesize($target) < 100) {
        @unlink($target);
        throw new RuntimeException('Feature download failed (HTTP ' . $status . '). ' . $error);
    }
    if (filesize($target) > 20971520) {
        @unlink($target);
        throw new RuntimeException('Feature package exceeds the 20 MB safety limit.');
    }
}

function gps_feature_safe_entry(string $name): string
{
    $normalized = str_replace('\\', '/', $name);
    if ($name === '' || str_contains($name, "\0") || str_starts_with($normalized, '/') || preg_match('#(^|/)\.\.(/|$)#', $normalized)) {
        throw new RuntimeException('Feature package contains an unsafe path.');
    }
    return trim($normalized, '/');
}

function gps_feature_content_checksum(ZipArchive $zip, array $files): array
{
    $records = [];
    $size = 0;
    $sorted = array_values($files);
    sort($sorted, SORT_STRING);
    foreach ($sorted as $path) {
        $contents = $zip->getFromName($path);
        if (!is_string($contents)) {
            throw new RuntimeException('A declared feature file is missing: ' . $path);
        }
        $bytes = strlen($contents);
        $size += $bytes;
        $records[] = ['path' => $path, 'size' => $bytes, 'sha256' => hash('sha256', $contents)];
    }
    return [
        'checksum' => hash('sha256', json_encode($records, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)),
        'installation_size' => $size,
    ];
}

$feature = strtolower(trim((string)($_POST['feature'] ?? '')));
if ($feature === '' || !preg_match('/^[a-z][a-z0-9_]{1,63}$/', $feature)) {
    gps_feature_reply(422, ['ok' => false, 'message' => 'Unknown PRO feature.']);
}
if (!class_exists('ZipArchive')) {
    gps_feature_reply(500, ['ok' => false, 'message' => 'PHP ZipArchive is required.']);
}

$temporaryRoot = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR);
$temporaryZip = tempnam($temporaryRoot, 'gps-pro-feature-');
if ($temporaryZip === false) {
    gps_feature_reply(500, ['ok' => false, 'message' => 'Could not reserve secure temporary storage.']);
}
$temporaryDirectory = $temporaryRoot . DIRECTORY_SEPARATOR . 'gps-pro-feature-' . bin2hex(random_bytes(8));
$installedFiles = [];
try {
    $manifest = gps_license_get_feature_manifest($feature);
    if (empty($manifest['ok'])) {
        gps_feature_reply(403, ['ok' => false, 'message' => (string)($manifest['message'] ?? 'The PRO feature is unavailable.')]);
    }
    $claims = $manifest['claims'];
    gps_feature_download((string)$claims['download_url'], $temporaryZip);
    if (!hash_equals(strtolower((string)$claims['checksum']), strtolower((string)hash_file('sha256', $temporaryZip)))) {
        throw new RuntimeException('Feature package checksum verification failed.');
    }
    if (version_compare(gps_license_current_cms_version(), (string)$claims['minimum_cms_version'], '<')) {
        throw new RuntimeException('This feature requires CMS v' . (string)$claims['minimum_cms_version'] . ' or newer.');
    }

    $zip = new ZipArchive();
    if ($zip->open($temporaryZip) !== true) {
        throw new RuntimeException('Could not open the feature package.');
    }
    $entries = [];
    for ($index = 0; $index < $zip->numFiles; $index++) {
        $rawName = str_replace('\\', '/', (string)$zip->getNameIndex($index));
        if (str_ends_with($rawName, '/')) {
            continue;
        }
        $name = gps_feature_safe_entry($rawName);
        $stat = $zip->statIndex($index);
        $attributes = is_array($stat) ? (int)($stat['external_attributes'] ?? 0) : 0;
        if ((($attributes >> 16) & 0170000) === 0120000) {
            $zip->close();
            throw new RuntimeException('Symbolic links are not allowed in feature packages.');
        }
        if ($name !== '') {
            $entries[] = $name;
        }
    }
    $descriptorRaw = $zip->getFromName('.gameportalscript-feature.json');
    $descriptor = is_string($descriptorRaw) ? json_decode($descriptorRaw, true) : null;
    if (!is_array($descriptor)
        || !hash_equals($feature, (string)($descriptor['slug'] ?? ''))
        || !hash_equals((string)$claims['version'], (string)($descriptor['version'] ?? ''))
        || !hash_equals((string)$claims['minimum_cms_version'], (string)($descriptor['minimum_cms_version'] ?? ''))
        || !hash_equals((string)$claims['content_checksum'], strtolower((string)($descriptor['checksum'] ?? '')))
        || (int)$claims['installation_size'] !== (int)($descriptor['installation_size'] ?? 0)
        || !hash_equals((string)$claims['tutorial_url'], (string)($descriptor['tutorial_url'] ?? ''))
        || !is_array($descriptor['files'] ?? null)) {
        $zip->close();
        throw new RuntimeException('Feature package descriptor is invalid.');
    }
    $declared = array_values(array_map('strval', $descriptor['files']));
    if ($declared !== array_values(array_map('strval', $claims['files']))) {
        $zip->close();
        throw new RuntimeException('Feature file list does not match the signed manifest.');
    }
    $migration = $descriptor['database_migration'] ?? null;
    if ($migration !== ($claims['database_migration'] ?? null)) {
        $zip->close();
        throw new RuntimeException('Feature database migration does not match the signed manifest.');
    }
    if ($migration !== null) {
        $migration = gps_feature_safe_entry((string)$migration);
        if (!str_ends_with(strtolower($migration), '.sql')) {
            $zip->close();
            throw new RuntimeException('Feature database migration must be a safe SQL file.');
        }
    }
    sort($declared);
    $actual = array_values(array_filter($entries, static fn(string $path): bool => !str_starts_with($path, '.gameportalscript-') && ($migration === null || $path !== $migration)));
    sort($actual);
    if ($actual !== $declared) {
        $zip->close();
        throw new RuntimeException('Feature package file list does not match its signed descriptor.');
    }
    $payloadFiles = $declared;
    if ($migration !== null) {
        $payloadFiles[] = $migration;
    }
    $payload = gps_feature_content_checksum($zip, $payloadFiles);
    if (!hash_equals((string)$claims['content_checksum'], $payload['checksum'])
        || (int)$claims['installation_size'] !== $payload['installation_size']) {
        $zip->close();
        throw new RuntimeException('Feature content metadata verification failed.');
    }
    foreach ($declared as $relative) {
        $relative = gps_feature_safe_entry($relative);
        $lower = strtolower($relative);
        if ($relative === '' || str_ends_with($relative, '/')
            || in_array($lower, ['assets/includes/config.php', 'assets/includes/license/license-state.php', 'assets/includes/license/public.key'], true)
            || str_contains($lower, 'private.key') || str_contains($lower, '/.git/')) {
            $zip->close();
            throw new RuntimeException('Feature package requests a protected file.');
        }
    }
    if (!mkdir($temporaryDirectory, 0755, true) || !$zip->extractTo($temporaryDirectory)) {
        $zip->close();
        throw new RuntimeException('Could not extract the feature package.');
    }
    $zip->close();

    $markerDirectory = $root . '/json/pro-features';
    $markerPath = $markerDirectory . '/' . $feature . '.json';
    $existingMarker = is_file($markerPath) ? json_decode((string)@file_get_contents($markerPath), true) : null;
    $existingOriginalFiles = is_array($existingMarker) && is_array($existingMarker['original_files'] ?? null)
        ? $existingMarker['original_files'] : [];
    $originalBackupRelative = is_array($existingMarker)
        ? trim((string)($existingMarker['original_backup_root'] ?? ''), '/') : '';
    if ($originalBackupRelative === '' || !str_starts_with($originalBackupRelative, 'json/pro-feature-backups/')) {
        $originalBackupRelative = 'json/pro-feature-backups/' . $feature . '-original-' . gmdate('Ymd-His');
    }
    $originalBackupRoot = $root . '/' . gps_feature_safe_entry($originalBackupRelative);
    $originalFiles = [];
    $transactionBackupRoot = $root . '/json/pro-feature-backups/' . $feature . '-install-' . gmdate('Ymd-His');
    $legacyBackupRoots = is_array($existingMarker)
        ? (glob($root . '/json/pro-feature-backups/' . $feature . '-*', GLOB_ONLYDIR) ?: []) : [];
    sort($legacyBackupRoots, SORT_STRING);
    foreach ($declared as $relative) {
        $source = $temporaryDirectory . '/' . $relative;
        $destination = $root . '/' . $relative;
        if (!is_file($source)) {
            throw new RuntimeException('Feature file is missing: ' . $relative);
        }
        $backup = null;
        if (is_file($destination)) {
            $backup = $transactionBackupRoot . '/' . $relative;
            if (!is_dir(dirname($backup)) && !mkdir(dirname($backup), 0750, true) && !is_dir(dirname($backup))) {
                throw new RuntimeException('Could not create the feature backup directory.');
            }
            if (!copy($destination, $backup)) {
                throw new RuntimeException('Could not back up an existing feature file.');
            }
        }

        if (array_key_exists($relative, $existingOriginalFiles)) {
            $savedOriginal = $existingOriginalFiles[$relative];
            if ($savedOriginal === null || $savedOriginal === '') {
                $originalFiles[$relative] = null;
            } else {
                $savedOriginal = gps_feature_safe_entry((string)$savedOriginal);
                if (!str_starts_with($savedOriginal, 'json/pro-feature-backups/') || !is_file($root . '/' . $savedOriginal)) {
                    throw new RuntimeException('The saved original backup is missing or unsafe: ' . $relative);
                }
                $originalFiles[$relative] = $savedOriginal;
            }
        } elseif (is_file($destination)) {
            $legacyOriginal = null;
            foreach ($legacyBackupRoots as $legacyRoot) {
                foreach ([$legacyRoot . '/' . $relative, $legacyRoot . '/files/' . $relative] as $candidate) {
                    if (is_file($candidate)) {
                        $legacyOriginal = $candidate;
                        break 2;
                    }
                }
            }
            $original = $originalBackupRoot . '/files/' . $relative;
            if (!is_dir(dirname($original)) && !mkdir(dirname($original), 0750, true) && !is_dir(dirname($original))) {
                throw new RuntimeException('Could not create the original-file backup directory.');
            }
            if (!copy($legacyOriginal ?? $destination, $original)) {
                throw new RuntimeException('Could not preserve the original file: ' . $relative);
            }
            $originalFiles[$relative] = trim(substr($original, strlen($root)), '/\\');
        } else {
            $originalFiles[$relative] = null;
        }

        if (!is_dir(dirname($destination)) && !mkdir(dirname($destination), 0755, true) && !is_dir(dirname($destination))) {
            throw new RuntimeException('Could not create a feature directory.');
        }
        $staged = $destination . '.gps-feature-' . bin2hex(random_bytes(4));
        if (!copy($source, $staged) || !rename($staged, $destination)) {
            @unlink($staged);
            throw new RuntimeException('Could not install feature file: ' . $relative);
        }
        @chmod($destination, 0644);
        $installedFiles[] = ['destination' => $destination, 'backup' => $backup];
    }

    $migrationResult = null;
    if ($migration !== null) {
        $migrationResult = gps_feature_run_database_migration(
            $feature,
            (string)$claims['version'],
            $temporaryDirectory . '/' . $migration
        );
    }

    if (!is_dir($markerDirectory) && !mkdir($markerDirectory, 0750, true) && !is_dir($markerDirectory)) {
        throw new RuntimeException('Could not create the installed-feature marker directory.');
    }
    $marker = [
        'feature' => $feature,
        'version' => (string)$claims['version'],
        'minimum_cms_version' => (string)$claims['minimum_cms_version'],
        'files' => array_values($claims['files']),
        'original_backup_root' => trim(substr($originalBackupRoot, strlen($root)), '/\\'),
        'original_files' => $originalFiles,
        'database_migration' => $claims['database_migration'],
        'database_migration_result' => $migrationResult,
        'checksum' => (string)$claims['content_checksum'],
        'installation_size' => (int)$claims['installation_size'],
        'tutorial_url' => (string)$claims['tutorial_url'],
        'installed_at' => gmdate('c'),
        'watermark_id' => (string)($claims['watermark_id'] ?? ''),
        'licensed_domain' => (string)($claims['domain'] ?? ''),
        'installation_id_hash' => (string)($claims['installation_id_hash'] ?? ''),
        'signed_manifest' => $manifest['manifest'],
    ];
    if (file_put_contents($markerDirectory . '/' . $feature . '.json', json_encode($marker, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX) === false) {
        throw new RuntimeException('Could not save the installed-feature marker.');
    }

    @unlink($temporaryZip);
    gps_feature_delete_directory($temporaryDirectory);
    $featureLabel = ucwords(str_replace('_', ' ', $feature));
    gps_feature_reply(200, [
        'ok' => true,
        'feature' => $feature,
        'version' => (string)$claims['version'],
        'message' => $featureLabel . ' v' . (string)$claims['version'] . ' installed.',
    ]);
} catch (Throwable $error) {
    foreach (array_reverse($installedFiles) as $installed) {
        $destination = (string)$installed['destination'];
        $backup = $installed['backup'];
        if (is_string($backup) && is_file($backup)) {
            $restore = $destination . '.gps-restore-' . bin2hex(random_bytes(4));
            if (@copy($backup, $restore)) {
                @rename($restore, $destination);
            }
            @unlink($restore);
        } else {
            @unlink($destination);
        }
    }
    @unlink($temporaryZip);
    gps_feature_delete_directory($temporaryDirectory);
    gps_feature_reply(500, ['ok' => false, 'message' => $error->getMessage()]);
}
