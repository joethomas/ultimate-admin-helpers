<?php
/*
	Plugin Name: Ultimate Admin Helpers
	Description: This plugin adds helpers for the WordPress Admin.
	Plugin URI: https://github.com/joethomas/ultimate-admin-helpers
	Version: 1.2.0
	Author: Joe Thomas
	Author URI: http://joethomas.co
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
	Text Domain: ultimate-admin-helpers
	Domain Path: /languages
*/

// Prevent direct file access
defined( 'ABSPATH' ) or exit;


/* Setup Plugin
==============================================================================*/

/**
 * Define the constants for use within the plugin
 */

// Plugin
function joeuadminhelpers_get_plugin_data_version() {
	$plugin = get_plugin_data( __FILE__, false, false );

	define( 'JOEUADMINHELPERS_VER', $plugin['Version'] );
	define( 'JOEUADMINHELPERS_PREFIX', $plugin['TextDomain'] );
	define( 'JOEUADMINHELPERS_NAME', $plugin['Name'] );
}
add_action( 'init', 'joeuadminhelpers_get_plugin_data_version' );

// Plugin basename
define( 'JOEUADMINHELPERS_BASENAME', plugin_basename(__DIR__) );
define( 'JOEUADMINHELPERS_BASENAME_FILE', plugin_basename(__FILE__) );
define( 'JOEUADMINHELPERS_BASENAME_FILENAME', basename(__FILE__) );

// Plugin paths
define( 'JOEUADMINHELPERS_DIR', untrailingslashit( plugin_dir_path(__FILE__) ) );
define( 'JOEUADMINHELPERS_DIR_URI', untrailingslashit( plugin_dir_url(__FILE__) ) );


/* Admin Bar
==============================================================================*/

/**
 * Customize admin bar items
 *
 * @link http://www.paulund.co.uk/how-to-remove-links-from-wordpress-admin-bar/
 * @since 1.0.0
 */
function joeuadminhelpers_remove_admin_bar_items() {
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
	$wp_admin_bar->remove_menu( 'analytify' );           // Analytify
	$wp_admin_bar->remove_menu( 'wpseo-menu' );           // Yoast SEO
	$wp_admin_bar->remove_node( 'updraft_admin_node' );   // UpdraftPlus
}
add_action( 'wp_before_admin_bar_render', 'joeuadminhelpers_remove_admin_bar_items', 999 );

/**
 * Change "Howdy" message
 *
 * @since 1.0.0
 */
function joeuadminhelpers_remove_howdy( $translated, $text, $domain ) {

    if ( ! is_admin() || 'default' != $domain )
        return $translated;

    if ( false !== strpos( $translated, 'Howdy' ) )
        return str_replace( 'Howdy,', '', $text );

    return $translated;

}
add_filter( 'gettext', 'joeuadminhelpers_remove_howdy', 10, 3 );


/* Admin Menu
==============================================================================*/

/**
 * Rearrange the WordPress Admin menu
 * Use Admin Menu Slugs dashboard widget to find menu slugs for reordering.
 *
 * @link http://codex.wordpress.org/Plugin_API/Filter_Reference/menu_order
 * @since 1.1.0
 */

