<?php
/* Admin Styles
==============================================================================*/

/**
 * Enqueue admin CSS for all wp-admin (single site + multisite).
 */
function uah_enqueue_admin_styles( $hook ) {
	// Choose minified in production unless SCRIPT_DEBUG is on.
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	// Adjust to where your CSS actually lives.
	$rel = 'assets/css/admin-style' . $suffix . '.css';

	// Build URL & PATH from your plugin constants (preferred).
	$base_url  = defined( 'UAH_URL' )  ? UAH_URL  : plugin_dir_url( __FILE__ );   // Fallback rarely used if constants missing
	$base_path = defined( 'UAH_PATH' ) ? UAH_PATH : plugin_dir_path( __FILE__ );

	$url  = $base_url . $rel;
	$path = $base_path . $rel;

	// Version: prefer your plugin version; else file mtime to bust cache during dev.
	$ver = defined( 'UAH_VER' ) ? UAH_VER : ( file_exists( $path ) ? filemtime( $path ) : false );

	$handle = UAH_PREFIX . 'admin';
	wp_enqueue_style( $handle, $url, array(), $ver );
}
add_action( 'admin_enqueue_scripts', 'uah_enqueue_admin_styles' );
