<?php
header('Content-Type: application/json; charset=utf-8');

error_reporting(E_ALL);
ini_set('display_errors', 0);
set_time_limit(0);
ignore_user_abort(true);

$ROOT = dirname(__DIR__, 3);
$NOTICE_FILE = $ROOT . '/json/cms-update-notice.json';
$VERSION_FILE = $ROOT . '/json/cms-version.json';
$CMS_ZIP = $ROOT . '/latest-cms.zip';
$DATABASE_SQL = $ROOT . '/database.sql';
$TEMP_DIR = $ROOT . '/_cms_update_tmp_' . date('Ymd_His');

$summary = [
	'download_zip' => false,
	'download_sql' => false,
	'files_replaced' => 0,
	'files_created' => 0,
	'files_same' => 0,
	'files_skipped' => 0,
	'db_tables_created' => 0,
	'db_columns_added' => 0,
	'cleanup_deleted' => 0,
	'htaccess_patched' => 0,
	'errors' => []
];

function out($data) {
	echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	exit;
}

function addErr($msg) {
	global $summary;
	$summary['errors'][] = $msg;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	out(['ok' => false, 'error' => 'Use POST to run a CMS update.']);
}

require_once $ROOT . '/assets/includes/core.php';

if (!is_logged() || !is_admin()) {
	http_response_code(401);
	out(['ok' => false, 'error' => 'Administrator login required.']);
}

require_once $ROOT . '/assets/includes/license/bootstrap.php';

function normalizePath($path) {
	$path = str_replace('\\', '/', $path);
	$path = preg_replace('#/+#', '/', $path);
	return trim($path, '/');
}

function zipEntriesAreSafe(ZipArchive $zip) {
	for ($index = 0; $index < $zip->numFiles; $index++) {
		$name = (string)$zip->getNameIndex($index);
		$normalized = str_replace('\\', '/', $name);
		if ($name === '' || strpos($name, "\0") !== false || substr($normalized, 0, 1) === '/' || preg_match('#(^|/)\.\.(/|$)#', $normalized)) {
			return false;
		}
		$stat = $zip->statIndex($index);
		$attributes = is_array($stat) ? (int)($stat['external_attributes'] ?? 0) : 0;
		if ((($attributes >> 16) & 0170000) === 0120000) {
			return false;
		}
	}
	return true;
}

function deleteDirSafe($dir) {
	if (!is_dir($dir)) return;

	$items = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);

	foreach ($items as $item) {
		if ($item->isDir()) {
			@rmdir($item->getPathname());
		} else {
			@unlink($item->getPathname());
		}
	}

	@rmdir($dir);
}

function downloadFile($url, $targetFile) {
	$url = trim((string)$url);

	if ($url === '') {
		return ['ok' => false, 'error' => 'Empty URL'];
	}

	$tmpFile = $targetFile . '.tmp';

	$fp = fopen($tmpFile, 'w');
	if (!$fp) {
		return ['ok' => false, 'error' => 'Cannot write temp file'];
	}

	$ch = curl_init($url);
	curl_setopt_array($ch, [
		CURLOPT_FILE => $fp,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_TIMEOUT => 900,
		CURLOPT_CONNECTTIMEOUT => 30,
		CURLOPT_SSL_VERIFYPEER => true,
		CURLOPT_SSL_VERIFYHOST => 2,
		CURLOPT_USERAGENT => 'CMS Updater'
	]);

	$ok = curl_exec($ch);
	$error = curl_error($ch);
	$http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	fclose($fp);

	if (!$ok || $http < 200 || $http >= 300) {
		@unlink($tmpFile);
		return ['ok' => false, 'error' => 'Download failed HTTP ' . $http . ' ' . $error];
	}

	if (!is_file($tmpFile) || filesize($tmpFile) < 50) {
		@unlink($tmpFile);
		return ['ok' => false, 'error' => 'Downloaded file too small'];
	}

	rename($tmpFile, $targetFile);

	return [
		'ok' => true,
		'size' => filesize($targetFile)
	];
}

