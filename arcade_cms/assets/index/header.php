<?php

if (is_logged()) {
	$themeData['header_user_avatar'] = getAvatar($userData['avatar_id'], $userData['gender'], 'thumb');
	$themeData['user_panel_xp'] = numberFormat($userData['xp']);
	$themeData['csrf_logout_token'] = \GameMonetize\CSRF::set(3, 3600);
}
$themeData['website_name'] = $_SERVER['HTTP_HOST'];
$themeData['site_name'] = $config['site_name'];
$date =  date('Ymdms');
$date = strtotime($date);
$themeData['cms'] = "<script src='https://api.gamemonetize.com/cms.js?" . $date . "'></script>";
$themeData['cookie'] = ($config['ads_status']) ? '<script type="text/javascript">
window.cookieconsent_options = { "message":"This website uses cookies to ensure you get the best experience on our website.","dismiss":"Got it!","learnMore":"Learn more","link":"/privacy","target":"_blank","theme":"dark-bottom" };
</script>
<script type="text/javascript" src="' . $config['theme_path'] . '/js/cookieconsent.min.js"></script>' : '';

$themeData['header_class_access_menu'] = (is_logged()) ? '_rP5' : '';

$themeData['header_panel_menu_admin'] = (is_logged() && $userData['admin'] == 1) ? \GameMonetize\UI::view('header/header_panel_menu_admin') : '';

$isPokiPublicTheme = in_array((string)($config['site_theme'] ?? ''), ['poki-like', 'poki-pro'], true)
	|| (function_exists('gps_theme_is') && (gps_theme_is('poki-like') || gps_theme_is('poki-pro')));

$publicProBootstrap = ABSPATH . 'assets/includes/license/bootstrap.php';
if (is_file($publicProBootstrap)) {
	require_once $publicProBootstrap;
}
$themeData['public_pro_badge'] = '';
$themeData['public_pro_icon'] = '';
$themeData['public_pro_menu_label'] = '';
$themeData['public_pro_cta'] = '';
$themeData['public_pro_footer_cta'] = '';
$themeData['public_pro_sidebar_cta'] = '';
if (function_exists('gps_license_cached_bound_claims')) {
	$publicProClaims = gps_license_cached_bound_claims();
	$publicProPlan = strtolower(trim((string)($publicProClaims['plan'] ?? '')));
	$publicProStatus = strtolower(trim((string)($publicProClaims['status'] ?? '')));
	$publicProExpiryRaw = trim((string)($publicProClaims['license_expires_at'] ?? ''));
	$publicProExpiry = $publicProExpiryRaw !== '' ? strtotime($publicProExpiryRaw . ' UTC') : false;
	$publicProActive = $publicProClaims !== []
		&& $publicProPlan !== 'free'
		&& ($publicProStatus === '' || $publicProStatus === 'active')
		&& ($publicProExpiry === false || $publicProExpiry >= time());
	if ($publicProActive) {
		$themeData['public_pro_badge'] = '<span class="gps-public-pro-badge" title="GamePortalScript PRO" aria-label="GamePortalScript PRO" style="display:inline-flex;align-items:center;gap:4px;height:22px;box-sizing:border-box;padding:0 7px;border:1px solid rgba(255,211,77,.82);border-radius:999px;background:linear-gradient(135deg,#3b2f0c,#17150c);color:#ffd34d;box-shadow:0 2px 8px rgba(0,0,0,.28);font:800 10px/1 Arial,sans-serif;letter-spacing:.05em;white-space:nowrap;"><svg viewBox="0 0 24 24" width="13" height="13" aria-hidden="true" focusable="false" style="display:block;fill:currentColor;"><path d="M3 18h18l-1.4-10.2-4.4 3.1L12 4 8.8 10.9 4.4 7.8 3 18Zm1.8 2h14.4v-1.4H4.8V20Z"/></svg><span>PRO</span></span>';
		$themeData['public_pro_icon'] = '<svg class="gps-public-pro-icon" viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false" style="display:block;fill:#ffd34d;filter:drop-shadow(0 1px 2px rgba(0,0,0,.45));"><path d="M3 18h18l-1.4-10.2-4.4 3.1L12 4 8.8 10.9 4.4 7.8 3 18Zm1.8 2h14.4v-1.4H4.8V20Z"/></svg>';
		$themeData['public_pro_menu_label'] = '<span class="gps-public-pro-menu-label" aria-hidden="true" style="color:#ffd34d;font:700 9px/1 Arial,sans-serif;letter-spacing:.1em;opacity:.82;">PRO</span>';
	} else {
		$publicProCta = '<a class="gps-public-pro-cta" href="https://gameportalscript.com/pro.php" target="_blank" rel="noopener" title="Get GamePortalScript PRO CMS" style="display:inline-flex;align-items:center;gap:4px;color:inherit;font:inherit;letter-spacing:normal;text-decoration:none;white-space:nowrap;"><svg viewBox="0 0 24 24" width="13" height="13" aria-hidden="true" focusable="false" style="display:block;flex:0 0 auto;fill:#ffd34d;"><path d="M3 18h18l-1.4-10.2-4.4 3.1L12 4 8.8 10.9 4.4 7.8 3 18Zm1.8 2h14.4v-1.4H4.8V20Z"/></svg><span>Get PRO CMS</span></a>';
		$themeData['public_pro_badge'] = $publicProCta;
		$themeData['public_pro_cta'] = $publicProCta;
		$themeData['public_pro_footer_cta'] = $publicProCta . '<span class="gps-public-pro-footer-separator" aria-hidden="true"> | </span>';
		$themeData['public_pro_sidebar_cta'] = '<a class="gps-public-pro-sidebar-cta border-transparent border-l-4 flex h-[34px] items-center text-[15px] font-semibold no-underline hover:text-opacity-65 hover:[&amp;>div]:pl-2 [&amp;>div]:mr-2 hover:[&amp;>div]:mr-0" href="https://gameportalscript.com/pro.php" target="_blank" rel="noopener" title="Get GamePortalScript PRO CMS" style="color:#ffd34d;"><span class="sidebar-admin-icon-wrap"><i class="fa-solid fa-crown sidebar-admin-icon" style="color:#a970ff;"></i></span><div class="relative transition-all duration-300">Get PRO CMS</div></a>';
	}
}


