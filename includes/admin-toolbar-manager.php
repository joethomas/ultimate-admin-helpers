<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * Ultimate Admin — Toolbar (Admin Bar) Manager
 * - Per-role profiles (accordions)
 * - Single site + Multisite (network defaults)
 * - Dynamic discovery of toolbar nodes (top-level + first-level children)
 * - Reorder + hide nodes
 * - Optional: remove the "Howdy, " text
 */

// Option + page slugs (separate from your Menu Manager for simplicity)
define( 'UAH_TB_OPTION_SITE', UAH_PREFIX . 'toolbar_manager_site' );
define( 'UAH_TB_OPTION_NET',  UAH_PREFIX . 'toolbar_manager_network' );
define( 'UAH_TB_PAGE_SITE',   UAH_PREFIX . 'toolbar-manager' );
define( 'UAH_TB_PAGE_NET',    UAH_PREFIX . 'toolbar-manager-network' );

/** ─────────────────────────────────────────────────────────────────────────
 * Utilities
 * ───────────────────────────────────────────────────────────────────────── */

function uah_tb_roles_list() {
	$roles = [];
	$wr = wp_roles();
	if ( $wr && is_array( $wr->roles ) ) {
		foreach ( $wr->roles as $slug => $def ) {
			$roles[ $slug ] = $def['name'];
		}
	}
	return array_merge( [ 'default' => 'Default (all roles)' ], $roles );
}

function uah_tb_empty_profile() {
	return [
		'top_order'   => [], // array of top-level IDs
		'hidden'      => [], // array of IDs (top or sub) to hide
		'sub_order'   => [], // map: parentID => [child IDs...]
		'remove_howdy'=> false,
	];
}

function uah_tb_get_site_config() {
	$opt = get_option( UAH_TB_OPTION_SITE );
	if ( ! is_array( $opt ) ) $opt = [];
	return wp_parse_args( $opt, [ 'profiles' => [], 'version' => 1 ] );
}

function uah_tb_get_network_config() {
	if ( ! is_multisite() ) return [ 'profiles' => [], 'version' => 1 ];
	$opt = get_site_option( UAH_TB_OPTION_NET );
	if ( ! is_array( $opt ) ) $opt = [];
	return wp_parse_args( $opt, [ 'profiles' => [], 'version' => 1 ] );
}

/**
 * Resolve effective toolbar profile for current user:
 * site(role) → site(default) → network(role) → network(default) → empty
 */
function uah_tb_get_effective_profile() {
	$site = uah_tb_get_site_config();
	$net  = uah_tb_get_network_config();

	$user  = wp_get_current_user();
	$roles = (array) ( $user->roles ?? [] );
	$role  = count( $roles ) ? (string) $roles[0] : 'default';

	$ps = (array) ( $site['profiles'] ?? [] );
	$pn = (array) ( $net['profiles']  ?? [] );

	if ( isset( $ps[ $role ] ) )      return wp_parse_args( $ps[ $role ], uah_tb_empty_profile() );
	if ( isset( $ps['default'] ) )    return wp_parse_args( $ps['default'], uah_tb_empty_profile() );
	if ( isset( $pn[ $role ] ) )      return wp_parse_args( $pn[ $role ], uah_tb_empty_profile() );
	if ( isset( $pn['default'] ) )    return wp_parse_args( $pn['default'], uah_tb_empty_profile() );
	return uah_tb_empty_profile();
}

/**
 * Snapshot current toolbar nodes (after they’re built).
 * Returns an ordered structure:
 * [
 *   'top' => [ ['id','title'], ... in current order ... ],
 *   'subs' => [ parentId => [ ['id','title'], ... ] ]
 * ]
 */
function uah_tb_current_snapshot() {
	global $wp_admin_bar;
	$out = [ 'top' => [], 'subs' => [] ];

	if ( ! is_object( $wp_admin_bar ) || ! method_exists( $wp_admin_bar, 'get_nodes' ) ) {
		return $out;
	}

	$nodes = (array) $wp_admin_bar->get_nodes();
	if ( empty( $nodes ) ) return $out;

	// Maintain insertion order by iterating in the array's natural order.
	$seen_top = [];
	foreach ( $nodes as $node ) {
		/* @var stdClass $node */
		$parent = isset( $node->parent ) && $node->parent ? (string) $node->parent : 'root';
		$id     = (string) $node->id;
		$title  = wp_strip_all_tags( isset( $node->title ) ? $node->title : $id );

		if ( $parent === 'root' ) {
			if ( ! isset( $seen_top[ $id ] ) ) {
				$out['top'][] = [ 'id' => $id, 'title' => $title ];
				$seen_top[ $id ] = true;
			}
		}
	}

	// Children (first level)
	foreach ( $nodes as $node ) {
		$parent = isset( $node->parent ) && $node->parent ? (string) $node->parent : 'root';
		$id     = (string) $node->id;
		$title  = wp_strip_all_tags( isset( $node->title ) ? $node->title : $id );

		if ( $parent !== 'root' && isset( $seen_top[ $parent ] ) ) {
			if ( ! isset( $out['subs'][ $parent ] ) ) $out['subs'][ $parent ] = [];
			$out['subs'][ $parent ][] = [ 'id' => $id, 'title' => $title ];
		}
	}

	return $out;
}

