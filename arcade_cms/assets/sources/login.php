<?php
	if ( !is_logged() ) {
		$themeData['is_redirect_login'] = (isset($_GET['redirect_url']) && !empty($_GET['redirect_url'])) ? '<input name="redirect_url" value="'.$_GET['redirect_url'].'" type="hidden">' : '';
		$settings = $GameMonetizeConnect->query("SELECT * FROM " . SETTING . " WHERE id='1'");
		$settings = $settings->fetch_assoc();
		$themeData['google_oauth_login_button'] = '';
		$googleOnlyLogin = false;
		$classicLoginRequested = isset($_GET['classic_login']) && (string)$_GET['classic_login'] === '1';
		$googleInstalled = is_file(ABSPATH . 'json/pro-features/google_login.json')
			&& is_file(ABSPATH . 'assets/includes/google-oauth.php')
			&& is_file(ABSPATH . 'google-login.php')
			&& is_file(ABSPATH . 'google-callback.php');
		if ($googleInstalled) {
			require_once ABSPATH . 'assets/includes/google-oauth.php';
		}
		if ($googleInstalled && gps_google_login_is_pro()) {
			$google = gps_google_config($GameMonetizeConnect);
			if ($google['enabled'] && $google['client_id'] !== '' && $google['client_secret'] !== '') {
				$googleOnlyLogin = !$classicLoginRequested;
				if ($googleOnlyLogin && isset($themeData['header_scripts'])) {
					$themeData['header_scripts'] = preg_replace(
						'~<script[^>]+https://www\.google\.com/recaptcha/api\.js[^>]*>\s*</script>~i',
						'',
						(string)$themeData['header_scripts']
					);
					$themeData['header_tags'] = \GameMonetize\UI::view('global/header/all');
				}
				$themeData['google_oauth_login_button'] = ($googleOnlyLogin ? '<style id="gps-google-only-login">.signin-form{display:none!important}</style>' : '')
					. '<div style="margin-top:14px;text-align:center;">'
					. '<a href="' . rtrim(siteUrl(), '/') . '/google-login.php" style="display:flex;align-items:center;justify-content:center;gap:10px;padding:11px 14px;background:#fff;color:#202124;border:1px solid #dadce0;border-radius:6px;font-weight:600;text-decoration:none;">'
					. '<span style="font-size:20px;font-weight:700;color:#4285f4;">G</span> Continue with Google</a>'
					. ($googleOnlyLogin ? '<a href="' . rtrim(siteUrl(), '/') . '/index.php?p=login&amp;classic_login=1" style="display:inline-block;margin-top:12px;color:#9fb0ca;font-size:12px;text-decoration:none;">Use emergency password login</a>' : '')
					. '</div>';
			}
		}
		$themeData['google_oauth_error'] = isset($_GET['google_error'])
			? '<div style="margin-bottom:12px;padding:9px 11px;background:#4a2530;color:#ffb7c0;border-left:3px solid #ff6377;">Google login could not be completed. Please try again.</div>'
			: '';
		$themeData['is_recaptcha'] = "no";
		$themeData['login_recaptcha'] = '';
		if (!$googleOnlyLogin && $settings['recaptcha_site_key'] > 0){
			$themeData['is_recaptcha'] = "yes";
			$themeData['recaptcha_site_key'] = $settings['recaptcha_site_key'];
			$themeData['login_recaptcha'] = \GameMonetize\UI::view('welcome/login_recaptcha');
		}
		$themeData['page_content'] = \GameMonetize\UI::view('welcome/login');
	}
	else {
		if (is_admin()) {
			include(ABSPATH . 'assets/sources/admin-panel.php');
		} else {
			header('Location: '.siteUrl().'/');
			die();
		}
	}
