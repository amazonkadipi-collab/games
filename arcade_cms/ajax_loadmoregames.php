<?php
require_once 'assets/includes/core.php';

// Dynamic game requests bypass index.php, so load the licensed visual-theme
// preview here as well. Otherwise a Kizi, Poki or Y8 page receives the site's
// default CrazyGames card markup when infinite scroll requests more games.
$visualPresetsBootstrap = ABSPATH . 'assets/pro/crazy_visual_presets/bootstrap.php';
if (is_file($visualPresetsBootstrap)) {
	require_once ABSPATH . 'assets/includes/license/bootstrap.php';
	if (function_exists('gps_license_installed_feature_allowed')
		&& (gps_license_installed_feature_allowed('crazy_visual_presets')
			|| gps_license_installed_feature_allowed('other_fixes'))) {
		require_once $visualPresetsBootstrap;
	}
}

// New Games loaded after scroll bypass index.php too. Apply the shared
// 24-hour badge rule only for installations licensed for Other Fixes PRO.
$otherFixesBootstrap = ABSPATH . 'assets/pro/other_fixes/bootstrap.php';
if (is_file($otherFixesBootstrap)) {
	require_once ABSPATH . 'assets/includes/license/bootstrap.php';
	if (function_exists('gps_license_installed_feature_allowed')
		&& gps_license_installed_feature_allowed('other_fixes')) {
		require_once $otherFixesBootstrap;
	}
}

if (isset($_GET["LoadedGamesNum"]) && isset($_GET["num"]) && isset($_GET["ids"])) {

	$from = $_GET["LoadedGamesNum"];
	$num = $_GET["num"];
	$ids = $_GET["ids"];
	if ($ids == "") exit;

	if (!is_numeric($from) || !is_numeric($num)) exit;
	if (isset($_GET["cat"])) {
		$cat = $_GET["cat"];
		$newGames_query = $GameMonetizeConnect->query("SELECT * FROM " . GAMES . " WHERE published='1' and category=$cat and `game_id` not in(" . $ids . ") order by `game_id` asc,  featured desc  limit " . $from . "," . $num);
	} else if (isset($_GET["pagetype"])) {
		if ($_GET["pagetype"] == "best") {
			$newGames_query = $GameMonetizeConnect->query("SELECT * FROM " . GAMES . " WHERE published='1' and `game_id` not in(" . $ids . ") order by `plays` desc  limit " . $from . "," . $num);
		} else if ($_GET["pagetype"] == "tags") {
			$from += 50;
			$newGames_query = $GameMonetizeConnect->query("SELECT * FROM " . GAMES . " WHERE published='1' and tags_ids LIKE '%\"{$ids}\"%' order by `plays` desc  limit " . $from . "," . $num);
		} else {
			$newGames_query = $GameMonetizeConnect->query("SELECT * FROM " . GAMES . " WHERE published='1' and `game_id` not in(" . $ids . ") ORDER BY featured DESC, date_added desc, featured_sorting desc limit " . $from . "," . $num);
		}
	} else {
		$newGames_query = $GameMonetizeConnect->query("SELECT * FROM " . GAMES . " WHERE published='1' and `game_id` not in(" . $ids . ") order by `game_id` asc  limit " . $from . "," . $num);
	}
	// var_dump("SELECT * FROM " . GAMES . " WHERE published='1' and tags_ids LIKE '%\"{$ids}\"% order by `plays` desc  limit " . $from . "," . $num);die;
	if ($newGames_query->num_rows > 0) {
		while ($newGames = $newGames_query->fetch_array()) {
			$newGamesBadge = '';
			if (isset($_GET['pagetype'])
				&& $_GET['pagetype'] === 'games'
				&& function_exists('gps_other_fixes_new_badge')) {
				$newGamesBadge = gps_other_fixes_new_badge($newGames['date_added'] ?? 0);
			}
			preg_match("/\/([a-zA-Z0-9]+)\//", $newGames['image'], $matches);
			$baseImagePath = $_SERVER['DOCUMENT_ROOT'] . '/games-image/' . $matches[1] . '/250x150.webp';
			if (file_exists($baseImagePath)) {
				$newGames_image = str_replace($_SERVER['DOCUMENT_ROOT'], '', $baseImagePath);
			} else {
				$newGames_image = $newGames['image'];
			}

			preg_match('/([^\/]+\.mp4)$/', $newGames['wt_video'], $matches);
			if (isset($matches[1])) {
				$baseVideoThumbPath = $_SERVER['DOCUMENT_ROOT'] . '/games-thumb-video/' . $matches[1];
				if (file_exists($baseVideoThumbPath)) {
					$newGames_wt_video = str_replace($_SERVER['DOCUMENT_ROOT'], '', $baseVideoThumbPath);
				} else {
					$newGames_wt_video = $newGames['wt_video'];
				}
			} else {
				$newGames_wt_video = $newGames['wt_video'];
			}

			if (gps_theme_is('crazygames-like')) {
					$game = "<a href='" . $config['site_url'] . "/game/" . $newGames['game_name'] . "' class='relative overflow-hidden new-games-list' aria-label='" . $newGames['name'] . "' data-url='" . $newGames['video_url'] . "' data-scale='1.2' data-wt-video='" . $newGames_wt_video . "' data-translate='-23px,-25px'>
							<img src='" .$newGames_image . "' alt='" . $newGames['name'] . "' loading='lazy' class='object-cover w-full rounded-2xl'>";
				echo $game;
				echo $newGamesBadge;
				if ($newGames['featured'] == "1") {
					echo "<span class='featured_icon'></span>";
				}
				echo "</a>";
			} else {
				echo "<div class='post'>
						<a href='" . $config['site_url'] . "/game/" . $newGames['game_name'] . "' class='game-item' data-wt-video='" . $newGames_wt_video . "'>
							<img src='" . $newGames_image . "' alt='" . $newGames['name'] . "'>
							<p class='post-name' data-url='" . $newGames['video_url'] . "' data-scale='1.2' data-translate='-23px,-25px'>" . $newGames['name'] . "</p>
							<div class='rank'></div>";
				echo $newGamesBadge;
	
				if ($newGames['featured'] == "1") {
					echo "<span class='featured_icon'></span>";
				}
				echo "</a>
					</div>";
			}
		}
	} else {
		echo "NoData";
		exit;
	}
} else {
	header("Location : /");
}
