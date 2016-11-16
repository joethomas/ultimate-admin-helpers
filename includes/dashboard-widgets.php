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
function joe_uah_add_dashboard_widgets() {

	global $menu;
	global $shortcode_tags;
	$color_blue = '#0075b8';
	$color_green = '#50b948';

	// Admin Menu Slugs
	wp_add_dashboard_widget(
		'uah-menu-slugs',
		__( 'Admin Menu Slugs', 'ultimate-admin-helpers' ) . '<span style="margin-left: .5em; padding: .25em .4em .3em; background: ' . $color_blue . '; border-radius: 1px;  color: #fff; font-size: .825em;">' . count( $menu ) . '</span>',
		'joe_uah_wpadmin_menu_slugs_dashboard_widget'
	);

	// Available Shortcodes
	wp_add_dashboard_widget(
		'uah-available-shortcodes',
		__( 'Available Shortcodes', 'ultimate-admin-helpers' ) . '<span style="margin-left: .5em; padding: .25em .4em .3em; background: ' . $color_green . '; border-radius: 1px;  color: #fff; font-size: .825em;">' . count( $shortcode_tags ) . '</span>',
		'joe_uah_available_shortcodes_dashboard_widget'
	);

}
add_action( 'wp_dashboard_setup', 'joe_uah_add_dashboard_widgets' );