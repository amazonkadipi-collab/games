<?php
	$themeData['ads_header'] = getADS('header');
	$themeData['ads_footer'] = getADS('footer');
	$themeData['ads_sidebar'] = getADS('column_one');
	# >>

	$themeData['new_game_page'] = "";
	$footer_description = getFooterDescription('contact');
	// $footer_description = getFooterDescription('about');

	$themeData['footer_description'] = isset($footer_description->description) ? htmlspecialchars_decode($footer_description->description): "";;
	$themeData['footer_description_has_content'] = isset($footer_description->has_content) ? $footer_description->has_content: "";
	$themeData['footer_description_content_value'] = isset($footer_description->content_value) ? htmlspecialchars_decode($footer_description->content_value): "";
if (trim(strip_tags((string)$themeData['footer_description_content_value'])) === '') {
    $themeData['footer_description_content_value'] = '<p>For a broken game, incorrect game information, or another issue, open the affected game page and use its <strong>Report</strong> action. Including the game page makes the problem easier to identify.</p><p>For general site feedback, use the contact channel made available by the site operator.</p>';
}

	$themeData['new_games'] = \GameMonetize\UI::view('game/contact');

	$themeData['page_content'] = \GameMonetize\UI::view('game/contact');