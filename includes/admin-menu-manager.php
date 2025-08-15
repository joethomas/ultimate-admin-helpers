<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Options + page slugs
define( 'UAH_MM_OPTION_SITE', UAH_PREFIX . 'menu_manager_site' );
define( 'UAH_MM_OPTION_NET',  UAH_PREFIX . 'menu_manager_network' );
define( 'UAH_MM_PAGE_SITE',   UAH_PREFIX . 'menu-manager' );
define( 'UAH_MM_PAGE_NET',    UAH_PREFIX . 'menu-manager-network' );

/**
 * Helpers
 */
function uah_roles_list() {
	$roles = [];
	$wr = wp_roles();
	if ( $wr && is_array( $wr->roles ) ) {
		foreach ( $wr->roles as $slug => $def ) {
			$roles[ $slug ] = $def['name'];
		}
	}
	// "Default" catch-all
	return array_merge( [ 'default' => 'Default (all roles)' ], $roles );
}

function uah_current_menu_snapshot() {
	global $menu, $submenu;

	$items = [];
	if ( is_array( $menu ) ) {
		ksort( $menu, SORT_NUMERIC );
		foreach ( $menu as $m ) {
			$slug = isset( $m[2] ) ? (string) $m[2] : '';
			if ( $slug === '' ) continue;

			$is_sep = false;
			if ( isset( $m[4] ) && is_string( $m[4] ) && strpos( $m[4], 'wp-menu-separator' ) !== false ) $is_sep = true;
			elseif ( substr( $slug, 0, 9 ) === 'separator' ) $is_sep = true;

			$items[ $slug ] = [
				'title'  => wp_strip_all_tags( $m[0] ?? '' ),
				'slug'   => $slug,
				'is_sep' => $is_sep,
				'subs'   => [],
			];

			if ( isset( $submenu[ $slug ] ) && is_array( $submenu[ $slug ] ) ) {
				foreach ( $submenu[ $slug ] as $sm ) {
					$sub_slug = isset( $sm[2] ) ? (string) $sm[2] : '';
					if ( $sub_slug === '' ) continue;
					$items[ $slug ]['subs'][] = [
						'title' => wp_strip_all_tags( $sm[0] ?? '' ),
						'slug'  => $sub_slug,
					];
				}
			}
		}
	}
	return $items;
}

function uah_empty_profile() {
	return [
		'top_order'  => [],
		'hidden'     => [],
		'sub_order'  => [], // parent => [sub slugs]
		'sub_hidden' => [], // parent => [sub slugs]
	];
}

function uah_get_site_config() {
	$opt = get_option( UAH_MM_OPTION_SITE );
	if ( ! is_array( $opt ) ) $opt = [];
	$opt = wp_parse_args( $opt, [ 'profiles' => [], 'version' => 2 ] );
	return $opt;
}

function uah_get_network_config() {
	if ( ! is_multisite() ) return [ 'profiles' => [], 'version' => 2 ];
	$opt = get_site_option( UAH_MM_OPTION_NET );
	if ( ! is_array( $opt ) ) $opt = [];
	$opt = wp_parse_args( $opt, [ 'profiles' => [], 'version' => 2 ] );
	return $opt;
}

/**
 * Choose the effective profile for the current user (site admin).
 * Precedence: site(role) → site(default) → network(role) → network(default) → empty
 */
function uah_get_effective_profile() {
	$site = uah_get_site_config();
	$net  = uah_get_network_config();

	$user = wp_get_current_user();
	$roles = (array) ( $user->roles ?? [] );
	$role  = count( $roles ) ? (string) $roles[0] : 'default';

	$profiles_site = (array) ( $site['profiles'] ?? [] );
	$profiles_net  = (array) ( $net['profiles']  ?? [] );

	if ( isset( $profiles_site[ $role ] ) ) return wp_parse_args( $profiles_site[ $role ], uah_empty_profile() );
	if ( isset( $profiles_site['default'] ) ) return wp_parse_args( $profiles_site['default'], uah_empty_profile() );
	if ( isset( $profiles_net[ $role ] ) )  return wp_parse_args( $profiles_net[ $role ], uah_empty_profile() );
	if ( isset( $profiles_net['default'] ) )return wp_parse_args( $profiles_net['default'], uah_empty_profile() );

	return uah_empty_profile();
}

