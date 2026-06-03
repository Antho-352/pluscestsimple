<?php
/**
 * Taxonomies v2 — 6 taxonomies pour pcs_boutique.
 *
 *  pcs_cat    — catégorie produits  (Meubles, Décoration, etc.)
 *  pcs_type   — type d'enseigne     (Grande enseigne, Indépendant, etc.)
 *  pcs_mode   — mode de vente       (En boutique, En ligne, Les deux)
 *  pcs_dept   — département         (45, 75, …)  hierarchical=true
 *  pcs_region — région              (Centre-Val de Loire, …)
 *  pcs_ville  — ville               (créée dynamiquement à l'import)
 *
 * @package PCS_Directory
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function pcs_directory_register_taxonomies(): void {

	// ─── Catégorie ────────────────────────────────────────────────────────────
	register_taxonomy( 'pcs_cat', [ PCS_DIR_CPT ], [
		'labels'             => [
			'name'          => 'Catégories',
			'singular_name' => 'Catégorie',
			'menu_name'     => 'Catégories',
			'all_items'     => 'Toutes les catégories',
		],
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_nav_menus'  => true,
		'show_in_rest'       => true,
		'show_admin_column'  => true,
		'hierarchical'       => false,
		'rewrite'            => [ 'slug' => 'annuaire/categorie', 'with_front' => false ],
	] );

	// ─── Type d'enseigne ──────────────────────────────────────────────────────
	register_taxonomy( 'pcs_type', [ PCS_DIR_CPT ], [
		'labels'             => [
			'name'          => 'Types',
			'singular_name' => 'Type',
			'menu_name'     => 'Types',
			'all_items'     => 'Tous les types',
		],
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_nav_menus'  => true,
		'show_in_rest'       => true,
		'show_admin_column'  => false,
		'hierarchical'       => false,
		'rewrite'            => [ 'slug' => 'annuaire/type-boutique', 'with_front' => false ],
	] );

	// ─── Mode de vente ────────────────────────────────────────────────────────
	register_taxonomy( 'pcs_mode', [ PCS_DIR_CPT ], [
		'labels'             => [
			'name'          => 'Modes de vente',
			'singular_name' => 'Mode de vente',
			'menu_name'     => 'Mode de vente',
			'all_items'     => 'Tous les modes',
		],
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_nav_menus'  => false,
		'show_in_rest'       => true,
		'show_admin_column'  => false,
		'hierarchical'       => false,
		'rewrite'            => [ 'slug' => 'annuaire/mode', 'with_front' => false ],
	] );

	// ─── Département ──────────────────────────────────────────────────────────
	register_taxonomy( 'pcs_dept', [ PCS_DIR_CPT ], [
		'labels'             => [
			'name'          => 'Départements',
			'singular_name' => 'Département',
			'menu_name'     => 'Départements',
			'all_items'     => 'Tous les départements',
		],
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_nav_menus'  => true,
		'show_in_rest'       => true,
		'show_admin_column'  => true,
		'hierarchical'       => true,
		'rewrite'            => [ 'slug' => 'annuaire/departement', 'with_front' => false ],
	] );

	// ─── Région ───────────────────────────────────────────────────────────────
	register_taxonomy( 'pcs_region', [ PCS_DIR_CPT ], [
		'labels'             => [
			'name'          => 'Régions',
			'singular_name' => 'Région',
			'menu_name'     => 'Régions',
			'all_items'     => 'Toutes les régions',
		],
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_nav_menus'  => true,
		'show_in_rest'       => true,
		'show_admin_column'  => false,
		'hierarchical'       => false,
		'rewrite'            => [ 'slug' => 'annuaire/region', 'with_front' => false ],
	] );

	// ─── Ville ────────────────────────────────────────────────────────────────
	register_taxonomy( 'pcs_ville', [ PCS_DIR_CPT ], [
		'labels'             => [
			'name'          => 'Villes',
			'singular_name' => 'Ville',
			'menu_name'     => 'Villes',
			'all_items'     => 'Toutes les villes',
		],
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => false,
		'show_in_nav_menus'  => false,
		'show_in_rest'       => true,
		'show_admin_column'  => false,
		'hierarchical'       => false,
		'rewrite'            => [ 'slug' => 'annuaire/ville', 'with_front' => false ],
	] );
}
add_action( 'init', 'pcs_directory_register_taxonomies', 9 );

// ─── Mapping département → région ─────────────────────────────────────────────

function pcs_directory_dept_to_region(): array {
	return [
		'01' => 'Centre-Val de Loire', // placeholder — real map below
		'18' => 'Centre-Val de Loire', '28' => 'Centre-Val de Loire',
		'36' => 'Centre-Val de Loire', '37' => 'Centre-Val de Loire',
		'41' => 'Centre-Val de Loire', '45' => 'Centre-Val de Loire',
		'01' => 'Auvergne-Rhône-Alpes', '03' => 'Auvergne-Rhône-Alpes',
		'07' => 'Auvergne-Rhône-Alpes', '15' => 'Auvergne-Rhône-Alpes',
		'26' => 'Auvergne-Rhône-Alpes', '38' => 'Auvergne-Rhône-Alpes',
		'42' => 'Auvergne-Rhône-Alpes', '43' => 'Auvergne-Rhône-Alpes',
		'63' => 'Auvergne-Rhône-Alpes', '69' => 'Auvergne-Rhône-Alpes',
		'73' => 'Auvergne-Rhône-Alpes', '74' => 'Auvergne-Rhône-Alpes',
		'21' => 'Bourgogne-Franche-Comté', '25' => 'Bourgogne-Franche-Comté',
		'39' => 'Bourgogne-Franche-Comté', '58' => 'Bourgogne-Franche-Comté',
		'70' => 'Bourgogne-Franche-Comté', '71' => 'Bourgogne-Franche-Comté',
		'89' => 'Bourgogne-Franche-Comté', '90' => 'Bourgogne-Franche-Comté',
		'22' => 'Bretagne', '29' => 'Bretagne', '35' => 'Bretagne', '56' => 'Bretagne',
		'2a' => 'Corse', '2b' => 'Corse',
		'08' => 'Grand Est', '10' => 'Grand Est', '51' => 'Grand Est',
		'52' => 'Grand Est', '54' => 'Grand Est', '55' => 'Grand Est',
		'57' => 'Grand Est', '67' => 'Grand Est', '68' => 'Grand Est', '88' => 'Grand Est',
		'02' => 'Hauts-de-France', '59' => 'Hauts-de-France', '60' => 'Hauts-de-France',
		'62' => 'Hauts-de-France', '80' => 'Hauts-de-France',
		'75' => 'Île-de-France', '77' => 'Île-de-France', '78' => 'Île-de-France',
		'91' => 'Île-de-France', '92' => 'Île-de-France', '93' => 'Île-de-France',
		'94' => 'Île-de-France', '95' => 'Île-de-France',
		'14' => 'Normandie', '27' => 'Normandie', '50' => 'Normandie',
		'61' => 'Normandie', '76' => 'Normandie',
		'16' => 'Nouvelle-Aquitaine', '17' => 'Nouvelle-Aquitaine', '19' => 'Nouvelle-Aquitaine',
		'23' => 'Nouvelle-Aquitaine', '24' => 'Nouvelle-Aquitaine', '33' => 'Nouvelle-Aquitaine',
		'40' => 'Nouvelle-Aquitaine', '47' => 'Nouvelle-Aquitaine', '64' => 'Nouvelle-Aquitaine',
		'79' => 'Nouvelle-Aquitaine', '86' => 'Nouvelle-Aquitaine', '87' => 'Nouvelle-Aquitaine',
		'09' => 'Occitanie', '11' => 'Occitanie', '12' => 'Occitanie', '30' => 'Occitanie',
		'31' => 'Occitanie', '32' => 'Occitanie', '34' => 'Occitanie', '46' => 'Occitanie',
		'48' => 'Occitanie', '65' => 'Occitanie', '66' => 'Occitanie',
		'81' => 'Occitanie', '82' => 'Occitanie',
		'44' => 'Pays de la Loire', '49' => 'Pays de la Loire', '53' => 'Pays de la Loire',
		'72' => 'Pays de la Loire', '85' => 'Pays de la Loire',
		'04' => 'Provence-Alpes-Côte d\'Azur', '05' => 'Provence-Alpes-Côte d\'Azur',
		'06' => 'Provence-Alpes-Côte d\'Azur', '13' => 'Provence-Alpes-Côte d\'Azur',
		'83' => 'Provence-Alpes-Côte d\'Azur', '84' => 'Provence-Alpes-Côte d\'Azur',
		'971' => 'Guadeloupe', '972' => 'Martinique', '973' => 'Guyane',
		'974' => 'La Réunion', '976' => 'Mayotte',
	];
}

/**
 * Retourne le nom de région pour un code département donné.
 */
function pcs_directory_region_from_dept( string $dept ): string {
	$map = pcs_directory_dept_to_region();
	return $map[ strtolower( $dept ) ] ?? '';
}