// Public pages never expose vendor PRO promotion links.
$themeData['public_pro_badge'] = '';
$themeData['public_pro_icon'] = '';
$themeData['public_pro_menu_label'] = '';
$themeData['public_pro_cta'] = '';
$themeData['public_pro_footer_cta'] = '';
$themeData['public_pro_sidebar_cta'] = '';

if ($_GET['p'] != 'login') {
	if (
		$userData['admin'] == 0
		|| $_GET['p'] == 'play'
		|| $_GET['p'] == 'new-games'
		|| $_GET['p'] == 'search'
		|| $_GET['p'] == 'terms'
		|| $_GET['p'] == 'privacy'
		|| $_GET['p'] == 'about'
		|| $_GET['p'] == 'categories'
		|| $_GET['p'] == 'best-games'
		|| $_GET['p'] == 'featured-games'
		|| $_GET['p'] == 'played-games'
		|| $_GET['p'] == 'favorite-games'
		|| $_GET['p'] == 'tagspage'
		|| $_GET['p'] == 'tags'
		|| $_GET['p'] == 'contact'
		|| $_GET['p'] == 'blogs'
		|| is_page('home')
	) {

        		if ($isPokiPublicTheme || gps_theme_is('y8-like') || gps_theme_is('y8-pro')) {
			$sql_cat_query = $GameMonetizeConnect->query("SELECT * FROM " . CATEGORIES);
			$ct_r = '';
			while ($category = $sql_cat_query->fetch_array()) {
				$themeData['category_id'] = $category['id'];
				$themeData['category_name'] = $category['name'];
				$themeData['category_image'] = $category['image'];

				$themeData['category_url'] = siteUrl() . '/category/'	. slugify($category['name']);
				$ct_r .= \GameMonetize\UI::view('category/categories-list-2');
			}

			$themeData['categories_list_2'] = $ct_r;
			$themeData['category_content'] = \GameMonetize\UI::view('category/categories-2');

			if (!gps_theme_is('crazygames-like')) {
				$sql_tag_query = $GameMonetizeConnect->query("SELECT * FROM " . TAGS . " WHERE show_home = 1");
				$tag_r = '';
				while ($tag = $sql_tag_query->fetch_array()) {
					$themeData['tag_id'] = $tag['id'];
					$themeData['tag_name'] = $tag['name'];

					$themeData['tag_url'] = siteUrl() . '/tag/'	. slugify($tag['name']);

					$baseTagImagePath = 'tag-img/' . slugify($tag['name']);
					$formats = ['.png', '.webp'];
					$defaultTagImagePath = 'templates/poki-like/image/tag.png';

					$themeData['tags_thumb'] = $defaultTagImagePath; // Default value
					$themeData['tag_image'] = $defaultTagImagePath; // Default value

					foreach ($formats as $format) {
						if (file_exists($baseTagImagePath . $format)) {
							$themeData['tags_thumb'] = '/' . $baseTagImagePath . $format;
							$themeData['tag_image'] = '/' . $baseTagImagePath . $format;
							break;
						}
					}

					$tag_r .= \GameMonetize\UI::view('tags/tags-list-home');
				}

				$themeData['tags_list'] = $tag_r;
			}
		}

		if (!function_exists('buildSidebarAdminIconHtml')) {
			function buildSidebarAdminIconHtml($icon = '', $fallbackImg = '', $alt = '')
			{
				$icon = trim((string)$icon);
				$fallbackImg = trim((string)$fallbackImg);
				$alt = htmlspecialchars((string)$alt, ENT_QUOTES);

				if ($icon !== '') {
					if (strpos($icon, 'fa-') !== false) {
						return '<div class="sidebar-admin-icon-wrap">
							<i class="' . htmlspecialchars($icon, ENT_QUOTES) . ' sidebar-admin-icon"></i>
						</div>';
					}

					return '<div class="sidebar-admin-icon-wrap">
						<span class="material-symbols-outlined sidebar-admin-icon">' . htmlspecialchars($icon, ENT_QUOTES) . '</span>
					</div>';
				}

				if ($fallbackImg !== '') {
					return '<div class="sidebar-admin-icon-wrap">
						<img src="' . htmlspecialchars($fallbackImg, ENT_QUOTES) . '" alt="' . $alt . ' image" class="sidebar-admin-icon-img">
					</div>';
				}

				return '<div class="sidebar-admin-icon-wrap">
					<span class="material-symbols-outlined sidebar-admin-icon">sell</span>
				</div>';
			}
		}

		if (gps_theme_is('crazygames-like')) {
			// Get sidebar data
			$sidebarItems = "";
			$sidebarDataQuery = "SELECT * FROM " . SIDEBAR . " ORDER BY CAST(ordering AS UNSIGNED)";
			$sidebarData = $GameMonetizeConnect->query($sidebarDataQuery);
			$ct_r = "";
			$tag_r = "";
			while ($sidebar = $sidebarData->fetch_array()) {
				if ($sidebar['type'] != "separator" && $sidebar['type'] != "search") {
						if ($sidebar['type'] == "category") {
							$categoryData = $GameMonetizeConnect->query("SELECT * FROM " . CATEGORIES . " WHERE id = " . (int)$sidebar['category_tags_id'] . " LIMIT 1");
							if ($categoryData !== null && $categoryData->num_rows > 0) {
								$categoryData = $categoryData->fetch_array();

								$categorySlug = !empty($categoryData['category_pilot']) ? $categoryData['category_pilot'] : slugify($categoryData['name']);

								$themeData['category_id'] = $categoryData['id'];
								$themeData['category_name'] = $sidebar['name'] !== '' ? $sidebar['name'] : $categoryData['name'];
								$themeData['category_image'] = $categoryData['image'];
								$themeData['category_url'] = siteUrl() . '/category/' . $categorySlug;

								$themeData['sidebar_icon_html'] = buildSidebarAdminIconHtml(
									$sidebar['icon'],
									$categoryData['image'],
									$themeData['category_name']
								);

								$ct_r .= \GameMonetize\UI::view('category/categories-list-2');
							}
						}

					if ($sidebar['type'] == "tags") {
						$tagsData = $GameMonetizeConnect->query("SELECT * FROM " . TAGS . " WHERE id = " . (int)$sidebar['category_tags_id'] . " LIMIT 1");
						if ($tagsData !== null && $tagsData->num_rows > 0) {
							$tagsData = $tagsData->fetch_array();

							$tagSlug = !empty($tagsData['url']) ? $tagsData['url'] : slugify($tagsData['name']);

							$themeData['tag_id'] = $tagsData['id'];
							$themeData['tag_name'] = $sidebar['name'] !== '' ? $sidebar['name'] : $tagsData['name'];
							$themeData['tag_url'] = siteUrl() . '/tag/' . $tagSlug;
							$themeData['tag_image'] = siteUrl() . '/templates/crazygames-like/image/tag.png';

							$iconHtml = buildSidebarAdminIconHtml(
								$sidebar['icon'],
								siteUrl() . '/templates/crazygames-like/image/tag.png',
								$themeData['tag_name']
							);

							$themeData['tags_home_card_html'] = '<a href="' . $themeData['tag_url'] . '" class="border-transparent border-l-4 text-white flex items-center font-semibold no-underline h-[34px] hover:text-opacity-65 hover:[&>div.truncate]:pl-2 [&>div.truncate]:mr-2 hover:[&>div.truncate]:mr-0 text-[15px]">
								' . $iconHtml . '
								<div class="relative truncate transition-all duration-300">' . htmlspecialchars($themeData['tag_name'], ENT_QUOTES) . '</div>
							</a>';

							$tag_r .= \GameMonetize\UI::view('tags/tags-list-home');
						}
					}
				}
			}
			$themeData['categories_list_2'] = $ct_r;
			$themeData['category_content'] = \GameMonetize\UI::view('category/categories-2');
			$themeData['tags_list'] = $tag_r;
		}

		$themeData['config_this_year'] =  date("Y");

		$whitelist = array(
			'127.0.0.1',
			'::1'
		);
		$themeData['load_more_url'] = "";
		if (!in_array($_SERVER['REMOTE_ADDR'], $whitelist)) {
			$themeData['load_more_url'] = "";
		} else {
			$themeData['load_more_url'] = siteUrl();
		}

		$themeData['footer_bar'] = \GameMonetize\UI::view('footer/footer_bar');
		$themeData['footer_content'] = \GameMonetize\UI::view('footer/content');
		$themeData['pro_visual_preset_control'] = function_exists('gps_cvp_public_control')
			? gps_cvp_public_control()
			: '';
		if (!gps_theme_is('crazygames-like')) {
			$themeData['footer_content'] .= $themeData['pro_visual_preset_control'];
		}
		$themeData['pro_menu_design'] = function_exists('gps_menu_design_markup')
			? gps_menu_design_markup()
			: '';
		$themeData['pro_menu_design_actions'] = function_exists('gps_menu_design_actions_markup')
			? gps_menu_design_actions_markup()
			: '';
		$themeData['header'] = \GameMonetize\UI::view('header/content');

		// Get setting data
		$settingDataQuery = "SELECT * FROM " . SETTING . " LIMIT 1";
		$settingData = $GameMonetizeConnect->query($settingDataQuery);
		$settingData = $settingData->fetch_array();

		$keepY8MenuHeader = $config['site_theme'] === 'y8-pro' && $themeData['pro_menu_design'] !== '';
		if ($settingData['is_sidebar_enabled'] && !$isPokiPublicTheme && !gps_theme_is('crazygames-like') && !$keepY8MenuHeader) {
			$themeData['header'] = "";

			// Get sidebar data
			$sidebarItems = "";
			$sidebarDataQuery = "SELECT * FROM " . SIDEBAR . " ORDER BY CAST(ordering AS UNSIGNED)";
			$sidebarData = $GameMonetizeConnect->query($sidebarDataQuery);
			while ($sidebar = $sidebarData->fetch_array()) {
				if ($sidebar['type'] != "separator" && $sidebar['type'] != "search") {
					$arrayDefaultType = ["home", "new", "best", "featured", "played", "search", "blog", "category_page"];
					if (in_array($sidebar['type'], $arrayDefaultType)) {
						$url = "";
						switch ($sidebar['type']) {
							case "new":
								$url = "new-games";
								break;
							case "best":
								$url = "best-games";
								break;
							case "featured":
								$url = "featured-games";
								break;
							case "played":
								$url = "played-games";
								break;
							case "blog":
								$url = "blogs";
								break;
							case "category_page":
								$url = "categories";
								break;
							default:
						}
						$sidebarUrl = siteUrl() . "/" . $url;
					}

					if ($sidebar['type'] == "category") {
						$categoryData = $GameMonetizeConnect->query("SELECT * FROM " . CATEGORIES . " WHERE id = {$sidebar['category_tags_id']} LIMIT 1");
						if ($categoryData !== null) {
							$categoryData = $categoryData->fetch_array();
							$sidebarUrl = siteUrl() . "/category/" . $categoryData['category_pilot'];
						}
					}

					if ($sidebar['type'] == "tags") {
						$tagsData = $GameMonetizeConnect->query("SELECT * FROM " . TAGS . " WHERE id = {$sidebar['category_tags_id']} LIMIT 1");
						if ($tagsData !== null) {
							$tagsData = $tagsData->fetch_array();
							$sidebarUrl = siteUrl() . "/tag/" . $tagsData['url'];
						}
					}

					$themeData['header_sidebar_target'] = "_self";

					if ($sidebar['type'] == "custom") {
						$sidebarUrl = $sidebar['custom_link'];
						$themeData['header_sidebar_target'] = "_blank";
					}


					// Icon
					$sidebarIcon = $sidebar['icon'];
					if (strpos($sidebar['icon'], "fa-") !== false) {
						$sidebarIcon = "<i class='" . $sidebar['icon'] . "'></i>";
					}
					$themeData['header_sidebar_url'] = $sidebarUrl;
					$themeData['header_sidebar_icon'] = $sidebarIcon;
					$themeData['header_sidebar_name'] = $sidebar['name'];
					$sidebarItems .= \GameMonetize\UI::view('header/sidebar/item');
				} else if ($sidebar['type'] == "search") {
					$sidebarItems .= \GameMonetize\UI::view('header/sidebar/search');
				} else {
					$sidebarItems .= \GameMonetize\UI::view('header/sidebar/separator');
				}
			}

			$sidebarItems .= \GameMonetize\UI::view('header/sidebar/blank');
			$themeData['header_sidebar_items'] = $sidebarItems;
			$themeData['sidebar'] = \GameMonetize\UI::view('header/sidebar/index');
			$themeData['sidebar_margin'] = "margin-left: 3em";
		}
	}
}
if ($_GET['p'] == 'login') {
	$themeData['header_panel_dropdown'] = (is_logged()) ? \GameMonetize\UI::view('header/header_user_panel') : '';
	// $themeData['footer_content'] = \GameMonetize\UI::view('footer/content_admin');
}