function findRealCmsRoot($extractDir) {
	$rootFiles = ['index.php', 'gm-load.php', 'assets', 'templates'];
	$score = 0;

	foreach ($rootFiles as $item) {
		if (file_exists($extractDir . '/' . $item)) {
			$score++;
		}
	}

	if ($score >= 2) {
		return $extractDir;
	}

	$items = array_values(array_filter(scandir($extractDir), function ($item) use ($extractDir) {
		return $item !== '.' && $item !== '..' && is_dir($extractDir . '/' . $item);
	}));

	if (count($items) === 1) {
		$inside = $extractDir . '/' . $items[0];
		$insideScore = 0;

		foreach ($rootFiles as $item) {
			if (file_exists($inside . '/' . $item)) {
				$insideScore++;
			}
		}

		if ($insideScore >= 2) {
			return $inside;
		}
	}

	return $extractDir;
}

function collectFiles($sourceRoot) {
	$files = [];

	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($sourceRoot, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::SELF_FIRST
	);

	foreach ($iterator as $item) {
		if ($item->isDir()) continue;

		$full = $item->getPathname();
		$relative = normalizePath(substr($full, strlen($sourceRoot) + 1));

		if ($relative !== '') {
			$files[] = [
				'source' => $full,
				'relative' => $relative
			];
		}
	}

	usort($files, function ($a, $b) {
		return strcmp($a['relative'], $b['relative']);
	});

	return $files;
}

function isProtectedFile($relative) {
	$relative = normalizePath($relative);
	$base = strtolower(basename($relative));

	$protectedExact = [
		'.htaccess',
		'ads.txt',
		'robots.txt',
		'sitemap.xml',
		'assets/includes/config.php',
		'assets/includes/license/license-state.php',
		'assets/includes/license/public.key',
		'json/cms-sites.json',
		'json/cms-version.json',
		'json/cms-update-notice.json',
	];

	if (in_array($relative, $protectedExact, true)) {
		return true;
	}

	if ($base === 'config.php') {
		return true;
	}

	return false;
}

function patchHtaccessBlocks($sourceRoot) {
	global $ROOT, $summary;

	$sourceHtaccess = $sourceRoot . '/.htaccess';
	$targetHtaccess = $ROOT . '/.htaccess';

	if (!is_file($sourceHtaccess)) {
		return;
	}

	$sourceContent = file_get_contents($sourceHtaccess);
	if ($sourceContent === false || trim($sourceContent) === '') {
		return;
	}

	$targetContent = is_file($targetHtaccess) ? file_get_contents($targetHtaccess) : '';
	if ($targetContent === false) {
		$targetContent = '';
	}

	preg_match_all(
		'/# CMS UPDATE BLOCK START: ([a-zA-Z0-9_\-\.]+)(.*?)# CMS UPDATE BLOCK END: \1/s',
		$sourceContent,
		$matches,
		PREG_SET_ORDER
	);

	foreach ($matches as $match) {
		$blockName = trim($match[1]);
		$fullBlock = trim($match[0]);

		if ($blockName === '' || $fullBlock === '') {
			continue;
		}

		$pattern = '/# CMS UPDATE BLOCK START: ' . preg_quote($blockName, '/') . '.*?# CMS UPDATE BLOCK END: ' . preg_quote($blockName, '/') . '/s';

		if (preg_match($pattern, $targetContent)) {
			$targetContent = preg_replace($pattern, $fullBlock, $targetContent);
		} else {
			$targetContent = rtrim($targetContent) . "\n\n" . $fullBlock . "\n";
		}
	}

	if (is_file($targetHtaccess)) {
		@copy($targetHtaccess, $targetHtaccess . '.backup-' . date('Ymd-His'));
	}

	file_put_contents($targetHtaccess, $targetContent);

	$summary['htaccess_patched']++;
}

function findInnerCmsZip($sourceRoot) {
	$zipFiles = glob($sourceRoot . '/*.zip');

	if (empty($zipFiles)) {
		return '';
	}

	foreach ($zipFiles as $zipFile) {
		$name = strtolower(basename($zipFile));

		if (strpos($name, 'cms') !== false) {
			return $zipFile;
		}
	}

	return $zipFiles[0];
}

