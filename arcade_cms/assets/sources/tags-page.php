<?php
require_once ABSPATH . 'assets/includes/discovery-description.php';

if (!empty($_GET['tag'])) {
	$get_tags_id = secureEncode($_GET['tag']);
	$themeData['new_game_page'] = "games";
	$sql_tag_query = $GameMonetizeConnect->query("SELECT * FROM " . TAGS . " WHERE url='" . $get_tags_id . "'");
	if ($sql_tag_query->num_rows > 0) {
		$get_tags = $sql_tag_query->fetch_array();

        $tagPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $tagPerPage = 60;
        $tagId = (int)$get_tags['id'];
        $tagPhrase = trim((string)preg_replace('/\\s+games?$/i', '', (string)$get_tags['name']));
        $tagGamesTotal = 0;
        $useNameFallback = false;

        $tagCountQuery = $GameMonetizeConnect->query(
            "SELECT COUNT(*) AS total FROM " . GAMES . " WHERE tags_ids LIKE '%\"" . $tagId . "\"%' AND published='1'"
        );
        if ($tagCountQuery && ($tagCountRow = $tagCountQuery->fetch_assoc())) {
            $tagGamesTotal = max(0, (int)($tagCountRow['total'] ?? 0));
        }
        if ($tagGamesTotal === 0 && mb_strlen($tagPhrase, 'UTF-8') >= 3) {
            $escapedTagPhrase = $GameMonetizeConnect->real_escape_string($tagPhrase);
            $fallbackCountQuery = $GameMonetizeConnect->query(
                "SELECT COUNT(*) AS total FROM " . GAMES . " WHERE name LIKE '%{$escapedTagPhrase}%' AND published='1'"
            );
            if ($fallbackCountQuery && ($fallbackCountRow = $fallbackCountQuery->fetch_assoc())) {
                $tagGamesTotal = max(0, (int)($fallbackCountRow['total'] ?? 0));
                $useNameFallback = $tagGamesTotal > 0;
            }
        }
        $tagTotalPages = max(1, (int)ceil($tagGamesTotal / $tagPerPage));
        if ($tagPage > $tagTotalPages) $tagPage = $tagTotalPages;
        $tagOffset = ($tagPage - 1) * $tagPerPage;

        if ($useNameFallback) {
            $escapedTagPhrase = $GameMonetizeConnect->real_escape_string($tagPhrase);
            $sql_c_games_query = $GameMonetizeConnect->query(
                "SELECT * FROM " . GAMES . " WHERE name LIKE '%{$escapedTagPhrase}%' AND published='1' ORDER BY featured DESC, plays DESC, game_id ASC LIMIT {$tagPerPage} OFFSET {$tagOffset}"
            );
        } else {
            $sql_c_games_query = $GameMonetizeConnect->query(
                "SELECT * FROM " . GAMES . " WHERE tags_ids LIKE '%\"" . $tagId . "\"%' AND published='1' ORDER BY featured DESC, plays DESC, game_id ASC LIMIT {$tagPerPage} OFFSET {$tagOffset}"
            );
        }

        $tagGamesRows = [];
        if ($sql_c_games_query) {
            while ($tagGameRow = $sql_c_games_query->fetch_array()) {
                $tagGamesRows[] = $tagGameRow;
            }
        }

		$themeData['tags_name'] = ucwords($get_tags['name']);
		$themeData['tags_games_title'] = preg_match('/\bgames$/i', $themeData['tags_name'])
			? $themeData['tags_name']
			: $themeData['tags_name'] . ' Games';
		$ids = '';
		if (!empty($tagGamesRows)) {
			$ctgm_r = '';
			foreach ($tagGamesRows as $tag_games) {
				$get_game_data = gameData($tag_games);
				$themeData['tags_game_name'] = $get_game_data['name'];
				$themeData['tags_game_url'] = $get_game_data['game_url'];
				$themeData['tags_game_image'] = $get_game_data['image_url'];

				preg_match("/\/([a-zA-Z0-9]+)\//", $get_game_data['image_url'], $matches);
				$baseImagePath = $_SERVER['DOCUMENT_ROOT'] . '/games-image/' . $matches[1] . '/250x150.webp';
				if (file_exists($baseImagePath)) {
					$themeData['tags_game_image'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', $baseImagePath);
				} else {
					$themeData['tags_game_image'] = $get_game_data['image_url'];
				}

				$themeData['tags_game_rating'] = $tag_games['rating'];
				$themeData['tags_game_video_url'] = $tag_games['video_url'];
				$themeData['tags_game_wt_video'] = $tag_games['wt_video'];
				$themeData['tags_game_featured_icon'] = ((int)($tag_games['featured'] ?? 0) === 1)
					? '<span class="featured_icon"></span>'
					: '';

				preg_match('/([^\/]+\.mp4)$/', $tag_games['wt_video'], $matches);
				$baseVideoThumbPath = $_SERVER['DOCUMENT_ROOT'] . '/games-thumb-video/' . $matches[1];
				if (file_exists($baseVideoThumbPath)) {
					$themeData['tags_game_wt_video'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', $baseVideoThumbPath);
				} else {
					$themeData['tags_game_wt_video'] = $tag_games['wt_video'];
				}

				$ctgm_r .= \GameMonetize\UI::view('category/tags-games-list');

				$ids .= $tag_games['game_id'] . ',';
			}
			$themeData['tags_games_list'] = $ctgm_r;
		} else {
			$themeData['tags_games_list'] = \GameMonetize\UI::view('category/category-games-notfound');
		}
		$themeData['tagsid'] = $get_tags['id'];

		$themeData['new_game_ids'] .= rtrim($ids, ',');
		$themeData['footer_description'] = htmlspecialchars_decode($get_tags['footer_description']);
		$descriptionParts = gps_discovery_split_description($themeData['footer_description']);
		$themeData['header_desc'] = $descriptionParts['header'];
		$themeData['footer_description_modified'] = $descriptionParts['footer'];


        $tagLinks = '';
        $tagStart = max(1, $tagPage - 2);
        $tagEnd = min($tagTotalPages, $tagPage + 2);
        if ($tagPage > 1) $tagLinks .= '<a href="' . siteUrl() . '/tag/' . $get_tags['url'] . '?page=' . ($tagPage - 1) . '" rel="prev">Previous</a>';
        for ($tagI = $tagStart; $tagI <= $tagEnd; $tagI++) {
            $tagLinks .= ($tagI === $tagPage)
                ? '<span class="active" aria-current="page">' . $tagI . '</span>'
                : '<a href="' . siteUrl() . '/tag/' . $get_tags['url'] . '?page=' . $tagI . '">' . $tagI . '</a>';
        }
        if ($tagPage < $tagTotalPages) $tagLinks .= '<a href="' . siteUrl() . '/tag/' . $get_tags['url'] . '?page=' . ($tagPage + 1) . '" rel="next">Next</a>';
        $themeData['tags_pagination'] = '<nav class="poki-pagination" aria-label="Tag games pages">' . $tagLinks . '</nav>';
        $themeData['tags_page_label'] = 'Page ' . $tagPage . ' of ' . $tagTotalPages . ($tagGamesTotal > 0 ? ' · ' . number_format($tagGamesTotal) . ' games' : '');
		$themeData['tags_content'] = \GameMonetize\UI::view('category/tags-games');
	} else {
		$themeData['tags_content'] = \GameMonetize\UI::view('category/category-notfound');
	}
} else {
	$sql_tag_query = $GameMonetizeConnect->query("SELECT * FROM " . TAGS);
	$ct_r = '';
	while ($tags = $sql_tag_query->fetch_array()) {
		$themeData['tags_id'] = $tags['id'];
		$themeData['tags_name'] = $tags['name'];

		$baseTagImagePath = 'tag-img/' . slugify($tags['name']);
		$formats = ['.png', '.webp'];
		$defaultTagImagePath = 'templates/poki-like/image/tag.png';

		$themeData['tags_thumb'] = $defaultTagImagePath; // Default value

		foreach ($formats as $format) {
			if (file_exists($baseTagImagePath . $format)) {
				$themeData['tags_thumb'] = $baseTagImagePath . $format;
				break;
			}
		}
		
		$numbergames = $GameMonetizeConnect->query("SELECT COUNT(*) FROM " . GAMES . " where tags_ids LIKE '%\"{$tags['id']}\"%'");
		$numbergames = $numbergames->fetch_array()[0];

		$themeData['tags_number'] = $numbergames;
		$themeData['tags_url'] = siteUrl() . '/tag/' . slugify($tags['name']);
		$ct_r .= \GameMonetize\UI::view('category/tags-list');
	}

	/*
		$countactiongames = $GameMonetizeConnect->query("SELECT COUNT(*) FROM " . GAMES ." where category=1");
		$countactiongames = $countactiongames->fetch_array()[0];

		$countracinggames = $GameMonetizeConnect->query("SELECT COUNT(*) FROM " . GAMES ." where category=2");
		$countracinggames = $countracinggames->fetch_array()[0];

		$countshootinggames = $GameMonetizeConnect->query("SELECT COUNT(*) FROM " . GAMES ." where category=3");
		$countshootinggames = $countshootinggames->fetch_array()[0];

		$countarcadegames = $GameMonetizeConnect->query("SELECT COUNT(*) FROM " . GAMES ." where category=4");
		$countarcadegames = $countarcadegames->fetch_array()[0];

		$countpuzzlegames = $GameMonetizeConnect->query("SELECT COUNT(*) FROM " . GAMES ." where category=5");
		$countpuzzlegames = $countpuzzlegames->fetch_array()[0];

		$countmultiplayergames = $GameMonetizeConnect->query("SELECT COUNT(*) FROM " . GAMES ." where category=7");
		$countmultiplayergames = $countmultiplayergames->fetch_array()[0];

		$countsportsgames = $GameMonetizeConnect->query("SELECT COUNT(*) FROM " . GAMES ." where category=8");
		$countsportsgames = $countsportsgames->fetch_array()[0];

		$countfightinggames = $GameMonetizeConnect->query("SELECT COUNT(*) FROM " . GAMES ." where category=9");
		$countfightinggames = $countfightinggames->fetch_array()[0];

		$themeData['categories_list'] = $ct_r;

		$themeData['count_action_games'] = $countactiongames;
		$themeData['count_racing_games'] = $countracinggames;
		$themeData['count_shooting_games'] = $countshootinggames;
		$themeData['count_arcade_games'] = $countarcadegames;
		$themeData['count_puzzle_games'] = $countpuzzlegames;
		$themeData['count_multiplayer_games'] = $countmultiplayergames;
		$themeData['count_sports_games'] = $countsportsgames;
		$themeData['count_fighting_games'] = $countfightinggames;
		*/
	$themeData['categories_list'] = $ct_r;
	$themeData['tags_content'] = \GameMonetize\UI::view('game/tags');
}
$themeData['page_content'] = \GameMonetize\UI::view('category/tags-content');
