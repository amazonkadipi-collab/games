<?php
$themeData['new_game_page'] = "";

$themeData['new_games_list'] = $ngm_r;
$footer_description = getFooterDescription('about');

$themeData['footer_description'] = isset($footer_description->description) ? htmlspecialchars_decode($footer_description->description): "";
$themeData['footer_description_has_content'] = isset($footer_description->has_content) ? $footer_description->has_content: "";
$themeData['footer_description_content_value'] = isset($footer_description->content_value) ? htmlspecialchars_decode($footer_description->content_value): "";
if (trim(strip_tags((string)$themeData['footer_description_content_value'])) === '') {
    $themeData['footer_description_content_value'] = '<p><strong>PlayGrid Games</strong> is an independent browser-game discovery site. We organize a large catalog of games into searchable, browsable pages so players can find something to play quickly.</p><p>Games may be delivered through third-party game technology and content providers. PlayGrid provides the discovery experience and links to the playable game experience.</p>';
}


$themeData['new_games'] = \GameMonetize\UI::view('game/about');

$themeData['page_content'] = \GameMonetize\UI::view('game/about');
