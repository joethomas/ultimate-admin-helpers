<?php
/**
 * Rearrange the WordPress Admin menu
 * Use the Admin Menu Slugs dashboard widget to find menu slugs to reorder.
 *
 * @link http://codex.wordpress.org/Plugin_API/Filter_Reference/menu_order
 * @since 1.1.0
 */

// Set the admin menu order.
function joe_uah_custom_wp_admin_menu_order( $menu_ord ) {
	if ( ! $menu_ord ) return true;

    	return array(
		'index.php', // Dashboard
		'duplicator', // Duplicator (plugin)
		'options-general.php?page=github-updater', // Github Updater (plugin)
		'et_divi_options', // Divi (theme)
		'uncode-system-status', // Uncode (theme)
		'avia', // Enfold (theme)
		'genesis', // Genesis (theme)
		'wpex-panel', // Total (theme)
		'wpshopify', // WP Shopify (plugin)
		'woocommerce', // WooCommerce (plugin)
		'edit.php?post_type=product', // WooCommerce Products (plugin)
		'edit.php?post_type=shopify_product', // Product Management System (plugin)
		'edit.php?post_type=page', // Pages
		'nestedpages', // Pages (if using the Nested Pages plugin)
		'edit.php', // Posts
		'edit-comments.php', // Comments
		'edit.php?post_type=lesson', // Lessons (plugin)
		'edit.php?post_type=influencer', // Influencers (plugin)
		'edit.php?post_type=deal', // Deals (plugin)
		'edit.php?post_type=portfolio', // Portfolio (part of Uncode theme) OR (plugin)
		'edit.php?post_type=uncodeblock', // Uncode Content Block (part of Uncode theme)
		'edit.php?post_type=content_block', // Custom Post Widget (plugin)
		'gf_edit_forms', // Gravity Forms (plugin)
		'wpcf7', // Contact Form 7 (plugin)
		'edit.php?post_type=testimonials', // Testimonials (plugin)
		'edit.php?post_type=popup', // Popup Maker (plugin)
		'upload.php', // Media
		'edit.php?post_type=uncode_gallery', // Uncode - Galleries (theme)
		'separator1', // Separator (1st)
		'themes.php', // Appearance
		'plugins.php', // Plugins
		'users.php', // Users
		'options-general.php', // Settings
		'tools.php', // Tools
		'separator2', // Separator (2nd)
		'edit.php?post_type=acf', // Advanced Custom Fields (plugin)
		'vc-general', // WPBakery Page Builder (plugin)
		'revslider', // Slider Revolution (plugin)
		'layerslider', // LayerSlider (plugin)
		'metaslider', // Meta Slider (plugin)
		'rank-math', // Rank Math SEO (plugin)
		'wpseo_dashboard', // Yoast SEO (plugin)
		'redirect-updates', // Quick Page/Post Redirect Plugin (plugin)
		'w3tc_dashboard', // W3 Total Cache (plugin)
		'wpfastestcacheoptions', // WP Fastest Cache (plugin)
		'gadash_settings', // Google Analytics Dashboard for WP (GADWP) (plugin)
		'gawd_subscribe', // Google Analytics WD (plugin)
		'gawd_settings', // Google Analytics WD (plugin)
		'monsterinsights_dashboard', // Google Analytics for WordPress by MonsterInsights (plugin)
		'analytify-dashboard', // Analytify - Google Analytics Dashboard (plugin)
		'envato-wordpress-toolkit', // Envato Toolkit (plugin)
		'envato-market', // Envato Market (plugin)
		'separator-last', // Separator (last)
		'sucuriscan', // Sucuri Security (plugin)
		'Wordfence', // Wordfence (plugin)
		'mb_email_configuration' // WP Mail Bank (plugin)
	);

}
add_filter( 'custom_menu_order', 'joe_uah_custom_wp_admin_menu_order' );
add_filter( 'menu_order', 'joe_uah_custom_wp_admin_menu_order' );

/**
 * Remove top level menu items.
 * Use the Admin Menu Slugs dashboard widget to find menu slugs to removing.
 *
 * @link https://markwilkinson.me/2014/11/altering-wordpress-admin-menus/
 * @since 1.3.9
 */
function joe_uah_remove_top_level_menu_items() {

	remove_menu_page(
		//'edit-comments.php' // Comments
	);

}
add_action( 'admin_menu', 'joe_uah_remove_top_level_menu_items', 999 );
