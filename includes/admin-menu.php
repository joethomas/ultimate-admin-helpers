<?php
/**
 * Rearrange the WordPress Admin menu
 * Use Admin Menu Slugs dashboard widget to find menu slugs for reordering.
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
		'envato-wordpress-toolkit', // Envato Toolkit (plugin)
		'envato-market', // Envato Market (Plugin)
		'options-general.php?page=github-updater', // Github Updater (plugin)
		'uncode-menu', // Uncode (theme)
		'avia', // Enfold (theme)
		'genesis', // Genesis (theme)
		'wpex-panel', // Total (theme)
		'upload.php', // Media
		'wpshopify', // WP Shopify (plugin)
		'woocommerce', // WooCommerce (plugin)
		'edit.php?post_type=product', // WooCommerce Products (plugin)
		'edit.php?post_type=page', // Pages
		'nestedpages', // Pages (if using the Nested Pages plugin)
		'edit.php', // Posts
		'edit-comments.php', // Comments
		'edit.php?post_type=lesson', // Lessons (plugin)
		'edit.php?post_type=influencer', // Influencers (plugin)
		'edit.php?post_type=deal', // Deals (plugin)
		'edit.php?post_type=portfolio', // Portfolio (part of Uncode theme) OR (plugin)
		'separator1', // First separator
		'edit.php?post_type=uncodeblock', // Uncode Content Block (part of Uncode theme)
		'edit.php?post_type=content_block', // Custom Post Widget (plugin)
		'edit.php?post_type=acf', // Advanced Custom Fields (plugin)
		'gf_edit_forms', // Gravity Forms (plugin)
		'wpcf7', // Contact Form 7 (plugin)
		'edit.php?post_type=testimonials', // Testimonials (plugin)
		'edit.php?post_type=popup', // Popup Maker (plugin)
		'themes.php', // Appearance
		'plugins.php', // Plugins
		'users.php', // Users
		'tools.php', // Tools
		'options-general.php', // Settings
		'separator2', // Second separator
		'vc-general', // Visual Composer (plugin)
		'revslider', // Slider Revolution (plugin)
		'layerslider', // LayerSlider (plugin)
		'metaslider', // Meta Slider (plugin)
		'wpseo_dashboard', // Yoast SEO (plugin)
		'redirect-updates', // Quick Page/Post Redirect Plugin (plugin)
		'wpfastestcacheoptions', // WP Fastest Cache (plugin)
		'gadash_settings', // Google Analytics Dashboard for WP (plugin)
		'gawd_analytics', // Google Analytics WD (plugin)
		'yst_ga_dashboard', // Google Analytics by MonsterInsights (plugin)
		'analytify-dashboard', // Analytify (plugin)
		'separator-last', // Last separator
		'sucuriscan', // Sucuri Security (plugin)
		'Wordfence', // Wordfence (plugin)
	);

}
add_filter( 'custom_menu_order', 'joe_uah_custom_wp_admin_menu_order' );
add_filter( 'menu_order', 'joe_uah_custom_wp_admin_menu_order' );