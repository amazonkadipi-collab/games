<?php
require_once ABSPATH . 'assets/includes/discovery-description.php';

$themeData['ads_header'] = getADS('728x90');
$themeData['ads_footer'] = getADS('300x250');
$themeData['ads_sidebar'] = getADS('600x300');
$themeData['ads_top'] = getADS('728x90_main');

$date =  date('Ymdms');
$date = strtotime($date);
$themeData['cms'] = "<script src='https://api.gamemonetize.com/cms.js?" . $date . "'></script>";
# >>


if (gps_theme_is('poki-like')) {
	$newGames_query = $GameMonetizeConnect->query("SELECT * FROM " . GAMES . " WHERE published='1' AND featured='1' ORDER BY date_added DESC LIMIT 60");
} else {
	$newGames_query = $GameMonetizeConnect->query("SELECT * FROM " . GAMES . " WHERE published='1' AND featured='1' ORDER BY date_added DESC");
}

$ngm_r = '';
while ($newGames = $newGames_query->fetch_array()) {
	$newGame_data = gameData($newGames);
	$themeData['new_game_url'] = $newGame_data['game_url'];
	preg_match("/\/([a-zA-Z0-9]+)\//", $newGame_data['image_url'], $matches);
	$baseImagePath = $_SERVER['DOCUMENT_ROOT'] . '/games-image/' . $matches[1] . '/250x150.webp';
	if (file_exists($baseImagePath)) {
		$themeData['new_game_image'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', $baseImagePath);
	} else {
		$themeData['new_game_image'] = $newGame_data['image_url'];
	}
	$themeData['new_game_name'] = $newGame_data['name'];
	$themeData['new_game_video_url'] = $newGame_data['video_url'];
	$themeData['new_game_wt_video'] = isset($newGame_data['wt_video']) ? $newGame_data['wt_video'] : '';

	preg_match('/([^\/]+\.mp4)$/', $newGames['wt_video'], $matches);
	$baseVideoThumbPath = $_SERVER['DOCUMENT_ROOT'] . '/games-thumb-video/' . $matches[1];
	if (file_exists($baseVideoThumbPath)) {
		$themeData['new_game_wt_video'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', $baseVideoThumbPath);
	} else {
		$themeData['new_game_wt_video'] = isset($newGame_data['wt_video']) ? $newGame_data['wt_video'] : '';
	}

	$themeData['new_game_featured'] = $newGame_data['featured'];

	if (gps_theme_is('crazygames-like')) {
		$ngm_r .= \GameMonetize\UI::view('game/list-each/new-games-list-1');
	} else {
		$ngm_r .= \GameMonetize\UI::view('game/list-each/new-games-list');
	}
}

$themeData['discovery_pagination'] = '';
$themeData['discovery_pagination_label'] = '';

$pageLabel = 'Page ' . $page . ' of ' . $totalPages . ($total > 0 ? ' · ' . number_format($total) . ' games' : '');
$pageLabelLinks = '';
if ($page > 1) $pageLabelLinks .= '<a href="' . siteUrl() . '/featured-games?page=' . ($page - 1) . '" rel="prev">Previous</a>';
$pageLabelStart = max(1, $page - 2);
$pageLabelEnd = min($totalPages, $page + 2);
for ($i = $pageLabelStart; $i <= $pageLabelEnd; $i++) {
    $pageLabelLinks .= ($i === $page)
        ? '<span class="active" aria-current="page">' . $i . '</span>'
        : '<a href="' . siteUrl() . '/featured-games?page=' . $i . '">' . $i . '</a>';
}
if ($page < $totalPages) $pageLabelLinks .= '<a href="' . siteUrl() . '/featured-games?page=' . ($page + 1) . '" rel="next">Next</a>';
$themeData['discovery_pagination_label'] = $pageLabel;
$themeData['discovery_pagination'] = '<nav class="poki-pagination" aria-label="Game pages">' . $pageLabelLinks . '</nav>';


if (gps_theme_is('y8-like') || gps_theme_is('poki-like')) {
	$sql_cat_query = $GameMonetizeConnect->query("SELECT * FROM " . CATEGORIES . " WHERE show_home='1'");
	$ct_r = '';
	while ($category = $sql_cat_query->fetch_array()) {
		$themeData['category_id'] = $category['id'];
		$themeData['category_name'] = $category['name'];
		$themeData['category_image'] = $category['image'];

		$numbergames = $GameMonetizeConnect->query("SELECT COUNT(*) FROM " . GAMES . " where category=" . $category['id']);
		$numbergames = $numbergames->fetch_array()[0];

		$themeData['category_number'] = $numbergames;
		$themeData['category_url'] = siteUrl() . '/category/'	. slugify($category['name']);
		$ct_r .= \GameMonetize\UI::view('category/categories-list-home');
	}

	$themeData['categories_list_home'] = $ct_r;
	$themeData['category_content'] = \GameMonetize\UI::view('category/categories-list-home');

	$sql_tag_query = $GameMonetizeConnect->query("SELECT * FROM " . TAGS . " WHERE show_home='1'");
	$tag_r = '';
	while ($tag = $sql_tag_query->fetch_array()) {
		$themeData['tag_id'] = $tag['id'];
		$themeData['tag_name'] = $tag['name'];

		$baseTagImagePath = 'tag-img/' . slugify($tag['name']);
		$formats = ['.png', '.webp'];
		$defaultTagImagePath = 'templates/poki-like/image/tag.png';

		$themeData['tag_image'] = $defaultTagImagePath; // Default value

		foreach ($formats as $format) {
			if (file_exists($baseTagImagePath . $format)) {
				$themeData['tag_image'] = $baseTagImagePath . $format;
				break;
			}
		}

		$themeData['tag_url'] = siteUrl() . '/tag/'	. slugify($tag['name']);
		$tag_r .= \GameMonetize\UI::view('tags/tags-list-home');
	}

	$themeData['tags_list_home'] = $tag_r;
}

// Get setting data
$settingDataQuery = "SELECT * FROM " . SETTING . " LIMIT 1";
$settingData = $GameMonetizeConnect->query($settingDataQuery);
$settingData = $settingData->fetch_array();

if ($settingData["is_sidebar_enabled"]) {
	$themeData['categories_tags_home'] = "";
} else {
	$themeData['categories_tags_home'] = \GameMonetize\UI::view('home/categories-tags-home');
}

gps_discovery_assign_page_description($themeData, 'featured-games');

$themeData['new_games_list'] = $ngm_r;
$themeData['new_games'] = \GameMonetize\UI::view('game/new-games');

$themeData['page_content'] = \GameMonetize\UI::view('home/content');