/**
 * Admin pages (site + network)
 */
add_action( 'admin_menu', function () {
	add_options_page(
		'Ultimate Admin',
		'Ultimate Admin',
		'manage_options',
		UAH_MM_PAGE_SITE,
		'uah_render_menu_manager_page_site'
	);
}, 20 );

add_action( 'network_admin_menu', function () {
	if ( ! is_multisite() ) return;
	add_submenu_page(
		'settings.php',
		'Ultimate Admin (Network)',
		'Ultimate Admin',
		'manage_network_options',
		UAH_MM_PAGE_NET,
		'uah_render_menu_manager_page_network'
	);
}, 20 );

/**
 * Assets (only on our pages)
 */
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	$ok = in_array( $hook, [
		'settings_page_' . UAH_MM_PAGE_SITE,
		'settings_page_' . UAH_MM_PAGE_NET,
	], true );
	if ( ! $ok ) return;

	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_add_inline_script( 'jquery-ui-sortable', <<<JS
jQuery(function($){
	function collect(){
		var data = { profiles: {} };

		$('.uah-role').each(function(){
			var role = $(this).data('role');
			var topOrder = [];
			var hidden = [];
			var subOrder = {};
			var subHidden = {};

			$(this).find('> ul.uah-sortable > li.uah-item').each(function(){
				var slug = $(this).data('slug');
				topOrder.push(slug);
				if ($(this).find('.uah-hide input').is(':checked')) hidden.push(slug);

				var subUL = $(this).find('ul.uah-sub');
				if (subUL.length){
					var subs = [];
					var subsHidden = [];
					subUL.find('> li.uah-subitem').each(function(){
						var s = $(this).data('slug');
						subs.push(s);
						if ($(this).find('.uah-hide input').is(':checked')) subsHidden.push(s);
					});
					if (subs.length) subOrder[slug] = subs;
					if (subsHidden.length) subHidden[slug] = subsHidden;
				}
			});

			data.profiles[role] = {
				top_order: topOrder,
				hidden: hidden,
				sub_order: subOrder,
				sub_hidden: subHidden
			};
		});

		$('#uah-profiles-json').val(JSON.stringify(data.profiles));
	}

	$('.uah-sortable').sortable({ handle: '.uah-drag', axis: 'y', placeholder: 'uah-placeholder', items: '> li' });
	$('.uah-sub').sortable({ handle: '.uah-drag', axis: 'y', placeholder: 'uah-placeholder', items: '> li' });

	$('#uah-form').on('submit', function(){ collect(); });
	$('#uah-reset').on('click', function(){
		$('#uah-profiles-json').val('');
	});
});
JS
);
	wp_add_inline_style( 'common', <<<CSS
#uah .uah-grid{display:grid;grid-template-columns:1fr;gap:12px;max-width:1000px}
#uah .uah-card{background:#fff;border:1px solid #ccd0d4;border-radius:8px;padding:12px}
#uah .uah-help{color:#555}
#uah details{border:1px solid #e2e4e7;border-radius:8px;padding:8px;margin-bottom:12px;background:#fff}
#uah summary{cursor:pointer;font-weight:600}
#uah ul.uah-sortable, #uah ul.uah-sub{margin:8px 0 0 0;padding:0;list-style:none}
#uah li.uah-item, #uah li.uah-subitem{display:grid;grid-template-columns:28px 1fr 120px;align-items:center;gap:10px;padding:8px;border:1px solid #e2e4e7;border-radius:6px;margin-bottom:6px;background:#fff}
#uah li.uah-subitem{grid-template-columns:28px 1fr 110px}
#uah .uah-drag{cursor:move}
#uah .uah-slug{font-family:monospace;color:#666;font-size:11px}
#uah .uah-title{font-weight:600}
#uah .uah-sep{opacity:.75}
#uah .uah-placeholder{border:2px dashed #b4b9be;height:38px;margin-bottom:6px;border-radius:6px}
#uah .uah-sub{margin-left:38px;margin-top:6px}
CSS
);
});

