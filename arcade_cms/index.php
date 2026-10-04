<?php
// Production diagnostics: keep the public response unchanged, but log fatal
// PHP errors instead of silently returning an empty 200 response.
register_shutdown_function(function () {
    $error = error_get_last();
    if (is_array($error) && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true)) {
        error_log('[Arcade CMS] Fatal request error: ' . ($error['message'] ?? 'unknown') . ' in ' . ($error['file'] ?? 'unknown') . ':' . ($error['line'] ?? 0));
    }
});
/**
* @package GameMonetize.com CMS - Modern Arcade Script
*
* @author GameMonetize.com
*
*/

$cmsDemoHosts = [
    'demo.arcadegames.com.es',
    'crazy.gameportalscript.com',
    'y8.gameportalscript.com',
    'kizi.gameportalscript.com',
    'poki.gameportalscript.com',
];
$cmsRequestHost = strtolower((string)($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? ''));
$cmsRequestHost = preg_replace('/:\d+$/', '', $cmsRequestHost);
$cmsIsDemoHost = in_array($cmsRequestHost, $cmsDemoHosts, true);

if ($cmsIsDemoHost) {
    header('X-Robots-Tag: noindex, nofollow, noarchive', true);
}

$gpsPageSpeedEarlyCache = __DIR__ . '/assets/pro/pagespeed/early-cache.php';
if (is_file($gpsPageSpeedEarlyCache)) {
    require $gpsPageSpeedEarlyCache;
}

// Database bootstrap is handled by gm-load.php/core.php.
// Production uses the ArcadeDatabase adapter and Neon DATABASE_URL.
// Keep this entrypoint free of legacy MySQL connection code.

