<?php
// Full public game archive with stable pagination. Uses the same card renderer as the main catalog.
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 60;
$offset = ($page - 1) * $perPage;

$total = 0;
if (isset($GameMonetizeConnect)) {
    $countResult = $GameMonetizeConnect->query("SELECT COUNT(*) AS total FROM " . GAMES . " WHERE published='1' AND game_id IS NOT NULL AND name IS NOT NULL AND TRIM(name) <> ''");
    if ($countResult && ($countRow = $countResult->fetch_assoc())) {
        $total = max(0, (int)($countRow['total'] ?? 0));
    }
}
$totalPages = max(1, (int)ceil($total / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

$themeData['all_games_list'] = '';
$query = $GameMonetizeConnect->query(
    "SELECT * FROM " . GAMES . " WHERE published='1' AND game_id IS NOT NULL AND name IS NOT NULL AND TRIM(name) <> '' ORDER BY name ASC, game_id ASC LIMIT {$perPage} OFFSET {$offset}"
);

if ($query) {
    while ($game = $query->fetch_array()) {
        $data = gameData($game);
        $themeData['new_game_url'] = $data['game_url'];
        $themeData['new_game_name'] = $data['name'];
        $themeData['new_game_image'] = $data['image_url'];
        $themeData['new_game_video_url'] = $data['video_url'] ?? '';
        $themeData['new_game_wt_video'] = $data['wt_video'] ?? ($game['wt_video'] ?? '');
        $themeData['new_game_featured'] = $data['featured'] ?? '';
        $themeData['all_games_list'] .= GameMonetizeUI::view('game/list-each/new-games-list');
    }
}

$pages = '';
$start = max(1, $page - 2);
$end = min($totalPages, $page + 2);

if ($page > 1) {
    $pages .= '<a href="' . siteUrl() . '/all-games?page=' . ($page - 1) . '" rel="prev">Previous</a>';
}
for ($i = $start; $i <= $end; $i++) {
    if ($i === $page) {
        $pages .= '<span class="active" aria-current="page">' . $i . '</span>';
    } else {
        $pages .= '<a href="' . siteUrl() . '/all-games?page=' . $i . '">' . $i . '</a>';
    }
}
if ($page < $totalPages) {
    $pages .= '<a href="' . siteUrl() . '/all-games?page=' . ($page + 1) . '" rel="next">Next</a>';
}

$themeData['all_games_pagination'] = '<nav class="poki-pagination" aria-label="All games pages">' . $pages . '</nav>';
$themeData['all_games_page_label'] = 'Page ' . $page . ' of ' . $totalPages . ($total > 0 ? ' · ' . number_format($total) . ' games' : '');
$themeData['page_content'] = GameMonetizeUI::view('all-games/content');
