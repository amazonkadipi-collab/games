<?php

require_once ABSPATH . 'vendor/autoload.php';
require_once ABSPATH . 'assets/includes/license/bootstrap.php';
require_once ABSPATH . 'assets/includes/license/feature-catalog.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

$date =  date('Ymdms');
$date = strtotime($date);
$themeData['cms'] = "<script src='https://api.gamemonetize.com/cms.js?" . $date . "'></script>";

global $totalRowLength;

// Keep /admin protected, but make the unauthenticated entry point useful.
// Previously the admin controller rendered the generic homepage/error shell
// when the session was missing, which made /admin look like a broken route.
if (!is_logged() || empty($userData['admin'])) {
    $redirectTarget = '/admin';
    header('Location: /login?redirect=' . rawurlencode($redirectTarget), true, 302);
    exit;
}

if (is_logged() && $userData['admin']) {
	// Keep the admin dashboard local and deterministic. External GameMonetize
	// registry/blacklist requests are intentionally not part of authentication
	// or rendering because they can block or redirect the CMS on Vercel.

	$date =  date('Ymdms');
	$date = strtotime($date);

	$themeData['news'] = '<div class="stats-box" style="width:100%;padding:10px;">
				<iframe style="min-height: 198px;" src="https://api.gamemonetize.com/cms.html?' . $date . '" width="100%" height="100%" scrolling="none" frameborder="0"></iframe>
				</div>';

	$navigation_menu_data = (isset($_GET['section'])) ? $_GET['section'] : 'global';
	$themeData['nav_menu_global'] = listMenu($navigation_menu_data, 'global');
	$themeData['nav_menu_addgame'] = listMenu($navigation_menu_data, 'addgame');
	$themeData['nav_menu_setting'] = listMenu($navigation_menu_data, 'setting');
	$themeData['nav_menu_games'] = listMenu($navigation_menu_data, 'games');
	$themeData['nav_menu_games_images_and_videos'] = listMenu($navigation_menu_data, 'games-images-and-videos');
	$themeData['nav_menu_categories'] = listMenu($navigation_menu_data, 'categories');
	$themeData['nav_menu_users'] = listMenu($navigation_menu_data, 'users');
	$themeData['nav_menu_ads'] = listMenu($navigation_menu_data, 'ads');
	$themeData['nav_menu_tags'] = listMenu($navigation_menu_data, 'tags');
	$themeData['nav_menu_footer_description'] = listMenu($navigation_menu_data, 'footerdescription');
	$themeData['nav_menu_blogs'] = listMenu($navigation_menu_data, 'blogs');
	$themeData['nav_menu_chatgpt'] = listMenu($navigation_menu_data, 'chatgpt');
	$themeData['pro_language_menu'] = function_exists('gps_other_fixes_localization_admin_menu')
		? gps_other_fixes_localization_admin_menu(listMenu($navigation_menu_data, 'language'))
		: '';
	$proState = gps_license_state_read();
	$proClaims = gps_license_cached_bound_claims();
	$proPlan = strtolower((string)($proClaims['plan'] ?? ''));
	$proStatus = strtolower((string)($proClaims['status'] ?? $proState['license_status'] ?? ''));
	$proExpiresRaw = trim((string)($proClaims['license_expires_at'] ?? $proState['license_expires_at'] ?? ''));
	$proExpiresAt = $proExpiresRaw !== '' ? strtotime($proExpiresRaw . ' UTC') : false;
	$proExpired = !empty($proState['license_key']) && (
		$proStatus === 'expired'
		|| ($proExpiresAt !== false && $proExpiresAt < time())
	);
	$proActive = $proClaims !== []
		&& !$proExpired
		&& ($proStatus === '' || $proStatus === 'active')
		&& $proPlan !== 'free';
	$themeData['gps_news_order'] = $proActive ? '-1' : '2';
	$themeData['gps_news_display'] = $proActive ? 'none' : 'block';
	// Use the direct front-controller route so PRO works even on hosts whose
	// legacy .htaccess does not yet recognize the newer /admin/pro path.
	$proLink = '/index.php?p=admin&section=pro';
	if ($proActive) {
		$proMenuLabel = 'PRO Active';
		$proMenuColor = '#ffd34d';
		$proMenuBackground = 'linear-gradient(90deg,#3b2f0c,#211d0d)';
		$proMenuBorder = '#b88914';
		$proPanelTitle = 'GamePortalScript PRO is active';
		$proPanelText = 'Your premium CMS tools and the latest licensed updates are unlocked.'
			. ($proExpiresAt !== false ? ' Updates included until ' . gmdate('M j, Y', $proExpiresAt) . '.' : '');
		$proPanelBorder = '#ffd34d';
		$proPanelButton = 'View PRO Features';
	} elseif ($proExpired) {
		$proMenuLabel = 'PRO Expired';
		$proMenuColor = '#ff9f43';
		$proMenuBackground = 'linear-gradient(90deg,rgba(255,107,61,.20),rgba(255,159,67,.06))';
		$proMenuBorder = '#d86832';
		$proPanelTitle = 'Your GamePortalScript PRO license has expired';
		$proPanelText = 'Your website remains online. Renew PRO to continue receiving the latest SEO tools, performance optimizations, design improvements, new CMS features and priority fixes.';
		$proPanelBorder = '#ff7b54';
		$proPanelButton = 'Renew PRO';
	} else {
		$proMenuLabel = 'Upgrade to PRO';
		$proMenuColor = '#ffd34d';
		$proMenuBackground = 'linear-gradient(90deg,rgba(118,75,255,.20),rgba(29,185,255,.10))';
		$proMenuBorder = '#7c4dff';
		$proPanelTitle = 'Grow your game portal with GamePortalScript PRO';
		$proPanelText = 'Unlock 12 months of CMS updates and improvements created to help your website rank better, load faster and look more professional.';
		$proPanelBorder = '#7c4dff';
		$proPanelButton = 'Explore PRO';
	}
	$proMenuActive = in_array($navigation_menu_data, ['pro', 'upgrade-pro'], true) ? ' active' : '';
	$themeData['gps_pro_menu_item'] = '<li id="gps-pro-menu-item" class="_4lf' . $proMenuActive . '" style="position:relative;background:' . $proMenuBackground . ';border-left:4px solid ' . $proMenuBorder . ';">'
		. '<a href="' . $proLink . '" class="spf-link _p"></a>'
		. '<i class="fa fa-crown nav-icon icon-middle" style="color:' . $proMenuColor . ';"></i>'
		. '<span id="gps-pro-menu-label" style="color:' . $proMenuColor . ';font-weight:700;">' . $proMenuLabel . '</span></li>';
	$proLicenseControl = '';
	if ($proActive) {
		$proLicenseControl = '<div id="gps-license-activation-box" style="margin-top:12px;padding-top:11px;border-top:1px solid rgba(255,255,255,.14);">'
			. '<div id="gps-license-status" style="margin:7px 0;color:#dbeafe;">Reading saved license statusâ€¦</div>'
			. '<button id="gps-license-refresh" type="button" class="btn-p btn-p1"><i class="fa fa-refresh"></i> Check license now</button>'
			. '<div id="gps-license-debug" style="display:none;margin-top:7px;color:#ff918a;white-space:pre-wrap;word-break:break-word;"></div></div>';
	} else {
		$proLicenseControl = '<div id="gps-license-activation-box" style="margin-top:12px;padding-top:11px;border-top:1px solid rgba(255,255,255,.14);">'
			. '<strong style="display:block;color:#fff;margin-bottom:4px;">Activate CMS PRO</strong>'
			. '<span style="display:block;color:#bfc9dc;margin-bottom:8px;">Enter your GamePortalScript license key.</span>'
			. '<div id="gps-license-status" style="margin:7px 0;color:#dbeafe;">Reading saved license status…</div>'
			. '<div id="gps-license-control" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">'
			. '<input id="gps-license-key" type="text" placeholder="Enter GamePortalScript license key" autocomplete="off" style="position:static!important;flex:1;min-width:210px;height:38px;padding:0 11px!important;background:#3e4454!important;border:1px solid rgba(255,255,255,.18);border-radius:5px;color:#fff;">'
			. '<button id="gps-license-activate" type="button" class="btn-p btn-p1" style="height:38px;">Activate PRO</button></div>'
			. '<div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px;">'
			. '<a href="' . $proLink . '" class="spf-link" style="display:inline-block;padding:8px 12px;border:1px solid #ffd34d;border-radius:5px;color:#ffd34d;text-decoration:none;">View PRO details</a>'
			. '<a href="https://gameportalscript.com/contact.php" target="_blank" rel="noopener noreferrer" style="display:inline-block;padding:8px 12px;background:#ffd34d;border:1px solid #ffd34d;border-radius:5px;color:#211d0d;font-weight:700;text-decoration:none;">Contact GamePortalScript</a></div>'
			. '<div id="gps-license-debug" style="display:none;margin-top:7px;color:#ff918a;white-space:pre-wrap;word-break:break-word;"></div></div>';
	}
	$dashboardInstalledVersion = htmlspecialchars(gps_license_current_cms_version(), ENT_QUOTES, 'UTF-8');
	if ($proActive) {
		$themeData['gps_pro_dashboard_panel'] = '<div class="general-box" style="box-sizing:border-box;padding:11px 16px;background:linear-gradient(110deg,#2e1f57,#24364f);border-left:4px solid ' . $proPanelBorder . ';color:#fff;">'
			. '<strong id="gps-pro-panel-title" style="display:block;color:#ffd34d;font-size:17px;margin-bottom:3px;"><i class="fa fa-crown"></i> ' . $proPanelTitle . '</strong>'
			. '<span id="gps-pro-panel-text" style="display:block;color:#dbeafe;line-height:1.32;">' . $proPanelText . '</span>'
			. '<div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-top:8px;">'
			. '<span style="color:#ffd34d;">Installed CMS: v' . $dashboardInstalledVersion . '</span>'
			. '<a href="' . $proLink . '" class="spf-link" style="display:inline-flex;align-items:center;gap:6px;padding:7px 12px;background:linear-gradient(90deg,#b88914,#ffd34d);border:1px solid #ffd34d;border-radius:6px;color:#211d0d;font-weight:800;text-decoration:none;"><i class="fa fa-crown"></i> View PRO features</a></div>'
			. '</div>';
	} else {
		$themeData['gps_pro_dashboard_panel'] = '<div class="general-box" style="height:100%;box-sizing:border-box;padding:18px 20px;background:linear-gradient(135deg,#30205b 0%,#263c58 100%);border-left:4px solid ' . $proPanelBorder . ';border-radius:0 8px 8px 0;color:#fff;box-shadow:0 10px 28px rgba(7,12,28,.22);">'
			. '<div>'
			. '<strong id="gps-pro-panel-title" style="display:block;color:#ffd34d;font-size:18px;margin-bottom:6px;"><i class="fa fa-crown"></i> ' . $proPanelTitle . '</strong>'
			. '<span id="gps-pro-panel-text" style="color:#dbeafe;line-height:1.45;">' . $proPanelText . '</span>'
			. '<div style="margin-top:7px;color:#ffd34d;">Installed CMS: v' . $dashboardInstalledVersion . '</div></div>'
			. $proLicenseControl
			. '</div>';
	}
	$cacheDir = ABSPATH . 'assets/cache';
	$cacheFile = $cacheDir . '/gameportalscript-cms-news.json';
	$news = [];
	$cached = is_file($cacheFile) ? json_decode((string)@file_get_contents($cacheFile), true) : null;
	if (is_array($cached) && isset($cached['items'])) { $news = $cached['items']; }
	if (!is_array($cached) || empty($news) || ((int)($cached['fetched_at'] ?? 0) < time() - 604800)) {
		$context = stream_context_create(['http'=>['timeout'=>3], 'https'=>['timeout'=>3]]);
		$remote = @file_get_contents('https://api.gameportalscript.com/cms-news.php', false, $context);
		if ((!is_string($remote) || $remote === '') && function_exists('curl_init')) {
			$curl = curl_init('https://api.gameportalscript.com/cms-news.php');
			curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 5, CURLOPT_SSL_VERIFYPEER => true]);
			$remote = curl_exec($curl);
			curl_close($curl);
		}
		$remote = is_string($remote) ? json_decode($remote, true) : null;
		if (is_array($remote) && !empty($remote['ok']) && isset($remote['items'])) { $news = $remote['items']; if (!is_dir($cacheDir)) { @mkdir($cacheDir, 0755, true); } @file_put_contents($cacheFile, json_encode(['fetched_at'=>time(),'items'=>$news], JSON_UNESCAPED_SLASHES)); }
	}
	$themeData['cms_update_notice_box'] = '';
	if (!empty($news[0])) { $item=$news[0]; $themeData['cms_update_notice_box'] = '<div class="general-box" style="margin-bottom:15px;padding:16px 20px;background:#24364f;border-left:4px solid #00bcd4;color:#fff;"><strong style="color:#00d4ff;">CMS News'.(!empty($item['version'])?' · '.htmlspecialchars($item['version']):'').'</strong><div style="margin-top:7px;">'.htmlspecialchars((string)$item['title']).' — '.htmlspecialchars((string)$item['body']).'</div>'.(!empty($item['cta_url'])?'<a href="'.htmlspecialchars((string)$item['cta_url']).'" target="_blank" rel="noopener noreferrer" style="display:inline-block;margin-top:10px;color:#ffd34d;">Read more</a>':'').'</div>'; }
	
	
	$themeData['nav_menu_links'] = listMenu($navigation_menu_data, 'links');
	$themeData['nav_menu_sliders'] = listMenu($navigation_menu_data, 'sliders');
	$themeData['nav_menu_sidebar'] = listMenu($navigation_menu_data, 'sidebar');
	$themeData['admin_navigation_menu'] = \GameMonetize\UI::view('admin/nav-menu');
	$themeData['sitemap_xml_link'] = siteUrl() . '/sitemap.xml';
	$themeData['rss_feed_link'] = siteUrl() . '/feed';
	if (!isset($_GET['section']) || $_GET['section'] == "global") {

		$gameplay = $GameMonetizeConnect->query("SELECT * FROM " . SETTING . " WHERE id='1'");
		$gameplay = $gameplay->fetch_array()[14];
		$themeData['admin_stats_games'] = getStats('games');
		$themeData['admin_stats_users'] = getStats('users');
		$themeData['admin_stats_categories'] = $gameplay;

		$getLastUser_registered = lastUser('registered', 4);
		$lsturgtd_r = '';
		foreach ($getLastUser_registered as $last_user) {
			$getInfo = getInfo($last_user['id']);
			$themeData['stats_user_avatar'] = getAvatar($last_user['avatar_id'], $getInfo['gender'], 'thumb');
			$themeData['stats_user_name'] = $last_user['name'];
			$themeData['stats_user_username'] = $last_user['username'];

			$lsturgtd_r .= \GameMonetize\UI::view('admin/stats-list-user');
		}
		$themeData['stats_last_user_registered_list'] = $lsturgtd_r;
		# >>

		$getLastUser_registered = lastUser('logged', 4);
		$lstulggd_r = '';
		foreach ($getLastUser_registered as $last_user) {
			$getInfo = getInfo($last_user['id']);
			$themeData['stats_user_avatar'] = getAvatar($last_user['avatar_id'], $getInfo['gender'], 'thumb');
			$themeData['stats_user_name'] = $last_user['name'];
			$themeData['stats_user_username'] = $last_user['username'];

			$lstulggd_r .= \GameMonetize\UI::view('admin/stats-list-user');
		}
		$themeData['stats_last_user_logged_list'] = $lstulggd_r;
# >>

$activeLinks = 0;
$activeLinksQuery = $GameMonetizeConnect->query("SELECT COUNT(*) AS total FROM " . LINKS . " WHERE is_active = 1");
if ($activeLinksQuery && $activeLinksRow = $activeLinksQuery->fetch_assoc()) {
	$activeLinks = (int)$activeLinksRow['total'];
}

$lastPublishText = 'No games yet';
$lastGameQuery = $GameMonetizeConnect->query("SELECT date_added FROM " . GAMES . " ORDER BY date_added DESC LIMIT 1");
if ($lastGameQuery && $lastGameQuery->num_rows > 0) {
	$lastGameRow = $lastGameQuery->fetch_assoc();
	$secondsAgo = time() - (int)$lastGameRow['date_added'];

	if ($secondsAgo < 60) {
		$lastPublishText = 'Just now';
	} elseif ($secondsAgo < 3600) {
		$lastPublishText = floor($secondsAgo / 60) . ' minutes ago';
	} elseif ($secondsAgo < 86400) {
		$lastPublishText = floor($secondsAgo / 3600) . ' hours ago';
	} else {
		$lastPublishText = floor($secondsAgo / 86400) . ' days ago';
	}
}

$todayStart = strtotime(date('Y-m-d 00:00:00'));
$gamesToday = 0;
$gamesTodayQuery = $GameMonetizeConnect->query("SELECT COUNT(*) AS total FROM " . GAMES . " WHERE date_added >= '{$todayStart}'");
if ($gamesTodayQuery && $gamesTodayRow = $gamesTodayQuery->fetch_assoc()) {
	$gamesToday = (int)$gamesTodayRow['total'];
}

$totalGames = 0;
$totalGamesQuery = $GameMonetizeConnect->query("SELECT COUNT(*) AS total FROM " . GAMES);
if ($totalGamesQuery && $totalGamesRow = $totalGamesQuery->fetch_assoc()) {
	$totalGames = (int)$totalGamesRow['total'];
}

$themeData['autopost_active_links'] = $activeLinks;
$themeData['autopost_last_publish'] = $lastPublishText;
$themeData['autopost_games_today'] = $gamesToday;
$themeData['autopost_total_games'] = $totalGames;

/* CMS UPDATE NOTICE BOX */
$themeData['cms_update_notice_box'] = '';

$cmsUpdateNoticeFile = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/json/cms-update-notice.json';
$cmsInstalledVersion = gps_license_current_cms_version();
$cmsLatestVersion = '';
$cmsVersionNotice = [];

if (is_file($cmsUpdateNoticeFile)) {
	$cmsVersionNotice = json_decode((string)@file_get_contents($cmsUpdateNoticeFile), true);
	if (is_array($cmsVersionNotice)) {
		$cmsLatestVersion = trim((string)($cmsVersionNotice['version'] ?? $cmsVersionNotice['latest_version'] ?? ''));
	}
}

$themeData['cms_current_version'] = htmlspecialchars($cmsInstalledVersion, ENT_QUOTES, 'UTF-8');
$themeData['cms_latest_version'] = htmlspecialchars($cmsLatestVersion !== '' ? $cmsLatestVersion : 'not checked', ENT_QUOTES, 'UTF-8');
$cmsLastCheckedAt = !empty($cmsVersionNotice['checked_at']) ? strtotime((string)$cmsVersionNotice['checked_at']) : false;
$cmsShouldAutoCheck = $proActive
	&& empty($cmsVersionNotice['available'])
	&& ($cmsLastCheckedAt === false || $cmsLastCheckedAt < time() - 21600);
$themeData['gps_auto_update_check'] = $cmsShouldAutoCheck ? 'true' : 'false';

$cmsUpdatesAllowed = $proActive;

if ($cmsUpdatesAllowed && is_file($cmsUpdateNoticeFile)) {
	$cmsData = json_decode(file_get_contents($cmsUpdateNoticeFile), true);

	if (is_array($cmsData) && !empty($cmsData['available']) && !empty($cmsData['version'])) {
		$version = htmlspecialchars($cmsData['version'], ENT_QUOTES, 'UTF-8');
		$message = nl2br(htmlspecialchars($cmsData['message'] ?? 'A new CMS update is ready.', ENT_QUOTES, 'UTF-8'));

		$themeData['cms_update_notice_box'] = '
<div class="general-box" style="margin-bottom:15px;padding:20px;background:#3b1f22;border-left:4px solid #e11d48;color:#ffd4da;">
	<h3 style="margin:0 0 10px 0;color:#fff;">🚨 CMS Update Available v' . $version . '</h3>
	<p style="margin:0 0 12px 0;">' . $message . '</p>
	<button type="button" id="cmsUpdateNowBtn" class="btn-p btn-p1" style="background:#e11d48;">Update Now</button>
</div>';
	}
}

$themeData['page_admin_content'] = \GameMonetize\UI::view('admin/stats');
	} elseif (isset($_GET['section']) && $_GET['section'] == "addgame") {
		$addgame_category = $GameMonetizeConnect->query("SELECT * FROM " . CATEGORIES . " WHERE id!=0");
		$ctop_r = '';
		while ($select_category = $addgame_category->fetch_array()) {
			$ctop_r .= '<option value="' . $select_category['id'] . '">' . $select_category['name'] . '</option>';
		}
		$themeData['get_categories'] = $ctop_r;

		$addgame_tags = $GameMonetizeConnect->query("SELECT * FROM " . TAGS . " WHERE id!=0 ORDER BY name");
		$game_tags = '';
		while ($select_tags = $addgame_tags->fetch_array()) {
			$game_tags .= '<option value="' . $select_tags['id'] . '">' . $select_tags['name'] . '</option>';
		}
		$themeData['get_tags'] = $game_tags;

		$themeData['page_admin_content'] = \GameMonetize\UI::view('admin/add-game');
	} elseif (isset($_GET['section']) && in_array($_GET['section'], ['pro', 'upgrade-pro'], true)) {
		$googleFeatureMarker = ABSPATH . 'json/pro-features/google_login.json';
		$googleInstalled = is_file($googleFeatureMarker)
			&& is_file(ABSPATH . 'assets/includes/google-oauth.php')
			&& is_file(ABSPATH . 'google-login.php')
			&& is_file(ABSPATH . 'google-callback.php');
		if ($googleInstalled) {
			require_once ABSPATH . 'assets/includes/google-oauth.php';
		}
		$pageSpeedInstalled = is_file(ABSPATH . 'json/pro-features/pagespeed_optimization.json')
			&& is_file(ABSPATH . 'assets/pro/pagespeed/bootstrap.php')
			&& is_file(ABSPATH . 'assets/pro/pagespeed/optimizer.php');
		// Read the bound entitlement again at the point this page is rendered.
		// The Dashboard status request can refresh the state after the admin
		// shell was initialized, so reusing its earlier booleans can leave this
		// page visually locked while the same installation is already PRO.
		$pageProState = gps_license_state_read();
		$pageProClaims = gps_license_cached_bound_claims();
		$pageProPlan = strtolower(trim((string)($pageProClaims['plan'] ?? '')));
		$pageProStatus = strtolower(trim((string)($pageProClaims['status'] ?? $pageProState['license_status'] ?? '')));
		$pageProExpiresRaw = trim((string)($pageProClaims['license_expires_at'] ?? $pageProState['license_expires_at'] ?? ''));
		$pageProExpiresAt = $pageProExpiresRaw !== '' ? strtotime($pageProExpiresRaw . ' UTC') : false;
		$pageProExpired = !empty($pageProState['license_key']) && (
			$pageProStatus === 'expired'
			|| ($pageProExpiresAt !== false && $pageProExpiresAt < time())
		);
		$pageProActive = $pageProClaims !== []
			&& !$pageProExpired
			&& ($pageProStatus === '' || $pageProStatus === 'active')
			&& $pageProPlan !== 'free';
		$googleAllowed = $googleInstalled && ($pageProActive || $pageProExpired);
		$pageSpeedAllowed = $pageSpeedInstalled && gps_license_installed_feature_allowed('pagespeed_optimization');
		$proNotice = '';

		if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['save_google_login'])) {
			if (!$googleAllowed) {
				$proNotice = '<div style="padding:12px 14px;margin-bottom:15px;background:#4a2530;color:#ffb7c0;border-left:4px solid #ff6377;">A current PRO license is required.</div>';
			} elseif (!gps_google_ensure_schema($GameMonetizeConnect)) {
				$proNotice = '<div style="padding:12px 14px;margin-bottom:15px;background:#4a2530;color:#ffb7c0;border-left:4px solid #ff6377;">The Google Login database fields could not be prepared.</div>';
			} else {
				$currentGoogle = gps_google_config($GameMonetizeConnect);
				$clientId = trim((string)($_POST['google_oauth_client_id'] ?? ''));
				$clientSecret = trim((string)($_POST['google_oauth_client_secret'] ?? ''));
				if ($clientSecret === '') {
					$clientSecret = $currentGoogle['client_secret'];
				}
				$enabled = isset($_POST['google_oauth_enabled']) ? 1 : 0;
				if ($clientId !== '' && !preg_match('/^[A-Za-z0-9._-]+\.apps\.googleusercontent\.com$/', $clientId)) {
					$proNotice = '<div style="padding:12px 14px;margin-bottom:15px;background:#4a2530;color:#ffb7c0;border-left:4px solid #ff6377;">Google Client ID must end with .apps.googleusercontent.com.</div>';
				} elseif ($enabled && ($clientId === '' || $clientSecret === '')) {
					$proNotice = '<div style="padding:12px 14px;margin-bottom:15px;background:#4a2530;color:#ffb7c0;border-left:4px solid #ff6377;">Add both Google Client ID and Client Secret before enabling the feature.</div>';
				} else {
					$stmt = $GameMonetizeConnect->prepare('UPDATE `' . SETTING . '` SET `google_oauth_client_id`=?, `google_oauth_client_secret`=?, `google_oauth_enabled`=? WHERE `id`=1');
					$stmt->bind_param('ssi', $clientId, $clientSecret, $enabled);
					$saved = $stmt->execute();
					$stmt->close();
					$proNotice = $saved
						? '<div style="padding:12px 14px;margin-bottom:15px;background:#163c2c;color:#8ff0bd;border-left:4px solid #35d07f;">Google Login settings saved.</div>'
						: '<div style="padding:12px 14px;margin-bottom:15px;background:#4a2530;color:#ffb7c0;border-left:4px solid #ff6377;">Google Login settings could not be saved.</div>';
				}
			}
		}

		$licenseLabel = $pageProActive ? 'PRO Active' : ($pageProExpired ? 'PRO Expired' : 'Free CMS');
		$licenseDescription = $pageProActive
			? 'Licensed updates are included' . ($pageProExpiresAt !== false ? ' until ' . gmdate('M j, Y', $pageProExpiresAt) : '') . '.'
			: ($pageProExpired ? 'Your installed features remain available. Renew to receive new releases.' : 'Activate PRO from the Dashboard to unlock premium features.');
		$catalogItems = gps_pro_feature_catalog_load();
		$catalogBySlug = [];
		foreach ($catalogItems as $catalogItem) {
			$catalogBySlug[(string)$catalogItem['slug']] = $catalogItem;
		}
		$googleCatalog = $catalogBySlug['google_login'] ?? ['title' => 'Login with Google', 'description' => 'Secure Google administrator login.'];
		$pageSpeedCatalog = $catalogBySlug['pagespeed_optimization'] ?? ['title' => 'Google PageSpeed Optimization', 'description' => 'Core Web Vitals optimizations for every template.'];
		$googleTitle = htmlspecialchars((string)$googleCatalog['title'], ENT_QUOTES, 'UTF-8');
		$googleDescription = htmlspecialchars((string)$googleCatalog['description'], ENT_QUOTES, 'UTF-8');
		$pageSpeedTitle = htmlspecialchars((string)$pageSpeedCatalog['title'], ENT_QUOTES, 'UTF-8');
		$pageSpeedDescription = htmlspecialchars((string)$pageSpeedCatalog['description'], ENT_QUOTES, 'UTF-8');
		$pageSpeedImagePanel = '';
		if ($pageSpeedAllowed && is_file(ABSPATH . 'assets/pro/pagespeed/image-optimizer.php')) {
			require_once ABSPATH . 'assets/pro/pagespeed/image-optimizer.php';
			if (function_exists('gps_pro_image_optimizer_admin_panel')) {
				$pageSpeedImagePanel = gps_pro_image_optimizer_admin_panel();
			}
		}
		if ($pageSpeedImagePanel !== '') {
			$pageSpeedImagePanel = '<details id="gps-pagespeed-tools" style="border-top:1px solid rgba(255,255,255,.1);">'
				. '<summary style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:11px 14px;cursor:pointer;color:#ffd34d;font-weight:800;list-style:none;">Speed optimization tools <span style="color:#9fb0ca;font-size:12px;font-weight:600;">Open settings &#9662;</span></summary>'
				. $pageSpeedImagePanel . '</details>';
		}
		$featureRemoveButton = static function (string $slug, bool $enabled): string {
			return '';
		};
		$featureInstalledVersion = static function (string $slug): string {
			$marker = ABSPATH . 'json/pro-features/' . $slug . '.json';
			$data = is_file($marker) ? json_decode((string)@file_get_contents($marker), true) : null;
			$version = is_array($data) ? trim((string)($data['version'] ?? '')) : '';
			return preg_match('/^[0-9]+(?:\.[0-9A-Za-z_-]+)*$/', $version) ? $version : '';
		};
		$featureUpdateButton = static function (string $slug, array $catalogItem, string $installedVersion) use ($pageProActive): string {
			$availableVersion = trim((string)($catalogItem['version'] ?? ''));
			if (!$pageProActive || $installedVersion === '' || $availableVersion === '' || version_compare($availableVersion, $installedVersion, '<=')) {
				return '';
			}
			$safeSlug = htmlspecialchars($slug, ENT_QUOTES, 'UTF-8');
			$safeVersion = htmlspecialchars($availableVersion, ENT_QUOTES, 'UTF-8');
			return '<button type="button" class="gps-install-pro-feature" data-feature="' . $safeSlug . '" data-status-id="gps-' . $safeSlug . '-install-status" style="padding:9px 14px;background:#d39b12;border:1px solid #ffd34d;border-radius:6px;color:#211d0d;font-weight:800;cursor:pointer;">Update to v' . $safeVersion . '</button>';
		};

		$googleCardId = $googleInstalled ? ' id="gps-google-login-card"' : ' id="gps-google-login-installer"';
		$googleCard = '<div' . $googleCardId . ' style="background:rgba(255,255,255,.05);border:1px solid rgba(255,211,77,.35);border-radius:10px;overflow:hidden;">';
		if ($googleAllowed) {
			$google = gps_google_config($GameMonetizeConnect);
			$googleCard .= '<form id="gps-google-login-form" method="post" action="' . htmlspecialchars($proLink, ENT_QUOTES, 'UTF-8') . '">'
				. '<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 14px;">'
				. '<label style="display:flex;align-items:center;gap:10px;color:#fff;font-weight:700;cursor:pointer;"><input type="checkbox" name="google_oauth_enabled" value="1"' . ($google['enabled'] ? ' checked' : '') . ' style="position:static;"> <span>' . $googleTitle . '<small class="gps-google-status" style="display:block;color:#9fb0ca;font-weight:400;">' . ($google['enabled'] ? 'Enabled' : 'Disabled') . ' · ' . $googleDescription . '</small></span></label>'
				. '<span style="display:flex;gap:8px;align-items:center;"><button type="button" class="gps-google-config-toggle" style="padding:7px 11px;background:transparent;border:1px solid #ffd34d;border-radius:6px;color:#ffd34d;cursor:pointer;">Configure</button>' . $featureUpdateButton('google_login', $googleCatalog, $featureInstalledVersion('google_login')) . '</span></div>'
				. '<div class="gps-google-config-fields" style="display:none;padding:0 14px 14px;border-top:1px solid rgba(255,255,255,.1);">'
				. '<div style="margin:12px 0;color:#a9b8d0;">Authorized redirect URI:<br><code style="color:#70ff7a;word-break:break-all;">' . htmlspecialchars(gps_google_callback_url(), ENT_QUOTES, 'UTF-8') . '</code></div>'
				. '<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;"><button type="button" class="gps-google-copy-callback" data-callback="' . htmlspecialchars(gps_google_callback_url(), ENT_QUOTES, 'UTF-8') . '" style="padding:8px 12px;background:#344054;border:1px solid #667085;border-radius:6px;color:#fff;cursor:pointer;">Copy callback URL</button><button type="button" class="gps-google-tutorial-open" style="padding:8px 12px;background:#6f42c1;border:1px solid #8b5cf6;border-radius:6px;color:#fff;font-weight:700;cursor:pointer;">Setup tutorial</button></div>'
				. '<button type="button" class="gps-google-tutorial-open" style="display:inline-block;margin:0 0 12px;padding:8px 12px;background:#4285f4;border:0;border-radius:6px;color:#fff;font-weight:700;cursor:pointer;">Create Google Client ID</button><small style="display:block;margin:-5px 0 12px;color:#9fb0ca;">Opens a visual step-by-step tutorial before Google Cloud.</small>'
				. '<label style="display:block;color:#cbd5e1;margin-bottom:5px;">Google Client ID</label><input type="text" name="google_oauth_client_id" value="' . htmlspecialchars($google['client_id'], ENT_QUOTES, 'UTF-8') . '" style="position:static!important;width:100%;box-sizing:border-box;padding:9px;margin-bottom:10px;background:#252b38;border:1px solid rgba(255,255,255,.16);border-radius:6px;color:#fff;">'
				. '<label style="display:block;color:#cbd5e1;margin-bottom:5px;">Google Client Secret</label><input type="password" name="google_oauth_client_secret" value="" placeholder="Leave blank to keep saved secret" autocomplete="new-password" style="position:static!important;width:100%;box-sizing:border-box;padding:9px;margin-bottom:10px;background:#252b38;border:1px solid rgba(255,255,255,.16);border-radius:6px;color:#fff;">'
				. '<button type="submit" name="save_google_login" value="1" class="btn-p btn-p1" style="padding:8px 14px;">Save</button><div id="gps-google-login-message" style="margin-top:8px;color:#cbd5e1;"></div></div></form>'
				. '<div id="gps-google-tutorial-modal" style="display:none;position:fixed;inset:0;z-index:10000;background:rgba(8,12,24,.78);align-items:center;justify-content:center;padding:18px;"><div style="position:relative;width:min(920px,100%);max-height:88vh;overflow:auto;padding:24px;background:#20283a;border:1px solid #8b5cf6;border-radius:12px;color:#e7eaff;box-shadow:0 20px 60px rgba(0,0,0,.45);"><button type="button" class="gps-google-tutorial-close" aria-label="Close tutorial" style="position:sticky;float:right;right:0;top:0;z-index:2;border:0;background:#39435a;color:#fff;width:38px;height:38px;border-radius:50%;font-size:26px;cursor:pointer;">&times;</button><h2 style="margin:0 50px 8px 0;color:#ffd34d;">Google Login setup</h2><p style="margin:0 0 16px;color:#b9c7dd;">Follow the picture and steps below. This window scrolls, so complete each step in order.</p><img src="/assets/images/tutorials/google-cloud-credentials.png" alt="Google Cloud Credentials page showing Create credentials and OAuth Client IDs" loading="lazy" style="display:block;width:100%;height:auto;margin:0 0 18px;border:2px solid #586a91;border-radius:9px;background:#fff;"><ol style="margin:0;padding-left:22px;line-height:1.7;"><li><strong>Select or create a project</strong> from the project selector at the top of Google Cloud.</li><li>If Google asks first, open <strong>OAuth consent screen</strong>, add the application name, support email and contact email, then save.</li><li>On the Credentials page shown in the picture, click <strong>+ Create credentials</strong>, then <strong>OAuth client ID</strong>.</li><li>Choose <strong>Web application</strong> and enter any clear name, for example your website name.</li><li>Under <strong>Authorized redirect URIs</strong>, click Add URI and paste the exact callback URL displayed in this CMS. Do not use only the homepage URL.</li><li>Click <strong>Create</strong>. Copy the Client ID and the new Client Secret immediately. Google may not show that secret again.</li><li>Return here, paste both values, check Login with Google and press <strong>Save</strong>.</li><li>Test only from the public <strong>/login</strong> page with Continue with Google.</li></ol><a href="https://console.cloud.google.com/apis/credentials" target="_blank" rel="noopener noreferrer" style="display:inline-block;margin-top:18px;padding:10px 14px;background:#4285f4;border-radius:6px;color:#fff;font-weight:700;text-decoration:none;">Open Google Cloud Credentials &#8599;</a><div style="margin-top:16px;padding:11px 13px;background:#302b16;border-left:4px solid #ffd34d;color:#ffeaa7;">Do not test by opening google-callback.php directly. That URL works only when Google returns the required secure code and state.</div></div></div>';
		} elseif ($pageProActive) {
			$googleCard .= '<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 14px;flex-wrap:wrap;">'
				. '<span style="color:#fff;font-weight:700;">' . $googleTitle . '<small id="gps-google-install-status" data-feature-status="google_login" style="display:block;color:#9fb0ca;font-weight:400;">' . $googleDescription . '</small></span>'
				. '<span style="display:flex;gap:8px;align-items:center;"><button type="button" id="gps-install-google-login" class="gps-install-pro-feature" data-feature="google_login" data-status-id="gps-google-install-status" style="padding:9px 14px;background:#2f9e55;border:1px solid #51cf66;border-radius:6px;color:#fff;font-weight:700;cursor:pointer;">Install feature</button>' . $featureRemoveButton('google_login', false) . '</span></div>';
		} elseif ($pageProExpired) {
			$googleCard .= '<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 14px;flex-wrap:wrap;"><label style="display:flex;align-items:center;gap:10px;color:#e7eaff;"><input type="checkbox" disabled style="position:static;"> <span>' . $googleTitle . '<small style="display:block;color:#9fb0ca;">Renew PRO to install · ' . $googleDescription . '</small></span></label>' . $featureRemoveButton('google_login', false) . '</div>';
		} else {
			$googleCard .= '<label style="display:flex;align-items:center;gap:10px;padding:12px 14px;color:#e7eaff;"><input type="checkbox" disabled style="position:static;"> <span>' . $googleTitle . '<small style="display:block;color:#9fb0ca;">Available with PRO · ' . $googleDescription . '</small></span></label>';
		}
		$googleCard .= '</div>';

		$pageSpeedCard = '<div id="gps-pagespeed-optimization-card" style="background:rgba(255,255,255,.05);border:1px solid rgba(255,211,77,.35);border-radius:10px;overflow:hidden;">';
		if ($pageSpeedInstalled && $pageProExpired) {
			$pageSpeedCard .= '<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 14px;flex-wrap:wrap;"><label style="display:flex;align-items:center;gap:10px;color:#e7eaff;"><input type="checkbox" checked disabled style="position:static;"> <span>' . $pageSpeedTitle . '<small style="display:block;color:#9fb0ca;">Installed package remains available. Renew PRO to receive updates.</small></span></label>' . $featureRemoveButton('pagespeed_optimization', true) . '</div>';
		} elseif ($pageSpeedAllowed) {
			$pageSpeedCard .= '<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 14px;flex-wrap:wrap;"><label style="display:flex;align-items:center;gap:10px;color:#e7eaff;"><input type="checkbox" checked disabled style="position:static;"> <span>' . $pageSpeedTitle . '<small id="gps-pagespeed-install-status" data-feature-status="pagespeed_optimization" style="display:block;color:#9fb0ca;">' . $pageSpeedDescription . '</small></span></label>' . $featureUpdateButton('pagespeed_optimization', $pageSpeedCatalog, $featureInstalledVersion('pagespeed_optimization')) . '</div>';
		} elseif ($pageSpeedInstalled) {
			$pageSpeedCard .= '<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 14px;flex-wrap:wrap;"><label style="display:flex;align-items:center;gap:10px;color:#e7eaff;"><input type="checkbox" disabled style="position:static;"> <span>' . $pageSpeedTitle . '<small style="display:block;color:#ffb7c0;">Installed package is waiting for license and domain verification</small></span></label>' . $featureRemoveButton('pagespeed_optimization', $pageProActive || $pageProExpired) . '</div>';
		} elseif ($pageProActive) {
			$pageSpeedCard .= '<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 14px;flex-wrap:wrap;"><span style="color:#fff;font-weight:700;">' . $pageSpeedTitle . '<small id="gps-pagespeed-install-status" data-feature-status="pagespeed_optimization" style="display:block;color:#9fb0ca;font-weight:400;">' . $pageSpeedDescription . '</small></span><span style="display:flex;gap:8px;align-items:center;"><button type="button" id="gps-install-pagespeed-optimization" class="gps-install-pro-feature" data-feature="pagespeed_optimization" data-status-id="gps-pagespeed-install-status" style="padding:9px 14px;background:#2f9e55;border:1px solid #51cf66;border-radius:6px;color:#fff;font-weight:700;cursor:pointer;">Install feature</button>' . $featureRemoveButton('pagespeed_optimization', false) . '</span></div>';
		} elseif ($pageProExpired) {
			$pageSpeedCard .= '<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 14px;flex-wrap:wrap;"><span style="color:#e7eaff;">' . $pageSpeedTitle . '<small id="gps-pagespeed-install-status" data-feature-status="pagespeed_optimization" style="display:block;color:#9fb0ca;">Renew PRO to install · ' . $pageSpeedDescription . '</small></span><span style="display:flex;gap:8px;align-items:center;"><button type="button" id="gps-install-pagespeed-optimization" class="gps-install-pro-feature" data-feature="pagespeed_optimization" data-status-id="gps-pagespeed-install-status" disabled style="display:none;padding:9px 14px;background:#2f9e55;border:1px solid #51cf66;border-radius:6px;color:#fff;font-weight:700;cursor:pointer;">Install feature</button>' . $featureRemoveButton('pagespeed_optimization', false) . '</span></div>';
		} else {
			$pageSpeedCard .= '<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 14px;flex-wrap:wrap;"><span style="color:#e7eaff;">' . $pageSpeedTitle . '<small id="gps-pagespeed-install-status" data-feature-status="pagespeed_optimization" style="display:block;color:#9fb0ca;">Available with PRO · ' . $pageSpeedDescription . '</small></span><button type="button" id="gps-install-pagespeed-optimization" class="gps-install-pro-feature" data-feature="pagespeed_optimization" data-status-id="gps-pagespeed-install-status" disabled style="display:none;padding:9px 14px;background:#2f9e55;border:1px solid #51cf66;border-radius:6px;color:#fff;font-weight:700;cursor:pointer;">Install feature</button></div>';
		}
		$pageSpeedCard .= $pageSpeedImagePanel . '</div>';

		$catalogCards = '';
		$missingPackage = false;
		foreach ($catalogItems as $catalogItem) {
			$slug = (string)$catalogItem['slug'];
			if ($slug === 'google_login') {
				$catalogCards .= $googleCard;
				$missingPackage = $missingPackage || !$googleInstalled;
				continue;
			}
			if ($slug === 'pagespeed_optimization') {
				$catalogCards .= $pageSpeedCard;
				$missingPackage = $missingPackage || !$pageSpeedInstalled;
				continue;
			}
			if ($slug === 'language') {
				$languageInstalled = function_exists('gps_other_fixes_localization_admin_prepare')
					&& gps_license_installed_feature_allowed('other_fixes');
				$languageUrl = htmlspecialchars(rtrim(siteUrl(), '/') . '/index.php?p=admin&section=language', ENT_QUOTES, 'UTF-8');
				$languageDescription = htmlspecialchars((string)$catalogItem['description'], ENT_QUOTES, 'UTF-8');
				if ($languageInstalled) {
					$languageStatus = $pageProExpired
						? 'Installed runtime remains available. Renew PRO before changing any language configuration.'
						: 'Installed and licensed through Other Fixes. Every configuration change is verified through the API.';
					$catalogCards .= '<div id="gps-language-feature-card" style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 14px;background:rgba(255,255,255,.05);border:1px solid rgba(112,255,122,.28);border-radius:8px;flex-wrap:wrap;color:#e7eaff;">'
						. '<label style="display:flex;align-items:center;gap:10px;"><input type="checkbox" checked disabled style="position:static;"><span><strong>Language</strong><small id="gps-language-install-status" data-feature-status="language" style="display:block;color:#9fb0ca;">' . $languageStatus . '</small></span></label>'
						. '<a href="' . $languageUrl . '" class="btn-p btn-p1" style="padding:8px 13px;text-decoration:none;">Open Language</a></div>';
				} else {
					$catalogCards .= '<div id="gps-language-feature-card" style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 14px;background:rgba(255,255,255,.05);border-radius:8px;flex-wrap:wrap;color:#e7eaff;">'
						. '<span><strong>Language</strong><small id="gps-language-install-status" data-feature-status="language" style="display:block;color:#9fb0ca;">' . ($pageProExpired ? 'Renew PRO and update Other Fixes to install. ' : 'Install or update Other Fixes to unlock. ') . $languageDescription . '</small></span></div>';
				}
				continue;
			}
			$title = htmlspecialchars((string)$catalogItem['title'], ENT_QUOTES, 'UTF-8');
			$description = htmlspecialchars((string)$catalogItem['description'], ENT_QUOTES, 'UTF-8');
			$releaseNotesRaw = trim((string)($catalogItem['release_notes'] ?? ''));
			if ($slug === 'other_fixes' && $releaseNotesRaw !== '') {
				$historyItems = array_values(array_filter(array_map('trim', preg_split('/(?:\r\n|\r|\n)+/', $releaseNotesRaw))));
				$latestHistoryItem = array_shift($historyItems);
				$historyHtml = '';
				foreach ($historyItems as $historyItem) {
					$historyHtml .= '<span style="display:block;padding:7px 8px;border-bottom:1px solid rgba(255,255,255,.09);color:#b9c7dd;">'
						. htmlspecialchars($historyItem, ENT_QUOTES, 'UTF-8') . '</span>';
				}
				$description .= '<details aria-label="Other Fixes update history" style="display:block;margin-top:8px;border:1px solid rgba(255,211,77,.28);border-radius:6px;background:rgba(8,12,30,.32);">'
					. '<summary style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:8px;cursor:pointer;list-style:none;color:#fff1a8;"><span><strong style="display:block;color:#ffd34d;margin-bottom:2px;">Latest feature added</strong>' . htmlspecialchars((string)$latestHistoryItem, ENT_QUOTES, 'UTF-8') . '</span><span style="flex:0 0 auto;color:#9fb0ca;font-weight:700;">History (' . (count($historyItems) + 1) . ') &#9662;</span></summary>'
					. ($historyHtml !== '' ? '<span style="display:block;max-height:132px;overflow-y:auto;border-top:1px solid rgba(255,255,255,.09);">' . $historyHtml . '</span>' : '')
					. '</details>';
			} elseif ($releaseNotesRaw !== '') {
				$description .= ' · Latest fix: ' . htmlspecialchars($releaseNotesRaw, ENT_QUOTES, 'UTF-8');
			}
			$delivery = (string)$catalogItem['delivery'];
			$menuDesignMigrated = $slug === 'menu_design'
				&& $pageProActive
				&& function_exists('gps_menu_design_markup');
			$markerInstalled = is_file(ABSPATH . 'json/pro-features/' . $slug . '.json') || $menuDesignMigrated;
			$featureAllowed = $menuDesignMigrated || ($markerInstalled && gps_license_installed_feature_allowed($slug));
			$featureConfiguration = $featureAllowed
				&& $slug === 'crazygames_professional'
				&& function_exists('gps_crazy_pro_admin_panel')
				? gps_crazy_pro_admin_panel()
				: '';
			if ($delivery === 'package') {
				$missingPackage = $missingPackage || !$markerInstalled;
				if ($markerInstalled && $pageProExpired) {
					$catalogCards .= '<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 14px;background:rgba(255,255,255,.05);border-radius:8px;flex-wrap:wrap;color:#e7eaff;"><label style="display:flex;align-items:center;gap:10px;"><input type="checkbox" checked disabled style="position:static;"><span>' . $title . '<small id="gps-' . $slug . '-install-status" data-feature-status="' . $slug . '" style="display:block;color:#9fb0ca;">Installed package remains available. Renew PRO to receive updates.</small></span></label>' . $featureRemoveButton($slug, true) . '</div>';
				} elseif ($featureAllowed) {
					$status = $description !== '' ? $description : 'Installed and verified for this domain';
					$catalogCards .= '<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 14px;background:rgba(255,255,255,.05);border-radius:8px;flex-wrap:wrap;color:#e7eaff;"><label style="display:flex;align-items:center;gap:10px;"><input type="checkbox" checked disabled style="position:static;"><span>' . $title . '<small id="gps-' . $slug . '-install-status" data-feature-status="' . $slug . '" style="display:block;color:#9fb0ca;">' . $status . '</small></span></label>' . $featureUpdateButton($slug, $catalogItem, $featureInstalledVersion($slug)) . $featureConfiguration . '</div>';
				} elseif ($markerInstalled) {
					$catalogCards .= '<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 14px;background:rgba(255,255,255,.05);border-radius:8px;flex-wrap:wrap;color:#e7eaff;"><span>' . $title . '<small id="gps-' . $slug . '-install-status" data-feature-status="' . $slug . '" style="display:block;color:#9fb0ca;">Installed package remains available. Renew PRO to receive updates.</small></span>' . $featureRemoveButton($slug, $pageProActive || $pageProExpired) . '</div>';
				} elseif ($pageProActive) {
					$catalogCards .= '<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 14px;background:rgba(255,255,255,.05);border-radius:8px;flex-wrap:wrap;color:#e7eaff;"><span>' . $title . '<small id="gps-' . $slug . '-install-status" data-feature-status="' . $slug . '" style="display:block;color:#9fb0ca;">' . $description . '</small></span><span style="display:flex;gap:8px;align-items:center;"><button type="button" class="gps-install-pro-feature" data-feature="' . $slug . '" data-status-id="gps-' . $slug . '-install-status" style="padding:9px 14px;background:#2f9e55;border:1px solid #51cf66;border-radius:6px;color:#fff;font-weight:700;cursor:pointer;">Install feature</button>' . $featureRemoveButton($slug, false) . '</span></div>';
				} else {
					$catalogCards .= '<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 14px;background:rgba(255,255,255,.05);border-radius:8px;flex-wrap:wrap;color:#e7eaff;"><span>' . $title . '<small id="gps-' . $slug . '-install-status" data-feature-status="' . $slug . '" style="display:block;color:#9fb0ca;">' . ($pageProExpired ? 'Renew PRO to install · ' : 'Available with PRO · ') . $description . '</small></span><span style="display:flex;gap:8px;align-items:center;"><button type="button" class="gps-install-pro-feature" data-feature="' . $slug . '" data-status-id="gps-' . $slug . '-install-status" disabled style="display:none;padding:9px 14px;background:#2f9e55;border:1px solid #51cf66;border-radius:6px;color:#fff;font-weight:700;cursor:pointer;">Install feature</button>' . $featureRemoveButton($slug, false) . '</span></div>';
				}
			} else {
				$included = $delivery === 'included' && ($pageProActive || $pageProExpired);
				$status = $delivery === 'coming_soon' ? 'Coming soon · ' . $description : ($included ? 'Included with PRO · ' . $description : 'Available with PRO · ' . $description);
				$catalogCards .= '<label style="display:flex;align-items:center;gap:10px;padding:12px 14px;background:rgba(255,255,255,.05);border-radius:8px;color:#e7eaff;"><input type="checkbox"' . ($included ? ' checked' : '') . ' disabled style="position:static;"><span>' . $title . '<small style="display:block;color:#9fb0ca;">' . $status . '</small></span></label>';
			}
		}

		$installAllControl = $missingPackage
			? '<div id="gps-install-all-control" style="display:' . ($pageProActive ? 'flex' : 'none') . ';align-items:center;gap:12px;flex-wrap:wrap;margin:0 0 14px;"><button type="button" id="gps-install-all-pro-features" style="padding:10px 15px;background:linear-gradient(90deg,#b88914,#ffd34d);border:1px solid #ffd34d;border-radius:7px;color:#211d0d;font-weight:800;cursor:pointer;">Install all PRO features</button><span id="gps-install-all-status" style="color:#b9c7dd;">Installs every available package one at a time.</span></div>'
			: '';

		$themeData['page_admin_content'] = ($pageProActive && is_file(ABSPATH . 'assets/includes/license/install-all.js') ? '<script defer src="/assets/includes/license/install-all.js?v=1.18.62"></script>' : '') . '<div class="gamemonetize-main-headself"><i class="fa fa-crown" style="color:#ffd34d;"></i></div>'
			. '<div class="general-box _yt10 _yb10 _0e4" style="max-width:1050px;padding:24px;background:linear-gradient(135deg,#252b38,#29265a);">'
			. $proNotice
			. '<div style="display:flex;justify-content:space-between;gap:16px;align-items:flex-start;flex-wrap:wrap;border-bottom:1px solid rgba(255,255,255,.12);padding-bottom:18px;margin-bottom:18px;"><div><div style="color:#ffd34d;font-weight:800;letter-spacing:.06em;">GAMEPORTALSCRIPT PRO</div><h1 id="gps-pro-page-license-label" style="color:#fff;margin:6px 0;">' . $licenseLabel . '</h1><p id="gps-pro-page-license-description" style="color:#cbd5e1;margin:0;">' . $licenseDescription . '</p></div><div style="display:grid;gap:8px;justify-items:stretch;"><a href="https://gameportalscript.com/pro.php" target="_blank" rel="noopener noreferrer" style="color:#ffd34d;border:1px solid #ffd34d;border-radius:7px;padding:10px 14px;text-decoration:none;text-align:center;">' . ($pageProExpired ? 'Renew PRO' : 'View PRO details') . '</a><button type="button" id="gps-pro-check-updates" style="padding:10px 14px;background:#2f9e55;border:1px solid #51cf66;border-radius:7px;color:#fff;font-weight:800;cursor:pointer;">Check updates</button><small id="gps-pro-update-check-status" style="display:none;max-width:240px;color:#b9c7dd;line-height:1.35;"></small></div></div>'
			. '<div><h2 style="margin:0 0 10px;color:#fff;">CMS PRO features</h2>' . $installAllControl . '<div style="display:grid;gap:8px;">' . $catalogCards . '</div></div>'
			. '</div>'
			. '<script>(function(){function requestInstall(feature,button,status){button.disabled=true;button.textContent="Installing...";if(status){status.textContent="Checking license and downloading the protected package...";status.style.color="#9fb0ca";}var body=new URLSearchParams();body.set("feature",feature);return fetch("/assets/requests/admin/pro-feature-install.php",{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/x-www-form-urlencoded;charset=UTF-8"},body:body.toString()}).then(function(response){return response.json().then(function(data){return {ok:response.ok,data:data};});}).then(function(result){if(!result.ok||!result.data.ok){throw new Error(result.data.message||"Installation failed.");}button.textContent="Installed";if(status){status.textContent=result.data.message;}return result.data;}).catch(function(error){button.disabled=false;button.textContent="Try installation again";if(status){status.textContent=error.message;status.style.color="#ff9b9b";}throw error;});}function statusFor(button){return document.getElementById(button.dataset.statusId||"")||document.querySelector("[data-feature-status=\""+button.dataset.feature+"\"]");}function bindInstaller(){var card=document.getElementById("gps-google-login-installer"),label=document.getElementById("gps-pro-page-license-label"),active=label&&label.textContent.trim()==="PRO Active",allControl=document.getElementById("gps-install-all-control");if(allControl){allControl.style.display=active?"flex":"none";}if(card&&active&&!document.getElementById("gps-install-google-login")){card.innerHTML="<div style=\"display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 14px;flex-wrap:wrap;\"><span style=\"color:#fff;font-weight:700;\">Login with Google<small id=\"gps-google-install-status\" data-feature-status=\"google_login\" style=\"display:block;color:#9fb0ca;font-weight:400;\">Ready to install with this PRO license</small></span><button type=\"button\" id=\"gps-install-google-login\" class=\"gps-install-pro-feature\" data-feature=\"google_login\" data-status-id=\"gps-google-install-status\" style=\"padding:9px 14px;background:#2f9e55;border:1px solid #51cf66;border-radius:6px;color:#fff;font-weight:700;cursor:pointer;\">Install feature</button></div>";}document.querySelectorAll(".gps-install-pro-feature").forEach(function(button){if(active&&button.disabled){button.disabled=false;button.style.display="inline-block";var status=statusFor(button);if(status){status.textContent="Ready to install with this PRO license";}}if(button.dataset.bound==="1"){return;}button.dataset.bound="1";button.addEventListener("click",function(){requestInstall(button.dataset.feature,button,statusFor(button)).then(function(){window.setTimeout(function(){window.location.reload();},700);}).catch(function(){});});});var allButton=document.getElementById("gps-install-all-pro-features");if(allButton&&allButton.dataset.bound!=="1"){allButton.dataset.bound="1";allButton.addEventListener("click",function(){var buttons=Array.prototype.slice.call(document.querySelectorAll(".gps-install-pro-feature:not(:disabled)")),allStatus=document.getElementById("gps-install-all-status"),completed=0;if(!buttons.length){allStatus.textContent="Every available feature is already installed.";return;}allButton.disabled=true;allButton.textContent="Installing 0 / "+buttons.length;var chain=Promise.resolve();buttons.forEach(function(featureButton){chain=chain.then(function(){var feature=featureButton.dataset.feature;allStatus.textContent="Installing "+feature.replace(/_/g," ")+"...";return requestInstall(feature,featureButton,statusFor(featureButton)).then(function(){completed++;allButton.textContent="Installing "+completed+" / "+buttons.length;});});});chain.then(function(){allButton.textContent="All features installed";allStatus.textContent="Installation completed. Reloading...";window.setTimeout(function(){window.location.reload();},700);}).catch(function(error){allButton.disabled=false;allButton.textContent="Continue installation";allStatus.textContent="Stopped: "+error.message;allStatus.style.color="#ff9b9b";});});}}var licenseLabel=document.getElementById("gps-pro-page-license-label");if(licenseLabel&&window.MutationObserver){new MutationObserver(bindInstaller).observe(licenseLabel,{childList:true,subtree:true,characterData:true});}bindInstaller();})();</script>'
			. '<script>(function(){var button=document.getElementById("gps-pro-check-updates"),status=document.getElementById("gps-pro-update-check-status"),timer=0;if(!button){return;}function wait(seconds){window.clearInterval(timer);seconds=Math.max(1,parseInt(seconds||0,10));button.disabled=true;function draw(){var h=Math.floor(seconds/3600),m=Math.ceil((seconds%3600)/60);button.textContent="Try in "+(h?h+"h ":"")+m+"m";if(--seconds<0){window.clearInterval(timer);button.disabled=false;button.textContent="Check updates";}}draw();timer=window.setInterval(draw,60000);}button.addEventListener("click",function(){button.disabled=true;button.textContent="Checking...";status.style.display="block";status.style.color="#b9c7dd";status.textContent="Verifying this exact domain with GamePortalScript...";fetch("/assets/requests/admin/pro-update-check.php",{method:"POST",credentials:"same-origin"}).then(function(response){return response.json().then(function(data){return {ok:response.ok,data:data};});}).then(function(result){status.textContent=result.data.message||"Update check finished.";status.style.color=result.ok?"#8ff0bd":"#ffb7c0";if(result.ok){button.textContent="Checked";window.setTimeout(function(){window.location.reload();},900);return;}if(result.data.retry_after){wait(result.data.retry_after);return;}button.disabled=false;button.textContent="Check updates";}).catch(function(){status.textContent="The license server could not be reached. No installed feature was changed.";status.style.color="#ffb7c0";button.disabled=false;button.textContent="Check updates";});});})();</script>';
	} elseif (isset($_GET['section']) && $_GET['section'] == "setting") {
		$settings = $GameMonetizeConnect->query("SELECT * FROM " . SETTING . " WHERE id='1'");
		$settings = $settings->fetch_assoc();
		$themeData['config_recaptcha_site_key'] = $settings['recaptcha_site_key'];
		$themeData['config_recaptcha_secret_key'] = $settings['recaptcha_secret_key'];
		$themeData['other_fixes_google_tag_setting'] = function_exists('gps_other_fixes_google_tag_admin_field')
			? gps_other_fixes_google_tag_admin_field($settings['google_tag_id'] ?? '')
			: '';
		$themeData['pro_menu_design_logo_field'] = function_exists('gps_menu_design_logo_admin_field')
			? gps_menu_design_logo_admin_field()
			: '';

		$THEME_dir = opendir(ABSPATH . 'templates/');
		$THEME_dr_array = array();
		while (false !== ($file = readdir($THEME_dir))) {
			$THEME_dr_array[] = $file;
		}
		closedir($THEME_dir);
		$thm_r = '';
		$themeLabels = [
			'crazygames-like' => 'CrazyGames Classic',
			'crazygames-pro' => 'CrazyGames Pro',
			'y8-like' => 'Y8 Classic',
			'y8-pro' => 'Y8 Pro',
			'kizi' => 'Kizi Classic',
			'kizi-pro' => 'Kizi Pro',
			'poki-like' => 'Poki Classic',
			'poki-pro' => 'Poki Pro',
		];
		$themeFeatures = [
			'crazygames-pro' => 'crazygames_professional',
			'y8-pro' => 'menu_design',
			'kizi-pro' => 'professional_showcase',
			'poki-pro' => 'professional_showcase',
		];
		foreach ($THEME_dr_array as $file) {
			if ($file != "." && $file != ".." && $file != "Thumbs.db" && $file != ".DS_Store" && $file != "images") {
				if (!isset($themeLabels[$file])) {
					continue;
				}
				$requiredFeature = $themeFeatures[$file] ?? '';
				$featureAvailable = $requiredFeature === ''
					|| (function_exists('gps_license_installed_feature_allowed') && gps_license_installed_feature_allowed($requiredFeature));
				if (!$featureAvailable
					&& $requiredFeature !== ''
					&& function_exists('gps_license_installed_feature_allowed')) {
					$featureAvailable = gps_license_installed_feature_allowed('other_fixes');
				}
				if (!$featureAvailable) {
					continue;
				}
				$themeLabel = htmlspecialchars($themeLabels[$file], ENT_QUOTES, 'UTF-8');
				if ($config['site_theme'] == $file) {
					$thm_r .= '<option value="' . $file . '" selected>' . $themeLabel . '</option>';
				} else {
					$thm_r .= '<option value="' . $file . '">' . $themeLabel . '</option>';
				}
			}
		}
		$themeData['setting_get_themes'] = $thm_r;
		# >>

		$LANG_dir = opendir('assets/language/');
		$LANG_dr_array = array();
		while (false !== ($file = readdir($LANG_dir))) {
			$LANG_dr_array[] = $file;
		}
		closedir($LANG_dir);
		$lng_r = '';
		foreach ($LANG_dr_array as $file) {
			if ($file != "." && $file != ".." && $file != "Thumbs.db" && $file != ".DS_Store" && $file != "images") {
				$val_file = str_replace('.php', '', $file);
				if ($config['language'] == $val_file) {
					$lng_r .= '<option value="' . $val_file . '" selected>' . $val_file . '</option>';
				} else {
					$lng_r .= '<option value="' . $val_file . '">' . $val_file . '</option>';
				}
			}
		}
		$themeData['setting_get_languages'] = $lng_r;
		# >>

		$themeData['setting_ads_checked'] = ($config['ads_status']) ? 'checked' : '';
		$themeData['setting_remote_game_images_checked'] = (($settings['settings_10'] ?? '') === 'remote_images') ? 'checked' : '';

		$themeData['theme_custom_css'] = '';
		$themeData['theme_custom_css_theme'] = '';

		$safe_theme_folder = isset($config['site_theme']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $config['site_theme']) : '';
		$themeData['theme_custom_css_theme'] = htmlspecialchars($safe_theme_folder, ENT_QUOTES, 'UTF-8');

		if (!empty($safe_theme_folder)) {
			$custom_theme_css_path = __DIR__ . '/../../templates/' . $safe_theme_folder . '/css/custom-theme.css';

			if (file_exists($custom_theme_css_path)) {
				$themeData['theme_custom_css'] = htmlspecialchars(file_get_contents($custom_theme_css_path), ENT_QUOTES, 'UTF-8');
			}
		}

		$themeData['page_admin_content'] = \GameMonetize\UI::view('admin/setting');
	} elseif (isset($_GET['section']) && $_GET['section'] == "games") {
		if (!isset($_GET['action']) || $_GET['action'] == "view") {
			// Check game feed url
			$settings = $GameMonetizeConnect->query("SELECT * FROM " . SETTING . " WHERE id='1'");
			$settings = $settings->fetch_assoc();
			if (is_null($settings['custom_game_feed_url'])) {
				$themeData['custom_game_feed_url'] = 'https://gamemonetize.com/feed.php?format=0&num=30';
			} else {
				$themeData['custom_game_feed_url'] = $settings['custom_game_feed_url'];
			}

			$pageno = isset($_GET['page']) ? (int) $_GET['page'] : 1;
			$no_of_records_per_page = 102;
			$offset = ($pageno - 1) * $no_of_records_per_page;

			$result = $GameMonetizeConnect->query("SELECT COUNT(*) FROM " . GAMES);
			$total_pages = ceil($result->fetch_array()[0] / $no_of_records_per_page);

			$sql_global_games = $GameMonetizeConnect->query("SELECT * FROM " . GAMES . " WHERE game_id!=0 ORDER BY date_added DESC LIMIT $offset, $no_of_records_per_page");

			if ($sql_global_games->num_rows > 0) {
				$vgmsgbl_r = '';
				while ($global_games = $sql_global_games->fetch_array()) {
					$themeData['view_game_id'] = $global_games['game_id'];
					$themeData['view_game_name'] = $global_games['name'];
					if (strpos($global_games['image'], "http://") !== false || strpos($global_games['image'], "https://") !== false) {
						$themeData['view_game_image'] =   $global_games['image'];
					} else {
						$themeData['view_game_image'] = siteUrl() . $global_games['image'];
					}
					$themeData['view_game_featured'] = $global_games['featured'];
					$themeData['view_game_published'] = $global_games['published'];
					$themeData['view_published_class_status'] = ($global_games['published'] == 1) ? 'pub-active' : '';
					$themeData['view_featured_class_status'] = ($global_games['featured'] == 1) ? 'feat-active' : '';

					$vgmsgbl_r .= \GameMonetize\UI::view('admin/sections/view-games-list');
				}
				$themeData['view_games_list'] = $vgmsgbl_r;

				$themeData['view_games_pagination'] = '
					<ul class="pagination">
						<li><a href="' . siteUrl() . '/admin/games/1">First</a></li>
						<li class="' . (($pageno <= 1) ? 'disabled' : '') . '">
							<a href="' . siteUrl() . '/admin/games' . (($pageno <= 1) ? '#' : '/' . ($pageno - 1)) . '">Prev</a>
						</li>
						<li class="' . (($pageno >= $total_pages) ? 'disabled' : '') . '">
							<a href="' . siteUrl() . '/admin/games' . (($pageno >= $total_pages) ? '#' : '/' . ($pageno + 1)) . '">Next</a>
						</li>
						<li><a href="' . siteUrl() . '/admin/games/' . $total_pages . '">Last</a></li>
					</ul>
					';



				$themeData['games_container'] = \GameMonetize\UI::view('admin/sections/view-games-container');
			} else {
				$themeData['games_container'] = \GameMonetize\UI::view('admin/sections/view-games-notfound');
			}

			$themeData['games_section_content'] = \GameMonetize\UI::view('admin/sections/view-games-section');
		} elseif (isset($_GET['action']) && $_GET['action'] == "edit" && !empty($_GET['gid'])) {
			$get_game_id = secureEncode($_GET['gid']);
			$get_game = getGame($get_game_id);
			if ($get_game) {
				$themeData['edit_game_id'] = $get_game['game_id'];
				$themeData['edit_game_name_url'] = $get_game['game_name'];
				$themeData['edit_game_name'] = $get_game['name'];
				$themeData['edit_game_image'] = $get_game['image'];
				$themeData['edit_game_description'] = $get_game['description'];
				$themeData['edit_game_instructions'] = $get_game['instructions'];
				$themeData['edit_game_file'] = $get_game['file'];
				$themeData['edit_game_width'] = $get_game['w'];
				$themeData['edit_game_height'] = $get_game['h'];
				$themeData['edit_game_sorting'] = $get_game['featured_sorting'];
				$themeData['edit_game_type_swf_status'] = ($get_game['game_type'] == 'swf') ? 'selected' : '';
				$themeData['edit_game_type_other_status'] = ($get_game['game_type'] !== 'swf') ? 'selected' : '';

				$themeData['edit_game_rating_0'] = ($get_game['rating'] == 0) ? 'selected' : '';
				$themeData['edit_game_rating_0_5'] = ($get_game['rating'] == 0.5) ? 'selected' : '';
				$themeData['edit_game_rating_1'] = ($get_game['rating'] == 1) ? 'selected' : '';
				$themeData['edit_game_rating_1_5'] = ($get_game['rating'] == 1.5) ? 'selected' : '';
				$themeData['edit_game_rating_2'] = ($get_game['rating'] == 2) ? 'selected' : '';
				$themeData['edit_game_rating_2_5'] = ($get_game['rating'] == 2.5) ? 'selected' : '';
				$themeData['edit_game_rating_3'] = ($get_game['rating'] == 3) ? 'selected' : '';
				$themeData['edit_game_rating_3_5'] = ($get_game['rating'] == 3.5) ? 'selected' : '';
				$themeData['edit_game_rating_4'] = ($get_game['rating'] == 4) ? 'selected' : '';
				$themeData['edit_game_rating_4_5'] = ($get_game['rating'] == 4.5) ? 'selected' : '';
				$themeData['edit_game_rating_5'] = ($get_game['rating'] == 5) ? 'selected' : '';
				$themeData['edit_game_video_url'] = $get_game['video_url'];
				$themeData['edit_game_is_last_rewrite'] = "";
				if($get_game['is_last_rewrite'] == '1'){
					$themeData['edit_game_is_last_rewrite'] = "checked";
				}

				$addgame_category = $GameMonetizeConnect->query("SELECT * FROM " . CATEGORIES . " WHERE id!=0");
				$gcts_r = '';
				while ($select_category = $addgame_category->fetch_array()) {
					if ($get_game['category'] == $select_category['id']) {
						$gcts_r .= '<option value="' . $select_category['id'] . '" selected>' . $select_category['name'] . '</option>';
					} else {
						$gcts_r .= '<option value="' . $select_category['id'] . '">' . $select_category['name'] . '</option>';
					}
				}
				$themeData['edit_game_categories'] = $gcts_r;

				$game_tags = [];
				if ($get_game['tags_ids'] != 'null' && !is_null($get_game['tags_ids'])) {
					$game_tags = json_decode($get_game['tags_ids']);
				}
				$addgame_tags = $GameMonetizeConnect->query("SELECT * FROM " . TAGS . " WHERE id!=0");
				$tags_option = '';
				$tags_click_copy = '';
				while ($select_tags = $addgame_tags->fetch_array()) {
					if (in_array("{$select_tags['id']}", $game_tags)) {
						$tags_option .= '<option value="' . $select_tags['id'] . '" selected>' . $select_tags['name'] . '</option>';

						// Tags click copy
						$themeData['tags_url_copy'] = siteUrl() . "/tag/" . $select_tags['url'];
						$themeData['tags_name_copy'] = $select_tags['name'];
						$tags_click_copy .= \GameMonetize\UI::view('admin/sections/tags-click-copy');
					} else {
						$tags_option .= '<option value="' . $select_tags['id'] . '">' . $select_tags['name'] . '</option>';
					}
				}
				$themeData['edit_game_tags'] = $tags_option;
				$themeData['tags_click_to_copy'] = $tags_click_copy;

				$gameNameExplode = explode(' ', $get_game['name']);
				$firstName = substr($gameNameExplode[0], 0, 4);
				$secondName = substr($gameNameExplode[1], 0, 4);
				$threeRandomGame = array();

				// First word similar
				$first_name_click_copy = '';
				$sqlQuerySimilar = $GameMonetizeConnect->query("SELECT * FROM " . GAMES . " WHERE name LIKE '%{$firstName}%' AND published='1' AND name != '{$get_game['name']}' ORDER BY name ASC LIMIT 10");
				if ($sqlQuerySimilar->num_rows > 0) {
					while ($similarGames = $sqlQuerySimilar->fetch_array()) {
						$themeData['tags_url_copy'] = siteUrl() . "/game/" . $similarGames['game_name'];

						$firstWordRandomGame = $threeRandomGame[0] = "<a href='{$themeData['tags_url_copy']}' target='_self' class='gameKeyword'><bold>{$similarGames['name']}</bold></a>";

						$themeData['tags_name_copy'] = $similarGames['name'];
						$first_name_click_copy .= \GameMonetize\UI::view('admin/sections/tags-click-copy');
					}
				}

				$themeData['first_word_click_to_copy'] = $first_name_click_copy;

				// Second word similar
				$second_name_click_copy = '';
				$sqlQuerySimilar = $GameMonetizeConnect->query("SELECT * FROM " . GAMES . " WHERE name LIKE '%{$secondName}%' AND published='1' AND name != '{$get_game['name']}' ORDER BY name ASC LIMIT 10");
				if ($sqlQuerySimilar->num_rows > 0) {
					while ($similarGames = $sqlQuerySimilar->fetch_array()) {
						$themeData['tags_url_copy'] = siteUrl() . "/game/" . $similarGames['game_name'];
						$secondWordRandomGame = $threeRandomGame[1] = "<a href='{$themeData['tags_url_copy']}' target='_self' class='gameKeyword'><bold>{$similarGames['name']}</bold></a>";

						$themeData['tags_name_copy'] = $similarGames['name'];
						$second_name_click_copy .= \GameMonetize\UI::view('admin/sections/tags-click-copy');
					}
				}

				$themeData['second_word_click_to_copy'] = $second_name_click_copy;

				// Random word similar
				$oneRandomGame = "";
				$random_name_click_copy = '';
				$sqlQuerySimilar = $GameMonetizeConnect->query("SELECT * FROM " . GAMES . " WHERE  published='1' AND name != '{$get_game['name']}' ORDER BY RAND() LIMIT 10");
				if ($sqlQuerySimilar->num_rows > 0) {
					$index = 0;
					while ($similarGames = $sqlQuerySimilar->fetch_array()) {
						$themeData['tags_url_copy'] = siteUrl() . "/game/" . $similarGames['game_name'];
						$oneRandomGame = "<a href='{$themeData['tags_url_copy']}' target='_self' class='gameKeyword'><bold>{$similarGames['name']}</bold></a>";

						$themeData['tags_name_copy'] = $similarGames['name'];
						$random_name_click_copy .= \GameMonetize\UI::view('admin/sections/tags-click-copy');
						if ($index > 1) {
							$thirdRandomGame = siteUrl() . "/game/" . $similarGames['game_name'];
							$threeRandomGame[2] = "<a href='{$thirdRandomGame}' target='_self' class='gameKeyword'><bold>{$similarGames['name']}</bold></a>";

						}
						$index++;
					}
				}

				// Chatgpt template
				$chatgpt_query = $GameMonetizeConnect->query("SELECT * FROM " . CHATGPT . " WHERE id!=0");
				if ($chatgpt_query && $chatgpt_query->num_rows > 0) {
					$chatgpt_data = $chatgpt_query->fetch_assoc();

					$gameDescription = $get_game['description'];
					$gameTitle = $get_game['name'];
					if (!empty($game_tags)) {
						$beforeWordArray = explode(",", $chatgpt_data['random_words_before_tags']);
						$afterWordArray = explode(",", $chatgpt_data['random_words_after_tags']);

						$allRandomBeforeAfter = [];
						$allGameTagsIds = implode(",", $game_tags);
						$allGameTags = [];
						$gameTags = $GameMonetizeConnect->query("SELECT * FROM " . TAGS . " WHERE id IN({$allGameTagsIds}) ");
						$allRandomSimTagLink = [];
						while ($tagsData = $gameTags->fetch_array()) {
							$beforeWord = $beforeWordArray[array_rand($beforeWordArray)] . " ";
							$afterWord = " " . $afterWordArray[array_rand($afterWordArray)];
							$allGameTags[] = $beforeWord . $tagsData['name'] . $afterWord;

							$randomSimTagLink = siteUrl() . "/tag/" . $tagsData['url'];
							$randomSimTagName = $tagsData['name'];
							$allRandomSimTagLink[] = "<a href='{$randomSimTagLink}' target='_self' class='gameKeyword'><bold>{$randomSimTagName} Games</bold></a>";
						}

						$randomSimTagLink = $allRandomSimTagLink[array_rand($allRandomSimTagLink)];

						$gameTags = implode(",", $allGameTags);
					}

					$gameCategory = $GameMonetizeConnect->query("SELECT * FROM " . CATEGORIES . " WHERE id = '{$get_game['category']}'");
					if ($gameCategory) {
						$gameCategory = $gameCategory->fetch_assoc();
						$gameCategory = $gameCategory['name'];
					}

					// Random similar tag
					// $randomSimTag = $GameMonetizeConnect->query("SELECT * FROM " . TAGS . " ORDER BY RAND() LIMIT 1");
					// $randomSimTag = $randomSimTag->fetch_assoc();
					// $randomSimTagLink = siteUrl() . "/tag/" . $randomSimTag['url'];
					// $randomSimTagName = ucfirst($randomSimTag['name']);
					// $randomSimTagLink = "<a href='{$randomSimTagLink}' target='_self' class='gameKeyword'><bold>{$randomSimTagName} Games</bold></a>";
					
					// Random tag link
					$randomTag = $GameMonetizeConnect->query("SELECT * FROM " . TAGS . " ORDER BY RAND() LIMIT 1");
					$randomTag = $randomTag->fetch_assoc();
					$randomTagLink = siteUrl() . "/tag/" . $randomTag['url'];
					$randomTagName = ucfirst($randomTag['name']);
					$randomTagLink = "<a href='{$randomTagLink}' target='_self' class='gameKeyword'><bold>{$randomTagName} Games</bold></a>";
					
					$themeData['chat_gpt_template_game'] = str_replace(
						[
							"\$description",
							"\$title",
							"\$tags",
							"\$category",
							"\$game_link",
							"\$game_first_word",
							"\$game_second_word",
							"\$three_random_game",
							"\$random_similar_tags",
							"\$random_tags_link",
						],
						[
							$gameDescription,
							$gameTitle,
							$gameTags,
							$gameCategory,
							$oneRandomGame,
							$firstWordRandomGame,
							$secondWordRandomGame,
							implode(",", $threeRandomGame),
							$randomSimTagLink,
							$randomTagLink
						],
						$chatgpt_data['template_game']
					);

					$themeData['chat_gpt_template_game'] = str_replace('"', "'", $themeData['chat_gpt_template_game']);
				}


				$themeData['random_word_click_to_copy'] = $random_name_click_copy;
				$themeData['games_section_content'] = \GameMonetize\UI::view('admin/sections/edit-games-section');
			} else {
				$themeData['games_section_content'] = \GameMonetize\UI::view('welcome/error-section');
			}
		} else {
			$themeData['games_section_content'] = \GameMonetize\UI::view('welcome/error-section');
		}

		$themeData['page_admin_content'] = \GameMonetize\UI::view('admin/games');
	} elseif (isset($_GET['section']) && $_GET['section'] == "games-images-and-videos") {
		$newGames_query = $GameMonetizeConnect->query("SELECT * FROM ".GAMES." WHERE published='1' AND date_added >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 DAY)) ORDER BY date_added DESC");

		if ($newGames_query->num_rows == 0) {
			$themeData['games_list_image'] = 'No games for last 30 days.';
			$themeData['games_list_video'] = 'No games for last 30 days.';
		} else {
			while ($newGames = $newGames_query->fetch_array()) {
				$newGame_data = gameData($newGames);

				$new_game_image = "";
				$image_status = "";
				preg_match("/\/([a-zA-Z0-9]+)\//", $newGame_data['image_url'], $matches);
				$baseImagePath = $_SERVER['DOCUMENT_ROOT'] . '/games-image/' . $matches[1] . '/250x150.webp';
				if (file_exists($baseImagePath)) {
					$new_game_image = str_replace($_SERVER['DOCUMENT_ROOT'], '', $baseImagePath);
					$image_status = "<span style='color: green;display:inline-block;margin-left:8px;'>Image found</span>";
				} else {
					$new_game_image = $newGame_data['image_url'];
					$image_status = "<span style='color: red;display:inline-block;margin-left:8px;'>Image not found</span>";
				}

				$new_game_video = "";
				$video_status = "";
				preg_match('/([^\/]+\.mp4)$/', $newGame_data['wt_video'], $matches);
				$baseVideoThumbPath = $_SERVER['DOCUMENT_ROOT'] . '/games-thumb-video/' . $matches[1];
				if (file_exists($baseVideoThumbPath) &&  $newGame_data['wt_video'] != "") {
					$new_game_video = str_replace($_SERVER['DOCUMENT_ROOT'], '', $baseVideoThumbPath);
					$video_status = "<span style='color: green;display:inline-block;margin-left:8px;'>Video found</span>";
				} else {
					$new_game_video = $newGame_data['wt_video'];
					$video_status = "<span style='color: red;display:inline-block;margin-left:8px;'>Video not found</span>";
				}

				$themeData['games_list_image'] .= "<li style='display:flex;align-items:center;border-bottom: 1px solid #43495a;padding: 8px 16px;'><img src='" . $new_game_image . "' style='width: 40px;margin-right: 10px;'>" . $newGame_data['name'] . $image_status . "</li>";
				$themeData['games_list_video'] .= "<li style='display:flex;align-items:center;border-bottom: 1px solid #43495a;padding: 8px 16px;'><img src='" . $new_game_image . "' style='width: 40px;margin-right: 10px;'>" . $newGame_data['name'] . $video_status . "</li>";
			}
		}

		$ngrok_url = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/VPS SERVER IP HERE.txt');
		$themeData['ngrok_url'] = "http://" . $ngrok_url;
		$themeData['cron_url_30_days'] = "https://" . $_SERVER['HTTP_HOST'] . "/ajax_updategamesvideos.php?action=update_last_30_days&ngrokurl=http%3A%2F%2F" . $ngrok_url;
		$themeData['cron_url_all_games'] = "https://" . $_SERVER['HTTP_HOST'] . "/ajax_updategamesvideos.php?action=update_all&ngrokurl=http%3A%2F%2F" . $ngrok_url;

		$themeData['page_admin_content'] = \GameMonetize\UI::view('admin/games-images-and-videos');
	} elseif (isset($_GET['section']) && $_GET['section'] == "categories") {
		if (!isset($_GET['action']) || $_GET['action'] == "view") {
			$sql_global_categories = $GameMonetizeConnect->query("SELECT * FROM " . CATEGORIES . " WHERE id!=0");
			$gcts_r = '';
			while ($global_categories = $sql_global_categories->fetch_array()) {
				$themeData['view_category_id'] = $global_categories['id'];
				$themeData['view_category_name'] = $global_categories['name'];
				$themeData['view_category_image'] = $global_categories['image'];
				$themeData['view_category_button_delete'] = ($global_categories['id'] != 1) ? \GameMonetize\UI::view('admin/sections/view-categories-button-delete') : '';

				$gcts_r .= \GameMonetize\UI::view('admin/sections/view-categories-list');
			}
			$themeData['view_categories_list'] = $gcts_r;

			$themeData['categories_section_content'] = \GameMonetize\UI::view('admin/sections/view-categories-section');
		} elseif (isset($_GET['action']) && $_GET['action'] == "add") {
			$themeData['categories_section_content'] = \GameMonetize\UI::view('admin/sections/view-categories-add');
		} elseif (isset($_GET['action']) && $_GET['action'] == "edit" && !empty($_GET['cid'])) {
			$category_id = secureEncode($_GET['cid']);
			$sql_select_editcategory = $GameMonetizeConnect->query("SELECT * FROM " . CATEGORIES . " WHERE id='{$category_id}'");
			if ($sql_select_editcategory->num_rows == 1) {
				$edit_category = $sql_select_editcategory->fetch_array();
				$themeData['edit_category_id'] = $edit_category['id'];
				$themeData['edit_category_name'] = $edit_category['name'];
				$themeData['edit_category_footer_description'] = $edit_category['footer_description'];
				$themeData['edit_category_show_home'] = $edit_category['show_home'] ? 'checked' : '';
				$themeData['edit_category_url'] = siteUrl() . '/category/' . $edit_category['category_pilot'];
				// Chatgpt template
				$chatgpt_query = $GameMonetizeConnect->query("SELECT * FROM " . CHATGPT . " WHERE id!=0");
				if ($chatgpt_query && $chatgpt_query->num_rows > 0) {
					$chatgpt_data = $chatgpt_query->fetch_assoc();

					$categoryDescription = $edit_category['footer_description'];
					$categoryTitle = $edit_category['name'];

					$categoryRandomGame = '';
					$sqlQuerySimilar = $GameMonetizeConnect->query("SELECT * FROM " . GAMES . " WHERE category = {$edit_category['id']} AND published='1' ORDER BY RAND() LIMIT 1");
					if ($sqlQuerySimilar->num_rows > 0) {
						while ($similarGames = $sqlQuerySimilar->fetch_array()) {
							$categoryRandomGame = siteUrl() . "/game/" . $similarGames['game_name'];
							$categoryRandomGame = "<a href='{$categoryRandomGame}' target='_self' class='gameKeyword'><bold>{$similarGames['name']}</bold></a>";

						}
					}

					$firstWordGame = $secondWordGame = $firstWordCategoryTitle = $secondWordCategoryTitle = "";

					// First word game
					$categoryTitleExploded = explode(" ", $categoryTitle);

					$firstWordCategoryTitle = substr($categoryTitleExploded[0], 0, 4);
					$sqlGameFirstWord = $GameMonetizeConnect->query("SELECT * FROM " . GAMES . " WHERE name LIKE '%{$firstWordCategoryTitle}%' AND published='1' LIMIT 1");
					if ($sqlGameFirstWord && $sqlGameFirstWord->num_rows > 0) {
						$firstGameData = $sqlGameFirstWord->fetch_array();
						$firstGameLink = siteUrl() . "/game/" . $firstGameData['game_name'];
						$firstWordGame = "<a href='{$firstGameLink}' target='_self' class='gameKeyword'><bold>{$firstGameData['name']}<bold></a>";
					}

					if ($firstWordGame == "") {
					}

					if (count($categoryTitleExploded) > 1) {
						$secondWordTagTitle = substr($categoryTitleExploded[1], 0, 4);

						$sqlGameSecondWord = $GameMonetizeConnect->query("SELECT * FROM " . GAMES . " WHERE name LIKE '%{$secondWordTagTitle}%' AND published='1' LIMIT 1");

						if ($sqlGameSecondWord && $sqlGameSecondWord->num_rows > 0) {
							$secondGameData = $sqlGameSecondWord->fetch_array();
							$secondGameLink = siteUrl() . "/game/" . $secondGameData['game_name'];
							$secondWordGame = "<a href='{$secondGameLink}' target='_self' class='gameKeyword'><bold>{$secondGameData['name']}</bold></a>";
						}
					}

					// Random games
					$sqlRandomGame = $GameMonetizeConnect->query("SELECT * FROM " . GAMES . " WHERE published='1' ORDER BY RAND() LIMIT 1");
					if ($sqlRandomGame && $sqlRandomGame->num_rows > 0) {
						$randomGameData = $sqlRandomGame->fetch_array();
						$randomGameLink = siteUrl() . "/game/" . $randomGameData['game_name'];
						$randomGame = "<a href='{$randomGameLink}' target='_self' class='gameKeyword'><bold>{$randomGameData['name']}</bold></a>";
					}

					// // Random category
					// $sqlRandomcategory = $GameMonetizeConnect->query("SELECT * FROM " . CATEGORIES . " ORDER BY RAND() LIMIT 1");
					// if ($sqlRandomcategory && $sqlRandomcategory->num_rows > 0) {
					// 	$randomcategoryData = $sqlRandomcategory->fetch_array();
					// 	$randomcategoryLink = siteUrl() . "/category/" . $randomcategoryData['category_pilot'];
					// 	$randomCategory = "<a href='{$randomcategoryLink}' target='_self' class='gameKeyword'></bold>{$randomcategoryData['name']}</bold></a>";
					// 	$randomCategoryText = "{$randomcategoryData['name']}";
					// }

					// Random tags
					$sqlRandomTags = $GameMonetizeConnect->query("SELECT * FROM " . TAGS . " ORDER BY RAND() LIMIT 1");

					if ($sqlRandomTags && $sqlRandomTags->num_rows > 0) {
						$randomTagsData = $sqlRandomTags->fetch_array();
						$randomTagsLink = siteUrl() . "/tag/" . $randomTagsData['url'];
						$randomTags = "<a href='{$randomTagsLink}' target='_self' class='gameKeyword'><bold>{$randomTagsData['name']} Games</bold></a>";
						$randomTagText = "{$randomTagsData['name']} Games";
					}

					// category with random before and after words
					$beforeWordArray = explode(",", $chatgpt_data['random_words_before_tags']);
					$afterWordArray = explode(",", $chatgpt_data['random_words_after_tags']);
					$allRandomBeforeAfter = [];
					for ($i = 0; $i < 10; $i++) {
						$beforeWord = $beforeWordArray[array_rand($beforeWordArray)] . " ";
						$afterWord = " " . $afterWordArray[array_rand($afterWordArray)];
						$categoryTitle = str_replace("Games", "", $categoryTitle);
						$categoryTitle = str_replace("games", "", $categoryTitle);
						$allRandomBeforeAfter[] = $beforeWord . $categoryTitle . $afterWord;
					}
					$allRandomBeforeAfter = implode(",", $allRandomBeforeAfter);


					$themeData['chat_gpt_template_category'] = str_replace(
						[
							"\$description",
							"\$title",
							"\$game_link",
							"\$firstWord",
							"\$secondWord",
							"\$randomSimGames",
							"\$randomSimTags",
							// "\$randomSimCategoryText",
							"\$randomSimTagBeforeAfter",
						],
						[
							$categoryDescription,
							$categoryTitle,
							$categoryRandomGame,
							$firstWordGame,
							$secondWordGame,
							$randomGame,
							$randomTags,
							// $randomCategoryText,
							$allRandomBeforeAfter,
						],
						$chatgpt_data['template_category']
					);
				}

				$themeData['categories_section_content'] = \GameMonetize\UI::view('admin/sections/view-categories-edit');
			} else {
				$themeData['categories_section_content'] = \GameMonetize\UI::view('welcome/error-section');
			}
		} else {
			$themeData['categories_section_content'] = \GameMonetize\UI::view('welcome/error-section');
		}

		$themeData['page_admin_content'] = \GameMonetize\UI::view('admin/categories');
	} elseif (isset($_GET['section']) && $_GET['section'] == "tags") {
		if (!isset($_GET['action']) || $_GET['action'] == "view") {
			$sql_global_tags = $GameMonetizeConnect->query("SELECT * FROM " . TAGS . " WHERE id!=0");
			$gcts_r = '';
			while ($global_tags = $sql_global_tags->fetch_array()) {
				$updateStatus = "";
				if (strlen($global_tags['footer_description'])) {
					$updateStatus = " (description updated)";
				}
				$themeData['view_tags_id'] = $global_tags['id'];
				$themeData['view_tags_name'] = $global_tags['name'] . $updateStatus;
				$themeData['view_tags_url_open'] = siteUrl() . "/tag/" . $global_tags['url'];
				$themeData['view_tags_button_delete'] = \GameMonetize\UI::view('admin/sections/view-tags-button-delete');

				$gcts_r .= \GameMonetize\UI::view('admin/sections/view-tags-list');
			}
			$themeData['view_tags_list'] = $gcts_r;

			$themeData['tags_section_content'] = \GameMonetize\UI::view('admin/sections/view-tags-section');
		} elseif (isset($_GET['action']) && $_GET['action'] == "add") {
			$themeData['tags_section_content'] = \GameMonetize\UI::view('admin/sections/view-tags-add');
		} elseif (isset($_GET['action']) && $_GET['action'] == "edit" && !empty($_GET['cid'])) {
			$tags_id = secureEncode($_GET['cid']);
			$sql_select_edittags = $GameMonetizeConnect->query("SELECT * FROM " . TAGS . " WHERE id='{$tags_id}'");
			if ($sql_select_edittags->num_rows == 1) {
				$edit_tags = $sql_select_edittags->fetch_array();
				$themeData['edit_tags_id'] = $edit_tags['id'];
				$themeData['edit_tags_name'] = $edit_tags['name'];
				$themeData['edit_tags_footer_description'] = $edit_tags['footer_description'];
				$themeData['edit_tags_show_home'] = $edit_tags['show_home'] ? 'checked' : '';
				$themeData['edit_tags_url'] = siteUrl() . '/tag/' . $edit_tags['url'];
				$themeData['edit_last_id'] = $edit_tags['last_id'];
				$themeData['edit_tags_is_last_rewrite'] = "";
				if($edit_tags['is_last_rewrite'] == '1'){
					$themeData['edit_tags_is_last_rewrite'] = "checked";
				}
				$chatgpt_query = $GameMonetizeConnect->query("SELECT * FROM " . CHATGPT . " WHERE id!=0");
				if ($chatgpt_query && $chatgpt_query->num_rows > 0) {
					$chatgpt_data = $chatgpt_query->fetch_assoc();

					$tagsDescription = $edit_tags['footer_description'];
					$tagsTitle = $edit_tags['name'];

					$tagsRandomGame = '';
					$sqlQuerySimilar = $GameMonetizeConnect->query("SELECT * FROM " . GAMES . " WHERE tags_ids LIKE '%\"{$edit_tags['id']}\"%' AND published='1' ORDER BY RAND() LIMIT 1");
					if ($sqlQuerySimilar->num_rows > 0) {
						while ($similarGames = $sqlQuerySimilar->fetch_array()) {
							$tagsRandomGame = siteUrl() . "/game/" . $similarGames['game_name'];
							$tagsRandomGame = "<a href='{$tagsRandomGame}' target='_self' class='gameKeyword'><bold>{$similarGames['name']}</bold></a>";

						}
					}

					$firstWordGame = $secondWordGame = $firstWordTagTitle = $secondWordTagTitle = "";

					// First word game
					$tagsTitleExploded = explode(" ", $tagsTitle);

					$firstWordTagTitle = substr($tagsTitleExploded[0], 0, 4);
					$sqlGameFirstWord = $GameMonetizeConnect->query("SELECT * FROM " . GAMES . " WHERE name LIKE '%{$firstWordTagTitle}%' AND published='1' LIMIT 1");
					if ($sqlGameFirstWord && $sqlGameFirstWord->num_rows > 0) {
						$firstGameData = $sqlGameFirstWord->fetch_array();
						$firstGameLink = siteUrl() . "/game/" . $firstGameData['game_name'];
						$firstWordGame = "<a href='{$firstGameLink}' target='_self' class='gameKeyword'><bold>{$firstGameData['name']}</bold></a>";
					}

					if ($firstWordGame == "") {
					}

					if ($sqlGameFirstWord->num_rows > 0) {
						while ($similarGames = $sqlQuerySimilar->fetch_array()) {
							$tagsRandomGame = siteUrl() . "/game/" . $similarGames['game_name'];
							$tagsRandomGame = "<a href='{$tagsRandomGame}' target='_self' class='gameKeyword'><bold>{$similarGames['name']}</bold></a>";
						}
					}

					if (count($tagsTitleExploded) > 1) {
						$secondWordTagTitle = $tagsTitleExploded[1];

						$sqlGameSecondWord = $GameMonetizeConnect->query("SELECT * FROM " . GAMES . " WHERE name LIKE '%{$secondWordTagTitle}%' AND published='1' LIMIT 1");

						if ($sqlGameSecondWord && $sqlGameSecondWord->num_rows > 0) {
							$secondGameData = $sqlGameSecondWord->fetch_array();
							$secondGameLink = siteUrl() . "/game/" . $secondGameData['game_name'];
							$secondWordGame = "<a href='{$secondGameLink}' target='_self' class='gameKeyword'><bold>{$secondGameData['name']}</bold></a>";
						}
					}

					// Random games
					$sqlRandomGame = $GameMonetizeConnect->query("SELECT * FROM " . GAMES . " WHERE published='1' ORDER BY RAND() LIMIT 1");

					if ($sqlRandomGame && $sqlRandomGame->num_rows > 0) {
						$randomGameData = $sqlRandomGame->fetch_array();
						$randomGameLink = siteUrl() . "/game/" . $randomGameData['game_name'];
						$randomGame = "<a href='{$randomGameLink}' target='_self' class='gameKeyword'><bold>{$randomGameData['name']}</bold></a>";
					}

					// Random tags
					$sqlRandomTags = $GameMonetizeConnect->query("SELECT * FROM " . TAGS . " ORDER BY RAND() LIMIT 1");

					if ($sqlRandomTags && $sqlRandomTags->num_rows > 0) {
						$randomTagsData = $sqlRandomTags->fetch_array();
						$randomTagsLink = siteUrl() . "/tag/" . $randomTagsData['url'];
						$randomTags = "<a href='{$randomTagsLink}' target='_self' class='gameKeyword'><bold>{$randomTagsData['name']} Games</bold></a>";
						$randomTagText = "{$randomTagsData['name']} Games";
					}

					// Tags with random before and after words
					$beforeWordArray = explode(",", $chatgpt_data['random_words_before_tags']);
					$afterWordArray = explode(",", $chatgpt_data['random_words_after_tags']);

					$allRandomBeforeAfter = [];
					for ($i = 0; $i < 10; $i++) {
						$beforeWord = $beforeWordArray[array_rand($beforeWordArray)] . " ";
						$afterWord = " " . $afterWordArray[array_rand($afterWordArray)];
						$allRandomBeforeAfter[] = $beforeWord . $tagsTitle . $afterWord;
					}
					$allRandomBeforeAfter = implode(",", $allRandomBeforeAfter);

					$chatGptTemplateTags = str_replace(
						[
							"\$description",
							"\$title",
							"\$game_link",
							"\$firstWord",
							"\$secondWord",
							"\$randomSimGames",
							"\$randomSimTags",
							// "\$randomSimTagTxt",
							"\$randomSimTagBeforeAfter",
							// "\$randomSimTag",
							// "\$randomTagLink",
						],
						[
							$tagsDescription,
							$tagsTitle,
							$tagsRandomGame,
							$firstWordGame,
							$secondWordGame,
							$randomGame,
							$randomTags,
							// $randomTagText,
							$allRandomBeforeAfter,
						],
						$chatgpt_data['template_tags']
					);

					$chatGptTemplateTags = str_replace('"', "'", $chatGptTemplateTags);
					$themeData['chat_gpt_template_tags'] = $chatGptTemplateTags;
				}

				$themeData['tags_section_content'] = \GameMonetize\UI::view('admin/sections/view-tags-edit');
			} else {
				$themeData['tags_section_content'] = \GameMonetize\UI::view('welcome/error-section');
			}
		} else {
			$themeData['categories_section_content'] = \GameMonetize\UI::view('welcome/error-section');
		}

		$themeData['page_admin_content'] = \GameMonetize\UI::view('admin/tags');
	} elseif (isset($_GET['section']) && $_GET['section'] == "footerdescription") {
		if (!isset($_GET['action']) || $_GET['action'] == "view") {
			$sqlQuery = $GameMonetizeConnect->query("SELECT * FROM " . FOOTER_DESCRIPTION);
			$footerList = '';
			while ($footer = $sqlQuery->fetch_array()) {
				$themeData['view_footer_description_id'] = $footer['id'];
				$themeData['view_footer_description_name'] = $footer['page_name'];
				// $themeData['view_footer_url_open'] = siteUrl() . "/tag/" . $footer['url'];
				// $themeData['view_footer_button_delete'] = \GameMonetize\UI::view('admin/sections/view-tags-button-delete');

				$footerList .= \GameMonetize\UI::view('admin/sections/view-footer-description-list');
			}
			$themeData['view_footer_description_list'] = $footerList;

			$themeData['footer_description_section_content'] = \GameMonetize\UI::view('admin/sections/view-footer-description-section');
		} elseif (isset($_GET['action']) && $_GET['action'] == "edit" && !empty($_GET['cid'])) {
			$footer_description_id = secureEncode($_GET['cid']);
			$sqlQuery = $GameMonetizeConnect->query("SELECT * FROM " . FOOTER_DESCRIPTION . " WHERE id='{$footer_description_id}'");
			if ($sqlQuery->num_rows == 1) {
				$edit_footer_description = $sqlQuery->fetch_array();
				$themeData['edit_footer_description_id'] = $edit_footer_description['id'];
				$themeData['edit_footer_description_name'] = ucwords($edit_footer_description['page_name']);
				$themeData['edit_footer_description_value'] = $edit_footer_description['description'];
				if ($edit_footer_description['has_content'] == '1') {
					$themeData['edit_footer_description_value_content_value'] = $edit_footer_description['content_value'];
					$themeData['edit_footer_description_content'] = \GameMonetize\UI::view('admin/sections/view-footer-description-edit-content');
				}

				$chatgpt_query = $GameMonetizeConnect->query("SELECT * FROM " . CHATGPT . " WHERE id!=0");
				if ($chatgpt_query && $chatgpt_query->num_rows > 0) {
					$chatgpt_data = $chatgpt_query->fetch_assoc();

					$footerDescription = $edit_footer_description['description'];
					$footerTitle = $edit_footer_description['page_name'];

					$themeData['chat_gpt_template_footer'] = str_replace(
						[
							"\$description",
							"\$title",
						],
						[
							$footerDescription,
							$footerTitle,
						],
						$chatgpt_data['template_footer']
					);
				}


				$themeData['edit_footer_description_description'] = \GameMonetize\UI::view('admin/sections/view-footer-description-edit-description');

				$themeData['footer_description_section_content'] = \GameMonetize\UI::view('admin/sections/view-footer-description-edit');
			} else {
				$themeData['footer_description_section_content'] = \GameMonetize\UI::view('welcome/error-section');
			}
		}

		$themeData['page_admin_content'] = \GameMonetize\UI::view('admin/footerdescription');
	} elseif (isset($_GET['section']) && $_GET['section'] == "blogs") {
		if (!isset($_GET['action']) || $_GET['action'] == "view") {
			$sql_global = $GameMonetizeConnect->query("SELECT * FROM " . BLOGS . " ORDER BY id DESC");
			$gcts_r = '';
			while ($global = $sql_global->fetch_array()) {
				$themeData['view_blog_id'] = $global['id'];
				$themeData['view_blog_name'] = $global['title'];
				$themeData['view_blog_image'] = $global['image_url'];
				$themeData['view_blog_post'] = $global['post'];
				$themeData['view_blog_button_delete'] = \GameMonetize\UI::view('admin/sections/view-blogs-button-delete');

				$gcts_r .= \GameMonetize\UI::view('admin/sections/view-blogs-list');
			}
			$themeData['view_blogs_list'] = $gcts_r;
			$themeData['blogs_section_content'] = \GameMonetize\UI::view('admin/sections/view-blogs-section');
		} elseif (isset($_GET['action']) && $_GET['action'] == "add") {
			$themeData['blogs_section_content'] = \GameMonetize\UI::view('admin/sections/view-blogs-add');
		} elseif (isset($_GET['action']) && $_GET['action'] == "edit" && !empty($_GET['cid'])) {
			$blog_id = secureEncode($_GET['cid']);
			$sql_select_editblog = $GameMonetizeConnect->query("SELECT * FROM " . BLOGS . " WHERE id='{$blog_id}'");
			if ($sql_select_editblog->num_rows == 1) {
				$edit_blog = $sql_select_editblog->fetch_array();
				$themeData['edit_blog_id'] = $edit_blog['id'];
				$themeData['edit_blog_title'] = $edit_blog['title'];
				$themeData['edit_blog_image_url'] = $edit_blog['image_url'];
				$themeData['edit_blog_post'] = $edit_blog['post'];
				$themeData['edit_blog_url'] = siteUrl() . '/blog/' . $edit_blog['url'];

				$themeData['blogs_section_content'] = \GameMonetize\UI::view('admin/sections/view-blogs-edit');
			} else {
				$themeData['blogs_section_content'] = \GameMonetize\UI::view('welcome/error-section');
			}
		} else {
			$themeData['blogs_section_content'] = \GameMonetize\UI::view('welcome/error-section');
		}

		$themeData['page_admin_content'] = \GameMonetize\UI::view('admin/blogs');
	} elseif (isset($_GET['section']) && $_GET['section'] == "users") {
		if (isset($_GET['action']) && $_GET['action'] == "edit" && !empty($_GET['uid'])) {
			$get_user_uid = secureEncode($_GET['uid']);
			if (is_numeric($get_user_uid)) {
				$user_uid_type = "id = " . $get_user_uid;
			} elseif (preg_match('/[A-Za-z0-9_]/', $get_user_uid)) {
				$user_uid_type = "username = '{$get_user_uid}'";
			}
			$get_user_query = $GameMonetizeConnect->query("SELECT * FROM " . ACCOUNTS . " WHERE " . $user_uid_type);

			if ($get_user_query->num_rows > 0) {
				$get_user_account = $get_user_query->fetch_array();
				$get_user_info = getInfo($get_user_account['id']);
				$themeData['user_profile_avatar'] = getAvatar($get_user_account['avatar_id'], $get_user_info['gender']);
				$themeData['user_profile_id'] = $get_user_account['id'];
				$themeData['user_profile_username'] = $get_user_account['username'];
				$themeData['user_profile_name'] = $get_user_account['name'];
				$themeData['user_profile_ip'] = $get_user_account['ip'];
				$themeData['user_profile_email'] = $get_user_account['email'];
				$themeData['user_profile_xp'] = $get_user_account['xp'];
				$themeData['user_profile_about'] = $get_user_info['about'];
				$themeData['user_profile_rank_status_0'] = ($get_user_account['admin'] == 0) ? 'selected' : '';
				$themeData['user_profile_rank_status_1'] = ($get_user_account['admin'] == 1) ? 'selected' : '';
				$themeData['user_profile_gender_status_1'] = ($get_user_info['gender'] == 1) ? 'selected' : '';
				$themeData['user_profile_gender_status_2'] = ($get_user_info['gender'] == 2) ? 'selected' : '';
				$themeData['user_profile_active_status'] = ($get_user_account['active']) ? 'checked' : '';
				$ueLANG_dir = opendir('assets/language/');
				$ueLANG_dr_array = array();
				while (false !== ($file = readdir($ueLANG_dir))) {
					$ueLANG_dr_array[] = $file;
				}
				closedir($ueLANG_dir);
				$gusrlng_r = '';
				foreach ($ueLANG_dr_array as $file) {
					if ($file != "." && $file != ".." && $file != "Thumbs.db" && $file != ".DS_Store" && $file != "images") {
						$val_file = str_replace('.php', '', $file);
						$gusrlng_r .= ($get_user_account['language'] == $val_file) ?
							'<option value="' . $val_file . '" selected>' . $val_file . '</option>' :
							'<option value="' . $val_file . '">' . $val_file . '</option>';
					}
				}
				$themeData['user_profile_language_option'] = $gusrlng_r;

				$themeData['user_section_content'] = \GameMonetize\UI::view('admin/sections/view-user-edit');
			} else {
				$themeData['user_section_content'] = \GameMonetize\UI::view('welcome/error');
			}
		} else {
			$themeData['user_section_content'] = \GameMonetize\UI::view('admin/sections/view-user-search');
		}

		$themeData['page_admin_content'] = \GameMonetize\UI::view('admin/users');
	} elseif (isset($_GET['section']) && $_GET['section'] == "ads") {
		$get_ads_data = $GameMonetizeConnect->query("SELECT * FROM " . ADS . "");
		$get_ads = $get_ads_data->fetch_array();
		$themeData['ads_area_header'] = $get_ads['728x90'];
		$themeData['ads_area_footer'] = $get_ads['300x250'];
		$themeData['ads_area_column_one'] = $get_ads['600x300'];
		$themeData['ads_area_gametop'] = $get_ads['728x90_main'];
		$themeData['ads_area_gamebottom'] = $get_ads['300x250_main'];
		$themeData['ads_area_gameinfo'] = $get_ads['ads_video'];

		$themeData['page_admin_content'] = \GameMonetize\UI::view('admin/ads');
	} elseif (isset($_GET['section']) && $_GET['section'] == "adstxt") {

		if (!defined('ABSPATH')) {
			define('ABSPATH', dirname(dirname(__FILE__)) . '/');
		}
		$url = ABSPATH . 'ads.txt';

		$text = file_get_contents($url);

		$themeData['ads_txt'] = $text;



		$themeData['page_admin_content'] = \GameMonetize\UI::view('admin/adstxt');

	} elseif (isset($_GET['section']) && $_GET['section'] == "chatgpt") {

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {

$llm_provider = isset($_POST['llm_provider']) ? $GameMonetizeConnect->real_escape_string(trim($_POST['llm_provider'])) : 'openai';

$openai_api_key = isset($_POST['openai_api_key']) ? $GameMonetizeConnect->real_escape_string(trim($_POST['openai_api_key'])) : '';
$deepseek_api_key = isset($_POST['deepseek_api_key']) ? $GameMonetizeConnect->real_escape_string(trim($_POST['deepseek_api_key'])) : '';
$mimo_api_key = isset($_POST['mimo_api_key']) ? $GameMonetizeConnect->real_escape_string(trim($_POST['mimo_api_key'])) : '';
$gemini_api_key = isset($_POST['gemini_api_key']) ? $GameMonetizeConnect->real_escape_string(trim($_POST['gemini_api_key'])) : '';

$template_game = isset($_POST['template_game']) ? $GameMonetizeConnect->real_escape_string(trim($_POST['template_game'])) : '';
$template_category = isset($_POST['template_category']) ? $GameMonetizeConnect->real_escape_string(trim($_POST['template_category'])) : '';
$template_tags = isset($_POST['template_tags']) ? $GameMonetizeConnect->real_escape_string(trim($_POST['template_tags'])) : '';
$template_footer = isset($_POST['template_footer']) ? $GameMonetizeConnect->real_escape_string(trim($_POST['template_footer'])) : '';
$template_blog = isset($_POST['template_blog']) ? $GameMonetizeConnect->real_escape_string(trim($_POST['template_blog'])) : '';
$template_blog_title = isset($_POST['template_blog_title']) ? $GameMonetizeConnect->real_escape_string(trim($_POST['template_blog_title'])) : '';
$template_blog_related_box = isset($_POST['template_blog_related_box']) ? $GameMonetizeConnect->real_escape_string(trim($_POST['template_blog_related_box'])) : '';
$random_words_before_tags = isset($_POST['random_words_before_tags']) ? $GameMonetizeConnect->real_escape_string(trim($_POST['random_words_before_tags'])) : '';
$random_words_after_tags = isset($_POST['random_words_after_tags']) ? $GameMonetizeConnect->real_escape_string(trim($_POST['random_words_after_tags'])) : '';
$chatgpt_model = isset($_POST['chatgpt_model']) ? $GameMonetizeConnect->real_escape_string(trim($_POST['chatgpt_model'])) : '';
$maximum_words = isset($_POST['maximum_words']) ? (int) $_POST['maximum_words'] : 0;

$sql = "UPDATE " . CHATGPT . " SET
	llm_provider = '$llm_provider',
	openai_api_key = '$openai_api_key',
	deepseek_api_key = '$deepseek_api_key',
	mimo_api_key = '$mimo_api_key',
	gemini_api_key = '$gemini_api_key',
	template_game = '$template_game',
	template_category = '$template_category',
	template_tags = '$template_tags',
	template_footer = '$template_footer',
	random_words_before_tags = '$random_words_before_tags',
	random_words_after_tags = '$random_words_after_tags',
	chatgpt_model = '$chatgpt_model',
	maximum_words = '$maximum_words'
WHERE id = 1";

$result = $GameMonetizeConnect->query($sql);

if (!$result) {
	die('MYSQL ERROR: ' . $GameMonetizeConnect->error);
}
		}

		$get_chatgpt_data = $GameMonetizeConnect->query("SELECT * FROM " . CHATGPT . " LIMIT 1");
		$get_chatgpt = $get_chatgpt_data->fetch_array();
        $themeData['chatgpt_model_value'] = $get_chatgpt['chatgpt_model'];
		$themeData['openai_api_key'] = $get_chatgpt['openai_api_key'];
		$themeData['deepseek_api_key'] = $get_chatgpt['deepseek_api_key'];
		$themeData['mimo_api_key'] = $get_chatgpt['mimo_api_key'];
		$themeData['gemini_api_key'] = $get_chatgpt['gemini_api_key'];
		$themeData['openrouter_api_key'] = $get_chatgpt['openrouter_api_key'];

		$themeData['chatgpt_template_game'] = $get_chatgpt['template_game'];
		$themeData['chatgpt_template_category'] = $get_chatgpt['template_category'];
		$themeData['chatgpt_template_tags'] = $get_chatgpt['template_tags'];
		$themeData['chatgpt_random_words_before_tags'] = $get_chatgpt['random_words_before_tags'];
		$themeData['chatgpt_random_words_after_tags'] = $get_chatgpt['random_words_after_tags'];
		$themeData['chatgpt_template_footer'] = $get_chatgpt['template_footer'];
		$themeData['chatgpt_template_blog'] = stripslashes($get_chatgpt['template_blog']);
		$themeData['chatgpt_template_blog_tag'] = stripslashes($get_chatgpt['template_blog_tag']);
		$themeData['chatgpt_template_blog_title'] = stripslashes($get_chatgpt['template_blog_title']);
		$themeData['chatgpt_template_blog_related_box'] = stripslashes($get_chatgpt['template_blog_related_box']);
		$themeData['chatgpt_maximum_words'] = $get_chatgpt['maximum_words'];
		$themeData['rewrite_old_games_limit'] = $get_chatgpt['rewrite_old_games_limit'];
		$themeData['chatgpt_model_value'] = $get_chatgpt['chatgpt_model'];

		$currentProvider = !empty($get_chatgpt['llm_provider']) ? $get_chatgpt['llm_provider'] : 'cmsai';
		$currentModel = !empty($get_chatgpt['chatgpt_model']) ? $get_chatgpt['chatgpt_model'] : 'cms-ai-free';

		$themeData['llm_provider_options'] = '
					<option value="cmsai"' . ($currentProvider == 'cmsai' ? ' selected' : '') . '>CMS AI Free</option>
					<option value="openai"' . ($currentProvider == 'openai' ? ' selected' : '') . '>OpenAI</option>
					<option value="deepseek"' . ($currentProvider == 'deepseek' ? ' selected' : '') . '>DeepSeek</option>
					<option value="mimo"' . ($currentProvider == 'mimo' ? ' selected' : '') . '>MiMo</option>
					<option value="gemini"' . ($currentProvider == 'gemini' ? ' selected' : '') . '>Gemini</option>
					<option value="openrouter"' . ($currentProvider == 'openrouter' ? ' selected' : '') . '>OpenRouter</option>
				';

		$chatgpt_models = "<option value=\"" . htmlspecialchars($currentModel, ENT_QUOTES) . "\" selected>" . htmlspecialchars($currentModel) . "</option>";
		$themeData['chatgpt_models'] = $chatgpt_models;

		$themeData['page_admin_content'] = \GameMonetize\UI::view('admin/chatgpt');

	} elseif (isset($_GET['section']) && $_GET['section'] == "report") {


    // 1. POST DELETE LOGIC (Stays on the same page)
    if (isset($_POST['delete_report_id'])) {
        $del_id = (int)$_POST['delete_report_id'];
        $GameMonetizeConnect->query("DELETE FROM gm_reports WHERE id = $del_id");
    }

    // 2. FETCH DATA
    $reports_list = '';
    $get_reports = $GameMonetizeConnect->query("SELECT * FROM gm_reports ORDER BY Id DESC");

    if ($get_reports && $get_reports->num_rows > 0) {
        while ($report = $get_reports->fetch_assoc()) {
            $g_id = $report['game_id'];

            // Fetch game slug from gm_games table for the Play button
            $game_query = $GameMonetizeConnect->query("SELECT game_name FROM " . GAMES . " WHERE game_id = '$g_id' LIMIT 1");
            $game_data = $game_query->fetch_assoc();
            $game_slug = ($game_data) ? $game_data['game_name'] : '';

            // Generate URLs
            $edit_url = siteUrl() . '/admin/games/edit/' . $g_id;
            $play_url = siteUrl() . '/game/' . $game_slug;

            $reports_list .= '
            <tr>
                <td style="padding: 15px 12px; border-bottom: 1px solid #333; color: #ced4da;"><strong>' . htmlspecialchars($report['game_name']) . '</strong></td>
                <td style="padding: 15px 12px; border-bottom: 1px solid #333; color: #ced4da;">' . htmlspecialchars($report['user_email']) . '</td>
                <td style="padding: 15px 12px; border-bottom: 1px solid #333;">
                    <span style="background: #383f47; padding: 4px 8px; border-radius: 4px; color: #58a6ff; font-size: 0.8rem;">
                        ' . htmlspecialchars($report['report_type']) . '
                    </span>
                </td>
                <td style="padding: 15px 12px; border-bottom: 1px solid #333; color: #ced4da;">' . nl2br(htmlspecialchars($report['message'])) . '</td>
                <td style="padding: 15px 12px; border-bottom: 1px solid #333; color: #ced4da;">' . htmlspecialchars($report['created_at']) . '</td>
                <td style="padding: 15px 12px; border-bottom: 1px solid #333; text-align: right;">
                    <div style="display: flex; gap: 8px; justify-content: flex-end; align-items: center;">
                        
                        <a href="' . $edit_url . '" class="spf-link" style="background: #5cb85c; color: white; padding: 5px 10px; border-radius: 4px; text-decoration: none;"><i class="fa fa-pencil"></i></a>
                        
                        <a href="' . $play_url . '" target="_blank" style="background: #337ab7; color: white; padding: 5px 10px; border-radius: 4px; text-decoration: none;"><i class="fa fa-gamepad"></i></a>
                        
                        <form method="POST" style="display:inline;" onsubmit="return confirm(\'Permanently delete this report?\')">
                            <input type="hidden" name="delete_report_id" value="' . $report['id'] . '">
                            <button type="submit" style="background:transparent; border:none; color:#888; font-size:1.2rem; cursor:pointer; padding:0;">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>

                    </div>
                </td>
            </tr>';
        }
    } else {
        $reports_list = '<tr><td colspan="6" style="text-align:center; padding: 40px; color: #888;">No reports found.</td></tr>';
    }

    $themeData['reports_list'] = $reports_list;
    $themeData['nav_menu_report'] = 'active';
    $themeData['page_admin_content'] = \GameMonetize\UI::view('admin/report');
	
	
	
	
		} elseif (isset($_GET['section']) && $_GET['section'] == "gamedescription") {
		$spreadsheet = new Spreadsheet();
		$activeWorksheet = $spreadsheet->getActiveSheet();

		$rowLength = 0;
		$row = 1;
		$isMultiple = false;
		$allXlsxContent = [];

		// Game data
		$gameData = $GameMonetizeConnect->query("SELECT * FROM " . GAMES);
		while ($game = $gameData->fetch_array()) {
			$pattern = "/\b\w+'\w+\b/";
			$value = preg_replace($pattern, "", $game['description']);
			$value = str_replace('"', "'", $value);
			$value = strip_tags($value, ["a", "b", "p"]);
			$value = replaceOriginalLinks($value);
			if (strlen($value) > 0) {
				$key = $game['game_id'];
				$activeWorksheet->setCellValue('A' . $row, $key);
				$activeWorksheet->setCellValue('B' . $row, $value);
				$rowLength = strlen($key) + strlen($value);
				countRow($row, $spreadsheet, $activeWorksheet, $isMultiple, $allXlsxContent, $rowLength);
			}
		}

		if ($isMultiple) {
			$allXlsxContent[] = $this->generateAndSaveXLSX($spreadsheet);
			// Create a ZIP file containing all XLSX files
			$zip = new ZipArchive();
			$zipFileName = 'Unstranslated.zip';
			$zip->open($zipFileName, ZipArchive::CREATE);
			foreach ($allXlsxContent as $index => $content) {
				$zip->addFromString('Unstranslated' . ($index + 1) . ".xlsx", $content);
			}
			// $zip->addFromString('File2.xlsx', $content2);
			// $zip->addFromString('File3.xlsx', $content3);
			$zip->close();

			// Set the appropriate headers to force a download
			header('Content-Type: application/zip');
			header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
			header('Content-Length: ' . filesize($zipFileName));

			// Send the ZIP file to the browser
			readfile($zipFileName);

			// Clean up (delete) the temporary ZIP file from the server
			unlink($zipFileName);
		} else {
			generateAndDownloadXLSX($spreadsheet, "Untranslated");
		}

		exit;
	} elseif (isset($_GET['section']) && $_GET['section'] == "links") {
		// error_reporting(-1);
		if (!isset($_GET['action']) || $_GET['action'] == "view") {
			$get_links_data = $GameMonetizeConnect->query("SELECT * FROM " . LINKS);
			$linkList = '';
			while ($link = $get_links_data->fetch_array()) {
				$themeData['view_links_id'] = $link['id'];  
				$themeData['view_links_cron_button'] = ''; 

				if (
					in_array($link['name'], ['autopost', 'autopost_old_games', 'autopost_tags'])
					&& $link['is_active'] == 1
					&& !empty($link['url'])
				) {

				 $themeData['view_links_cron_button'] = '';

					if (
						in_array($link['name'], ['autopost', 'autopost_old_games', 'autopost_tags'])
						&& $link['is_active'] == 1
						&& !empty($link['url'])
					) {
						$linkPath = 'links';

						if ($link['name'] == 'autopost_old_games') {
							$linkPath = 'links_old_games';
						} elseif ($link['name'] == 'autopost_tags') {
							$linkPath = 'links_tags';
						}

						$targetUrl = siteUrl() . '/' . $linkPath . '/' . $link['url'];
						$cronUrl = 'https://www.freecronjob.com.es/schedule-url.php?url=' . urlencode($targetUrl);

						$themeData['view_links_cron_button'] = '
							<a href="' . $cronUrl . '" target="_blank" class="btn-p btn-small btn-p1">
								Add Cron
							</a>
						';
					}

					$themeData['view_links_cron_button'] = '
						<a href="' . $cronUrl . '" target="_blank" class="btn-p btn-small btn-p1">
							Add Cron
						</a>
					';
				}
				$isActive = $link['is_active'];
				$linkName = str_replace("autopost", "links", $link['name']);
				if ($isActive) {
					$linkUrl = " -> <a href='" . siteUrl() . "/" . $linkName . "/" . $link['url'] . "' target='_blank'>" . $link['url'] . "</a>";
					$themeData['view_links_enable_disable'] = 'disable';
					$themeData['view_links_icon'] = 'fa-close';
					$themeData['view_links_button_class'] = 'btn-danger';
				} else {
					$linkUrl = " (not active)";
					$themeData['view_links_enable_disable'] = 'enable';
					$themeData['view_links_button_class'] = 'btn-p2';
					$themeData['view_links_icon'] = 'fa-check-circle';
				}

				$themeData['view_links_name'] = $link['name'] . $linkUrl;

				$linkList .= \GameMonetize\UI::view('admin/sections/view-links-list');
			}
			$themeData['view_links_list'] = $linkList;

			// Rewrite method
			$methods = ['off', 'google', 'spinner', 'chatgpt'];
			$get_links_data = $GameMonetizeConnect->query("SELECT * FROM " . LINKS . " WHERE name='autopost'");
			$autopostLinkData = $get_links_data->fetch_array();
			$rewriteMethodOptions = "";
			foreach ($methods as $method) {
				$selected = "";
				if ($autopostLinkData['rewrite_method'] == $method) {
					$selected = "selected";
				}

				$rewriteMethodOptions .= "<option value='{$method}' $selected>" . ucfirst($method) . "</option>";
			}
			$themeData['link_rewrite_method'] = $rewriteMethodOptions;
			$themeData['google_translate_language'] = $autopostLinkData['google_translate_language'];
			$themeData['localization_admin_hidden'] = 'hidden';
			$themeData['links_admin_hidden'] = '';

			$themeData['links_section_content'] = \GameMonetize\UI::view('admin/sections/view-links-section');
		}

		$themeData['page_admin_content'] = \GameMonetize\UI::view('admin/links');
	} elseif (isset($_GET['section'])
		&& $_GET['section'] == "language"
		&& function_exists('gps_other_fixes_localization_admin_prepare')) {
		$themeData['localization_admin_hidden'] = '';
		$themeData['links_admin_hidden'] = 'hidden';
		gps_other_fixes_localization_admin_prepare($themeData, $GameMonetizeConnect);
		$themeData['language_section_content'] = \GameMonetize\UI::view('admin/sections/view-links-section');
		$themeData['page_admin_content'] = \GameMonetize\UI::view('admin/language');
	} elseif (isset($_GET['section']) && $_GET['section'] == "sliders") {
		if (!isset($_GET['action']) || $_GET['action'] == "view") {
			$sql_item = $GameMonetizeConnect->query("SELECT * FROM " . SLIDERS . " ORDER BY ordering ASC");
			$lists = '';
			while ($item = $sql_item->fetch_array()) {
				$category_tags_name = "-";
				if ($item['type'] == 'tags') {
					$keyword_item = $GameMonetizeConnect->query("SELECT * FROM " . TAGS . " WHERE id = {$item['category_tags_id']}");
					if ($keyword_item && $keyword_item->num_rows > 0) {
						$category_tags = $keyword_item->fetch_array();
						$category_tags_name = $category_tags['name'];
					}
				}

				if ($item['type'] == 'category') {
					$category_item = $GameMonetizeConnect->query("SELECT * FROM " . CATEGORIES . " WHERE id = {$item['category_tags_id']}");
					if ($category_item && $category_item->num_rows > 0) {
						$category_tags = $category_item->fetch_array();
						$category_tags_name = $category_tags['name'];
					}
				}

				$themeData['view_slider_id'] = $item['id'];
				$themeData['view_slider_type'] = $item['type'];
				$themeData['view_slider_category_tags'] = $category_tags_name;
				$themeData['view_slider_ordering'] = $item['ordering'];
				$themeData['view_slider_button_delete'] = \GameMonetize\UI::view('admin/sections/sliders/view-button-delete');

				$lists .= \GameMonetize\UI::view('admin/sections/sliders/view-list');
			}
			$themeData['view_sliders_list'] = $lists;
			$themeData['sliders_section_content'] = \GameMonetize\UI::view('admin/sections/sliders/view-section');
		} elseif (isset($_GET['action']) && $_GET['action'] == "add") {
			$sql_item = $GameMonetizeConnect->query("SELECT * FROM " . SLIDERS . " ORDER BY ordering ASC");
			$lists = '';
			while ($item = $sql_item->fetch_array()) {
				$category_tags_name = "-";
				if ($item['type'] == 'tags') {
					$keyword_item = $GameMonetizeConnect->query("SELECT * FROM " . TAGS . " WHERE id = {$item['category_tags_id']}");
					if ($keyword_item && $keyword_item->num_rows > 0) {
						$category_tags = $keyword_item->fetch_array();
						$category_tags_name = $category_tags['name'];
					}
				}

				if ($item['type'] == 'category') {
					$category_item = $GameMonetizeConnect->query("SELECT * FROM " . CATEGORIES . " WHERE id = {$item['category_tags_id']}");
					if ($category_item && $category_item->num_rows > 0) {
						$category_tags = $category_item->fetch_array();
						$category_tags_name = $category_tags['name'];
					}
				}

				$themeData['view_slider_id'] = $item['id'];
				$themeData['view_slider_type'] = $item['type'];
				$themeData['view_slider_category_tags'] = $category_tags_name;
				$themeData['view_slider_ordering'] = $item['ordering'];
				$themeData['view_slider_button_delete'] = \GameMonetize\UI::view('admin/sections/sliders/view-button-delete');

				$lists .= \GameMonetize\UI::view('admin/sections/sliders/view-list');
			}
			$themeData['view_sliders_list'] = $lists;

			$slider_types = "";
			foreach (SLIDERS_TYPE as $type) {
				$slider_types .= "<option value='{$type}'>{$type}</option>";
			}

			$sql_item = $GameMonetizeConnect->query("SELECT id, name, 'tags' AS category_tags FROM " . TAGS . " UNION " . "SELECT id,name, 'category' as category_tags FROM " . CATEGORIES);

			$slider_category_tags = "<option value='none'>none</option>";
			if ($sql_item && $sql_item->num_rows > 0) {
				while ($item = $sql_item->fetch_array()) {
					$slider_category_tags .= "<option value='{$item['category_tags']}-{$item['id']}'>{$item['category_tags']}: {$item['name']}</option>";
				}
			}

			$themeData['slider_types'] = $slider_types;
			$themeData['slider_category_tags'] = $slider_category_tags;
			$themeData['sliders_section_content'] = \GameMonetize\UI::view('admin/sections/sliders/view-add');
		} elseif (isset($_GET['action']) && $_GET['action'] == "edit" && !empty($_GET['cid'])) {
			$slider_id = secureEncode($_GET['cid']);
			$sql_item = $GameMonetizeConnect->query("SELECT * FROM " . SLIDERS . " WHERE id='{$slider_id}'");
			if ($sql_item->num_rows == 1) {
				$sliderData = $sql_item->fetch_array();
				$slider_types = "";
				foreach (SLIDERS_TYPE as $type) {
					$selected = "";
					if ($type == $sliderData['type']) {
						$selected = "selected";
					}
					$slider_types .= "<option value='{$type}' {$selected}>{$type}</option>";
				}

				$sql_item = $GameMonetizeConnect->query("SELECT id, name, 'tags' AS category_tags FROM " . TAGS . " UNION " . "SELECT id, name, 'category' as category_tags FROM " . CATEGORIES);

				$slider_category_tags = "<option value='none'>none</option>";
				if ($sql_item && $sql_item->num_rows > 0) {
					while ($item = $sql_item->fetch_array()) {
						$selected = "";
						if ($sliderData['type'] == $item['category_tags'] && $item['id'] == $sliderData['category_tags_id']) {
							$selected = "selected";
						}

						$slider_category_tags .= "<option value='{$item['category_tags']}-{$item['id']}' {$selected}>{$item['category_tags']}: {$item['name']}</option>";
					}
				}

				$themeData['slider_id'] = $sliderData['id'];
				$themeData['slider_ordering'] = $sliderData['ordering'];
				$themeData['slider_types'] = $slider_types;
				$themeData['slider_category_tags'] = $slider_category_tags;
				$themeData['sliders_section_content'] = \GameMonetize\UI::view('admin/sections/sliders/view-edit');
			} else {
				$themeData['sliders_section_content'] = \GameMonetize\UI::view('welcome/error-section');
			}
		} else {
			$themeData['sliders_section_content'] = \GameMonetize\UI::view('welcome/error-section');
		}

		$themeData['page_admin_content'] = \GameMonetize\UI::view('admin/sliders');
	} elseif (isset($_GET['section']) && $_GET['section'] == "sidebar") {
		if (!isset($_GET['action']) || $_GET['action'] == "view") {
			$sql_item = $GameMonetizeConnect->query("SELECT * FROM " . SIDEBAR . " ORDER BY ordering+0 ASC");
			$lists = '';
			while ($item = $sql_item->fetch_array()) {
				$category_tags_name = "-";
				if ($item['type'] == 'tags') {
					$keyword_item = $GameMonetizeConnect->query("SELECT * FROM " . TAGS . " WHERE id = {$item['category_tags_id']}");
					if ($keyword_item && $keyword_item->num_rows > 0) {
						$category_tags = $keyword_item->fetch_array();
						$category_tags_name = $category_tags['name'];
					}
				}

				if ($item['type'] == 'category') {
					$category_item = $GameMonetizeConnect->query("SELECT * FROM " . CATEGORIES . " WHERE id = {$item['category_tags_id']}");
					if ($category_item && $category_item->num_rows > 0) {
						$category_tags = $category_item->fetch_array();
						$category_tags_name = $category_tags['name'];
					}
				}
				$themeData['view_sidebar_id'] = $item['id'];
				$themeData['view_sidebar_name'] = $item['name'];
				$themeData['view_sidebar_type'] = $item['type'];
				$themeData['view_sidebar_category_tags'] = $category_tags_name;
				$themeData['view_sidebar_custom_link'] = $item['custom_link'];
				$themeData['view_sidebar_icon'] = $item['icon'];
				$themeData['view_sidebar_ordering'] = $item['ordering'];
				$themeData['view_sidebar_button_delete'] = \GameMonetize\UI::view('admin/sections/sidebar/view-button-delete');

				$lists .= \GameMonetize\UI::view('admin/sections/sidebar/view-list');
			}
			$themeData['view_sidebar_list'] = $lists;

			// Sidebar settings
			$settingsData = $GameMonetizeConnect->query("SELECT * FROM " . SETTING . " LIMIT 1");
			$settingsData = $settingsData->fetch_array();

			if ($settingsData['is_sidebar_enabled']) {
				$themeData['view_sidebar_enable_disable_icon'] = "xmark";
				$themeData['view_sidebar_enable_disable_button'] = "btn-p3";
				$themeData['view_sidebar_enable_disable_text'] = "Disable Sidebar";
			} else {
				$themeData['view_sidebar_enable_disable_icon'] = "check";
				$themeData['view_sidebar_enable_disable_button'] = "btn-p2";
				$themeData['view_sidebar_enable_disable_text'] = "Enable Sidebar";
			}
			$themeData['sidebar_section_content'] = \GameMonetize\UI::view('admin/sections/sidebar/view-section');
		} elseif (isset($_GET['action']) && $_GET['action'] == "add") {
			$sql_item = $GameMonetizeConnect->query("SELECT * FROM " . SIDEBAR . " ORDER BY ordering+0 ASC");
			$lists = '';
			while ($item = $sql_item->fetch_array()) {
				$category_tags_name = "-";
				if ($item['type'] == 'tags') {
					$keyword_item = $GameMonetizeConnect->query("SELECT * FROM " . TAGS . " WHERE id = {$item['category_tags_id']}");
					if ($keyword_item && $keyword_item->num_rows > 0) {
						$category_tags = $keyword_item->fetch_array();
						$category_tags_name = $category_tags['name'];
					}
				}

				if ($item['type'] == 'category') {
					$category_item = $GameMonetizeConnect->query("SELECT * FROM " . CATEGORIES . " WHERE id = {$item['category_tags_id']}");
					if ($category_item && $category_item->num_rows > 0) {
						$category_tags = $category_item->fetch_array();
						$category_tags_name = $category_tags['name'];
					}
				}
				$themeData['view_sidebar_id'] = $item['id'];
				$themeData['view_sidebar_name'] = $item['name'];
				$themeData['view_sidebar_type'] = $item['type'];
				$themeData['view_sidebar_category_tags'] = $category_tags_name;
				$themeData['view_sidebar_custom_link'] = $item['custom_link'];
				$themeData['view_sidebar_icon'] = $item['icon'];
				$themeData['view_sidebar_ordering'] = $item['ordering'];
				$themeData['view_sidebar_button_delete'] = \GameMonetize\UI::view('admin/sections/sidebar/view-button-delete');

				$lists .= \GameMonetize\UI::view('admin/sections/sidebar/view-list');
			}
			$themeData['view_sidebar_list'] = $lists;

			$sidebar_types = "";
			foreach (SIDEBAR_TYPE as $type) {
				$sidebar_types .= "<option value='{$type}'>{$type}</option>";
			}

			$sql_item = $GameMonetizeConnect->query("SELECT id, name, 'tags' AS category_tags FROM " . TAGS . " UNION " . "SELECT id,name, 'category' as category_tags FROM " . CATEGORIES);

			$sidebar_category_tags = "<option value='none'>none</option>";
			if ($sql_item && $sql_item->num_rows > 0) {
				while ($item = $sql_item->fetch_array()) {
					$sidebar_category_tags .= "<option value='{$item['category_tags']}-{$item['id']}'>{$item['category_tags']}: {$item['name']}</option>";
				}
			}

			$themeData['sidebar_types'] = $sidebar_types;
			$themeData['sidebar_category_tags'] = $sidebar_category_tags;
			$themeData['sidebar_section_content'] = \GameMonetize\UI::view('admin/sections/sidebar/view-add');
		} elseif (isset($_GET['action']) && $_GET['action'] == "edit" && !empty($_GET['cid'])) {
			$sidebar_id = secureEncode($_GET['cid']);
			$sql_item = $GameMonetizeConnect->query("SELECT * FROM " . SIDEBAR . " WHERE id='{$sidebar_id}'");
			if ($sql_item->num_rows == 1) {
				$sidebarData = $sql_item->fetch_array();

				$sidebar_types = "";
				foreach (SIDEBAR_TYPE as $type) {
					$selected = "";
					if ($type == $sidebarData['type']) {
						$selected = "selected";
					}
					$sidebar_types .= "<option value='{$type}' {$selected}>{$type}</option>";
				}

				$sql_item = $GameMonetizeConnect->query("SELECT id, name, 'tags' AS category_tags FROM " . TAGS . " UNION " . "SELECT id,name, 'category' as category_tags FROM " . CATEGORIES);

				$sidebar_category_tags = "<option value='none'>none</option>";
				if ($sql_item && $sql_item->num_rows > 0) {
					while ($item = $sql_item->fetch_array()) {
						$selected = "";
						$isSameType = $sidebarData['type'] == $item['category_tags'];
						$isSameCategoryTags = $item['id'] == $sidebarData['category_tags_id'];
						if ($isSameType && $isSameCategoryTags) {
							$selected = "selected";
						}
						$sidebar_category_tags .= "<option value='{$item['category_tags']}-{$item['id']}' {$selected}>{$item['category_tags']}: {$item['name']}</option>";
					}
				}
				$themeData['sidebar_types'] = $sidebar_types;
				$themeData['sidebar_category_tags'] = $sidebar_category_tags;

				$themeData['view_sidebar_id'] = $sidebarData['id'];
				$themeData['view_sidebar_custom_link'] = $sidebarData['custom_link'];
				$themeData['view_sidebar_name'] = $sidebarData['name'];
				$themeData['view_sidebar_icon'] = $sidebarData['icon'];
				$themeData['view_sidebar_ordering'] = $sidebarData['ordering'];

				$themeData['sidebar_section_content'] = \GameMonetize\UI::view('admin/sections/sidebar/view-edit');
			} else {
				$themeData['sidebar_section_content'] = \GameMonetize\UI::view('welcome/error-section');
			}
		} else {
			$themeData['sidebar_section_content'] = \GameMonetize\UI::view('welcome/error-section');
		}

		$themeData['page_admin_content'] = \GameMonetize\UI::view('admin/sidebar');
	} else {
		$themeData['page_admin_content'] = \GameMonetize\UI::view('welcome/error-section');
	}

	$themeData['page_content'] = \GameMonetize\UI::view('admin/content');
}

