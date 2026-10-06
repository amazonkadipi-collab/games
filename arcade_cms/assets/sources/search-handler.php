<?php
if (isset($_GET['q']) && !empty($_GET['q'])) {
    $search_parameter = secureEncode($_GET['q']);
    $search_query = searchGames($search_parameter, $config['site_theme']);
    // Keep autocomplete focused on exact/prefix title matches before broader metadata matches.
    if (is_array($search_query) && count($search_query) > 1) {
        $searchTerm = strtolower(trim((string)$search_parameter));
        $scoreSearchResult = static function ($game) use ($searchTerm) {
            $data = gameData($game);
            $name = strtolower(trim((string)($data['name'] ?? $game['name'] ?? '')));
            $score = 0;
            if ($name === $searchTerm) $score += 10000;
            elseif ($name !== '' && strpos($name, $searchTerm) === 0) $score += 7000;
            elseif ($name !== '' && strpos($name, $searchTerm) !== false) $score += 4500;
            $score += min(500, (int)floor(log(1 + max(0, (int)($game['plays'] ?? 0)), 2)) * 10);
            return $score;
        };
        usort($search_query, static function ($a, $b) use ($scoreSearchResult) {
            return $scoreSearchResult($b) <=> $scoreSearchResult($a);
        });
    }
	if (gps_theme_is('poki-like')) {
		$search_query = array_slice($search_query, 0, 12);
	}

    if ($search_query) {
        foreach ($search_query as $game_search) {
            $get_game_data_search = gameData($game_search);
            $game_url = $get_game_data_search['game_url'];
            $game_image = $get_game_data_search['image_url'];
            $game_name = $get_game_data_search['name'];
			if (gps_theme_is('poki-like')
				&& preg_match('~/([A-Za-z0-9_-]+)/~', $game_image, $imageIdMatch)) {
				$localImagePath = $_SERVER['DOCUMENT_ROOT'] . '/games-image/' . $imageIdMatch[1] . '/250x150.webp';
				if (is_file($localImagePath)) {
					$game_image = '/games-image/' . $imageIdMatch[1] . '/250x150.webp';
				}
			}
			$safeGameUrl = htmlspecialchars($game_url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
			$safeGameImage = htmlspecialchars($game_image, ENT_QUOTES | ENT_HTML5, 'UTF-8');
			$safeGameName = htmlspecialchars($game_name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            echo "
            <a href='{$safeGameUrl}' class='flex items-center text-[15px] text-white w-full' aria-label='{$safeGameName}'>
                <img src='{$safeGameImage}' alt='{$safeGameName}' loading='lazy' width='48' height='36'>
                {$safeGameName}
            </a>";
        }
        echo "<a href='{$config['site_url']}/search/{$search_parameter}' class='flex items-center text-[15px] text-white w-full' aria-label='View all results'>View all results</a>";
    } else {
        echo "<p>No games found</p>";
    }
} else {
    echo "<p>Type something to search</p>";
}
