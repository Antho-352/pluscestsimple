<?php
/**
 * Unified admin menu — single top-level "ARW Pulse" entry grouping
 * all CPTs + settings pages (Produits, Spots, Randos, Soumissions,
 * Kit média, Mentions, Contenu Accueil, Fonctionnalités…).
 *
 * Shared with the pack (`arw-pack-outdoor`) via the ARW_PULSE_ADMIN_SLUG
 * constant.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'ARW_PULSE_ADMIN_SLUG', 'arw-pulse-main' );

add_action( 'admin_menu', function () {
	add_menu_page(
		__( 'ARW Pulse', 'arw-pulse' ),
		__( 'ARW Pulse', 'arw-pulse' ),
		'edit_posts',
		ARW_PULSE_ADMIN_SLUG,
		'arw_pulse_render_overview_page',
		'dashicons-superhero-alt',
		2 // position = juste sous Tableau de bord
	);

	// Overview sub-item (same slug as parent = intro page).
	add_submenu_page(
		ARW_PULSE_ADMIN_SLUG,
		__( 'Vue d\'ensemble', 'arw-pulse' ),
		__( 'Vue d\'ensemble', 'arw-pulse' ),
		'edit_posts',
		ARW_PULSE_ADMIN_SLUG,
		'arw_pulse_render_overview_page'
	);
}, 5 );

/**
 * Ajoute les sous-menus taxonomies sous ARW Pulse.
 * Quand un CPT est déplacé vers un menu custom via `show_in_menu`, WP n'ajoute plus
 * automatiquement les sous-menus de ses taxonomies (ex : Produits → Catégories,
 * Spots → Régions/Saisons). On les ajoute explicitement.
 */
add_action( 'admin_menu', function () {
	$taxonomies = [
		'arw_product_cat' => [ 'label' => __( 'Catégories produit', 'arw-pulse' ), 'cap' => 'manage_categories' ],
		'arw_region'      => [ 'label' => __( 'Régions (spots)', 'arw-pulse' ),     'cap' => 'manage_categories' ],
		'arw_season'      => [ 'label' => __( 'Saisons (spots)', 'arw-pulse' ),     'cap' => 'manage_categories' ],
	];
	foreach ( $taxonomies as $tax => $meta ) {
		if ( ! taxonomy_exists( $tax ) ) { continue; }
		add_submenu_page(
			ARW_PULSE_ADMIN_SLUG,
			$meta['label'],
			$meta['label'],
			$meta['cap'],
			'edit-tags.php?taxonomy=' . $tax
		);
	}
}, 50 );

/**
 * Highlight le menu parent quand on édite une taxonomie rattachée au pack.
 */
add_filter( 'parent_file', function ( $parent_file ) {
	global $current_screen;
	if ( ! $current_screen ) { return $parent_file; }
	if ( in_array( $current_screen->taxonomy ?? '', [ 'arw_product_cat', 'arw_region', 'arw_season' ], true ) ) {
		return ARW_PULSE_ADMIN_SLUG;
	}
	return $parent_file;
} );

// ─── Overview page ────────────────────────────────────────────────────────────