function findFirstFileRecursive($dir, $extension, $nameContains = '') {
	if (!is_dir($dir)) return '';

	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
	);

	foreach ($iterator as $file) {
		if (!$file->isFile()) continue;

		$path = $file->getPathname();
		$name = strtolower($file->getFilename());

		if (strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== strtolower($extension)) {
			continue;
		}

		if ($nameContains !== '' && strpos($name, strtolower($nameContains)) === false) {
			continue;
		}

		return $path;
	}

	return '';
}

function findLargestZipRecursive($dir) {
	if (!is_dir($dir)) return '';

	$bestFile = '';
	$bestSize = 0;

	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
	);

	foreach ($iterator as $file) {
		if (!$file->isFile()) continue;

		$path = $file->getPathname();
		$name = strtolower($file->getFilename());

		if (pathinfo($name, PATHINFO_EXTENSION) !== 'zip') {
			continue;
		}

		$size = (int)$file->getSize();

		if ($size > $bestSize) {
			$bestSize = $size;
			$bestFile = $path;
		}
	}

	return $bestFile;
}

function runFileUpgrade() {
	global $ROOT, $CMS_ZIP, $TEMP_DIR, $summary;

	if (!class_exists('ZipArchive')) {
		addErr('PHP ZipArchive extension missing.');
		return false;
	}

	if (!is_file($CMS_ZIP)) {
		addErr('latest-cms.zip not found.');
		return false;
	}

	deleteDirSafe($TEMP_DIR);

	if (!mkdir($TEMP_DIR, 0755, true)) {
		addErr('Cannot create temp folder.');
		return false;
	}

	$zip = new ZipArchive();
	$open = $zip->open($CMS_ZIP);

	if ($open !== true) {
		addErr('Cannot open latest-cms.zip. Code: ' . $open);
		deleteDirSafe($TEMP_DIR);
		return false;
	}

	if (!zipEntriesAreSafe($zip)) {
		$zip->close();
		addErr('Update ZIP contains an unsafe path or symbolic link.');
		deleteDirSafe($TEMP_DIR);
		return false;
	}

	if (!$zip->extractTo($TEMP_DIR)) {
		$zip->close();
		addErr('Cannot extract latest-cms.zip.');
		deleteDirSafe($TEMP_DIR);
		return false;
	}

	$zip->close();

	$sourceRoot = findRealCmsRoot($TEMP_DIR);

global $DATABASE_SQL;

/* Find SQL anywhere inside GitHub wrapper ZIP */
$sqlFile = findFirstFileRecursive($TEMP_DIR, 'sql');
if ($sqlFile !== '' && !is_file($DATABASE_SQL)) {
	copy($sqlFile, $DATABASE_SQL);
}

/* Find CMS ZIP anywhere inside GitHub wrapper ZIP */
$innerCmsZip = findLargestZipRecursive($TEMP_DIR);

if ($innerCmsZip !== '') {
	$innerDir = $TEMP_DIR . '/_inner_cms_zip';

	deleteDirSafe($innerDir);
	@mkdir($innerDir, 0755, true);

	$innerZip = new ZipArchive();
	$innerOpen = $innerZip->open($innerCmsZip);

	if ($innerOpen === true) {
		if (!zipEntriesAreSafe($innerZip)) {
			$innerZip->close();
			addErr('Inner CMS ZIP contains an unsafe path or symbolic link.');
			deleteDirSafe($innerDir);
			return false;
		}
		$innerZip->extractTo($innerDir);
		$innerZip->close();

		$sourceRoot = findRealCmsRoot($innerDir);
	} else {
		addErr('Cannot open inner CMS zip. Code: ' . $innerOpen);
	}
}

patchHtaccessBlocks($sourceRoot);

$files = collectFiles($sourceRoot);

	if (empty($files)) {
		addErr('No files found inside ZIP.');
		deleteDirSafe($TEMP_DIR);
		return false;
	}

	foreach ($files as $file) {
		$relative = $file['relative'];
		$source = $file['source'];
		$destination = $ROOT . '/' . $relative;

		if (isProtectedFile($relative)) {
			$summary['files_skipped']++;
			continue;
		}

		$destinationExists = is_file($destination);
		$same = false;

		if ($destinationExists && filesize($source) === filesize($destination)) {
			$same = (@md5_file($source) === @md5_file($destination));
		}

		if ($same) {
			$summary['files_same']++;
			continue;
		}

		$destinationDir = dirname($destination);

		if (!is_dir($destinationDir) && !mkdir($destinationDir, 0755, true)) {
			addErr('Cannot create folder: ' . $destinationDir);
			continue;
		}

		if (!copy($source, $destination)) {
			addErr('Copy failed: ' . $relative);
			continue;
		}

		@chmod($destination, 0644);

		if ($destinationExists) {
			$summary['files_replaced']++;
		} else {
			$summary['files_created']++;
		}
	}

	deleteDirSafe($TEMP_DIR);
	return true;
}