// Set the admin menu order.
function joeuadminhelpers_custom_wp_admin_menu_order( $menu_ord ) {

	if ( ! $menu_ord ) return true;

    return array(
		'index.php', // Dashboard
		'uncode-menu', // Uncode (theme)
		'genesis', // Genesis (theme)
		'wpex-panel', // Total (theme)
		'avia', // Enfold (theme)
		'envato-wordpress-toolkit', // Envato Toolkit
		'woocommerce', // WooCommerce
		'edit.php?post_type=product', // WooCommerce Products
		'edit.php?post_type=page', // Pages
		'edit.php', // Posts
		'edit-comments.php', // Comments
		'edit.php?post_type=lesson', // Lessons
		'edit.php?post_type=deal', // Deals
		'edit.php?post_type=portfolio', // Portfolio
		'separator1', // First separator
		'edit.php?post_type=uncodeblock', // Uncode Content Block
		'edit.php?post_type=content_block', // Custom Post Widget (Plugin)
		'edit.php?post_type=acf', // Advanced Custom Fields
		'upload.php', // Media
		'gf_edit_forms', // Gravity Forms
		'wpcf7', // Contact Form 7
		'edit.php?post_type=testimonials', // Testimonials
		'edit.php?post_type=popup', // Popup Maker
		'redirect-updates', // Quick Page/Post Redirect Plugin
		'link-manager.php', // Links
		'wpseo_dashboard', // Yoast SEO
		'gadash_settings', // Google Analytics Dashboard for WP
		'gawd_analytics', // Google Analytics WD
		'yst_ga_dashboard', // Google Analytics by MonsterInsights
		'analytify-dashboard', // Analytify
		'separator2', // Second separator
		'themes.php', // Appearance
		'plugins.php', // Plugins
		'users.php', // Users
		'tools.php', // Tools
		'options-general.php', // Settings
		'separator-last', // Last separator
		'vc-general', // Visual Composer
		'revslider', // Slider Revolution
		'layerslider', // LayerSlider
		'metaslider', // Meta Slider
		'sucuriscan', // Sucuri Security
		'Wordfence', // Wordfence
		'mb_email_configuration', // Mail Bank
		'smtp_mail', // WP Mail Bank
		'wp-help-documents', // WP Help
		'envato-market', // Envato Market
		'wppusher', // WP Pusher
	);

}
add_filter( 'custom_menu_order', 'joeuadminhelpers_custom_wp_admin_menu_order' );
add_filter( 'menu_order', 'joeuadminhelpers_custom_wp_admin_menu_order' );


/* Dashboard Widgets
==============================================================================*/

/**
 * Add dashboard widgets.
 *
 * @since 1.2.0
 */

// Admin Menu Slugs dashboard widget output
function joeuadminhelpers_wpadmin_menu_slugs_dashboard_widget( $post, $callback_args ) {

	global $menu;

	$cellstyle1 = ' style="padding: 8px; text-align: left; vertical-align: top;"';
	$cellstyle2 = ' style="padding: 8px; text-align: left; vertical-align: top; overflow: hidden"';
	$style      = '';
	$output     = '<p>' . __( 'Here is a list of all WP Admin main menu items with their corresponding menu slugs.', 'ultimate-admin-helpers' ) . '</p>';
	$output    .= '<table class="fixed" style="width: 100%;">';
	$output    .= '<thead>';
	$output    .= '<th' . $cellstyle1 . '>Name</th>';
	$output    .= '<th' . $cellstyle2 . '>Slug</th>';
	$output    .= '</thead>';
	$output    .= '<tbody>';

	foreach ( $menu as $key => $value ) {

		$style = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';

		$output .= '<tr' . $style . '>';
		$output .= '<td' . $cellstyle1 . '><strong>' . $value[0] . '</strong></td>';
		$output .= '<td' . $cellstyle2 . '><code>' . $value[2] . '</code></td>';
		$output .= '</tr>';

	}

	$output   .= '</tbody>';
	$output   .= '</table>';

	echo $output;

}

// Available Shortcodes dashboard widget output
function joeuadminhelpers_available_shortcodes_dashboard_widget( $post, $callback_args ) {

	global $shortcode_tags;

	$cellstyle1 = ' style="padding: 8px; text-align: left; vertical-align: top;"';
	$cellstyle2 = ' style="padding: 8px; text-align: left; vertical-align: top; overflow: hidden;"';
	$style	    = '';
	$output     = '<p>' . __( 'Here is a list of all available shortcodes for you to use on your WordPress site. They may originate from multiple sources, including WordPress itself, themes (parent and child), and plugins.', 'ultimate-admin-helpers' ) . '</p>';
	$output    .= '<table class="fixed" style="width: 100%;">';
	$output    .= '<tr>';
	$output    .= '<th' . $cellstyle1 . '>Shortcode</th>';
	$output    .= '<th' . $cellstyle2 . '>Function</th>';
	$output    .= '</tr>';

	foreach( $shortcode_tags as $tag => $function ) {

		$style = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';

		if ( is_string( $function ) ) {

			$function = '<code>' . $function . '</code>';

		} else if ( is_array( $function ) ) {

			$object = '';
			$parameters = '';

			if ( is_string( $function[0] ) ) {

				$object = $function[0];

			} else if ( is_object( $function[0] ) ) {

				$object = get_class( $function[0] );

				foreach ( $function[0] as $parameter => $value ) {

					// if the array is empty
					if ( empty( $value ) )
						$value = __( 'The array is empty', 'ultimate-admin-helpers' );

					$parameters .=	'<li><code>' . $parameter . '</code> => <code>' . $value . '</code></li>';
				}

			}

			if ( ! empty( $parameters ) )
				$parameters = '<p><strong>' . __( 'Parameters of class', 'ultimate-admin-helpers' ) . ':</strong></p><ul>' . $parameters . '</ul>';

			$function = '<code>' . $object . '::' . $function[1] . '</code>' . $parameters;
		}
		else {
				$function = 'empty';
		}

		$output .= '<tr' . $style . '>';
		$output .= '<td' . $cellstyle1 . '><code><strong>' . $tag . '</strong></code></td>';
		$output .= '<td' . $cellstyle2 . '>' . $function . '</td>';
		$output .= '</tr>';

	}

	$output .= '</table>';

	echo $output;

}

