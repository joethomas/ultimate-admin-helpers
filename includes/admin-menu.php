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
add_filter( 'custom_menu_order', 'joe_uah_custom_wp_admin_menu_order' );
add_filter( 'menu_order', 'joe_uah_custom_wp_admin_menu_order' );