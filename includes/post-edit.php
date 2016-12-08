<?php
/**
 * Maintain post category order.
 *
 * Ensure categories in posts and public custom post types maintain hierarchical
 * order on the Post Edit screen.
 *
 * @since 1.0.0
 */
function joe_uah_maintain_category_hierarchy( $args, $post_id ) {
	if ( isset( $args['taxonomy'] ) )
		$args['checked_ontop'] = false;

	return $args;
}
add_filter( 'wp_terms_checklist_args', 'joe_uah_maintain_category_hierarchy', 10, 2 );