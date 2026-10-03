<?php
/**
* @package GameMonetize CMS 
*/
set_time_limit(0);
session_start();
date_default_timezone_set( 'UTC' ); // GameMonetize.com calculates offsets from UTC
define('CORE_PILOT', true);

if ( !defined( 'ABSPATH' ) ) 
    define('ABSPATH', dirname(dirname(dirname(__FILE__))) . '/');

$time = ceil( time() );
$date = date("j/m/y g:iA", $time);
$access = true;

if ( td_installing() ) {
    if (file_exists(ABSPATH . 'assets/includes/config.php')) {
        require_once ABSPATH . 'assets/includes/config.php';
    } else {
        $dbGM = [];
    }
    require_once ABSPATH . 'assets/includes/tables.php';

    /**
    * Connecting to PostgreSQL / Neon through the CMS compatibility layer.
    */
    require_once ABSPATH . 'assets/includes/db.php';
    $GameMonetizeConnect = new ArcadeDatabase($dbGM);

    /**
    * Set up connection charset
    */
    //$GameMonetizeConnect->set_charset("utf8");

    /**
    * Check connection status
    */
    if ($GameMonetizeConnect->connect_errno) {
        error_log('[Arcade CMS] Database connection failed: ' . ($GameMonetizeConnect->error ?: 'unknown connection error'));
        http_response_code(503);
        exit('Database connection failed.');
    }

    // Recreate the small runtime bootstrap that the legacy encrypted engine used to provide.
    // Keep the original theme selected in gm_setting; do not replace or invent a theme.
    global $config, $lang, $themeData, $userData;
    $config = [];
    $settingQuery = $GameMonetizeConnect->query("SELECT * FROM " . SETTING . " ORDER BY id ASC LIMIT 1");
    if ($settingQuery && ($settingRow = $settingQuery->fetch_assoc())) {
        $config = $settingRow;
    } else {
        error_log('[Arcade CMS] Could not load gm_setting: ' . ($GameMonetizeConnect->error ?: 'no rows returned'));
    }
    $config['site_url'] = rtrim((string)($config['site_url'] ?? ''), '/');
    if ($config['site_url'] === '') {
        $config['site_url'] = 'https://' . preg_replace('/:\\d+$/', '', (string)($_SERVER['HTTP_HOST'] ?? ''));
    }
    $config['site_theme'] = trim((string)($config['site_theme'] ?? ''));
    $config['theme_path'] = $config['site_url'] . '/templates/' . $config['site_theme'];

    // Load the bundled default language instead of leaving @placeholders empty.
    $lang = [];
    $languageFile = ABSPATH . 'assets/language/english.php';
    if (is_file($languageFile)) {
        require_once $languageFile;
    }
    // The original GameMonetize renderer exposes every gm_setting field to templates
    // as CONFIG_* placeholders. Rebuild that local mapping without enabling the
    // legacy encrypted engine, so the original theme/CSS keeps its real values.
    $themeData = [];
    foreach ($config as $configKey => $configValue) {
        $themeData['config_' . strtolower((string)$configKey)] = $configValue;
    }
    $themeData['config_theme_path'] = $config['theme_path'];
    $themeData['config_site_url'] = $config['site_url'];
    $themeData['config_site_theme'] = $config['site_theme'];
    $userData = ['admin' => 0];

    require_once ABSPATH . 'assets/classes/load.php';
    // The public header uses GameMonetize\\UI directly; load it explicitly so the CMS does not depend on autoloader state.
    require_once ABSPATH . 'assets/classes/UI.class.php';
    require_once ABSPATH . 'gm-content/addons/load.php';
    // The bundled legacy encrypted engine performs remote/vendor bootstrap work that can block the Vercel container for the full request timeout. The CMS compatibility layer is self-contained, so keep production requests local and deterministic.\n    // require_once ABSPATH . 'assets/includes/engine.php';
}


/* 
* General functions 
*/
function gps_theme_family($theme = null)
{
    global $config;
    $theme = $theme === null ? (string)($config['site_theme'] ?? '') : (string)$theme;
    $families = [
        'crazygames-pro' => 'crazygames-like',
        'y8-pro' => 'y8-like',
        'kizi-pro' => 'kizi',
        'poki-pro' => 'poki-like',
    ];

    return $families[$theme] ?? $theme;
}