// Register dashboard widgets
function joeuadminhelpers_add_dashboard_widgets() {

	global $menu;
	global $shortcode_tags;
	$color_blue = '#0075b8';
	$color_green = '#50b948';

	// Admin Menu Slugs
	wp_add_dashboard_widget(
		'joeuadminhelpers_menu_slugs',
		__( 'Admin Menu Slugs', 'ultimate-admin-helpers' ) . '<span style="margin-left: .5em; padding: .25em .4em .3em; background: ' . $color_blue . '; border-radius: 1px;  color: #fff; font-size: .825em;">' . count( $menu ) . '</span>',
		'joeuadminhelpers_wpadmin_menu_slugs_dashboard_widget'
	);

	// Available Shortcodes
	wp_add_dashboard_widget(
		'joeuadminhelpers_available_shortcodes',
		__( 'Available Shortcodes', 'ultimate-admin-helpers' ) . '<span style="margin-left: .5em; padding: .25em .4em .3em; background: ' . $color_green . '; border-radius: 1px;  color: #fff; font-size: .825em;">' . count( $shortcode_tags ) . '</span>',
		'joeuadminhelpers_available_shortcodes_dashboard_widget'
	);

}
add_action( 'wp_dashboard_setup', 'joeuadminhelpers_add_dashboard_widgets' );


/* Widgets
==============================================================================*/

/**
 * Allow shortcodes in text widgets.
 *
 * @since 1.0.0
 */
add_filter( 'widget_text', 'shortcode_unautop' );
add_filter( 'widget_text', 'do_shortcode' );

/**
 * Do not display widget title if it starts with '!'.
 *
 * @since 1.0.0
 */
function joeuadminhelpers_do_not_display_widget_title( $widget_title ) {
	if ( substr ( $widget_title, 0, 1 ) == '!' )
		return;
	else
		return ( $widget_title );
}
add_filter( 'widget_title', 'joeuadminhelpers_do_not_display_widget_title' );

/**
 * Allow HTML tags in widget title.
 *
 * @since 1.0.0
 */
function joeuadminhelpers_html_widget_title( $title) {
	$title = ( str_replace( '|:', '<', $title ) );
	$title = ( str_replace( ':|', '>', $title ) );

	return $title;
}
add_filter( 'widget_title', 'joeuadminhelpers_html_widget_title' );


/* Post Categories
==============================================================================*/

/**
 * Maintain post category order.
 *
 * Ensure categories in posts and public custom post types maintain hierarchical
 * order on the Post Edit screen.
 *
 * @since 1.0.0
 */
function joeuadminhelpers_maintain_category_hierarchy( $args, $post_id ) {
	if ( isset( $args['taxonomy'] ) )
		$args['checked_ontop'] = false;

	return $args;
}
add_filter( 'wp_terms_checklist_args', 'joeuadminhelpers_maintain_category_hierarchy', 10, 2);


/* Post/Page Editor
==============================================================================*/

/** Prevent TinyMCE from removing empty tags
 * @link http://www.bashbang.com/geek/div-tag-disappears-in-tinymce/
 */
function joeuadminhelpers_mce_options_save_empty_tags( $initArray ) {
	$initArray['extended_valid_elements'] .= 'div[*],i[*],p[*],span[*],br[*]';
	return $initArray;
}
add_filter( 'tiny_mce_before_init', 'joeuadminhelpers_mce_options_save_empty_tags' );


/* Languages
==============================================================================*/

/**
 * Load text domain for plugin translations
 */
function joeuadminhelpers_load_textdomain() {
	load_plugin_textdomain( 'ultimate-admin-helpers', FALSE, JOEUADMINHELPERS_BASENAME . '/languages/' );
}
add_action( 'plugins_loaded', 'joeuadminhelpers_load_textdomain' );
