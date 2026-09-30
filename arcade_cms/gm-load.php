<?php

/**
 * @package GameMonetize.com CMS - Modern Arcade Script
 */

 
if (!defined('ABSPATH'))
	define('ABSPATH', dirname(__FILE__) . '/');

error_reporting(0);

require_once ABSPATH . 'assets/includes/core.php';

if (!td_installing()
    && false === strpos((string)($_SERVER['REQUEST_URI'] ?? ''), 'setup-config')
    && false === strpos((string)($_SERVER['REQUEST_URI'] ?? ''), 'install')) {
    http_response_code(503);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!doctype html><html><head><meta charset="utf-8"><meta name="robots" content="noindex,nofollow"><title>Database configuration required</title></head><body><main style="max-width:720px;margin:60px auto;padding:24px;font-family:system-ui,sans-serif"><h1>Database configuration required</h1><p>Arcade CMS is deployed correctly, but the production PostgreSQL connection is not configured.</p><p>Add the Neon PostgreSQL connection as the Vercel <code>DATABASE_URL</code> environment variable, then redeploy.</p></main></body></html>';
    exit;
}