/**
 * Load/save helpers (site + network)
 */
function uah_load_profiles_for_context( $context = 'site' ) {
	$config = ( $context === 'network' ) ? uah_get_network_config() : uah_get_site_config();
	$profiles = (array) ( $config['profiles'] ?? [] );
	return $profiles;
}

function uah_save_profiles_for_context( $context = 'site' ) {
	if ( $context === 'network' ) {
		if ( ! current_user_can( 'manage_network_options' ) ) return;
		if ( ! isset( $_POST['uah_mm_nonce_net'] ) || ! wp_verify_nonce( $_POST['uah_mm_nonce_net'], 'uah_mm_save_net' ) ) return;
	} else {
		if ( ! current_user_can( 'manage_options' ) ) return;
		if ( ! isset( $_POST['uah_mm_nonce'] ) || ! wp_verify_nonce( $_POST['uah_mm_nonce'], 'uah_mm_save' ) ) return;
	}

	// If empty (Reset), store empty array; runtime will fall back to snapshot/network defaults.
	$profiles = [];
	if ( isset( $_POST['profiles_json'] ) && $_POST['profiles_json'] !== '' ) {
		$decoded = json_decode( wp_unslash( $_POST['profiles_json'] ), true );
		if ( is_array( $decoded ) ) $profiles = $decoded;
	}

	$payload = [ 'profiles' => $profiles, 'version' => 2 ];
	if ( $context === 'network' ) update_site_option( UAH_MM_OPTION_NET,  $payload );
	else update_option( UAH_MM_OPTION_SITE, $payload );

	$target = ( $context === 'network' )
		? network_admin_url( 'settings.php?page=' . UAH_MM_PAGE_NET . '&updated=true' )
		: admin_url( 'options-general.php?page=' . UAH_MM_PAGE_SITE . '&updated=true' );

	wp_safe_redirect( $target );
	exit;
}

add_action( 'admin_init', function(){ uah_save_profiles_for_context( 'site' ); } );
add_action( 'network_admin_edit_' . UAH_MM_PAGE_NET, function(){ uah_save_profiles_for_context( 'network' ); } );
// The above action name is for custom pages; we’ll also catch normal POST:
add_action( 'network_admin_edit_' . UAH_MM_PAGE_NET . '_post', function(){ uah_save_profiles_for_context( 'network' ); } );
add_action( 'network_admin_menu', function(){
	// Fallback save (when posting back to settings.php without custom action):
	if ( isset( $_POST['profiles_json'] ) && isset( $_POST['uah_mm_nonce_net'] ) ) uah_save_profiles_for_context( 'network' );
});

/**
 * Renderers (site + network)
 */
