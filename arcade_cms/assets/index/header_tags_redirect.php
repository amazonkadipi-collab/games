<?php
// --- COD DE REDIRECT AUTOMAT 301 ---
global $GameMonetizeConnect, $dbGM;
if (!isset($GameMonetizeConnect)) {
    require_once __DIR__ . '/../includes/db.php';
    $GameMonetizeConnect = new ArcadeDatabase(is_array($dbGM ?? null) ? $dbGM : []);
}

// Preluare URL curent
$currentUrl = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

// Căutare redirect
$safeCurrentUrl = $GameMonetizeConnect->real_escape_string($currentUrl);
$redirectResult = $GameMonetizeConnect->query("SELECT new_url FROM gm_redirects WHERE old_url = '{$safeCurrentUrl}' LIMIT 1");
$redirectRow = $redirectResult && $redirectResult->num_rows > 0 ? $redirectResult->fetch_assoc() : null;

if (!empty($redirectRow['new_url'])) {
    header("HTTP/1.1 301 Moved Permanently");
    header('Location: ' . $redirectRow['new_url']);
    exit;
}

// --- Restul fișierului header_tags.php original ---
$descriptionPixelChar = 135;
$themeData['config_site_description'] = substr($themeData['config_site_description'], 0, $descriptionPixelChar);
$themeData['date_all_css'] = date("Y-m-d\TH-i", filemtime($_SERVER["DOCUMENT_ROOT"] . '/templates/' . $config['site_theme'] . '/css/' . 'all.css'));
$themeData['date_play_css'] = date("Y-m-d\TH-i", filemtime($_SERVER["DOCUMENT_ROOT"] . '/templates/' . $config['site_theme'] . '/css/' . 'play.css'));
$specialPage = ['best-games', 'new-games', 'featured-games', 'played-games'];
// ... restul codului tău rămâne neschimbat ...