function getDb() {
	static $db = null;
	static $checked = false;

	if ($checked) return $db;
	$checked = true;

	$configFile = dirname(__DIR__, 3) . '/assets/includes/config.php';

	if (!is_file($configFile)) {
		addErr('Config file missing.');
		return null;
	}

	$dbGM = [];
	$config = [];
	require $configFile;

	if (isset($dbGM) && is_array($dbGM)) {
		$host = $dbGM['host'] ?? '';
		$name = $dbGM['name'] ?? '';
		$user = $dbGM['user'] ?? '';
		$pass = $dbGM['pass'] ?? '';

		if ($host && $name && $user) {
			$db = @new mysqli($host, $user, $pass, $name);
			if ($db instanceof mysqli && !$db->connect_error) {
				$db->set_charset('utf8mb4');
				return $db;
			}
		}
	}

	addErr('Database connection failed.');
	return null;
}

function tableExists($table) {
	$db = getDb();
	if (!$db) return false;

	$safe = $db->real_escape_string($table);
	$res = $db->query("SHOW TABLES LIKE '{$safe}'");

	return $res && $res->num_rows > 0;
}

function getColumns($table) {
	$db = getDb();
	$columns = [];

	if (!$db || !tableExists($table)) return $columns;

	$safeTable = str_replace('`', '', $table);
	$res = $db->query("SHOW COLUMNS FROM `{$safeTable}`");

	if ($res) {
		while ($row = $res->fetch_assoc()) {
			$columns[] = $row['Field'];
		}
	}

	return $columns;
}

function parseCreateTables($filePath) {
	$tables = [];

	if (!is_file($filePath)) return $tables;

	$handle = fopen($filePath, 'r');
	if (!$handle) return $tables;

	$collecting = false;
	$sql = '';
	$table = '';

	while (($line = fgets($handle)) !== false) {
		if (!$collecting) {
			if (preg_match('/^\s*CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?([a-zA-Z0-9_]+)`?/i', $line, $m)) {
				$collecting = true;
				$table = $m[1];
				$sql = $line;

				if (strpos($line, ';') !== false) {
					$tables[$table] = trim($sql);
					$collecting = false;
					$sql = '';
					$table = '';
				}
			}
			continue;
		}

		$sql .= $line;

		if (strpos($line, ';') !== false) {
			if ($table !== '') {
				$tables[$table] = trim($sql);
			}
			$collecting = false;
			$sql = '';
			$table = '';
		}
	}

	fclose($handle);
	return $tables;
}

function extractCreateColumns($createSql) {
	$columns = [];
	$start = strpos($createSql, '(');
	$end = strrpos($createSql, ')');

	if ($start === false || $end === false || $end <= $start) {
		return $columns;
	}

	$inside = substr($createSql, $start + 1, $end - $start - 1);
	$lines = preg_split('/\r\n|\r|\n/', $inside);

	foreach ($lines as $line) {
		$line = trim(rtrim(trim($line), ','));
		if ($line === '') continue;

		if (preg_match('/^`([^`]+)`\s+(.+)$/s', $line, $m)) {
			$columns[$m[1]] = $line;
		}
	}

	return $columns;
}

