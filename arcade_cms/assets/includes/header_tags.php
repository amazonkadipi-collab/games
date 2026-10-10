<?php
$themeData['title_tag'] = title_tag();
$cmsRequestedPath = trim((string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$descriptionPixelChar = 135;
$cmsRequestedPath = trim((string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$themeData['config_site_description'] = substr($themeData['config_site_description'], 0, $descriptionPixelChar);
$themeData['date_all_css'] = date("Y-m-d\TH-i", filemtime($_SERVER["DOCUMENT_ROOT"] . '/templates/' . $config['site_theme'] . '/css/' . 'all.css'));
$themeData['date_play_css'] = date("Y-m-d\TH-i", filemtime($_SERVER["DOCUMENT_ROOT"] . '/templates/' . $config['site_theme'] . '/css/' . 'play.css'));
$specialPage = ['best-games', 'new-games', 'featured-games', 'played-games', 'favorite-games'];
if (is_page('play')) {
	$game_data = getGame2($_GET['id']);
	$game_info = gameData($game_data);
	$themeData['game_meta_title'] = $game_info['name'] . " - Play Online Games Free";
	$themeData['game_meta_name'] = $game_info['name'];
	$themeData['game_meta_game_url'] = $game_info['game_url'];
	$themeData['game_meta_image'] = $game_info['image_url'];
	$themeData['game_meta_description'] = substr(strip_tags(htmlspecialchars_decode($game_info['description'])), 0, $descriptionPixelChar);
	$themeData['header_metatags'] .= \GameMonetize\UI::view('global/header/game_metatags');
	$themeData['header_metatags'] .= '<link rel="canonical" href="' . $game_info['game_url'] . '">';
} else {
	if ($_GET['p'] == 'home') {
		$cat = $_GET["cat"];
		if ($cat <> "") {
			$cat = str_replace('-', '.', $cat);
			$cat = ucfirst($cat);
			$themeData['game_meta_description2'] = "Play " . $cat . " Free Online at PlayGrid Games! We have chosen top " . $cat . " games which you can play online for free. enjoy! ";
			$themeData['header_title'] = \GameMonetize\UI::view('global/header/title');
			$themeData['header_metatags'] = \GameMonetize\UI::view('global/header/metatags2');
		} else {
			$themeData['header_title'] = \GameMonetize\UI::view('global/header/title');
			$themeData['header_metatags'] = \GameMonetize\UI::view('global/header/metatags');
		}
	} 
	else if ($_GET['p'] == 'tagspage'){
		$tags_data = getTagsByTitle($_GET['tag']);
		$tagPageName = ucwords($tags_data['name']);
		$tagPageName = preg_match('/\bgames$/i', $tagPageName) ? $tagPageName : $tagPageName . ' Games';
		$themeData['category_tags_meta_title'] = "Tag " . $tagPageName . " - Play Online Games Free";
		$themeData['category_tags_meta_description'] = substr(strip_tags(htmlspecialchars_decode($tags_data["footer_description"])), 0, $descriptionPixelChar);
		$themeData['header_metatags'] .= \GameMonetize\UI::view('global/header/category_tags_metatags');
		$themeData['header_metatags'] .= '<link rel="canonical" href="' . siteUrl() . "/tag/" . $tags_data['url'] . '">';
	}
	else if ($_GET['p'] == 'blogs'){
		if(!isset($_GET['blog'])){
			$blogs_footer_description = getFooterDescription('blogs');
			$themeData['category_tags_meta_title'] = "Our Blogs - Play Online Games Free";
			$themeData['category_tags_meta_description'] = substr(strip_tags(htmlspecialchars_decode($blogs_footer_description->description)), 0, $descriptionPixelChar);
			$themeData['header_metatags'] .= \GameMonetize\UI::view('global/header/category_tags_metatags');
			$themeData['header_metatags'] .= '<link rel="canonical" href="' . siteUrl() . '/blogs">';
		} else {
			$blog_data = getBlogByUrl($_GET['blog']);
			$themeData['category_tags_meta_title'] = substr(ucwords($blog_data['title']), 0, 20) . " - Play Online Games Free";
			$themeData['category_tags_meta_description'] = substr(strip_tags(htmlspecialchars_decode($blog_data["post"])), 0, $descriptionPixelChar);
			$themeData['header_metatags'] .= \GameMonetize\UI::view('global/header/category_tags_metatags');
			$themeData['header_metatags'] .= '<link rel="canonical" href="' . siteUrl() . "/blog/" . $blog_data['url'] . '">';
		}
	}
	else if ($_GET['p'] == 'categories'){
		if(!isset($_GET['category'])){
			$themeData['category_tags_meta_title'] = "Categories - Play Online Games Free";
			$category_footer_description = getFooterDescription('categories');
			$themeData['category_tags_meta_description'] = substr(strip_tags(htmlspecialchars_decode($category_footer_description->description)), 0, $descriptionPixelChar);
			$themeData['header_metatags'] .= \GameMonetize\UI::view('global/header/category_tags_metatags');
			$themeData['header_metatags'] .= '<link rel="canonical" href="' . siteUrl() . '/categories">';
		}else{
			$category_data = getCategoriesByUrl($_GET['category']);
			$themeData['category_tags_meta_title'] = "Category ".ucwords($category_data['name']);
			$themeData['category_tags_meta_description'] = substr(strip_tags(htmlspecialchars_decode($category_data["footer_description"])), 0, $descriptionPixelChar);
			$themeData['header_metatags'] .= \GameMonetize\UI::view('global/header/category_tags_metatags');
			$themeData['header_metatags'] .= '<link rel="canonical" href="' . siteUrl() . "/category/" . $category_data['category_pilot'] . '">';

		}
	} else if (in_array($_GET['p'], $specialPage)) {
		$isFavoritePage = $_GET['p'] === 'favorite-games';
		$footer_description = getFooterDescription($_GET['p']);
		$footerMetaText = trim(strip_tags(htmlspecialchars_decode((string)($footer_description->description ?? ''))));
		if ($isFavoritePage) {
			$favoriteDefaultDescription = 'Save your favorite free online games in one place, return anytime, and discover popular, new, and recommended arcade games to play instantly.';
			$themeData['category_tags_meta_title'] = 'Favorite Games - ' . $config['site_name'];
			$themeData['category_tags_meta_description'] = strlen($footerMetaText) >= 100
				? substr($footerMetaText, 0, 155)
				: $favoriteDefaultDescription;
		} else {
			$themeData['category_tags_meta_title'] = ($cmsRequestedPath === 'popular')
				? 'Popular Games - ' . $config['site_name']
				: td_title();
			$themeData['category_tags_meta_description'] = ($cmsRequestedPath === 'popular')
				? 'Check out the most popular free online games trending right now.'
				: substr($footerMetaText, 0, $descriptionPixelChar);
		}
		$themeData['header_metatags'] .= \GameMonetize\UI::view('global/header/category_tags_metatags');
		$canonicalSlug = ($cmsRequestedPath === 'popular') ? 'popular' : (string)$_GET['p'];
		$canonicalUrl = rtrim(siteUrl(), '/') . '/' . ltrim($canonicalSlug, '/');
		$themeData['header_metatags'] .= '<link rel="canonical" href="' . htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') . '">';
		if ($isFavoritePage) {
			$logoFolders = ['crazygames-like' => 'crazygames-like', 'crazygames-pro' => 'crazygames-like', 'poki-like' => 'poki', 'poki-pro' => 'poki', 'kizi' => 'kizi', 'kizi-pro' => 'kizi', 'y8-like' => 'y8', 'y8-pro' => 'y8'];
			$logoFolder = $logoFolders[$config['site_theme']] ?? 'crazygames-like';
			$metaTitle = htmlspecialchars($themeData['category_tags_meta_title'], ENT_QUOTES, 'UTF-8');
			$metaDescription = htmlspecialchars($themeData['category_tags_meta_description'], ENT_QUOTES, 'UTF-8');
			$metaUrl = htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8');
			$metaImage = htmlspecialchars(rtrim(siteUrl(), '/') . '/static/logo/' . $logoFolder . '/og-image.webp', ENT_QUOTES, 'UTF-8');
			$metaSiteName = htmlspecialchars($config['site_name'], ENT_QUOTES, 'UTF-8');
			$themeData['header_metatags'] .= '<meta name="robots" content="index,follow,max-image-preview:large">'
				. '<meta property="og:type" content="website"><meta property="og:title" content="' . $metaTitle . '">'
				. '<meta property="og:description" content="' . $metaDescription . '"><meta property="og:url" content="' . $metaUrl . '">'
				. '<meta property="og:image" content="' . $metaImage . '"><meta property="og:site_name" content="' . $metaSiteName . '">'
				. '<meta name="twitter:card" content="summary_large_image"><meta name="twitter:title" content="' . $metaTitle . '">'
				. '<meta name="twitter:description" content="' . $metaDescription . '"><meta name="twitter:image" content="' . $metaImage . '">';
		}
	}
	else {
		$themeData['header_title'] = \GameMonetize\UI::view('global/header/title');
		$themeData['header_metatags'] = \GameMonetize\UI::view('global/header/metatags');
	}
}

$themeData['header_favicon'] = \GameMonetize\UI::view('global/header/favicon');

if ($cmsRequestedPath === 'popular') {
	$themeData['title_tag'] = 'Popular Games - ' . $config['site_name'];
}

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
		|| $_GET['p'] == 'tags' 
		|| $_GET['p'] == 'tagspage' 
		|| $_GET['p'] == 'contact' 
		|| $_GET['p'] == 'blogs' 
		|| is_page('home')
	) {
		$themeData['header_stylesheets'] = \GameMonetize\UI::view('global/header/stylesheets');
	} else {
		$themeData['header_stylesheets'] .= \GameMonetize\UI::view('global/header/admin-stylesheets');
	}
}
if ($_GET['p'] == 'login') {
	$themeData['header_stylesheets'] .= \GameMonetize\UI::view('global/header/admin-stylesheets');
}

$whitelist = array(
	'127.0.0.1',
	'::1'
);

if (!in_array($_SERVER['REMOTE_ADDR'], $whitelist)) {
	$themeData['header_url'] = "//" . $_SERVER['HTTP_HOST'];
} else {
	$themeData['header_url'] = siteUrl();
}

$themeData['header_scripts'] = \GameMonetize\UI::view('global/header/scripts');

if (function_exists('gps_other_fixes_google_tag_markup')) {
	$themeData['header_scripts'] .= gps_other_fixes_google_tag_markup($config['google_tag_id'] ?? '');
}
if (function_exists('gps_other_fixes_poki_full_card_markup')) {
	$themeData['header_scripts'] .= gps_other_fixes_poki_full_card_markup();
}
if (function_exists('gps_menu_design_assets')) {
	$themeData['header_scripts'] .= gps_menu_design_assets();
}
if (function_exists('gps_crazy_pro_runtime_assets')) {
	$themeData['header_scripts'] .= gps_crazy_pro_runtime_assets();
}
if (function_exists('gps_showcase_runtime_assets')) {
	$themeData['header_scripts'] .= gps_showcase_runtime_assets();
}

/* Final header rendering is intentionally deferred until SEO metadata is repaired. */

function cleanText($text)
{
	$text = str_replace('"', ";", $text);
	return $text;
}


/* Consistent public SEO controls: one canonical, strong social metadata, and noindex for search URLs. */
$gpsSeoPublicPaths = !in_array((string)($_GET['p'] ?? ''), ['login', 'admin', 'setting', 'error'], true) && !is_page('play');
if ($gpsSeoPublicPaths) {
    $gpsSeoPath = trim((string)$cmsRequestedPath, '/');
    $gpsSeoTitle = trim(strip_tags(htmlspecialchars_decode((string)($themeData['page_title'] ?? ''))));
    if ($gpsSeoTitle === '') $gpsSeoTitle = trim(strip_tags(htmlspecialchars_decode((string)($themeData['category_tags_meta_title'] ?? ''))));
    if ($gpsSeoTitle === '') $gpsSeoTitle = trim(strip_tags(htmlspecialchars_decode((string)($themeData['title_tag'] ?? ''))));
    if ($gpsSeoTitle === '') $gpsSeoTitle = (string)($config['site_name'] ?? 'PlayGrid Games');

    $gpsSeoDescription = trim(strip_tags(htmlspecialchars_decode((string)($themeData['page_description'] ?? ''))));
    if ($gpsSeoDescription === '') $gpsSeoDescription = trim(strip_tags(htmlspecialchars_decode((string)($themeData['category_tags_meta_description'] ?? ''))));
    if ($gpsSeoDescription === '') $gpsSeoDescription = trim(strip_tags(htmlspecialchars_decode((string)($config['site_description'] ?? ''))));
    if ($gpsSeoDescription === '' && strpos($gpsSeoPath, 'category/') === 0) {
        $gpsSeoCategory = ucwords(str_replace('-', ' ', basename($gpsSeoPath)));
        $gpsSeoDescription = 'Play free ' . strtolower($gpsSeoCategory) . ' online games instantly in your browser. Discover new releases, popular picks and fun challenges on PlayGrid Games, with no download required.';
    }
    if ($gpsSeoDescription === '') $gpsSeoDescription = 'Play free online browser games instantly on ' . (string)($config['site_name'] ?? 'PlayGrid Games') . '.';

    $gpsSeoCanonical = rtrim(siteUrl(), '/') . ($gpsSeoPath === '' ? '/' : '/' . ltrim($gpsSeoPath, '/'));
    $gpsSeoRobots = (strpos($gpsSeoPath, 'search') === 0)
        ? 'noindex,follow,max-image-preview:large'
        : 'index,follow,max-image-preview:large';

    $gpsSeoMeta = $themeData['header_metatags'] ?? '';

    // Repair empty/short template metadata instead of leaving blank description
    // tags in place just because a template already emitted the tag.
    $gpsSeoDescription = trim(preg_replace('/\\s+/', ' ', $gpsSeoDescription));
    $gpsSeoDescription = mb_substr($gpsSeoDescription, 0, 160);
    $gpsSeoEscapedDescription = htmlspecialchars($gpsSeoDescription, ENT_QUOTES, 'UTF-8');
    $gpsSeoMeta = preg_replace(
        '~<meta\\s+name="description"\\s+content="[^"]*"[^>]*>~i',
        '<meta name="description" content="' . $gpsSeoEscapedDescription . '">',
        $gpsSeoMeta
    );
    $gpsSeoMeta = preg_replace(
        '~<meta\\s+property="og:description"\\s+content="[^"]*"[^>]*>~i',
        '<meta property="og:description" content="' . $gpsSeoEscapedDescription . '">',
        $gpsSeoMeta
    );
    $gpsSeoMeta = preg_replace(
        '~<meta\\s+name="twitter:description"\\s+content="[^"]*"[^>]*>~i',
        '<meta name="twitter:description" content="' . $gpsSeoEscapedDescription . '">',
        $gpsSeoMeta
    );
    if (stripos($gpsSeoMeta, 'rel="canonical"') === false) {
        $gpsSeoMeta .= '<link rel="canonical" href="' . htmlspecialchars($gpsSeoCanonical, ENT_QUOTES, 'UTF-8') . '">';
    }
    if (stripos($gpsSeoMeta, 'name="robots"') === false) {
        $gpsSeoMeta .= '<meta name="robots" content="' . htmlspecialchars($gpsSeoRobots, ENT_QUOTES, 'UTF-8') . '">';
    }
    if (stripos($gpsSeoMeta, 'property="og:title"') === false) {
        $gpsSeoMeta .= '<meta property="og:type" content="website"><meta property="og:title" content="' . htmlspecialchars($gpsSeoTitle, ENT_QUOTES, 'UTF-8') . '">';
        $gpsSeoMeta .= '<meta property="og:description" content="' . htmlspecialchars(substr($gpsSeoDescription, 0, 200), ENT_QUOTES, 'UTF-8') . '">';
        $gpsSeoMeta .= '<meta property="og:url" content="' . htmlspecialchars($gpsSeoCanonical, ENT_QUOTES, 'UTF-8') . '">';
        $gpsSeoMeta .= '<meta property="og:site_name" content="' . htmlspecialchars((string)$config['site_name'], ENT_QUOTES, 'UTF-8') . '">';
    }
    if (stripos($gpsSeoMeta, 'name="twitter:card"') === false) {
        $gpsSeoMeta .= '<meta name="twitter:card" content="summary_large_image"><meta name="twitter:title" content="' . htmlspecialchars($gpsSeoTitle, ENT_QUOTES, 'UTF-8') . '">';
        $gpsSeoMeta .= '<meta name="twitter:description" content="' . htmlspecialchars(substr($gpsSeoDescription, 0, 200), ENT_QUOTES, 'UTF-8') . '"><meta name="twitter:url" content="' . htmlspecialchars($gpsSeoCanonical, ENT_QUOTES, 'UTF-8') . '">';
    }
    $themeData['header_metatags'] = $gpsSeoMeta;
}


$themeData['header_tags'] = \\GameMonetize\\UI::view('global/header/all');
if (!empty($gpsSeoPublicPaths)) {
    $gpsSeoFinalDescription = htmlspecialchars((string)($gpsSeoDescription ?? ''), ENT_QUOTES, 'UTF-8');
    $gpsSeoFinalHead = (string)$themeData['header_tags'];
    $gpsSeoFinalHead = preg_replace('~<meta\\s+name="description"\\s+content="[^"]*"[^>]*>~i', '<meta name="description" content="' . $gpsSeoFinalDescription . '">', $gpsSeoFinalHead);
    $gpsSeoFinalHead = preg_replace('~<meta\\s+property="og:description"\\s+content="[^"]*"[^>]*>~i', '<meta property="og:description" content="' . $gpsSeoFinalDescription . '">', $gpsSeoFinalHead);
    $gpsSeoFinalHead = preg_replace('~<meta\\s+name="twitter:description"\\s+content="[^"]*"[^>]*>~i', '<meta name="twitter:description" content="' . $gpsSeoFinalDescription . '">', $gpsSeoFinalHead);
    $themeData['header_tags'] = $gpsSeoFinalHead;
}