/** ─────────────────────────────────────────────────────────────────────────
 * Admin pages (site + network)
 * ───────────────────────────────────────────────────────────────────────── */

add_action( 'admin_menu', function () {
	add_options_page(
		'Ultimate Admin – Toolbar',
		'Ultimate Admin – Toolbar',
		'manage_options',
		UAH_TB_PAGE_SITE,
		'uah_tb_render_page_site'
	);
}, 25 );

add_action( 'network_admin_menu', function () {
	if ( ! is_multisite() ) return;
	add_submenu_page(
		'settings.php',
		'Ultimate Admin – Toolbar (Network)',
		'Ultimate Admin – Toolbar',
		'manage_network_options',
		UAH_TB_PAGE_NET,
		'uah_tb_render_page_network'
	);
}, 25);

/** Save handlers */
add_action( 'admin_init', function(){
	if ( ! current_user_can( 'manage_options' ) ) return;
	if ( ! isset( $_POST['uah_tb_nonce'] ) ) return;
	if ( ! wp_verify_nonce( $_POST['uah_tb_nonce'], 'uah_tb_save' ) ) return;

	$profiles = [];
	if ( isset( $_POST['profiles_json'] ) && $_POST['profiles_json'] !== '' ) {
		$decoded = json_decode( wp_unslash( $_POST['profiles_json'] ), true );
		if ( is_array( $decoded ) ) $profiles = $decoded;
	}

	update_option( UAH_TB_OPTION_SITE, [ 'profiles' => $profiles, 'version' => 1 ] );
	wp_safe_redirect( add_query_arg( ['page' => UAH_TB_PAGE_SITE, 'updated' => 'true'], admin_url( 'options-general.php' ) ) );
	exit;
});

add_action( 'network_admin_menu', function(){
	// Capture normal POST to settings.php in network admin
	if ( ! is_multisite() ) return;
	if ( ! current_user_can( 'manage_network_options' ) ) return;
	if ( ! isset( $_POST['uah_tb_nonce_net'] ) ) return;
	if ( ! wp_verify_nonce( $_POST['uah_tb_nonce_net'], 'uah_tb_save_net' ) ) return;

	$profiles = [];
	if ( isset( $_POST['profiles_json'] ) && $_POST['profiles_json'] !== '' ) {
		$decoded = json_decode( wp_unslash( $_POST['profiles_json'] ), true );
		if ( is_array( $decoded ) ) $profiles = $decoded;
	}

	update_site_option( UAH_TB_OPTION_NET, [ 'profiles' => $profiles, 'version' => 1 ] );
	wp_safe_redirect( add_query_arg( ['page' => UAH_TB_PAGE_NET, 'updated' => 'true'], network_admin_url( 'settings.php' ) ) );
	exit;
});

/** Renderers */
function uah_tb_render_page_site()     { uah_tb_render_page( 'site' ); }
function uah_tb_render_page_network()  { uah_tb_render_page( 'network' ); }

