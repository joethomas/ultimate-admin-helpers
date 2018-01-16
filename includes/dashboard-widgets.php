<?php
/**
 * Add dashboard widgets.
 *
 * @since 1.2.0
 */

// Admin Menu Slugs dashboard widget output
function joe_uah_wpadmin_menu_slugs_dashboard_widget( $post, $callback_args ) {

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

// Admin Submenu Slugs dashboard widget output
function joe_uah_wpadmin_submenu_slugs_dashboard_widget( $post, $callback_args ) {

	global $submenu;

	$cellstyle1 = ' style="padding: 8px; text-align: left; vertical-align: top;"';
	$cellstyle2 = ' style="padding: 8px; text-align: left; vertical-align: top; overflow: hidden"';
	$style      = '';
	$output     = '<p>' . __( 'Here is a list of all WP Admin submenu items with their corresponding menu slugs.', 'ultimate-admin-helpers' ) . '</p>';
	$output    .= '<table class="fixed" style="width: 100%;">';
	$output    .= '<thead>';
	$output    .= '<th' . $cellstyle1 . '>Name</th>';
	$output    .= '<th' . $cellstyle2 . '>Slug</th>';
	$output    .= '</thead>';
	$output    .= '<tbody>';

	foreach ( $submenu as $group => $item ) {
		foreach ( $submenu[$group] as $key => $value ) {

			$style = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';

			$output .= '<tr' . $style . '>';
			$output .= '<td' . $cellstyle1 . '><strong>' . $submenu[$group][$key][0] . '</strong></td>';
			$output .= '<td' . $cellstyle2 . '><code>' . $submenu[$group][$key][2] . '</code></td>';
			$output .= '</tr>';
    	}
	}

	$output   .= '</tbody>';
	$output   .= '</table>';

	echo $output;

}

// Available Shortcodes dashboard widget output
function joe_uah_available_shortcodes_dashboard_widget( $post, $callback_args ) {

	global $shortcode_tags;

	$cellstyle1 = ' style="padding: 8px; text-align: left; vertical-align: top;"';
	$cellstyle2 = ' style="padding: 8px; text-align: left; vertical-align: top; overflow: hidden;"';
	$style	    = '';
	$output     = '<p>' . __( 'Here is a list of all available shortcodes for you to use on your WordPress site. They may originate from multiple sources, including WordPress itself, themes (parent and child), and plugins.', 'ultimate-admin-helpers' ) . '</p>';
	$output    .= '<table class="fixed" style="width: 100%;">';
	$output    .= '<tr>';
	$output    .= '<th' . $cellstyle1 . '>Shortcode</th>';
	$output    .= '</tr>';

	foreach( $shortcode_tags as $tag => $function ) {

		$style = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';

		$output .= '<tr' . $style . '>';
		$output .= '<td' . $cellstyle1 . '><code><strong>' . $tag . '</strong></code></td>';
		$output .= '</tr>';

	}

	$output .= '</table>';

	echo $output;

}

// Register dashboard widgets
function joe_uah_add_dashboard_widgets() {

	global $menu;
	global $submenu;
	global $shortcode_tags;

	$color_blue = '#0075b8';
	$color_green = '#50b948';
	$color_red = '#d40c0c';

	// Admin Menu Slugs
	wp_add_dashboard_widget(
		'uah-menu-slugs',
		__( 'Admin Menu Slugs', 'ultimate-admin-helpers' ) . '<span style="margin-left: .5em; padding: .25em .4em .3em; background: ' . $color_blue . '; border-radius: 1px;  color: #fff; font-size: .825em;">' . count( $menu ) . '</span>',
		'joe_uah_wpadmin_menu_slugs_dashboard_widget'
	);

	wp_add_dashboard_widget(
		'uah-submenu-slugs',
		__( 'Admin Menu > Submenu Slugs', 'ultimate-admin-helpers' ) . '<span style="margin-left: .5em; padding: .25em .4em .3em; background: ' . $color_green . '; border-radius: 1px;  color: #fff; font-size: .825em;">' . count( $submenu ) . '</span>',
		'joe_uah_wpadmin_submenu_slugs_dashboard_widget'
	);

	// Available Shortcodes
	wp_add_dashboard_widget(
		'uah-available-shortcodes',
		__( 'Available Shortcodes', 'ultimate-admin-helpers' ) . '<span style="margin-left: .5em; padding: .25em .4em .3em; background: ' . $color_red . '; border-radius: 1px;  color: #fff; font-size: .825em;">' . count( $shortcode_tags ) . '</span>',
		'joe_uah_available_shortcodes_dashboard_widget'
	);

}
add_action( 'wp_dashboard_setup', 'joe_uah_add_dashboard_widgets' );