function replaceOriginalLinks($text)
{
	$text = str_replace('"', "'", $text);
	$pattern = "/<a.*?href='([^']+)'[^>]*>([^<]*||.+?)<\/a>/";
	if (preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)) {
		foreach ($matches as $match) {
			$linkText = $match[2];
			$realLinkHref = $linkHref = $match[1];
			$hostName = explode(".", $_SERVER['HTTP_HOST']);
			$hostName = $hostName[1];
			$linkHref = str_replace(".", "", $linkHref);
			$linkHref = str_replace("%20", "-", $linkHref);
			$linkHref = rtrim($linkHref, '/');
			if (strpos($linkHref, "/category")) {
				// get category
				$lastLinkHref = end(explode("/", $linkHref));
				$itemId = getItemIdByUrl($lastLinkHref, CATEGORIES, "category_pilot");
				$uniqueCode = "3";
			} elseif (strpos($linkHref, "/tag")) {
				// get keyword
				$lastLinkHref = end(explode("/", $linkHref));
				$itemId = getItemIdByUrl($lastLinkHref, TAGS, "url");
				$uniqueCode = "2";
			} elseif (!strpos($realLinkHref, $hostName) && !strpos($linkHref, "/game/")) {
				// External links
				$itemId = getItemIdByUrl($realLinkHref, EXTERNAL_LINKS, "url", $linkText);
				$uniqueCode = "4";
			} else {
				$explodedHref = explode("/", $linkHref);
				$lastLinkHref = end($explodedHref);
				$itemId = getItemIdByUrl($lastLinkHref, GAMES, "game_name");
				$uniqueCode = "1";
			}

			if (!is_null($itemId)) {
				// Replace the link with the placeholder
				$placeholder = "{{" . $uniqueCode . "-" . $itemId . "}}";
				$text = str_replace($match[0], $placeholder, $text);
			}
		}
	}

	return $text;
}