function uah_tb_render_page( $context = 'site' ) {
	if ( $context === 'network' ) {
		if ( ! current_user_can( 'manage_network_options' ) ) return;
		$action_url = network_admin_url( 'settings.php?page=' . UAH_TB_PAGE_NET );
		$nonce_act  = 'uah_tb_save_net';
		$nonce_name = 'uah_tb_nonce_net';
		$saved      = uah_tb_get_network_config()['profiles'] ?? [];
		$title      = 'Ultimate Admin – Toolbar (Network Defaults)';
		$note       = 'These are network defaults. Each site can override them in its own Toolbar screen.';
	} else {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$action_url = admin_url( 'options-general.php?page=' . UAH_TB_PAGE_SITE );
		$nonce_act  = 'uah_tb_save';
		$nonce_name = 'uah_tb_nonce';
		$saved      = uah_tb_get_site_config()['profiles'] ?? [];
		$title      = 'Ultimate Admin – Toolbar Manager';
		$note       = 'Profiles are per role. Users get their role’s profile; if none, the Default profile.';
	}

	$snap  = uah_tb_current_snapshot();
	$roles = uah_tb_roles_list();

	$render_role = function( $role_key, $role_label ) use ( $saved, $snap ) {
		$prof = isset( $saved[ $role_key ] ) ? wp_parse_args( $saved[ $role_key ], uah_tb_empty_profile() ) : uah_tb_empty_profile();
		$hidden    = array_map( 'strval', (array) $prof['hidden'] );
		$top_saved = (array) $prof['top_order'];
		$sub_ord   = (array) $prof['sub_order'];
		$rm_howdy  = ! empty( $prof['remove_howdy'] );

		// Build display order for top-level: saved (that exist) + newcomers
		$live_top_ids = array_map( fn($r) => (string) $r['id'], $snap['top'] );
		$display = [];
		foreach ( $top_saved as $id ) if ( in_array( $id, $live_top_ids, true ) ) $display[] = $id;
		foreach ( $live_top_ids as $id ) if ( ! in_array( $id, $display, true ) ) $display[] = $id;

		$open = ( $role_key === 'default' ) ? ' open' : '';
		?>
		<details class="uah-role" data-role="<?php echo esc_attr( $role_key ); ?>"<?php echo $open; ?>>
			<summary><?php echo esc_html( $role_label ); ?></summary>

			<label style="display:inline-block;margin:8px 0 12px 0;">
				<input type="checkbox" class="uah-remove-howdy"<?php checked( $rm_howdy ); ?>> Remove “Howdy,” from account menu
			</label>

			<ul class="uah-sortable">
				<?php foreach ( $display as $id ):
					$row = null;
					foreach ( $snap['top'] as $r ) if ( $r['id'] === $id ) { $row = $r; break; }
					if ( ! $row ) continue;
					$title = $row['title'] ?: $row['id'];
					$chk   = in_array( $id, $hidden, true ) ? ' checked' : '';
					?>
					<li class="uah-item" data-slug="<?php echo esc_attr( $id ); ?>">
						<span class="dashicons dashicons-move uah-drag" aria-hidden="true"></span>
						<div>
							<div class="uah-title"><?php echo esc_html( $title ); ?></div>
							<div class="uah-slug"><?php echo esc_html( $id ); ?></div>
						</div>
						<label class="uah-hide"><input type="checkbox"<?php echo $chk; ?>> Hide</label>

						<?php
						$subs_live = $snap['subs'][ $id ] ?? [];
						if ( ! empty( $subs_live ) ):
							$current_sub_ids = array_map( fn($r)=> (string) $r['id'], $subs_live );
							$pref = isset( $sub_ord[ $id ] ) ? (array) $sub_ord[ $id ] : $current_sub_ids;
							$display_subs = [];
							foreach ( $pref as $s ) if ( in_array( $s, $current_sub_ids, true ) ) $display_subs[] = $s;
							foreach ( $current_sub_ids as $s ) if ( ! in_array( $s, $display_subs, true ) ) $display_subs[] = $s;
							?>
							<ul class="uah-sub">
								<?php foreach ( $display_subs as $sid ):
									$srow = null;
									foreach ( $subs_live as $r ) if ( $r['id'] === $sid ) { $srow = $r; break; }
									if ( ! $srow ) continue;
									$chk2 = in_array( $sid, $hidden, true ) ? ' checked' : '';
									?>
									<li class="uah-subitem" data-slug="<?php echo esc_attr( $srow['id'] ); ?>">
										<span class="dashicons dashicons-move uah-drag" aria-hidden="true"></span>
										<div>
											<div class="uah-title"><?php echo esc_html( $srow['title'] ?: $srow['id'] ); ?></div>
											<div class="uah-slug"><?php echo esc_html( $srow['id'] ); ?></div>
										</div>
										<label class="uah-hide"><input type="checkbox"<?php echo $chk2; ?>> Hide</label>
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
		<h1><?php echo esc_html( $title ); ?></h1>
		<p class="uah-help"><?php echo esc_html( $note ); ?></p>

		<div class="uah-grid">
			<div class="uah-card">
				<form id="uah-form" method="post" action="<?php echo esc_url( $action_url ); ?>">
					<?php
					if ( $context === 'network' ) wp_nonce_field( $nonce_act, $nonce_name );
					else wp_nonce_field( $nonce_act, $nonce_name );
					?>

					<?php foreach ( $roles as $role_key => $role_label ) $render_role( $role_key, $role_label ); ?>

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

/** Assets (reuse your admin styles if you like) */
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	$ok = in_array( $hook, [
		'settings_page_' . UAH_TB_PAGE_SITE,
		'settings_page_' . UAH_TB_PAGE_NET,
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
			var removeHowdy = $(this).find('> label .uah-remove-howdy').is(':checked');

			$(this).find('> ul.uah-sortable > li.uah-item').each(function(){
				var slug = $(this).data('slug');
				topOrder.push(slug);
				if ($(this).find('.uah-hide input').is(':checked')) hidden.push(slug);

				var subUL = $(this).find('ul.uah-sub');
				if (subUL.length){
					var subs = [];
					subUL.find('> li.uah-subitem').each(function(){
						var s = $(this).data('slug');
						subs.push(s);
						if ($(this).find('.uah-hide input').is(':checked')) hidden.push(s);
					});
					if (subs.length) subOrder[slug] = subs;
				}
			});

			data.profiles[role] = {
				top_order: topOrder,
				hidden: hidden,
				sub_order: subOrder,
				remove_howdy: removeHowdy
			};
		});
		$('#uah-profiles-json').val(JSON.stringify(data.profiles));
	}

	$('.uah-sortable').sortable({ handle: '.uah-drag', axis: 'y', placeholder: 'uah-placeholder', items: '> li' });
	$('.uah-sub').sortable({ handle: '.uah-drag', axis: 'y', placeholder: 'uah-placeholder', items: '> li' });
	$('#uah-form').on('submit', function(){ collect(); });
	$('#uah-reset').on('click', function(){ $('#uah-profiles-json').val(''); });
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
#uah .uah-placeholder{border:2px dashed #b4b9be;height:38px;margin-bottom:6px;border-radius:6px}
#uah .uah-sub{margin-left:38px;margin-top:6px}
CSS
);
});

