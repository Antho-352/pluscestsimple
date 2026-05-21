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

// Le menu admin de la taxonomy est exposé automatiquement par WordPress sous
// le menu du CPT (show_in_menu => true). Plus de nesting manuel sous un
// ARW_PULSE_ADMIN_SLUG (le thème pluscestsimple n'a pas de menu admin centralisé).

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
