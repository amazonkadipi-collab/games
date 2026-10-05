<?php
	$themeData['ads_header'] = getADS('header');
	$themeData['ads_footer'] = getADS('footer');
	$themeData['ads_sidebar'] = getADS('column_one');
	# >>

	$themeData['new_game_page'] = "";
	$footer_description = getFooterDescription('privacy');
	// $footer_description = getFooterDescription('about');

	$themeData['footer_description'] = isset($footer_description->description) ? htmlspecialchars_decode($footer_description->description): "";;
	$themeData['footer_description_has_content'] = isset($footer_description->has_content) ? $footer_description->has_content: "";
	$themeData['footer_description_content_value'] = isset($footer_description->content_value) ? htmlspecialchars_decode($footer_description->content_value): "";
if (trim(strip_tags((string)$themeData['footer_description_content_value'])) === '') {
    $themeData['footer_description_content_value'] = '<h2>Information collected</h2><p>Public gameplay can use browser cookies or local storage for features such as remembering recently played games. No player account is required to browse or play public games.</p><h2>Third-party services</h2><p>Playable games, advertising, analytics, or embedded media may be supplied by third-party services. Those services can process requests according to their own policies.</p><h2>Choices</h2><p>You can manage cookies through your browser settings. Where advertising is enabled, the site may display a consent control and a link to this privacy page.</p>';
}

	
	$themeData['new_games'] = \GameMonetize\UI::view('game/privacy');

	$themeData['page_content'] = \GameMonetize\UI::view('game/privacy');