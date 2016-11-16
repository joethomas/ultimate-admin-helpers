<?php
/**
 * Customize admin bar items
 *
 * @link http://www.paulund.co.uk/how-to-remove-links-from-wordpress-admin-bar/
 * @since 1.0.0
 */
function joe_uah_remove_admin_bar_items() {

	global $wp_admin_bar;

	$wp_admin_bar->remove_menu( 'wp-logo' );              // WordPress logo
	//$wp_admin_bar->remove_menu( 'about' );              // About WordPress link
	//$wp_admin_bar->remove_menu( 'wporg' );              // WordPress.org link
	//$wp_admin_bar->remove_menu( 'documentation' );      // WordPress Documentation link
	//$wp_admin_bar->remove_menu( 'support-forums' );     // Support Forums link
	//$wp_admin_bar->remove_menu( 'feedback' );           // Feedback link
	//$wp_admin_bar->remove_menu( 'site-name' );          // Site Name menu
	$wp_admin_bar->remove_menu( 'view-site' );            // View (or "Visit") Site link
	//$wp_admin_bar->remove_menu( 'updates' );            // Updates link
	//$wp_admin_bar->remove_menu( 'comments' );           // Comments link
	//$wp_admin_bar->remove_menu( 'new-content' );        // + New [Content] link
	//$wp_admin_bar->remove_menu( 'my-account' );         // User details tab
	$wp_admin_bar->remove_menu( 'analytify' );            // Analytify
	$wp_admin_bar->remove_menu( 'wpseo-menu' );           // Yoast SEO
	$wp_admin_bar->remove_node( 'updraft_admin_node' );   // UpdraftPlus

}
add_action( 'wp_before_admin_bar_render', 'joe_uah_remove_admin_bar_items', 999 );

/**
 * Change "Howdy" message
 *
 * @since 1.0.0
 */
function joe_uah_remove_howdy( $translated, $text, $domain ) {

    if ( ! is_admin() || 'default' != $domain )
        return $translated;

    if ( false !== strpos( $translated, 'Howdy' ) )
        return str_replace( 'Howdy,', '', $text );

    return $translated;

}
add_filter( 'gettext', 'joe_uah_remove_howdy', 10, 3 );