function cleanCreateSql($sql) {
	$sql = trim($sql);
	$sql = preg_replace('/^\s*DROP\s+TABLE\s+.*?;\s*/is', '', $sql);

	if (!preg_match('/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS/i', $sql)) {
		$sql = preg_replace('/CREATE\s+TABLE\s+/i', 'CREATE TABLE IF NOT EXISTS ', $sql, 1);
	}

	$lines = preg_split('/\r\n|\r|\n/', $sql);
	$new = [];

	foreach ($lines as $line) {
		$trim = trim($line);

		if (stripos($trim, 'CONSTRAINT ') === 0 && stripos($trim, 'FOREIGN KEY') !== false) continue;
		if (stripos($trim, 'FOREIGN KEY') === 0) continue;

		$new[] = $line;
	}

	$sql = implode("\n", $new);
	$sql = preg_replace('/,\s*\)\s*ENGINE=/is', "\n) ENGINE=", $sql);
	$sql = preg_replace('/,\s*\)\s*;/is', "\n);", $sql);

	return $sql;
}

function runDatabaseUpgrade() {
	global $DATABASE_SQL, $summary;

	if (!is_file($DATABASE_SQL)) {
		return true;
	}

	// The bundled SQL updater is MariaDB-specific. Neon production uses the
	// reviewed PostgreSQL schema and must never receive the legacy dump.
	addErr('Legacy MySQL database updater is disabled for Neon PostgreSQL. Use a reviewed PostgreSQL migration instead.');
	return false;

	$db = getDb();
	if (!$db) return false;

	$tables = parseCreateTables($DATABASE_SQL);

	if (empty($tables)) {
		addErr('No CREATE TABLE blocks found in database.sql.');
		return false;
	}

	$db->query('SET FOREIGN_KEY_CHECKS=0');

	foreach ($tables as $table => $createSql) {
		$safeTable = str_replace('`', '', $table);

		if (!tableExists($safeTable)) {
			$sql = cleanCreateSql($createSql);

			if ($db->query($sql)) {
				$summary['db_tables_created']++;
			} else {
				addErr('Create table failed ' . $safeTable . ': ' . $db->error);
			}

			continue;
		}

		$templateColumns = extractCreateColumns($createSql);
		$existingColumns = getColumns($safeTable);

		foreach ($templateColumns as $column => $definition) {
			if (in_array($column, $existingColumns, true)) continue;

			$sql = "ALTER TABLE `{$safeTable}` ADD COLUMN {$definition}";

			if ($db->query($sql)) {
				$summary['db_columns_added']++;
				$existingColumns[] = $column;
			} else {
				addErr('Add column failed ' . $safeTable . '.' . $column . ': ' . $db->error);
			}
		}
	}

	$db->query('SET FOREIGN_KEY_CHECKS=1');
	return true;
}

function cleanupFiles() {
	global $CMS_ZIP, $DATABASE_SQL, $NOTICE_FILE, $summary;

	foreach ([$CMS_ZIP, $DATABASE_SQL] as $file) {
		if (is_file($file) && @unlink($file)) {
			$summary['cleanup_deleted']++;
		}
	}
}

