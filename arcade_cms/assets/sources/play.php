<?php
$date =  time();
$themeData['cms'] = "<script type='text/javascript' src='https://api.gamemonetize.com/cms.js?" . $date . "'></script>";
$themeData['ima_sdk'] = "<script type='text/javascript'  src='//imasdk.googleapis.com/js/sdkloader/ima3.js'></script>
<script type='text/javascript' src='https://api.gamemonetize.com/imasdk.js?" . $date . "'></script>";
$ads_video_code = getADS('ads_video');
if ($ads_video_code) { 
    if (!preg_match('/\bdescription_url\b/', $ads_video_code)) {
        $themeData['ads_video'] = $ads_video_code . '&description_url=" + encodeURIComponent(descriptionURL) + "&correlator=' . $date;
    }
}
if (!empty($_GET['id'])) {
    $get_game_id = $_GET['id'];

    $get_game = getGame2($get_game_id);

    if ($get_game) {
        $get_game_data = gameData($get_game);
        $themeData['ads_header'] = getADS('728x90');
        $themeData['ads_header_display'] = '';
        $themeData['game_col_margin_top'] = 0;
        if(strlen($themeData['ads_header']) < 1){
            $themeData['game_col_margin_top'] = 85;
            $themeData['ads_header_display'] = 'none';
        }
        if($themeData['config_is_sidebar_enabled'] == 1){
            $themeData['game_col_margin_top'] = 10;
        }
        $themeData['ads_header_hide'] = "";
        if ($themeData['ads_header'] == "") {
            $themeData['ads_header_hide'] = "hide";
        }
        $actual_link = $config['theme_path'];
        if (getADS('300x250') != "") {
            $themeData['ads_footer'] = getADS('300x250');
        } else {
            $themeData['ads_footer'] = '<a class="ad300" href="https://gamemonetize.com/" target="_blank" style="display: inline;"><img src="' . $actual_link . '/image/banner/1450344261.jpg"></a>
                    <a class="ad300" href="https://gamemonetize.com/" target="_blank" style="display: none;"><img src="' . $actual_link . '/image/banner/1448529775.jpg"></a>
                    <a class="ad300" href="https://gamemonetize.com/" target="_blank" style="display: none;"><img src="' . $actual_link . '/image/banner/1453363439.jpg"></a>
                    <a class="ad300" href="https://gamemonetize.com/" target="_blank" style="display: none;"><img src="' . $actual_link . '/image/banner/1449131593.jpg"></a>
                    <a class="ad300" href="https://gamemonetize.com/" target="_blank" style="display: none;"><img src="' . $actual_link . '/image/banner/1455786054.jpg"></a>
                    <a class="ad300" href="https://gamemonetize.com/" target="_blank" style="display: none;"><img src="' . $actual_link . '/image/banner/1456391965.jpg"></a>
                    <a class="ad300" href="https://gamemonetize.com/" target="_blank" style="display: none;"><img src="' . $actual_link . '/image/banner/1450951822.jpg"></a>
                    <a class="ad300" href="https://gamemonetize.com/" target="_blank" style="display: none;"><img src="' . $actual_link . '/image/banner/1449733199.jpg"></a>';
        }
        $sidebar_ads = getADS('600x300');
        if ($sidebar_ads != "") {
            $themeData['ads_sidebar'] = '<div class="ad160 bgs fn-left">
                                                    ' . $sidebar_ads . '
                                                </div>';
        } else {
            $themeData['ads_sidebar'] = '<div class="fn-left" style="width: 160px;text-align: center;padding: 10px;"></div>';
        }
        $themeData['play_game_embed'] = $get_game_data['embed'];
        $themeData['play_game_embed2'] = $get_game_data['file'];
        $themeData['play_game_name'] = $get_game_data['name'];
        $themeData['play_game_image'] = $get_game_data['image_url'];
        preg_match("/\/([a-zA-Z0-9]+)\//", $get_game_data['image_url'], $matches);
		$baseImagePath = $_SERVER['DOCUMENT_ROOT'] . '/games-image/' . $matches[1] . '/250x150.webp';
		if (file_exists($baseImagePath)) {
			$themeData['play_game_image'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', $baseImagePath);
		} else {
			$themeData['play_game_image'] = $get_game_data['image_url'];
		}
        $themeData['play_game_url'] = $get_game_data['game_url'];
        $themeData['play_game_date'] = is_numeric($get_game_data['date_added'] ?? null) ? date('F j, Y', (int)$get_game_data['date_added']) : (string)($get_game_data['date_added'] ?? '');
        $playCount = (int)($get_game_data['plays'] ?? 0);
        $themeData['play_game_plays'] = $playCount > 0 ? numberFormat($playCount) : '';
        $themeData['play_game_plays_meta'] = $playCount > 0 ? '<span><i class="fa-solid fa-gamepad" aria-hidden="true"></i><strong>' . numberFormat($playCount) . '</strong> plays</span>' : '';
            $themeData['play_game_likes'] = isset($get_game['like_count']) ? (int)$get_game['like_count'] : 0;
            $themeData['play_game_dislikes'] = isset($get_game['dislike_count']) ? (int)$get_game['dislike_count'] : 0;
            $themeData['play_game_favorites'] = isset($get_game['favorite_count']) ? (int)$get_game['favorite_count'] : 0;
        // replace &lt; and &gt; with < and >
        $description = str_replace(array('&lt;', '&gt;', '&#039;'), array('<', '>', ''), $get_game_data['description']);
        $description = preg_replace('/\[(.*?)\]\((https?:\/\/[^\)]+)\)/', '<a href="$2">$1</a>', $description);
        $themeData['play_game_desc'] = html_entity_decode(html_entity_decode($description), ENT_QUOTES, 'UTF-8');
        $themeData['play_game_inst'] = $get_game_data['instructions'];
        $themeData['play_game_rating'] = $get_game['rating'];
        $themeData['play_game_id'] = $get_game['game_id'];
        $themeData['play_game_display'] = ($config['ads_status']) ? 'display:none;' : '';
        $themeData['play_game_video_url'] = $get_game['video_url'];
        $themeData['play_game_category_name'] = $get_game['category_name'];
        $themeData['play_game_category_image'] = $get_game['category_image'];
        $themeData['play_game_category_url'] = slugify($get_game['category_name']);
        $gameCanonicalUrl = siteUrl() . '/game/' . slugify($get_game_data['name']);
        $schemaDescription = trim(preg_replace('/\\s+/', ' ', strip_tags((string)$get_game_data['description'])));
        $themeData['play_game_schema'] = '<script type="application/ld+json">' . json_encode(array(
            '@context' => 'https://schema.org',
            '@type' => 'VideoGame',
            'name' => (string)$get_game_data['name'],
            'url' => $gameCanonicalUrl,
            'image' => (string)$get_game_data['image_url'],
            'description' => $schemaDescription,
            'genre' => (string)$get_game['category_name'],
            'applicationCategory' => 'Game',
            'offers' => array('@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'USD')
        ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
        $themeData['play_game_breadcrumb'] = '<nav class="pg-breadcrumb" aria-label="Breadcrumb">'
            . '<a href="' . htmlspecialchars(siteUrl(), ENT_QUOTES, 'UTF-8') . '">Home</a><span aria-hidden="true">›</span>'
            . '<a href="' . htmlspecialchars(siteUrl() . '/category/' . slugify($get_game['category_name']), ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars((string)$get_game['category_name'], ENT_QUOTES, 'UTF-8') . '</a><span aria-hidden="true">›</span>'
            . '<span aria-current="page">' . htmlspecialchars((string)$get_game_data['name'], ENT_QUOTES, 'UTF-8') . '</span></nav>';
        $themeData['play_game_schema'] .= '<script type="application/ld+json">' . json_encode(array(
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array(
                array('@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => siteUrl() . '/'),
                array('@type' => 'ListItem', 'position' => 2, 'name' => (string)$get_game['category_name'], 'item' => siteUrl() . '/category/' . slugify($get_game['category_name'])),
                array('@type' => 'ListItem', 'position' => 3, 'name' => (string)$get_game_data['name'], 'item' => $gameCanonicalUrl)
            )
        ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';

        $themeData['play_game_walkthrough'] = "";
        if(strlen($get_game['video_url'])){
            $themeData['play_game_walkthrough'] = "<a href='".$get_game['video_url']."' target='_blank'>Walkthrough</a>";
        }

        // Smart related-games rail: keep every published game in the catalog.
        // Discovery is ranked, not filtered: same category/tags/type are preferred,
        // then popularity/recency. Missing metadata simply receives a lower score.
        $relatedGamesHtml = '';
        $relatedSeen = array((int)$get_game['game_id']);
        $currentCategory = (int)$get_game['category'];
        $currentType = strtolower(trim((string)($get_game_data['game_type'] ?? $get_game['game_type'] ?? '')));
        $currentTagIds = is_string($get_game['tags_ids'] ?? '') ? json_decode($get_game['tags_ids'], true) : ($get_game['tags_ids'] ?? array());
        $currentTagIds = is_array($currentTagIds) ? array_values(array_filter(array_map('intval', $currentTagIds), static function ($id) { return $id > 0; })) : array();

        $relatedPool = array();
        $poolQuery = $GameMonetizeConnect->query(
            "SELECT * FROM " . GAMES . " WHERE published='1' AND game_id != " . (int)$get_game['game_id']
            . " AND (category=" . $currentCategory . " OR game_type='" . $GameMonetizeConnect->real_escape_string((string)($get_game_data['game_type'] ?? $get_game['game_type'] ?? '')) . "')"
            . " ORDER BY plays DESC, date_added DESC LIMIT 80"
        );
        if ($poolQuery) {
            while ($candidate = $poolQuery->fetch_array()) $relatedPool[] = $candidate;
        }

        $scoreRelated = static function ($candidate) use ($currentCategory, $currentType, $currentTagIds) {
            $score = 0;
            if ((int)($candidate['category'] ?? 0) === $currentCategory) $score += 100;
            $candidateType = strtolower(trim((string)($candidate['game_type'] ?? '')));
            if ($currentType !== '' && $candidateType !== '' && $candidateType === $currentType) $score += 30;
            $candidateTags = is_string($candidate['tags_ids'] ?? '') ? json_decode($candidate['tags_ids'], true) : ($candidate['tags_ids'] ?? array());
            $candidateTags = is_array($candidateTags) ? array_map('intval', $candidateTags) : array();
            if ($currentTagIds && $candidateTags) {
                $overlap = count(array_intersect($currentTagIds, $candidateTags));
                $score += min(45, $overlap * 15);
            }
            $plays = max(0, (int)($candidate['plays'] ?? 0));
            $score += min(25, (int)floor(log(1 + $plays, 2)));
            return $score;
        };
        usort($relatedPool, static function ($a, $b) use ($scoreRelated) {
            return $scoreRelated($b) <=> $scoreRelated($a);
        });

        $renderRelated = static function ($relatedGame) use (&$relatedSeen, &$relatedGamesHtml, $get_game) {
            $relatedId = (int)$relatedGame['game_id'];
            if (in_array($relatedId, $relatedSeen, true)) return false;
            $relatedSeen[] = $relatedId;
            $relatedData = gameData($relatedGame);
            $relatedName = htmlspecialchars((string)$relatedData['name'], ENT_QUOTES, 'UTF-8');
            $relatedUrl = htmlspecialchars((string)$relatedData['game_url'], ENT_QUOTES, 'UTF-8');
            $relatedImage = htmlspecialchars((string)$relatedData['image_url'], ENT_QUOTES, 'UTF-8');
            $relatedPlays = (int)($relatedGame['plays'] ?? 0);
            $relatedCategory = htmlspecialchars((string)($relatedData['category_name'] ?? $get_game['category_name'] ?? ''), ENT_QUOTES, 'UTF-8');
            $relatedMeta = $relatedPlays > 0 ? ' · ' . numberFormat($relatedPlays) . ' plays' : '';
            $relatedGamesHtml .= '<a class="pg-related-card" href="' . $relatedUrl . '">'
                . '<span class="pg-related-image"><img src="' . $relatedImage . '" alt="' . $relatedName . '" loading="lazy" decoding="async"></span>'
                . '<span class="pg-related-copy"><strong>' . $relatedName . '</strong><span>' . $relatedCategory . $relatedMeta . '</span></span></a>';
            return true;
        };

        foreach ($relatedPool as $candidate) {
            if (count($relatedSeen) >= 9) break;
            $renderRelated($candidate);
        }

        // Final fallback guarantees a populated recommendation rail without removing
        // any published game from discovery.
        if (count($relatedSeen) < 5) {
            $fallbackQuery = $GameMonetizeConnect->query(
                "SELECT * FROM " . GAMES . " WHERE published='1' AND game_id != " . (int)$get_game['game_id']
                . " ORDER BY plays DESC, date_added DESC LIMIT 40"
            );
            if ($fallbackQuery) {
                while ($candidate = $fallbackQuery->fetch_array()) {
                    if (count($relatedSeen) >= 9) break;
                    $renderRelated($candidate);
                }
            }
        }
        $themeData['play_related_games'] = $relatedGamesHtml;
        $categoryMoreHtml = '';
        $categoryMoreQuery = $GameMonetizeConnect->query("SELECT * FROM " . GAMES . " WHERE published='1' AND game_id != " . (int)$get_game['game_id'] . " AND category=" . (int)$get_game['category'] . " ORDER BY plays DESC, date_added DESC LIMIT 8 OFFSET 8");
        if ($categoryMoreQuery) {
            while ($categoryGame = $categoryMoreQuery->fetch_array()) {
                $categoryData = gameData($categoryGame);
                $categoryName = htmlspecialchars((string)$categoryData['name'], ENT_QUOTES, 'UTF-8');
                $categoryUrl = htmlspecialchars((string)$categoryData['game_url'], ENT_QUOTES, 'UTF-8');
                $categoryImage = htmlspecialchars((string)$categoryData['image_url'], ENT_QUOTES, 'UTF-8');
                $categoryPlays = (int)($categoryGame['plays'] ?? 0);
                $categoryMeta = $categoryPlays > 0 ? ' · ' . numberFormat($categoryPlays) . ' plays' : '';
                $categoryMoreHtml .= '<a class="pg-related-card" href="' . $categoryUrl . '"><span class="pg-related-image"><img src="' . $categoryImage . '" alt="' . $categoryName . '" loading="lazy" decoding="async"></span><span class="pg-related-copy"><strong>' . $categoryName . '</strong><span>' . htmlspecialchars((string)$get_game['category_name'], ENT_QUOTES, 'UTF-8') . $categoryMeta . '</span></span></a>';
            }
        }
        $themeData['play_more_category_section'] = $categoryMoreHtml !== ''
            ? '<section class="pg-related-section pg-more-category-section" aria-labelledby="pg-category-heading"><div class="pg-section-heading"><div><span class="pg-section-kicker">EXPLORE</span><h2 id="pg-category-heading">More from ' . htmlspecialchars((string)$get_game['category_name'], ENT_QUOTES, 'UTF-8') . '</h2></div></div><div class="pg-related-grid">' . $categoryMoreHtml . '</div></section>'
            : '';
        $instructions = trim((string)$get_game_data['instructions']);
        $themeData['play_game_controls_block'] = $instructions !== '' ? '<section class="pg-info-card pg-controls-card"><h2>How to Play</h2><div class="pg-controls-copy">' . $instructions . '</div></section>' : '';
        $descriptionText = trim((string)$themeData['play_game_desc']);
        $themeData['play_game_about_block'] = $descriptionText !== '' ? '<section class="pg-info-card"><h2>About this game</h2><div class="pg-info-copy">' . $themeData['play_game_desc'] . '</div></section>' : '';

        $similarGames = getSidebarWidget('similar-name', $get_game_data['name']);
        $themeData['play_sidebar_widgets'] = '';
        
        if (!gps_theme_is('poki-like')) {
            $anotherSimilarGames = getSidebarWidget('similar-name', $get_game_data['name'], $similarGames[1]);
            $themeData['play_sidebar_widgets2'] = $anotherSimilarGames[0];

            $themeData['play_sidebar_widgets3'] = getSidebarWidget('random');
            // $themeData['play_sidebar_widgets5'] = getSidebarWidget('top-user');
            //$themeData['play_game_featured'] = getFeaturedGames();
            //$themeData['play_widget_carousel_random_games'] = getCarouselWidget('carousel_random_games', 3);
            $themeData['play_game_ads_counter'] = ($config['ads_status']) ? \GameMonetize\UI::view('game/play-ads-counter') : '';
        }


        $themeData['description_url'] = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        /* Main page content */
        $gameImage = explode("/", $themeData['game_meta_image']);
        $themeData['game_unique_id'] = $gameImage[3];
        $themeData['game_video_url'] = $get_game_data['wt_video'];
		$themeData['play_game_video_block'] = '';

        preg_match('/([^\/]+\.mp4)$/', $get_game_data['wt_video'], $matches);
        $baseVideoThumbPath = $_SERVER['DOCUMENT_ROOT'] . '/games-thumb-video/' . $matches[1];
        if (file_exists($baseVideoThumbPath)) {
            $themeData['game_video_url'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', $baseVideoThumbPath);
        } else {
            $themeData['game_video_url'] = $get_game_data['wt_video'];
        }

        // Game tags
        $gameTags = $get_game["tags_ids"];
        $tags_list = "";

        $gameTagIds = is_string($gameTags) ? json_decode($gameTags, true) : $gameTags;
        $gameTagIds = is_array($gameTagIds) ? array_values(array_filter(array_map('intval', $gameTagIds), static function ($id) {
            return $id > 0;
        })) : [];

        if (!empty($gameTagIds)) {
            if (gps_theme_is('crazygames-like')) {
                $themeData['tags_url'] = siteUrl() . "/category/" . slugify($get_game['category_name']);
                $themeData['tags_name'] = $get_game['category_name'];

                $themeData['tags_number'] = $get_game['category_total_games'];
                $tags_list .= \GameMonetize\UI::view('game/tags/tags-list');
            }
            
            $sqlGetTags = "SELECT * FROM " . TAGS . " WHERE id IN(" . implode(',', $gameTagIds) . ") ORDER BY name";
            $sqlQueryTags = $GameMonetizeConnect->query($sqlGetTags);
            
            if ($sqlQueryTags->num_rows > 0) {
                $firstTag = $sqlQueryTags->fetch_array();
                $themeData['play_game_first_tag_name'] = $firstTag['name'];
                $themeData['play_game_first_tag_url'] = $firstTag['url'];

                $sqlQueryTags->data_seek(0);

                while ($tags = $sqlQueryTags->fetch_assoc()) {
                    $themeData['tags_url'] = siteUrl() . "/tag/" . $tags['url'];
                    $themeData['tags_name'] = ucwords($tags['name']);

                    $baseTagImagePath = 'tag-img/' . slugify($tags['name']);
                    $formats = ['.png', '.webp'];
                    $defaultTagImagePath = '../templates/poki-like/image/tag.png';

                    $themeData['tags_image'] = $defaultTagImagePath;

                    $themeData['tags_number'] = $tags['total_games'];

                    foreach ($formats as $format) {
                        if (file_exists($baseTagImagePath . $format)) {
                            $themeData['tags_image'] = "../" . $baseTagImagePath . $format;
                            break;
                        }
                    }

                    //$tag['image'] == '' ? $themeData['tags_image'] = '../templates/poki-like/image/tag.png' : $themeData['tags_image'] = $tag['image'];
                    $tags_list .= \GameMonetize\UI::view('game/tags/tags-list');
                    if (gps_theme_is('poki-like')) {
                        $tag_list_grid .= \GameMonetize\UI::view('game/tags/tag-list-grid');
                    }
                }
            } else {
                $tags_list .= "No tags found.";
            }

            $themeData["tags_list"] = $tags_list;
            $themeData['tags_list_grid'] = $tag_list_grid;
        }

        
	    $themeData['ads_300'] = getADS('300x250_main');

        $themeData["play_game_tags"] = \GameMonetize\UI::view('game/tags/tags-element');
        $themeData["play_container_css"] = "play-game-page-container";

        if (gps_theme_is('crazygames-like')) {
            if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
                $themeData['page_content'] = \GameMonetize\UI::view('game/play');
            } elseif (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(mobile|android|touch|webos|hpwos)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
                $themeData['page_content'] = \GameMonetize\UI::view('game/play');
            } else {
                $themeData['page_content'] = \GameMonetize\UI::view('game/play-desktop');
            }
        } else {
            $themeData['page_content'] = \GameMonetize\UI::view('game/play');
        }
    } else {
        $themeData['page_content'] = \GameMonetize\UI::view('game/error');
    }
} else {
    $themeData['page_content'] = \GameMonetize\UI::view('game/error');
}