/** ─────────────────────────────────────────────────────────────────────────
 * Apply toolbar changes (front-end + admin)
 * ───────────────────────────────────────────────────────────────────────── */

add_action( 'admin_bar_menu', function( $wp_admin_bar ){
	$prof = uah_tb_get_effective_profile();

	// Optional: remove “Howdy,” from my-account
	if ( ! empty( $prof['remove_howdy'] ) ) {
		$node = $wp_admin_bar->get_node( 'my-account' );
		if ( $node && isset( $node->title ) ) {
			$title = wp_strip_all_tags( $node->title );
			$title = preg_replace( '/^\s*Howdy,\s*/i', '', $title );
			$node->title = $title;
			$wp_admin_bar->add_node( (array) $node ); // update
		}
	}

	// Hide any nodes (top or children)
	if ( ! empty( $prof['hidden'] ) ) {
		foreach ( (array) $prof['hidden'] as $id ) {
			$wp_admin_bar->remove_node( (string) $id );
		}
	}

	// Reorder top-level + first-level children by remove → re-add (push to end)
	$nodes = (array) $wp_admin_bar->get_nodes();
	if ( empty( $nodes ) ) return;

	// Build quick maps
	$map = []; foreach ( $nodes as $n ) { $map[ (string) $n->id ] = $n; }
	// Current top-level order (insertion order)
	$top_now = [];
	foreach ( $nodes as $n ) {
		$parent = isset( $n->parent ) && $n->parent ? (string) $n->parent : 'root';
		if ( $parent === 'root' ) $top_now[] = (string) $n->id;
	}

	// Top-level reorder
	$saved_top = array_values( array_filter( (array) $prof['top_order'], function( $id ) use ( $top_now ){ return in_array( (string) $id, $top_now, true ); } ) );
	if ( ! empty( $saved_top ) ) {
		// Remove all top-level we're going to re-add (avoid duplicates)
		foreach ( $saved_top as $id ) {
			if ( isset( $map[ $id ] ) ) $wp_admin_bar->remove_node( $id );
		}
		// Re-add in saved order
		foreach ( $saved_top as $id ) {
			if ( isset( $map[ $id ] ) ) $wp_admin_bar->add_node( (array) $map[ $id ] );
		}
	}

	// Submenu reorder (first level children only)
	if ( ! empty( $prof['sub_order'] ) ) {
		foreach ( (array) $prof['sub_order'] as $parent => $children ) {
			$children = (array) $children;
			if ( empty( $children ) ) continue;

			// Remove then re-add children in saved order
			foreach ( $children as $cid ) {
				$cid = (string) $cid;
				// Only touch if it belongs to this parent right now
				if ( isset( $map[ $cid ] ) ) {
					$n = $map[ $cid ];
					$belongs = isset( $n->parent ) && (string) $n->parent === (string) $parent;
					if ( $belongs ) $wp_admin_bar->remove_node( $cid );
				}
			}
			foreach ( $children as $cid ) {
				if ( isset( $map[ $cid ] ) ) {
					$n = $map[ $cid ];
					$belongs = isset( $n->parent ) && (string) $n->parent === (string) $parent;
					if ( $belongs ) $wp_admin_bar->add_node( (array) $n );
				}
			}
		}
	}
}, 9999 ); // run after everyone else adds nodes
