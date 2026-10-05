<?php
	$themeData['ads_header'] = getADS('header');
	$themeData['ads_footer'] = getADS('footer');
	$themeData['ads_sidebar'] = getADS('column_one');
	# >>

	$themeData['new_game_page'] = "";
	$footer_description = getFooterDescription('terms');
	// $footer_description = getFooterDescription('about');

	$themeData['footer_description'] = isset($footer_description->description) ? htmlspecialchars_decode($footer_description->description): "";
	$themeData['footer_description_has_content'] = isset($footer_description->has_content) ? $footer_description->has_content: "";
	$themeData['footer_description_content_value'] = isset($footer_description->content_value) ? htmlspecialchars_decode($footer_description->content_value): "";
if (trim(strip_tags((string)$themeData['footer_description_content_value'])) === '') {
    $themeData['footer_description_content_value'] = '<h2>Using PlayGrid Games</h2><p>Use the site for lawful personal entertainment and do not attempt to disrupt, abuse, or bypass security controls.</p><h2>Game content</h2><p>Individual games may be hosted or delivered by third-party providers. Their content, controls, availability, and separate rules remain the responsibility of the applicable provider.</p><h2>Availability</h2><p>Games and site features can change, be removed, or become unavailable without notice. PlayGrid does not guarantee that every third-party game will always be playable.</p>';
}

	$themeData['new_games'] = \GameMonetize\UI::view('game/terms');

	$themeData['page_content'] = \GameMonetize\UI::view('game/terms');