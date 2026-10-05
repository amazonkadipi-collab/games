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
	
} else {
	
}

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 60;
$offset = ($page - 1) * $perPage;
$countResult = $GameMonetizeConnect->query("SELECT COUNT(*) AS total FROM " . GAMES . " WHERE published='1'");
$total = 0;
if ($countResult && ($countRow = $countResult->fetch_assoc())) $total = max(0, (int)($countRow['total'] ?? 0));
$totalPages = max(1, (int)ceil($total / $perPage));
if ($page > $totalPages) { $page = $totalPages; $offset = ($page - 1) * $perPage; }
$newGames_query = $GameMonetizeConnect->query("SELECT * FROM " . GAMES . " WHERE published='1' ORDER BY date_added DESC, game_id ASC LIMIT {$perPage} OFFSET {$offset}");

$ngm_r = '';
$ids = '';
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
	$themeData['new_game_video_url'] = isset($newGame_data['video_url']) ? $newGame_data['video_url'] : '';

	$themeData['new_game_wt_video'] = '';

	if (!empty($newGames['wt_video'])) {
		$themeData['new_game_wt_video'] = $newGames['wt_video'];
	}

	if (!empty($newGames['wt_video']) && preg_match('/([^\/]+\.mp4)$/', $newGames['wt_video'], $matches)) {
		$baseVideoThumbPath = $_SERVER['DOCUMENT_ROOT'] . '/games-thumb-video/' . $matches[1];

		if (file_exists($baseVideoThumbPath)) {
			$themeData['new_game_wt_video'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', $baseVideoThumbPath);
		}
	}

	// The separately installed Other Fixes PRO package limits NEW to the first
	// 24 hours after publication. Preserve the legacy Free CMS output when the
	// protected package is absent or not licensed for this installation.
	$themeData['new_games_icon'] = function_exists('gps_other_fixes_new_badge')
		? gps_other_fixes_new_badge($newGames['date_added'] ?? 0)
		: "<span class='new_icon'></span>";

	if (gps_theme_is('crazygames-like')) {
		$ngm_r .= \GameMonetize\UI::view('game/list-each/new-games-list-1');
	} else {
		$ngm_r .= \GameMonetize\UI::view('game/list-each/new-games-list');
	}

	$ids .= $newGames['game_id'] . ',';
}

$themeData['new_game_ids'] .= rtrim($ids, ',');
$themeData['new_game_page'] = "games";
$themeData['discovery_pagination'] = '';
$startPage = max(1, $page - 2);
$endPage = min($totalPages, $page + 2);
$links = '';
if ($page > 1) $links .= '<a href="' . siteUrl() . '/new-games?page=' . ($page - 1) . '" rel="prev">Previous</a>';
for ($i = $startPage; $i <= $endPage; $i++) $links .= ($i === $page) ? '<span class="active" aria-current="page">' . $i . '</span>' : '<a href="' . siteUrl() . '/new-games?page=' . $i . '">' . $i . '</a>';
if ($page < $totalPages) $links .= '<a href="' . siteUrl() . '/new-games?page=' . ($page + 1) . '" rel="next">Next</a>';
$themeData['discovery_pagination'] = '<nav class="poki-pagination" aria-label="New games pages">' . $links . '</nav>';
$themeData['discovery_pagination_label'] = 'Page ' . $page . ' of ' . $totalPages . ($total > 0 ? ' · ' . number_format($total) . ' games' : '');

$themeData['categories_list_home'] = '';
$themeData['tags_list_home'] = '';
$themeData['category_content'] = '';
gps_discovery_assign_page_description($themeData, 'new-games');

$themeData['new_games_list'] = $ngm_r;
$themeData['new_games'] = \GameMonetize\UI::view('game/new-games');

$themeData['page_content'] = \GameMonetize\UI::view('home/content');
