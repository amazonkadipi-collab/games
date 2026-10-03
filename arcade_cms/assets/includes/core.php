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

// The original encrypted engine defined this helper. Keep the runtime local:
// production is considered installed when a PostgreSQL connection URL exists.
if (!function_exists('td_installing')) {
    function td_installing() {
        return (bool)(getenv('DATABASE_URL') ?: getenv('POSTGRES_URL') ?: getenv('POSTGRES_URL_NON_POOLING') ?: getenv('NEON_DATABASE_URL') ?: getenv('SUPABASE_DB_URL'));
    }
}

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