function uah_render_menu_manager( $context = 'site' ) {
	if ( $context === 'network' ) {
		if ( ! current_user_can( 'manage_network_options' ) ) return;
	} else {
		if ( ! current_user_can( 'manage_options' ) ) return;
	}

	$snap    = uah_current_menu_snapshot();
	$roles   = uah_roles_list();
	$saved   = uah_load_profiles_for_context( $context );
	$is_net  = ( $context === 'network' );

	// Helper to build the role UI
	$render_role = function( $role_key, $role_label ) use ( $saved, $snap ) {
		$prof     = isset( $saved[ $role_key ] ) ? wp_parse_args( $saved[ $role_key ], uah_empty_profile() ) : uah_empty_profile();
		$top_saved= (array) $prof['top_order'];
		$hidden   = array_map( 'strval', (array) $prof['hidden'] );
		$sub_ord  = (array) $prof['sub_order'];
		$sub_hide = (array) $prof['sub_hidden'];

		// Determine displayed order: saved (that still exist) + newcomers
		$live_top = array_keys( $snap );
		$display  = [];
		foreach ( $top_saved as $slug ) if ( in_array( $slug, $live_top, true ) ) $display[] = $slug;
		foreach ( $live_top as $slug ) if ( ! in_array( $slug, $display, true ) ) $display[] = $slug;

		$open = ( $role_key === 'default' ) ? ' open' : '';
		?>
		<details class="uah-role" data-role="<?php echo esc_attr( $role_key ); ?>"<?php echo $open; ?>>
			<summary><?php echo esc_html( $role_label ); ?></summary>
			<ul class="uah-sortable">
				<?php foreach ( $display as $slug ):
					if ( ! isset( $snap[ $slug ] ) ) continue;
					$item   = $snap[ $slug ];
					$is_sep = ! empty( $item['is_sep'] );
					$title  = $item['title'] ?: ( $is_sep ? 'Separator' : '(Untitled)' );
					$chk    = in_array( $slug, $hidden, true ) ? ' checked' : '';
					?>
					<li class="uah-item<?php echo $is_sep ? ' uah-sep' : ''; ?>" data-slug="<?php echo esc_attr( $slug ); ?>">
						<span class="dashicons dashicons-move uah-drag" aria-hidden="true"></span>
						<div>
							<div class="uah-title"><?php echo esc_html( $title ); ?></div>
							<div class="uah-slug"><?php echo esc_html( $slug ); ?></div>
						</div>
						<label class="uah-hide"><input type="checkbox"<?php echo $chk; ?>> Hide</label>

						<?php if ( ! empty( $item['subs'] ) ):
							$current_sub_slugs = array_map( function( $r ){ return (string) $r['slug']; }, $item['subs'] );
							$pref = isset( $sub_ord[ $slug ] ) ? (array) $sub_ord[ $slug ] : $current_sub_slugs;
							$display_subs = [];
							foreach ( $pref as $s ) if ( in_array( $s, $current_sub_slugs, true ) ) $display_subs[] = $s;
							foreach ( $current_sub_slugs as $s ) if ( ! in_array( $s, $display_subs, true ) ) $display_subs[] = $s;
							?>
							<ul class="uah-sub">
								<?php foreach ( $display_subs as $sub_slug ):
									$row = null;
									foreach ( $item['subs'] as $r ) if ( $r['slug'] === $sub_slug ) { $row = $r; break; }
									if ( ! $row ) continue;
									$sub_checked = ( ! empty( $sub_hide[ $slug ] ) && in_array( $sub_slug, $sub_hide[ $slug ], true ) ) ? ' checked' : '';
									?>
									<li class="uah-subitem" data-slug="<?php echo esc_attr( $row['slug'] ); ?>">
										<span class="dashicons dashicons-move uah-drag" aria-hidden="true"></span>
										<div>
											<div class="uah-title"><?php echo esc_html( $row['title'] ?: '(Untitled)' ); ?></div>
											<div class="uah-slug"><?php echo esc_html( $row['slug'] ); ?></div>
										</div>
										<label class="uah-hide"><input type="checkbox"<?php echo $sub_checked; ?>> Hide</label>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</details>
		<?php
	};

	?>
	<div class="wrap" id="uah">
		<h1><?php echo $is_net ? 'Ultimate Admin – Menu Manager (Network Defaults)' : 'Ultimate Admin – Menu Manager'; ?></h1>
		<p class="uah-help">
			Drag to reorder. Check “Hide” to remove items. New/unknown items are appended automatically.
			<?php if ( $is_net ) : ?>
				<br>These are <strong>network defaults</strong>. Each site can override them in its own Settings → Ultimate Admin screen.
			<?php else : ?>
				<br>Profiles are <strong>per role</strong>. Users get their role’s profile; if none, the Default profile.
			<?php endif; ?>
		</p>

		<div class="uah-grid">
			<div class="uah-card">
				<form id="uah-form" method="post" action="<?php echo esc_url( $is_net ? network_admin_url( 'settings.php?page=' . UAH_MM_PAGE_NET ) : admin_url( 'options-general.php?page=' . UAH_MM_PAGE_SITE ) ); ?>">
					<?php
					if ( $is_net ) wp_nonce_field( 'uah_mm_save_net', 'uah_mm_nonce_net' );
					else wp_nonce_field( 'uah_mm_save', 'uah_mm_nonce' );
					?>

					<?php
					// Default first, then other roles
					foreach ( $roles as $role_key => $role_label ) {
						$render_role( $role_key, $role_label );
					}
					?>

					<input type="hidden" id="uah-profiles-json" name="profiles_json" value="">
					<p class="submit">
						<button type="submit" class="button button-primary">Save Changes</button>
						<button type="submit" id="uah-reset" name="uah_reset" value="1" class="button">Reset (clear saved profiles)</button>
					</p>
				</form>
			</div>
		</div>
	</div>
	<?php
}