function arw_pulse_render_overview_page(): void {
	$theme   = wp_get_theme();
	$version = $theme->get( 'Version' );
	?>
	<div class="wrap">
		<h1 style="display:flex;align-items:center;gap:10px">
			ARW Pulse
			<span style="font-size:12px;background:#f0f0f1;border-radius:12px;padding:3px 10px;color:#50575e">v<?php echo esc_html( $version ); ?></span>
		</h1>

		<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:16px;margin-top:20px">

			<div class="card" style="background:#fff;padding:20px;border:1px solid #dcdcde;border-radius:4px">
				<h2 style="margin-top:0;font-size:16px">📝 Contenu éditorial</h2>
				<p style="color:#50575e;font-size:13px">Articles, catégories, médias. Gérés via l'éditeur WordPress classique.</p>
				<p>
					<a href="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>" class="button">Articles</a>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>" class="button">Pages</a>
				</p>
			</div>

			<div class="card" style="background:#fff;padding:20px;border:1px solid #dcdcde;border-radius:4px">
				<h2 style="margin-top:0;font-size:16px">🎨 Apparence</h2>
				<p style="color:#50575e;font-size:13px">Skin du thème, logo, menus, templates FSE.</p>
				<p>
					<a href="<?php echo esc_url( admin_url( 'site-editor.php' ) ); ?>" class="button">Éditeur FSE</a>
					<a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>" class="button">Personnaliser</a>
				</p>
			</div>

			<div class="card" style="background:#fff;padding:20px;border:1px solid #dcdcde;border-radius:4px">
				<h2 style="margin-top:0;font-size:16px">🏠 Page d'accueil</h2>
				<p style="color:#50575e;font-size:13px">Titre, description, CTAs, image hero, manifesto.</p>
				<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=arw-homepage' ) ); ?>" class="button button-primary">Éditer la home</a></p>
			</div>

			<div class="card" style="background:#fff;padding:20px;border:1px solid #dcdcde;border-radius:4px">
				<h2 style="margin-top:0;font-size:16px">⚙️ Configuration site</h2>
				<p style="color:#50575e;font-size:13px">Modules actifs, kit média, mentions légales.</p>
				<p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=arw-features' ) ); ?>" class="button">Fonctionnalités</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=arw-media-kit' ) ); ?>" class="button">Kit média</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=arw-legal' ) ); ?>" class="button">Mentions légales</a>
				</p>
			</div>

			<?php if ( post_type_exists( 'arw_product' ) ) : ?>
			<div class="card" style="background:#fff;padding:20px;border:1px solid #dcdcde;border-radius:4px">
				<h2 style="margin-top:0;font-size:16px">🛒 Produits affiliés</h2>
				<p style="color:#50575e;font-size:13px"><?php echo (int) ( wp_count_posts( 'arw_product' )?->publish ?? 0 ); ?> publiés</p>
				<p><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=arw_product' ) ); ?>" class="button">Gérer les produits</a></p>
			</div>
			<?php endif; ?>

			<?php if ( post_type_exists( 'arw_spot' ) ) : ?>
			<div class="card" style="background:#fff;padding:20px;border:1px solid #dcdcde;border-radius:4px">
				<h2 style="margin-top:0;font-size:16px">📍 Spots (pack outdoor)</h2>
				<p style="color:#50575e;font-size:13px"><?php echo (int) ( wp_count_posts( 'arw_spot' )?->publish ?? 0 ); ?> publiés</p>
				<p><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=arw_spot' ) ); ?>" class="button">Gérer les spots</a></p>
			</div>
			<?php endif; ?>

			<?php if ( post_type_exists( 'arw_trail' ) ) : ?>
			<div class="card" style="background:#fff;padding:20px;border:1px solid #dcdcde;border-radius:4px">
				<h2 style="margin-top:0;font-size:16px">🥾 Randonnées</h2>
				<p style="color:#50575e;font-size:13px"><?php echo (int) ( wp_count_posts( 'arw_trail' )?->publish ?? 0 ); ?> publiées</p>
				<p><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=arw_trail' ) ); ?>" class="button">Gérer les randos</a></p>
			</div>
			<?php endif; ?>

			<div class="card" style="background:#fff;padding:20px;border:1px solid #dcdcde;border-radius:4px">
				<h2 style="margin-top:0;font-size:16px">📬 Soumissions formulaire</h2>
				<p style="color:#50575e;font-size:13px"><?php echo (int) ( wp_count_posts( 'arw_submission' )?->publish ?? 0 ); ?> reçues</p>
				<p><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=arw_submission' ) ); ?>" class="button">Voir les soumissions</a></p>
			</div>

		</div>

		<div style="margin-top:30px;padding:16px 20px;background:#f6f7f7;border-left:4px solid #2271b1;border-radius:2px">
			<p style="margin:0;font-size:13px">
				<strong>Besoin d'aide ?</strong> Consulte le README du thème
				(<code>wp-content/themes/arw-pulse/README.md</code>)
				ou le guide <code>docs/NEW-SITE.md</code> pour toute la procédure de démarrage.
			</p>
		</div>

	</div>
	<?php
}
