<?php
/* This plugin will redirect users to
 * login from /wp-login
 * 
 */
add_action('init', 'ydn_redirect_wp_login');

function ydn_redirect_wp_login() {
	global $pagenow;
	if($pagenow == 'wp-login.php') {
		if($_GET["safe"] != 'true') {
			$redirect_to = home_url('/login');
			wp_redirect($redirect_to);
			exit();
		}
	}
}
?>