function updateVersionFile($version) {
	global $VERSION_FILE;

	$dir = dirname($VERSION_FILE);
	if (!is_dir($dir)) {
		@mkdir($dir, 0755, true);
	}

	file_put_contents($VERSION_FILE, json_encode([
		'version' => $version,
		'updated_at' => date('Y-m-d H:i:s')
	], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action !== 'run') {
	out([
		'ok' => false,
		'error' => 'Bad action. Use action=run'
	]);
}

$freshCheck = gps_license_check_cms_update();
if (empty($freshCheck['ok'])) {
	out(['ok' => false, 'step' => 'signed_manifest', 'error' => (string)($freshCheck['message'] ?? 'Could not obtain a signed update manifest.')]);
}
if (empty($freshCheck['update_available'])) {
	out(['ok' => false, 'step' => 'signed_manifest', 'error' => 'CMS PRO is already up to date.']);
}

if (!is_file($NOTICE_FILE)) {
	out([
		'ok' => false,
		'error' => 'No pending CMS update found. Refresh the admin page.'
	]);
}

$notice = json_decode(file_get_contents($NOTICE_FILE), true);

if (!is_array($notice)) {
	out([
		'ok' => false,
		'error' => 'Invalid cms-update-notice.json'
	]);
}

if (!is_array($notice['manifest'] ?? null)) {
	out(['ok' => false, 'step' => 'signed_manifest', 'error' => 'The update notice has no signed manifest.']);
}
try {
	$manifestClaims = gps_license_verify_update_manifest($notice['manifest'], gps_license_exact_hostname(), gps_license_installation_id());
} catch (Throwable $manifestError) {
	out(['ok' => false, 'step' => 'signed_manifest', 'error' => $manifestError->getMessage()]);
}

$version = trim((string)$manifestClaims['version']);
$zipUrl = trim((string)$manifestClaims['download_url']);
$expectedSha256 = strtolower(trim((string)$manifestClaims['sha256']));
$sqlUrl = trim((string)($manifestClaims['sql_url'] ?? ''));
$expectedSqlSha256 = strtolower(trim((string)($manifestClaims['sql_sha256'] ?? '')));

if ($version === '') {
	out(['ok' => false, 'error' => 'Missing update version']);
}

if ($zipUrl === '') {
	out(['ok' => false, 'error' => 'Missing ZIP URL']);
}

if (!preg_match('/^[a-f0-9]{64}$/', $expectedSha256)) {
	out(['ok' => false, 'error' => 'Missing or invalid update package SHA-256']);
}

$zipResult = downloadFile($zipUrl, $CMS_ZIP);
$summary['download_zip'] = !empty($zipResult['ok']);

if (!$zipResult['ok']) {
	out([
		'ok' => false,
		'step' => 'download_zip',
		'error' => $zipResult['error'],
		'summary' => $summary
	]);
}

$downloadedSha256 = hash_file('sha256', $CMS_ZIP);
if (!is_string($downloadedSha256) || !hash_equals($expectedSha256, strtolower($downloadedSha256))) {
	@unlink($CMS_ZIP);
	out([
		'ok' => false,
		'step' => 'verify_zip',
		'error' => 'Update package integrity check failed.',
		'summary' => $summary
	]);
}

if ($sqlUrl !== '') {
	if (!preg_match('/^[a-f0-9]{64}$/', $expectedSqlSha256)) {
		out(['ok' => false, 'step' => 'verify_sql', 'error' => 'Missing or invalid SQL SHA-256.']);
	}
	$sqlResult = downloadFile($sqlUrl, $DATABASE_SQL);
	$summary['download_sql'] = !empty($sqlResult['ok']);

	if (!$sqlResult['ok']) {
		out([
			'ok' => false,
			'step' => 'download_sql',
			'error' => $sqlResult['error'],
			'summary' => $summary
		]);
	}
	$downloadedSqlSha256 = hash_file('sha256', $DATABASE_SQL);
	if (!is_string($downloadedSqlSha256) || !hash_equals($expectedSqlSha256, strtolower($downloadedSqlSha256))) {
		@unlink($DATABASE_SQL);
		out(['ok' => false, 'step' => 'verify_sql', 'error' => 'Update SQL integrity check failed.', 'summary' => $summary]);
	}
}

runFileUpgrade();
runDatabaseUpgrade();

if (!empty($summary['errors'])) {
	out([
		'ok' => false,
		'step' => 'upgrade',
		'error' => 'Update finished with errors',
		'summary' => $summary
	]);
}

updateVersionFile($version);

file_put_contents($NOTICE_FILE, json_encode([
	'available' => false,
	'installed' => true,
	'version' => $version,
	'installed_at' => date('Y-m-d H:i:s'),
	'checked_at' => gmdate('c')
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

cleanupFiles();

out([
	'ok' => true,
	'message' => 'CMS update complete',
	'version' => $version,
	'summary' => $summary
]);