function getPageTitleAndDescription()
{
	$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	$page = basename(trim($path, "/"));

	$pageTitle = explode(" - ", td_title())[0];
	$pageDescription = "";

	if ($page == "" || $page == "home") {
        $pageTitle = "Free Online Games";
        $pageDescription = "Play free online browser games instantly. Discover new, popular, multiplayer, puzzle, racing, action and casual games.";
    } elseif ($page == "popular") {
        $pageTitle = "Popular Games";
        $pageDescription = "Discover popular free online browser games ranked by player activity.";
    } elseif ($page == "all-games") {
        $pageTitle = "All Games";
        $pageDescription = "Browse the complete collection of free online browser games.";
    } elseif ($page == "search") {
        $pageTitle = "Search";
        $pageDescription = "Find free online browser games by title, genre, theme or gameplay style.";
    } elseif ($page == "new-games") {
		$pageTitle = "New Games";
		$pageDescription = "Discover the latest free online games!";
	} elseif ($page == "best-games") {
        $pageTitle = "Popular Games";
        $pageDescription = "Discover popular free online browser games ranked by player activity.";
	} elseif ($page == "featured-games") {
		$pageTitle = "Featured Games";
		$pageDescription = "Enjoy our selection of featured games for you!";
	} elseif ($page == "tags") {
		$pageTitle = "ALL FREE GAMES CATEGORIES. <br />CHOOSE ANY GAME TAG AND START PLAYING NOW!";
		$pageDescription = "Looking for a game of a certain type? Check out the extensive list of game categories. We have been labeling games using tags and categories for more than a decade. This page list hundreds of different tags representing entire collections of games that can be played in a browser.";
	}

	return [
		'title' => $pageTitle,
		'description' => $pageDescription
	];
}