function uah_render_menu_manager_page_site() { uah_render_menu_manager( 'site' ); }
function uah_render_menu_manager_page_network() { uah_render_menu_manager( 'network' ); }

/**
 * Apply ordering/hiding (site admin + network admin)
 */
add_filter( 'custom_menu_order', function( $r ){ return true; }, 9999 );
add_filter( 'menu_order', function( $order ){
	if ( ! is_array( $order ) ) $order = [];
	$profile = uah_get_effective_profile();
	$snap    = uah_current_menu_snapshot();
	$current = array_keys( $snap );

	$final = [];
	foreach ( (array) $profile['top_order'] as $slug ) {
		if ( in_array( $slug, $current, true ) ) $final[] = $slug;
	}
	foreach ( $current as $slug ) {
		if ( ! in_array( $slug, $final, true ) ) $final[] = $slug;
	}
	return $final;
}, 9999 );

function uah_apply_hides_and_submenus() {
	$profile = uah_get_effective_profile();

	// Hide top-level
	if ( ! empty( $profile['hidden'] ) ) {
		foreach ( (array) $profile['hidden'] as $slug ) {
			remove_menu_page( $slug );
		}
	}

	// Submenus
	global $submenu;
	if ( ! is_array( $submenu ) ) return;

	// Hide submenus
	if ( ! empty( $profile['sub_hidden'] ) ) {
		foreach ( (array) $profile['sub_hidden'] as $parent => $subs ) {
			if ( ! isset( $submenu[ $parent ] ) || ! is_array( $submenu[ $parent ] ) ) continue;
			foreach ( (array) $subs as $sub_slug ) remove_submenu_page( $parent, $sub_slug );
		}
	}

	// Reorder submenus (keep unknown/new at end)
	if ( ! empty( $profile['sub_order'] ) ) {
		foreach ( (array) $profile['sub_order'] as $parent => $saved_list ) {
			if ( ! isset( $submenu[ $parent ] ) || ! is_array( $submenu[ $parent ] ) ) continue;

			$map = [];
			foreach ( $submenu[ $parent ] as $row ) $map[ (string) $row[2] ] = $row;

			$new = [];
			foreach ( (array) $saved_list as $slug ) if ( isset( $map[ $slug ] ) ) $new[] = $map[ $slug ];

			foreach ( $submenu[ $parent ] as $row ) {
				$slug = (string) $row[2];
				$hidden = ! empty( $profile['sub_hidden'][ $parent ] ) && in_array( $slug, $profile['sub_hidden'][ $parent ], true );
				if ( $hidden ) continue;
				if ( ! in_array( $slug, (array) $saved_list, true ) ) $new[] = $row;
			}
			$submenu[ $parent ] = $new;
		}
	}
}
add_action( 'admin_menu', 'uah_apply_hides_and_submenus', 9999 );
add_action( 'network_admin_menu', 'uah_apply_hides_and_submenus', 9999 );