function countRow(&$row, &$spreadsheet, &$activeWorksheet, &$isMultiple, &$allXlsxContent, &$rowLength)
{
	$row++;
	global $totalRowLength;
	$totalRowLength += $rowLength;

	$rowPerPage = 6000000;
	$rowPerPage = $rowPerPage == 0 ? 3500000 : $rowPerPage;

	// * 1kb = 3,500 char, 1mb = 3,500,000 char (approx)
	// $oneMbChar = 3500000;
	if ($totalRowLength > $rowPerPage) {
		$isMultiple = true;
		$allXlsxContent[] = generateAndSaveXLSX($spreadsheet);

		$spreadsheet = new Spreadsheet();
		$activeWorksheet = $spreadsheet->getActiveSheet();
		$row = 0;
		$totalRowLength = 0;
	}
}

function generateAndSaveXLSX($spreadsheet)
{
	ob_start();
	$writer = new Xlsx($spreadsheet);
	$writer->save('php://output');
	return ob_get_clean();
}

function generateAndDownloadXLSX($spreadsheet, $filename)
{
	// Set the appropriate headers to force a download
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
	header('Cache-Control: max-age=0');

	$writer = new Xlsx($spreadsheet);
	$writer->save('php://output');
	exit;
}

function getItemIdByUrl($lastLinkHref, $table, $field, $text = "")
{
	global $GameMonetizeConnect;
	$itemData = $GameMonetizeConnect->query("SELECT * FROM " . $table . " WHERE " . $field . " = '{$lastLinkHref}' LIMIT 1");
	if (
		$itemData &&
		$itemData->num_rows > 0
	) {
		$itemData = $itemData->fetch_array();
		if ($table == GAMES) {
			return $itemData['game_id'];
		}
		return $itemData['id'];
	}

	if ($table == 'external_links' && $itemData) {
		$itemData = $GameMonetizeConnect->query("INSERT INTO " . EXTERNAL_LINKS . " (url, title) VALUES ('{$lastLinkHref}', '{$text}')");
		if ($itemData) {
			return $GameMonetizeConnect->insert_id;
		}
	}

	return null;
}