$pageData = getPageTitleAndDescription();

$themeData['page_title'] = $pageData['title'];
$themeData['page_description'] = $pageData['description'];
$themeData['config_site_name'] = $config['site_name'];
$themeData['config_site_description'] = $config['site_description'];
$themeData['config_site_keywords'] = $config['site_keywords'];
$gpsPublicPath = trim((string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$gpsPublicMetaEnabled = ($_GET['p'] ?? '') !== 'play'
    && !in_array((string)($_GET['p'] ?? ''), ['login','admin','setting','error'], true);

if ($gpsPublicMetaEnabled) {
    $gpsPublicTitle = trim(strip_tags((string)($themeData['page_title'] ?? '')));
    if ($gpsPublicTitle === '') $gpsPublicTitle = trim(strip_tags((string)($themeData['category_tags_meta_title'] ?? '')));
    if ($gpsPublicTitle === '') $gpsPublicTitle = 'Free Online Games';
    $gpsPublicKnownTitle = [
        '' => 'Free Online Games - ' . $config['site_name'],
        'home' => 'Free Online Games - ' . $config['site_name'],
        'popular' => 'Popular Games - ' . $config['site_name'],
        'all-games' => 'All Games - ' . $config['site_name'],
        'new-games' => 'New Games - ' . $config['site_name'],
        'featured-games' => 'Featured Games - ' . $config['site_name'],
        'search' => 'Search - ' . $config['site_name'],
        'categories' => 'Categories - ' . $config['site_name'],
        'tags' => 'Game Tags - ' . $config['site_name']
    ];
    if (isset($gpsPublicKnownTitle[$gpsPublicPath])) $themeData['title_tag'] = $gpsPublicKnownTitle[$gpsPublicPath];
    elseif (!empty($themeData['category_tags_meta_title'])) $themeData['title_tag'] = $themeData['category_tags_meta_title'];

    $gpsPublicDescription = trim(strip_tags((string)($themeData['page_description'] ?? '')));
    $gpsDescriptionFallbacks = [
        '' => 'Play free online browser games instantly. Discover new, popular, multiplayer, puzzle, racing, action and casual games.',
        'home' => 'Play free online browser games instantly. Discover new, popular, multiplayer, puzzle, racing, action and casual games.',
        'popular' => 'Discover popular free online browser games ranked by player activity.',
        'all-games' => 'Browse the complete collection of free online browser games.',
        'new-games' => 'Discover the latest free online browser games added to the catalog.',
        'featured-games' => 'Explore featured free online browser games selected from the catalog.',
        'search' => 'Find free online browser games by title, genre, theme or gameplay style.',
        'categories' => 'Browse free online games by category, including action, racing, puzzle, sports and more.',
        'tags' => 'Browse free online games by topic and gameplay tag.'
    ];
    if ($gpsPublicDescription === '') $gpsPublicDescription = $gpsDescriptionFallbacks[$gpsPublicPath] ?? (string)$config['site_description'];

    $gpsPublicCanonical = rtrim(siteUrl(), '/') . ($gpsPublicPath === '' ? '/' : '/' . ltrim($gpsPublicPath, '/'));
    $gpsPublicRobots = (strpos($gpsPublicPath, 'search') === 0)
        ? 'noindex,follow,max-image-preview:large'
        : 'index,follow,max-image-preview:large';

    $gpsMeta = (string)($themeData['header_metatags'] ?? '');
    $gpsEscDesc = htmlspecialchars(substr($gpsPublicDescription, 0, 160), ENT_QUOTES, 'UTF-8');
    $gpsEscTitle = htmlspecialchars((string)($themeData['title_tag'] ?? $gpsPublicTitle), ENT_QUOTES, 'UTF-8');
    $gpsEscCanon = htmlspecialchars($gpsPublicCanonical, ENT_QUOTES, 'UTF-8');
    $gpsEscSite = htmlspecialchars((string)$config['site_name'], ENT_QUOTES, 'UTF-8');

    if (preg_match('/<meta\s+name=["\']description["\'][^>]*>/i', $gpsMeta)) {
        $gpsMeta = preg_replace('/<meta\s+name=["\']description["\'][^>]*>/i', '<meta name="description" content="' . $gpsEscDesc . '">', $gpsMeta, 1);
    } else {
        $gpsMeta .= '<meta name="description" content="' . $gpsEscDesc . '">';
    }
    if (stripos($gpsMeta, 'rel="canonical"') === false) $gpsMeta .= '<link rel="canonical" href="' . $gpsEscCanon . '">';
    if (stripos($gpsMeta, 'name="robots"') === false) $gpsMeta .= '<meta name="robots" content="' . $gpsPublicRobots . '">';
    if (stripos($gpsMeta, 'property="og:title"') === false) {
        $gpsMeta .= '<meta property="og:type" content="website"><meta property="og:title" content="' . $gpsEscTitle . '"><meta property="og:description" content="' . $gpsEscDesc . '"><meta property="og:url" content="' . $gpsEscCanon . '"><meta property="og:site_name" content="' . $gpsEscSite . '">';
    }
    if (stripos($gpsMeta, 'name="twitter:card"') === false) {
        $gpsMeta .= '<meta name="twitter:card" content="summary"><meta name="twitter:title" content="' . $gpsEscTitle . '"><meta name="twitter:description" content="' . $gpsEscDesc . '"><meta name="twitter:url" content="' . $gpsEscCanon . '">';
    }
    $themeData['header_metatags'] = $gpsMeta;
}


/* PlayGrid JSON-LD: only describe content that is actually rendered. */
if (!function_exists('gps_playgrid_append_jsonld')) {
	function gps_playgrid_append_jsonld(&$themeData, $payload) {
		$themeData['header_metatags'] = (string)($themeData['header_metatags'] ?? '') . '<script type="application/ld+json">' . json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
	}
}
$gpsCurrentPath = trim((string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$gpsCanonical = rtrim(siteUrl(), '/') . ($gpsCurrentPath === '' ? '/' : '/' . $gpsCurrentPath);
if (($gpsCurrentPath === '' || $gpsCurrentPath === 'home') && ($_GET['p'] ?? '') === 'home') {
	gps_playgrid_append_jsonld($themeData, ['@context'=>'https://schema.org','@graph'=>[
		['@type'=>'WebSite','@id'=>rtrim(siteUrl(),'/').'/#website','url'=>rtrim(siteUrl(),'/').'/','name'=>(string)$config['site_name'],'description'=>(string)$config['site_description'],'potentialAction'=>['@type'=>'SearchAction','target'=>rtrim(siteUrl(),'/').'/search?q={search_term_string}','query-input'=>'required name=search_term_string']],
		['@type'=>'Organization','@id'=>rtrim(siteUrl(),'/').'/#organization','name'=>(string)$config['site_name'],'url'=>rtrim(siteUrl(),'/').'/']
	]]);
} elseif (($gpsCurrentPath !== '') && ($_GET['p'] ?? '') === 'play' && !empty($get_game_data)) {
	$gpsGameName = trim((string)($get_game_data['name'] ?? ''));
	$gpsGameDescription = trim(strip_tags(html_entity_decode((string)($get_game_data['description'] ?? ''), ENT_QUOTES, 'UTF-8')));
	$gpsGameImage = trim((string)($themeData['play_game_image'] ?? ''));
	$gpsRating = isset($get_game['rating']) ? (float)$get_game['rating'] : 0;
	$gpsPlays = isset($get_game['plays']) ? (int)$get_game['plays'] : (int)($get_game_data['plays'] ?? 0);
	$gpsCategory = trim((string)($get_game['category_name'] ?? ''));
	$gpsGameSchema = ['@context'=>'https://schema.org','@type'=>'VideoGame','name'=>$gpsGameName,'url'=>$gpsCanonical,'description'=>$gpsGameDescription !== '' ? $gpsGameDescription : 'Play '.$gpsGameName.' online in your browser.','applicationCategory'=>'Game','gamePlatform'=>['Web Browser'],'genre'=>$gpsCategory !== '' ? $gpsCategory : null,'image'=>$gpsGameImage !== '' ? $gpsGameImage : null];
	$gpsVoteCount=(int)($get_game['vote_count'] ?? $get_game['votes'] ?? 0);
if ($gpsVoteCount <= 0) $gpsVoteCount=(int)($get_game['like_count'] ?? 0)+(int)($get_game['dislike_count'] ?? 0);
if ($gpsRating > 0 && $gpsVoteCount > 0) $gpsGameSchema['aggregateRating']=['@type'=>'AggregateRating','ratingValue'=>$gpsRating,'bestRating'=>5,'worstRating'=>0,'ratingCount'=>$gpsVoteCount];
	if ($gpsPlays > 0) $gpsGameSchema['interactionStatistic']=['@type'=>'InteractionCounter','interactionType'=>['@type'=>'PlayAction'],'userInteractionCount'=>$gpsPlays];
	$gpsGameSchema=array_filter($gpsGameSchema,static function($v){return $v!==null && $v!=='';});
	gps_playgrid_append_jsonld($themeData,$gpsGameSchema);
	gps_playgrid_append_jsonld($themeData,['@context'=>'https://schema.org','@type'=>'BreadcrumbList','itemListElement'=>[
		['@type'=>'ListItem','position'=>1,'name'=>'Home','item'=>rtrim(siteUrl(),'/').'/'],
		['@type'=>'ListItem','position'=>2,'name'=>$gpsCategory !== '' ? $gpsCategory : 'Games','item'=>rtrim(siteUrl(),'/').'/category/'.slugify($gpsCategory !== '' ? $gpsCategory : 'games')],
		['@type'=>'ListItem','position'=>3,'name'=>$gpsGameName,'item'=>$gpsCanonical]
	]]);
}
if (stripos((string)($themeData['header_metatags'] ?? ''), 'rel="canonical"') === false) $themeData['header_metatags']=(string)($themeData['header_metatags'] ?? '').'<link rel="canonical" href="'.htmlspecialchars($gpsCanonical,ENT_QUOTES,'UTF-8').'">';


if ($isPokiPublicTheme) {
	$bestGames_query = $GameMonetizeConnect->query("SELECT * FROM " . GAMES . " WHERE published='1' ORDER BY plays DESC LIMIT 6");
	$bgm_r = '';
	$ids = '';
	while ($newGames = $bestGames_query->fetch_array()) {
		$newGame_data = gameData($newGames);
		$themeData['new_game_url'] = $newGame_data['game_url'];
		$themeData['new_game_image'] = $newGame_data['image_url'];
		$themeData['new_game_name'] = $newGame_data['name'];
		$themeData['new_game_video_url'] = $newGame_data['video_url'];

		$themeData['new_game_featured'] = $newGame_data['featured'];

		$bgm_r .= \GameMonetize\UI::view('game/list-each/new-games-list');
		$ids .= $newGames['game_id'] . ',';
	}

	$themeData['popular_game_list'] = $bgm_r;

	if (!isset($_COOKIE['playedgames'])) {
		$themeData['games_played_left'] = '';
	} else {
		$fav = explode(',,', $_COOKIE['playedgames']);
		$pgm_r = '';
		// remove empty values from $fav
		if (strlen($_COOKIE['playedgames']) > 0) {
			foreach ($fav as $game_id) {
				$resultset[] = $game_id;
			}
			$string = implode(",", $resultset);
			$str = trim($string, ",");
			$comma_separated = rtrim($str, ',');
			$playedGames_query = $GameMonetizeConnect->query("SELECT * FROM " . GAMES . " where `game_id` IN (" . $comma_separated . ") order by date_added DESC LIMIT 12");

			while ($newGames = $playedGames_query->fetch_array()) {
				$newGame_data = gameData($newGames);
				$themeData['new_game_url'] = $newGame_data['game_url'];
				$themeData['new_game_image'] = $newGame_data['image_url'];
				$themeData['new_game_name'] = $newGame_data['name'];
				$themeData['new_game_rating'] = $newGames['rating'];
				$themeData['new_game_wt_video'] = isset($newGames['wt_video']) ? $newGames['wt_video'] : '';

				$pgm_r .= \GameMonetize\UI::view('game/list-each/new-games-list');
			}
		}

		$themeData['games_played_left'] = $pgm_r;
	}
}
