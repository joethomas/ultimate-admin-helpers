<?php
/*
	Plugin Name: Ultimate Admin Helpers
	Description: This plugin adds some very useful helpers for the WordPress Admin area.
	Plugin URI: https://github.com/joethomas/ultimate-admin-helpers
	Version: 1.5.1
	Author: Joe Thomas
	Author URI: https://github.com/joethomas
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
	Text Domain: ultimate-admin-helpers
	Domain Path: /languages/

	GitHub Plugin URI: joethomas/ultimate-admin-helpers
	Primary Branch: master
*/

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/* Global Variables & Constants
==============================================================================*/

/**
 * Define the constants for use within the plugin
 */
if ( ! function_exists( 'get_plugin_data' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}
$plugin = get_plugin_data( __FILE__, false, false );

define( 'UAH_VER', $plugin['Version'] );
define( 'UAH_TEXTDOMAIN', $plugin['TextDomain'] );
define( 'UAH_NAME', $plugin['Name'] );
define( 'UAH_URL', plugin_dir_url( __FILE__ ) );
define( 'UAH_PATH', plugin_dir_path( __FILE__ ) );
define( 'UAH_PREFIX', 'uah_' );


/* Bootstrap
==============================================================================*/

if ( is_admin() ) {
	require_once UAH_PATH . 'includes/admin-toolbar-manager.php';
	require_once UAH_PATH . 'includes/admin-menu-manager.php';
	require_once UAH_PATH . 'includes/admin-styles.php';
}


/* Languages
==============================================================================*/

/**
 * Load text domain for plugin translations
 *
 * @since 1.2.0
 */
function uah_load_textdomain() {
	load_plugin_textdomain( 'ultimate-admin-helpers', false, basename( __DIR__ ) . '/languages/' );
}
add_action( 'plugins_loaded', 'uah_load_textdomain' );
