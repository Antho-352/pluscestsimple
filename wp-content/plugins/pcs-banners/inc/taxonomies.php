<?php
/**
 * Taxonomy pcs_banner_slot : emplacements (homepage-mid, category-intro, etc.).
 *
 * Hierarchical = false (tags-like) : un slot par bannière, pas d'arborescence.
 * Les 4 termes par défaut sont créés à l'activation.
 *
 * @package PCS_Banners
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Enregistre la taxonomy pcs_banner_slot rattachée au CPT pcs_banner.
 *
 * @return void
 */
function pcs_banner_register_taxonomy(): void {
	$labels = [
		'name'              => __( 'Emplacements', 'pluscestsimple' ),
		'singular_name'     => __( 'Emplacement', 'pluscestsimple' ),
		'search_items'      => __( 'Rechercher un emplacement', 'pluscestsimple' ),
		'all_items'         => __( 'Tous les emplacements', 'pluscestsimple' ),
		'edit_item'         => __( 'Modifier l\'emplacement', 'pluscestsimple' ),
		'update_item'       => __( 'Mettre à jour', 'pluscestsimple' ),
		'add_new_item'      => __( 'Ajouter un emplacement', 'pluscestsimple' ),
		'new_item_name'     => __( 'Nouvel emplacement', 'pluscestsimple' ),
		'menu_name'         => __( 'Emplacements', 'pluscestsimple' ),
	];

	register_taxonomy(
		PCS_BANNER_SLOT_TAX,
		[ PCS_BANNER_CPT ],
		[
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => false,
			'show_in_rest'       => true, // requis pour Gutenberg.
			'show_admin_column'  => true,
			'hierarchical'       => false,
			'rewrite'            => false,
			'meta_box_cb'        => 'post_categories_meta_box', // checkbox single-friendly.
		]
	);
}
add_action( 'init', 'pcs_banner_register_taxonomy', 9 );

/**
 * Crée les 4 termes par défaut (idempotent : skip si déjà présents).
 *
 * @return void
 */
function pcs_banner_seed_default_slots(): void {
	$defaults = [
		// Slots historiques (v1+)
		'homepage-mid'   => [ 'Accueil — milieu de page', 'Accueil, entre deux sections' ],
		'category-intro' => [ 'Catégorie — après intro', 'Page catégorie, après l\'intro' ],
		'category-mid'   => [ 'Catégorie — milieu', 'Page catégorie, au milieu' ],
		'in-article'     => [ 'Article — in-text', 'Article, in-text' ],
		// Nouveaux slots homepage v2.5.x (thème pluscestsimple front-page.php)
		'homepage-top'     => [ 'Accueil — bannière top', 'Tout en haut, au-dessus du héro' ],
		'homepage-sidebar' => [ 'Accueil — sidebar grille 2×2', 'À droite de la grille 2×2 sous le héro (format vertical)' ],
		// Sidebars des sections catégorie de la homepage
		'cat-sidebar-decoration'   => [ 'Accueil — sidebar Décoration', 'Sidebar section Décoration sur la homepage' ],
		'cat-sidebar-travaux'      => [ 'Accueil — sidebar Travaux', 'Sidebar section Travaux sur la homepage' ],
		'cat-sidebar-jardin'       => [ 'Accueil — sidebar Jardin', 'Sidebar section Jardin sur la homepage' ],
		'cat-sidebar-architecture' => [ 'Accueil — sidebar Architecture', 'Sidebar section Architecture sur la homepage' ],
		'cat-sidebar-lifestyle'    => [ 'Accueil — sidebar Lifestyle', 'Sidebar section Lifestyle sur la homepage' ],
		// Sidebar pub présent sur toutes les pages article single
		'article-sidebar'          => [ 'Article — sidebar', 'Sidebar verticale présente sur toutes les pages article' ],
	];

	foreach ( $defaults as $slug => [ $name, $description ] ) {
		if ( term_exists( $slug, PCS_BANNER_SLOT_TAX ) ) {
			continue;
		}
		wp_insert_term(
			$name,
			PCS_BANNER_SLOT_TAX,
			[
				'slug'        => $slug,
				'description' => $description,
			]
		);
	}
}
// Sécurité : si le plugin a été activé avant que ce code soit dispo, on seed quand même au boot.
add_action( 'init', 'pcs_banner_seed_default_slots', 11 );

/**
 * Retourne la liste des slots (terms) sous forme [ slug => name ].
 *
 * @return array<string, string>
 */
function pcs_banner_get_all_slots(): array {
	$terms = get_terms(
		[
			'taxonomy'   => PCS_BANNER_SLOT_TAX,
			'hide_empty' => false,
		]
	);
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return [];
	}
	$out = [];
	foreach ( $terms as $term ) {
		$out[ $term->slug ] = $term->name;
	}
	return $out;
}
