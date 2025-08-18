<?php
/* Admin Styles
==============================================================================*/

/**
 * Enqueue admin CSS for all wp-admin (single site + multisite).
 */
function uah_enqueue_admin_styles() {
	$rel  = 'assets/css/admin-style.css';            // your only stylesheet
	$path = ( defined('UAH_PATH') ? UAH_PATH : plugin_dir_path(__FILE__) ) . $rel;
	$url  = ( defined('UAH_URL')  ? UAH_URL  : plugin_dir_url(__FILE__) )  . $rel;

	if ( ! file_exists( $path ) ) return;            // avoid 404s if path is wrong

	$ver = defined('UAH_VER') ? UAH_VER : filemtime( $path );
	wp_enqueue_style( UAH_PREFIX . 'admin', $url, array(), $ver );
}
add_action( 'admin_enqueue_scripts', 'uah_enqueue_admin_styles' );