if (!isset($_GET['p'])) {
    $cmsRoutePath = trim((string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
    $cmsStaticRoutes = [
        '' => 'home', 'home' => 'home', 'new-games' => 'new-games', 'best-games' => 'best-games',
        'random' => 'random', 'about' => 'about', 'tags' => 'tags', 'privacy' => 'privacy',
        'terms' => 'terms', 'contact' => 'contact', 'featured-games' => 'featured-games',
        'played-games' => 'played-games', 'favorite-games' => 'favorite-games', 'categories' => 'categories',
        'blogs' => 'blogs', 'login' => 'login', 'setting' => 'setting', 'error' => 'error', 'admin' => 'admin',
    ];
    if (isset($cmsStaticRoutes[$cmsRoutePath])) {
        $_GET['p'] = $cmsStaticRoutes[$cmsRoutePath];
        if ($cmsRoutePath === 'setting') $_GET['section'] = 'info';
        elseif ($cmsRoutePath === 'admin') $_GET['section'] = 'global';
    } elseif (preg_match('~^admin/([A-Za-z0-9_-]+)(?:/([A-Za-z0-9_-]+))?(?:/([^/]+))?$~', $cmsRoutePath, $m)) {
        $_GET['p'] = 'admin'; $_GET['section'] = rawurldecode($m[1]);
        if ($_GET['section'] === 'gameDescriptionDownload') $_GET['section'] = 'gamedescription';
        if (!empty($m[2])) {
            if ($_GET['section'] === 'games' && ctype_digit($m[2]) && empty($m[3])) $_GET['page'] = (int)$m[2];
            else $_GET['action'] = rawurldecode($m[2]);
        }
        if (!empty($m[3])) $_GET[(['games'=>'gid','users'=>'uid'][$_GET['section']] ?? 'cid')] = rawurldecode($m[3]);
    } elseif (preg_match('~^game/(.+)$~', $cmsRoutePath, $m)) { $_GET['p']='play'; $_GET['id']=rawurldecode($m[1]);
    } elseif (preg_match('~^category/(.+)$~', $cmsRoutePath, $m)) { $_GET['p']='categories'; $_GET['category']=rawurldecode($m[1]);
    } elseif (preg_match('~^tag/(.+)$~', $cmsRoutePath, $m)) { $_GET['p']='tagspage'; $_GET['tag']=rawurldecode($m[1]);
    } elseif (preg_match('~^blog/(.+)$~', $cmsRoutePath, $m)) { $_GET['p']='blogs'; $_GET['blog']=rawurldecode($m[1]);
    } elseif (preg_match('~^search-handler/(.*)$~', $cmsRoutePath, $m)) { $_GET['p']='search-handler'; $_GET['q']=rawurldecode($m[1]);
    } elseif (preg_match('~^search/(.*)$~', $cmsRoutePath, $m)) { $_GET['p']='search'; $_GET['q']=rawurldecode($m[1]);
    } elseif (preg_match('~^([A-Za-z0-9.-]+)-games$~', $cmsRoutePath, $m)) { $_GET['p']='home'; $_GET['cat']=rawurldecode($m[1]);
    } elseif (preg_match('~^profile/(.+)$~', $cmsRoutePath, $m)) { $_GET['p']='profile'; $_GET['id']=rawurldecode($m[1]);
    } elseif (preg_match('~^logout/(.+)$~', $cmsRoutePath, $m)) { $_GET['p']='logout'; $_GET['token']=rawurldecode($m[1]); }
}

if (!isset($_GET['p'])) $_GET['p'] = 'home';

require_once dirname(__FILE__) . '/gm-load.php';

$cmsRequestPath = trim((string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$siteUrl = 'https://pokicrazygames.vercel.app';

// Fast, standards-compliant sitemap index. Game URLs are split into 5,000-URL chunks
// so Google never has to wait for one giant DB query or XML response.
if ($cmsRequestPath === 'sitemap.xml') {
    header('Content-Type: application/xml; charset=UTF-8');
    header('Cache-Control: public, max-age=3600, s-maxage=3600');
    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    $xml .= '<sitemap><loc>' . htmlspecialchars($siteUrl . '/sitemaps/static.xml', ENT_XML1) . '</loc></sitemap>';
    $gameCount = 0;
    if (isset($GameMonetizeConnect)) {
        $countResult = $GameMonetizeConnect->query("SELECT COUNT(*) AS total FROM " . GAMES . " WHERE game_id IS NOT NULL");
        if ($countResult) { $row = $countResult->fetch_assoc(); $gameCount = (int)($row['total'] ?? 0); }
    }
    $pages = max(1, (int)ceil($gameCount / 5000));
    for ($i=1; $i <= $pages; $i++) $xml .= '<sitemap><loc>' . htmlspecialchars($siteUrl . '/sitemaps/games-' . $i . '.xml', ENT_XML1) . '</loc></sitemap>';
    $xml .= '</sitemapindex>';
    echo $xml;
    exit;
}

if ($cmsRequestPath === 'sitemaps/static.xml') {
    header('Content-Type: application/xml; charset=UTF-8');
    header('Cache-Control: public, max-age=3600, s-maxage=3600');
    $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    $urls = ['/', '/games', '/categories', '/new-games', '/best-games', '/random', '/about', '/privacy', '/terms'];
    $cats = ['action','adventure','arcade','racing','sports','puzzle','shooting','strategy','multiplayer','2-player','io-games','skill','horror','zombie'];
    foreach ($cats as $slug) $urls[] = '/category/' . $slug;
    foreach ($urls as $url) $xml .= '<url><loc>' . htmlspecialchars($siteUrl . $url, ENT_XML1) . '</loc></url>';
    echo $xml . '</urlset>'; exit;
}

if (preg_match('~^sitemaps/games-(\d+)\.xml$~', $cmsRequestPath, $sm)) {
    header('Content-Type: application/xml; charset=UTF-8');
    header('Cache-Control: public, max-age=3600, s-maxage=3600');
    $page = max(1, (int)$sm[1]);
    $offset = ($page - 1) * 5000;
    $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    if (isset($GameMonetizeConnect)) {
        $query = $GameMonetizeConnect->query("SELECT game_id, name, date_added FROM " . GAMES . " WHERE game_id IS NOT NULL ORDER BY game_id ASC LIMIT 5000 OFFSET {$offset}");
        if ($query) while ($game = $query->fetch_assoc()) {
            $loc = $siteUrl . '/game/' . rawurlencode(slugify((string)($game['name'] ?? '')));
            $xml .= '<url><loc>' . htmlspecialchars($loc, ENT_XML1) . '</loc>';
            if (!empty($game['date_added'])) $xml .= '<lastmod>' . htmlspecialchars(date('c', is_numeric($game['date_added']) ? (int)$game['date_added'] : strtotime((string)$game['date_added'])), ENT_XML1) . '</lastmod>';
            $xml .= '</url>';
        }
    }
    echo $xml . '</urlset>'; exit;
}

if ($cmsRequestPath === 'robots.txt') {
    header('Content-Type: text/plain; charset=UTF-8');
    header('Cache-Control: public, max-age=3600, s-maxage=3600');
    echo "User-agent: *\nDisallow: /admin\nDisallow: /assets/includes/\nSitemap: {$siteUrl}/sitemap.xml\n";
    exit;
}

/* Neon-backed admin login bridge. */
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'
    && (($_GET['p'] ?? '') === 'login' || trim((string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/') === 'login')
    && isset($_POST['login_id'], $_POST['login_key'])) {
    $loginId = trim((string)$_POST['login_id']);
    $loginKey = (string)$_POST['login_key'];
    if ($loginId !== '' && $loginKey !== '' && isset($GameMonetizeConnect)) {
        $loginIdSafe = $GameMonetizeConnect->real_escape_string($loginId);
        $account = $GameMonetizeConnect->query("SELECT id, username, email, password, admin, active FROM " . ACCOUNTS . " WHERE (username='{$loginIdSafe}' OR email='{$loginIdSafe}') AND active='1' LIMIT 1");
        if ($account && $account->num_rows === 1) {
            $candidate = $account->fetch_assoc(); $storedPassword = (string)($candidate['password'] ?? '');
            $valid = $storedPassword !== '' && hash_equals($storedPassword, $loginKey);
            if (!$valid && isset($encryption)) $valid = $storedPassword !== '' && hash_equals($storedPassword, sha1(str_rot13($loginKey . $encryption)));
            if ($valid && !empty($candidate['admin'])) {
                $cookieOptions=['expires'=>time()+2592000,'path'=>'/','secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off','httponly'=>true,'samesite'=>'Lax'];
                setcookie('gm_ac_u',(string)$candidate['id'],$cookieOptions); setcookie('gm_ac_p',$storedPassword,$cookieOptions);
                header('Location: /admin',true,302); exit;
            }
        }
    }
    header('Location: /login?error=invalid',true,302); exit;
}

$userData=[];
if (isset($_COOKIE['gm_ac_u'],$_COOKIE['gm_ac_p']) && isset($GameMonetizeConnect)) {
    $cookieUserId=(int)$_COOKIE['gm_ac_u'];
    if ($cookieUserId>0) {
        $userDataQuery=$GameMonetizeConnect->query("SELECT * FROM " . ACCOUNTS . " WHERE id={$cookieUserId} AND active='1' LIMIT 1");
        if ($userDataQuery && $userDataQuery->num_rows===1) $userData=$userDataQuery->fetch_assoc();
    }
}


// Generic loader for the separately delivered PageSpeed PRO package. The
// Free CMS contains no optimization implementation and continues normally
// when the signed feature package is not installed for this host.
$gpsPageSpeedBootstrap = ABSPATH . 'assets/pro/pagespeed/bootstrap.php';
if (is_file($gpsPageSpeedBootstrap)) {
    require_once ABSPATH . 'assets/includes/license/bootstrap.php';
    if (gps_license_installed_feature_allowed('pagespeed_optimization')) {
        require_once $gpsPageSpeedBootstrap;
    }
}

// Load the separately delivered Crazy Visual Presets implementation only
// after its signed, domain-bound PRO entitlement has been verified.
$gpsVisualPresetsBootstrap = ABSPATH . 'assets/pro/crazy_visual_presets/bootstrap.php';
if (is_file($gpsVisualPresetsBootstrap)) {
    require_once ABSPATH . 'assets/includes/license/bootstrap.php';
    // Other Fixes also owns the protected browser-level Classic/PRO theme
    // chooser. The palette controls remain harmless when no Crazy PRO theme
    // is selected, while Free installations still never load this runtime.
    if (gps_license_installed_feature_allowed('crazy_visual_presets')
        || gps_license_installed_feature_allowed('other_fixes')) {
        require_once $gpsVisualPresetsBootstrap;
    }
}

// Load the separately delivered visitor translation feature only when its
// signed package belongs to this exact licensed domain and installation.
$gpsTranslateBootstrap = ABSPATH . 'assets/pro/translate/bootstrap.php';
if (is_file($gpsTranslateBootstrap)) {
    require_once ABSPATH . 'assets/includes/license/bootstrap.php';
    if (gps_license_installed_feature_allowed('translate')) {
        require_once $gpsTranslateBootstrap;
    }
}

// Small public-site corrections remain isolated in their separately signed
// and domain-bound PRO package. Free installations keep their legacy output.
$gpsOtherFixesBootstrap = ABSPATH . 'assets/pro/other_fixes/bootstrap.php';
if (is_file($gpsOtherFixesBootstrap)) {
    require_once ABSPATH . 'assets/includes/license/bootstrap.php';
    if (gps_license_installed_feature_allowed('other_fixes')) {
        require_once $gpsOtherFixesBootstrap;
    }
}

// Menu layouts are delivered as their own protected PRO feature. During the
// transition from the earlier combined package, an existing PRO installation
// may use the cached entitlement until the dedicated package marker is added.
$gpsMenuDesignBootstrap = ABSPATH . 'assets/pro/menu_design/bootstrap.php';
if (is_file($gpsMenuDesignBootstrap)) {
    require_once ABSPATH . 'assets/includes/license/bootstrap.php';
    $gpsMenuDesignAllowed = gps_license_installed_feature_allowed('menu_design');
    if (!$gpsMenuDesignAllowed && gps_license_installed_feature_allowed('other_fixes')) {
        $gpsMenuDesignClaims = function_exists('gps_license_cached_bound_claims')
            ? gps_license_cached_bound_claims()
            : [];
        $gpsMenuDesignStatus = strtolower(trim((string)($gpsMenuDesignClaims['status'] ?? '')));
        $gpsMenuDesignPlan = strtolower(trim((string)($gpsMenuDesignClaims['plan'] ?? 'free')));
        $gpsMenuDesignExpiryRaw = trim((string)($gpsMenuDesignClaims['license_expires_at'] ?? ''));
        $gpsMenuDesignExpiry = $gpsMenuDesignExpiryRaw === ''
            ? false
            : strtotime($gpsMenuDesignExpiryRaw . ' UTC');
        $gpsMenuDesignAllowed = $gpsMenuDesignClaims !== []
            && $gpsMenuDesignPlan !== 'free'
            && ($gpsMenuDesignStatus === '' || $gpsMenuDesignStatus === 'active')
            && ($gpsMenuDesignExpiry === false || $gpsMenuDesignExpiry >= time());
    }
    if ($gpsMenuDesignAllowed) {
        require_once $gpsMenuDesignBootstrap;
    }
}

// The redesigned CrazyGames dashboard and its player engagement system are
// installed as one dedicated, domain-bound PRO package. Free installations
// never load its UI or its database-backed runtime.
$gpsCrazyProfessionalBootstrap = ABSPATH . 'assets/pro/crazygames_professional/bootstrap.php';
if (is_file($gpsCrazyProfessionalBootstrap)) {
    require_once ABSPATH . 'assets/includes/license/bootstrap.php';
    if (gps_license_installed_feature_allowed('crazygames_professional')) {
        require_once $gpsCrazyProfessionalBootstrap;
    }
}

// The Poki PRO and Kizi PRO themes are delivered together as a protected
// presentation package. Classic templates never load this implementation.
$gpsProfessionalShowcaseBootstrap = ABSPATH . 'assets/pro/professional_showcase/bootstrap.php';
if (is_file($gpsProfessionalShowcaseBootstrap)) {
    require_once ABSPATH . 'assets/includes/license/bootstrap.php';
    $gpsProfessionalShowcaseAllowed = gps_license_installed_feature_allowed('professional_showcase')
        || gps_license_installed_feature_allowed('other_fixes');
    if ($gpsProfessionalShowcaseAllowed) {
        require_once $gpsProfessionalShowcaseBootstrap;
    }
}

require_once ABSPATH . 'assets/index/header_tags.php';
require_once ABSPATH . 'assets/index/header.php';
require_once ABSPATH . 'assets/index/footer.php';
require_once ABSPATH . 'assets/index/page.php';
$gpsRenderedIndex = \GameMonetize\UI::view('index');
if (function_exists('gmLocalizationApplyCorePage')) {
    gmLocalizationApplyCorePage($themeData);
    $gpsRenderedIndex = \GameMonetize\UI::view('index');
}
if (function_exists('gps_other_fixes_localization_render')) {
    $gpsRenderedIndex = gps_other_fixes_localization_render($gpsRenderedIndex);
}
echo $gpsRenderedIndex;

$GameMonetizeConnect->close();
