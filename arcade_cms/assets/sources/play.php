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
        $themeData['play_game_plays'] = numberFormat((int)($get_game_data['plays'] ?? 0));
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
        $themeData['play_game_walkthrough'] = "";
        if(strlen($get_game['video_url'])){
            $themeData['play_game_walkthrough'] = "<a href='".$get_game['video_url']."' target='_blank'>Walkthrough</a>";
        }

        // Real related-games rail: keep the full published catalog, prioritising the current game's category.
        // No invented safety flag is applied; the site keeps every published game available.
        $relatedGamesHtml = '';
        $relatedSeen = array((int)$get_game['game_id']);
        $relatedQuery = $GameMonetizeConnect->query(
            "SELECT * FROM " . GAMES . " WHERE published='1' AND game_id != " . (int)$get_game['game_id'] . " AND category=" . (int)$get_game['category'] . " ORDER BY plays DESC, date_added DESC LIMIT 8"
        );
        if ($relatedQuery) {
            while ($relatedGame = $relatedQuery->fetch_array()) {
                $relatedId = (int)$relatedGame['game_id'];
                if (in_array($relatedId, $relatedSeen, true)) continue;
                $relatedSeen[] = $relatedId;
                $relatedData = gameData($relatedGame);
                $relatedName = htmlspecialchars((string)$relatedData['name'], ENT_QUOTES, 'UTF-8');
                $relatedUrl = htmlspecialchars((string)$relatedData['game_url'], ENT_QUOTES, 'UTF-8');
                $relatedImage = htmlspecialchars((string)$relatedData['image_url'], ENT_QUOTES, 'UTF-8');
                $relatedPlays = numberFormat((int)($relatedGame['plays'] ?? 0));
                $relatedCategory = htmlspecialchars((string)$get_game['category_name'], ENT_QUOTES, 'UTF-8');
                $relatedGamesHtml .= '<a class="pg-related-card" href="' . $relatedUrl . '">'
                    . '<span class="pg-related-image"><img src="' . $relatedImage . '" alt="' . $relatedName . '" loading="lazy" decoding="async"></span>'
                    . '<span class="pg-related-copy"><strong>' . $relatedName . '</strong><span>' . $relatedCategory . ' · ' . $relatedPlays . ' plays</span></span>'
                    . '</a>';
            }
        }
        if (count($relatedSeen) < 5) {
            $fallbackQuery = $GameMonetizeConnect->query(
                "SELECT g.*, c.name AS category_name FROM " . GAMES . " g LEFT JOIN " . CATEGORIES . " c ON c.id=g.category WHERE g.published='1' AND g.game_id != " . (int)$get_game['game_id'] . " ORDER BY g.plays DESC, g.date_added DESC LIMIT 12"
            );
            if ($fallbackQuery) {
                while ($relatedGame = $fallbackQuery->fetch_array()) {
                    $relatedId = (int)$relatedGame['game_id'];
                    if (in_array($relatedId, $relatedSeen, true)) continue;
                    $relatedSeen[] = $relatedId;
                    $relatedData = gameData($relatedGame);
                    $relatedName = htmlspecialchars((string)$relatedData['name'], ENT_QUOTES, 'UTF-8');
                    $relatedUrl = htmlspecialchars((string)$relatedData['game_url'], ENT_QUOTES, 'UTF-8');
                    $relatedImage = htmlspecialchars((string)$relatedData['image_url'], ENT_QUOTES, 'UTF-8');
                    $relatedPlays = numberFormat((int)($relatedGame['plays'] ?? 0));
                    $relatedCategory = htmlspecialchars((string)($relatedGame['category_name'] ?? ''), ENT_QUOTES, 'UTF-8');
                    $relatedGamesHtml .= '<a class="pg-related-card" href="' . $relatedUrl . '">'
                        . '<span class="pg-related-image"><img src="' . $relatedImage . '" alt="' . $relatedName . '" loading="lazy" decoding="async"></span>'
                        . '<span class="pg-related-copy"><strong>' . $relatedName . '</strong><span>' . $relatedCategory . ' · ' . $relatedPlays . ' plays</span></span>'
                        . '</a>';
                    if (count($relatedSeen) >= 9) break;
                }
            }
        }
        $themeData['play_related_games'] = $relatedGamesHtml;
        $themeData['play_game_controls_block'] = trim((string)$get_game_data['instructions']) !== ''
            ? '<div class="pg-controls-copy">' . $get_game_data['instructions'] . '</div>'
            : '<p class="pg-empty-note">Controls are shown inside the game when available.</p>';

        $similarGames = getSidebarWidget('similar-name', $get_game_data['name']);
        $themeData['play_sidebar_widgets'] = $similarGames[0];
        
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
		$themeData['play_game_video_block'] = '<div class="description" id="gamemonetize-video"></div><script type="text/javascript">window.VIDEO_OPTIONS={gameid:"' . addslashes((string)$themeData['game_unique_id']) . '",width:"100%",height:"480px",color:"#3f007e"};(function(a,b,c){var d=a.getElementsByTagName(b)[0];a.getElementById(c)||(a=a.createElement(b),a.id=c,a.src="https://api.gamemonetize.com/video.js?v="+Date.now(),d.parentNode.insertBefore(a,d))})(document,"script","gamemonetize-video-api");</script>';
		if (($config['site_theme'] ?? '') === 'poki-pro' && function_exists('gps_other_fixes_poki_walkthrough_markup')) {
			$themeData['play_game_video_block'] = gps_other_fixes_poki_walkthrough_markup(
				(string)$themeData['game_unique_id'],
				(string)$themeData['play_game_name'],
				(string)$themeData['play_game_image']
			);
		}

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
