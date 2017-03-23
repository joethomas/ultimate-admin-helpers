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
function joe_uah_prevent_widget_title_display( $widget_title ) {
	if ( substr ( $widget_title, 0, 1 ) == '!' )
		return;
	else
		return ( $widget_title );
}
add_filter( 'widget_title', 'joe_uah_prevent_widget_title_display' );

/**
 * Allow HTML tags in widget title by using *| and |* for < and >, respectively.
 *
 * @since 1.0.0
 */
function joe_uah_allow_html_in_widget_titles( $title) {
	$title = ( str_replace( '*|', '<', $title ) );
	$title = ( str_replace( '|*', '>', $title ) );

	return $title;
}
add_filter( 'widget_title', 'joe_uah_allow_html_in_widget_titles' );