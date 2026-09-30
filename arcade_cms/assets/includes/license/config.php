<?php
declare(strict_types=1);

if (!defined('GPS_LICENSE_API_BASE')) {
    define('GPS_LICENSE_API_BASE', 'https://api.gameportalscript.com/license');
}
if (!defined('GPS_LICENSE_STATE_FILE')) {
    define('GPS_LICENSE_STATE_FILE', __DIR__ . '/license-state.php');
}
if (!defined('GPS_LICENSE_PUBLIC_KEY_FILE')) {
    define('GPS_LICENSE_PUBLIC_KEY_FILE', __DIR__ . '/public.key');
}
if (!defined('GPS_LICENSE_HTTP_TIMEOUT')) {
    define('GPS_LICENSE_HTTP_TIMEOUT', 12);
}
