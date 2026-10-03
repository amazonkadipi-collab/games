<?php
require_once __DIR__ . '/../../../assets/includes/core.php';
require_once __DIR__ . '/../../../assets/includes/license/bootstrap.php';

gps_require_pro_feature('sql_runner');

if (!isset($GLOBALS['access']) || $GLOBALS['access'] !== true) {
	http_response_code(403);
	exit('Access denied.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	exit('Invalid request.');
}

$sql = isset($_POST['sql_code']) ? trim($_POST['sql_code']) : '';

/*
	Fix escaped quotes coming from admin textarea / server config.
	Example: \'text\' becomes 'text'
*/
$sql = str_replace(array("\\'", '\\"'), array("'", '"'), $sql);
$sql = stripslashes($sql);
 
if ($sql === '') {
	exit('No SQL received.');
}

/*
	Safety block.
	Allow common manual admin SQL.
	Remove DROP/TRUNCATE by default because one mistake can destroy tables.
*/
$blocked = array(
	'/\bDROP\b/i',
	'/\bTRUNCATE\b/i',
	'/\bGRANT\b/i',
	'/\bREVOKE\b/i',
	'/\bCREATE\s+USER\b/i',
	'/\bALTER\s+USER\b/i',
	'/\bLOAD_FILE\b/i',
	'/\bINTO\s+OUTFILE\b/i',
	'/\bINTO\s+DUMPFILE\b/i'
);

foreach ($blocked as $pattern) {
	if (preg_match($pattern, $sql)) {
		http_response_code(400);
		exit('Blocked dangerous SQL command.');
	}
}

global $conn, $GameMonetizeConnect;

$db = null;

if (isset($conn) && is_object($conn) && method_exists($conn, 'query')) {
	$db = $conn;
} elseif (isset($GameMonetizeConnect) && is_object($GameMonetizeConnect) && method_exists($GameMonetizeConnect, 'query')) {
	$db = $GameMonetizeConnect;
}

if (!$db) {
	http_response_code(500);
	exit('Database connection not found. Check your core.php connection variable name.');
}

try {
	if (preg_match('/;\s*\S/', trim($sql))) {
		http_response_code(400);
		exit('Only one SQL statement is supported with PostgreSQL.');
	}
	$result = $db->query($sql);
} catch (Throwable $e) {
	http_response_code(500);
	exit('SQL error: ' . $e->getMessage());
}

$output = '';

	try {
	if (is_object($result) && method_exists($result, 'fetch_assoc')) {
		$rows = array();

		while ($row = $result->fetch_assoc()) {
			$rows[] = $row;
		}

		if (!empty($rows)) {
			$output .= print_r($rows, true) . "\n";
		} else {
			$output .= "Query returned 0 rows.\n";
		}

		$result->free();
	} else {
		$output .= "SQL executed.\n";
	}
	if (!empty($db->error)) {
		http_response_code(500);
		exit('SQL error: ' . $db->error);
	}
} catch (Throwable $e) {
	http_response_code(500);
	exit('SQL error: ' . $e->getMessage());
}

echo $output !== '' ? $output : 'SQL executed.';
