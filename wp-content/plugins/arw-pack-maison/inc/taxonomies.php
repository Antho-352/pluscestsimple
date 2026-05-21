<?php
/**
 * Taxonomies du pack Maison.
 *
 * `arw_compat_category` — hierarchical : sols / murs / cloisons / SDB / cuisine / structure / extérieur / chauffage.
 * Attachée au CPT `arw_compat_rule`.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function arw_maison_register_taxonomies(): void {
	register_taxonomy( ARW_MAISON_CATEGORY_TAX, [ ARW_MAISON_RULE_CPT ], [
		'labels' => [
			'name'          => __( 'Catégories Compatibilimètre', 'arw-maison' ),
			'singular_name' => __( 'Catégorie',                   'arw-maison' ),
			'menu_name'     => __( 'Catégories',                  'arw-maison' ),
			'all_items'     => __( 'Toutes les catégories',       'arw-maison' ),
			'edit_item'     => __( 'Modifier la catégorie',       'arw-maison' ),
			'add_new_item'  => __( 'Ajouter une catégorie',       'arw-maison' ),
		],
		'public'            => true,
		'hierarchical'      => true,
		'show_ui'           => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'show_in_menu'      => true,
		'rewrite'           => [ 'slug' => 'compatibilimetre/categorie', 'with_front' => false ],
	] );
}

add_action( 'init', 'arw_maison_register_taxonomies' );

// Manually expose the taxonomy as a submenu under ARW Pulse parent.
// (Auto-attachment doesn't happen when the CPT is nested under a parent slug.)
add_action( 'admin_menu', function () {
	if ( ! defined( 'ARW_PULSE_ADMIN_SLUG' ) ) { return; }
	add_submenu_page(
		ARW_PULSE_ADMIN_SLUG,
		__( 'Catégories Compatibilimètre', 'arw-maison' ),
		__( '⚙ Compatibilimètre — Catégories', 'arw-maison' ),
		'manage_categories',
		'edit-tags.php?taxonomy=' . ARW_MAISON_CATEGORY_TAX . '&post_type=' . ARW_MAISON_RULE_CPT
	);
}, 25 );

// Highlight the parent menu when editing taxonomy terms (fixes the "submenu not active" UX bug).
add_filter( 'parent_file', function ( $parent_file ) {
	global $current_screen;
	if ( $current_screen && $current_screen->taxonomy === ARW_MAISON_CATEGORY_TAX && defined( 'ARW_PULSE_ADMIN_SLUG' ) ) {
		return ARW_PULSE_ADMIN_SLUG;
	}
	return $parent_file;
} );

// ─── Default categories on activation ────────────────────────────────────────

add_action( 'init', function () {
	if ( ! get_option( 'arw_maison_default_cats_seeded' ) ) {
		$defaults = [
			'sols'       => 'Sols',
			'murs'       => 'Murs',
			'cloisons'   => 'Cloisons',
			'sdb'        => 'Salle de bain',
			'cuisine'    => 'Cuisine',
			'structure'  => 'Structure (porteur, charpente)',
			'exterieur'  => 'Extérieur',
			'chauffage'  => 'Chauffage',
			'electricite'=> 'Électricité',
			'plomberie'  => 'Plomberie',
		];
		foreach ( $defaults as $slug => $name ) {
			if ( ! term_exists( $slug, ARW_MAISON_CATEGORY_TAX ) ) {
				wp_insert_term( $name, ARW_MAISON_CATEGORY_TAX, [ 'slug' => $slug ] );
			}
		}
		update_option( 'arw_maison_default_cats_seeded', 1 );
	}
}, 20 );
