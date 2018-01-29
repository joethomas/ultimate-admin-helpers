<?php
/*
	Plugin Name: Ultimate Admin Helpers
	Description: This plugin adds some very useful helpers for the WordPress Admin area.
	Plugin URI: https://github.com/joethomas/ultimate-admin-helpers
	Version: 1.4.1
	Author: Joe Thomas
	Author URI: https://github.com/joethomas
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
	Text Domain: ultimate-admin-helpers
	Domain Path: /languages/

	GitHub Plugin URI: https://github.com/joethomas/ultimate-admin-helpers
	GitHub Branch: master
*/

// Prevent direct file access
defined( 'ABSPATH' ) or exit;


/* Global Variables & Constants
==============================================================================*/

/**
 * Define the constants for use within the plugin
 */
function joe_uah_get_plugin_data_version() {
	$plugin = get_plugin_data( __FILE__, false, false );

	define( 'JOE_UAH_VER', $plugin['Version'] );
	define( 'JOE_UAH_TEXTDOMAIN', $plugin['TextDomain'] );
	define( 'JOE_UAH_NAME', $plugin['Name'] );
}
add_action( 'init', 'joe_uah_get_plugin_data_version' );

define( 'JOE_UAH_PREFIX', 'ultimate-admin-helpers' );


/* Plugins Check
==============================================================================*/

function joe_uah_plugins_check() {

	// Set constants for active third party plugins
	define( 'JOE_UAH_MINIORANGE_2_FACTOR', class_exists( 'Miniorange_Authentication' ) );

}
add_action( 'plugins_loaded', 'joe_uah_plugins_check' );


/* Bootstrap
==============================================================================*/

require_once( 'includes/admin-bar.php' ); // controls WP Admin bar
require_once( 'includes/admin-menu.php' ); // controls WP Admin menu
require_once( 'includes/admin-styles.php' ); // controls WP Admin styles
require_once( 'includes/dashboard-widgets.php' ); // controls Dashboard widgets
require_once( 'includes/post-edit.php' ); // controls Post Edit screen
require_once( 'includes/updates.php' ); // controls plugin updates
require_once( 'includes/widgets.php' ); // controls widgets


/* Languages
==============================================================================*/

/**
 * Load text domain for plugin translations
 *
 * @since 1.2.0
 */
function joe_uah_load_textdomain() {
	load_plugin_textdomain( 'ultimate-admin-helpers', false, basename( __DIR__ ) . '/languages/' );
}
add_action( 'plugins_loaded', 'joe_uah_load_textdomain' );
