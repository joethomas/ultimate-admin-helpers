<?php
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
function joe_uah_do_not_display_widget_title( $widget_title ) {
	if ( substr ( $widget_title, 0, 1 ) == '!' )
		return;
	else
		return ( $widget_title );
}
add_filter( 'widget_title', 'joe_uah_do_not_display_widget_title' );

/**
 * Allow HTML tags in widget title.
 *
 * @since 1.0.0
 */
function joe_uah_html_widget_title( $title) {
	$title = ( str_replace( '|:', '<', $title ) );
	$title = ( str_replace( ':|', '>', $title ) );

	return $title;
}
add_filter( 'widget_title', 'joe_uah_html_widget_title' );