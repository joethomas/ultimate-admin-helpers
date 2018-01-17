<?php
/**
 * WordPress Admin Styles
 */

/* Styles
==============================================================================*/

/**
 * Load custom CSS in Admin
 */
function joe_uah_enqueue_admin_styles() {

	$handle     = JOE_UAH_PREFIX . '-admin';
	$deps       = array();

	wp_register_style( $handle, plugin_dir_url( __FILE__ ) . 'css/' . 'admin-style.css', $deps, JOE_UAH_VER );
	wp_enqueue_style( $handle );

}
add_action( 'admin_enqueue_scripts', 'joe_uah_enqueue_admin_styles' );