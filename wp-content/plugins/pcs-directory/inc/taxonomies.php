<?php
/**
 * Taxonomies de l'annuaire :
 *   - pcs_etab_type   : type de commerce (hierarchical) — 10 termes seedés
 *   - pcs_etab_region : région FR (hierarchical)        — 13 métropole + 5 DROM seedés
 *   - pcs_etab_ville  : ville (non-hierarchical)        — créée dynamiquement à l'import
 *
 * @package PCS_Directory
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Enregistre les 3 taxonomies de l'annuaire.
 *
 * @return void
 */
function pcs_directory_register_taxonomies(): void {
	// — Type de commerce (hierarchical) ────────────────────────────────────
	register_taxonomy(
		PCS_DIR_TAX_TYPE,
		[ PCS_DIR_CPT ],
		[
			'labels'             => [
				'name'              => __( 'Types de commerce', 'pluscestsimple' ),
				'singular_name'     => __( 'Type', 'pluscestsimple' ),
				'menu_name'         => __( 'Types', 'pluscestsimple' ),
				'all_items'         => __( 'Tous les types', 'pluscestsimple' ),
				'edit_item'         => __( 'Modifier le type', 'pluscestsimple' ),
				'add_new_item'      => __( 'Ajouter un type', 'pluscestsimple' ),
			],
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => true,
			'show_in_rest'       => true,
			'show_admin_column'  => true,
			'hierarchical'       => true,
			'rewrite'            => [ 'slug' => 'annuaire/type', 'with_front' => false ],
		]
	);

	// — Région FR (hierarchical) ───────────────────────────────────────────
	register_taxonomy(
		PCS_DIR_TAX_REGION,
		[ PCS_DIR_CPT ],
		[
			'labels'             => [
				'name'              => __( 'Régions', 'pluscestsimple' ),
				'singular_name'     => __( 'Région', 'pluscestsimple' ),
				'menu_name'         => __( 'Régions', 'pluscestsimple' ),
				'all_items'         => __( 'Toutes les régions', 'pluscestsimple' ),
				'edit_item'         => __( 'Modifier la région', 'pluscestsimple' ),
				'add_new_item'      => __( 'Ajouter une région', 'pluscestsimple' ),
			],
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => true,
			'show_in_rest'       => true,
			'show_admin_column'  => true,
			'hierarchical'       => true,
			'rewrite'            => [ 'slug' => 'annuaire/region', 'with_front' => false ],
		]
	);

	// — Ville (non-hierarchical, créée dynamiquement) ──────────────────────
	register_taxonomy(
		PCS_DIR_TAX_VILLE,
		[ PCS_DIR_CPT ],
		[
			'labels'             => [
				'name'              => __( 'Villes', 'pluscestsimple' ),
				'singular_name'     => __( 'Ville', 'pluscestsimple' ),
				'menu_name'         => __( 'Villes', 'pluscestsimple' ),
				'all_items'         => __( 'Toutes les villes', 'pluscestsimple' ),
				'edit_item'         => __( 'Modifier la ville', 'pluscestsimple' ),
				'add_new_item'      => __( 'Ajouter une ville', 'pluscestsimple' ),
			],
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => true,
			'show_in_rest'       => true,
			'show_admin_column'  => true,
			'hierarchical'       => false,
			'rewrite'            => [ 'slug' => 'annuaire/ville', 'with_front' => false ],
		]
	);
}
add_action( 'init', 'pcs_directory_register_taxonomies', 9 );

/**
 * Types de commerce par défaut (idempotent : skip si déjà présents).
 *
 * @return void
 */
function pcs_directory_seed_default_types(): void {
	$defaults = [
		'meubles'              => __( 'Meubles', 'pluscestsimple' ),
		'decoration'           => __( 'Décoration', 'pluscestsimple' ),
		'cuisine-equipee'      => __( 'Cuisine équipée', 'pluscestsimple' ),
		'salle-de-bain'        => __( 'Salle de bain', 'pluscestsimple' ),
		'outillage-bricolage'  => __( 'Outillage & bricolage', 'pluscestsimple' ),
		'jardin-exterieur'     => __( 'Jardin & extérieur', 'pluscestsimple' ),
		'luminaires'           => __( 'Luminaires', 'pluscestsimple' ),
		'textile-maison'       => __( 'Textile maison', 'pluscestsimple' ),
		'tapis-sols'           => __( 'Tapis & sols', 'pluscestsimple' ),
		'cadeaux-objets-deco'  => __( 'Cadeaux & objets déco', 'pluscestsimple' ),
	];
	foreach ( $defaults as $slug => $name ) {
		if ( term_exists( $slug, PCS_DIR_TAX_TYPE ) ) {
			continue;
		}
		wp_insert_term( $name, PCS_DIR_TAX_TYPE, [ 'slug' => $slug ] );
	}
}
add_action( 'init', 'pcs_directory_seed_default_types', 11 );

/**
 * Régions FR seedées à l'activation (13 métropole + 5 DROM).
 *
 * Source : INSEE — découpage administratif post-2016.
 *
 * @return void
 */
function pcs_directory_seed_default_regions(): void {
	$defaults = [
		// Métropole.
		'auvergne-rhone-alpes'        => 'Auvergne-Rhône-Alpes',
		'bourgogne-franche-comte'     => 'Bourgogne-Franche-Comté',
		'bretagne'                    => 'Bretagne',
		'centre-val-de-loire'         => 'Centre-Val de Loire',
		'corse'                       => 'Corse',
		'grand-est'                   => 'Grand Est',
		'hauts-de-france'             => 'Hauts-de-France',
		'ile-de-france'               => 'Île-de-France',
		'normandie'                   => 'Normandie',
		'nouvelle-aquitaine'          => 'Nouvelle-Aquitaine',
		'occitanie'                   => 'Occitanie',
		'pays-de-la-loire'            => 'Pays de la Loire',
		'provence-alpes-cote-d-azur'  => 'Provence-Alpes-Côte d\'Azur',
		// DROM.
		'guadeloupe'                  => 'Guadeloupe',
		'martinique'                  => 'Martinique',
		'guyane'                      => 'Guyane',
		'la-reunion'                  => 'La Réunion',
		'mayotte'                     => 'Mayotte',
	];
	foreach ( $defaults as $slug => $name ) {
		if ( term_exists( $slug, PCS_DIR_TAX_REGION ) ) {
			continue;
		}
		wp_insert_term( $name, PCS_DIR_TAX_REGION, [ 'slug' => $slug ] );
	}
}
add_action( 'init', 'pcs_directory_seed_default_regions', 11 );

/**
 * Mapping code département (2 chiffres) → slug région.
 *
 * Sert à l'import Sirene pour rattacher chaque établissement à sa région
 * sans dépendre d'un appel API supplémentaire.
 *
 * @return array<string, string>
 */
function pcs_directory_dept_to_region_map(): array {
	return [
		// Auvergne-Rhône-Alpes.
		'01' => 'auvergne-rhone-alpes', '03' => 'auvergne-rhone-alpes', '07' => 'auvergne-rhone-alpes',
		'15' => 'auvergne-rhone-alpes', '26' => 'auvergne-rhone-alpes', '38' => 'auvergne-rhone-alpes',
		'42' => 'auvergne-rhone-alpes', '43' => 'auvergne-rhone-alpes', '63' => 'auvergne-rhone-alpes',
		'69' => 'auvergne-rhone-alpes', '73' => 'auvergne-rhone-alpes', '74' => 'auvergne-rhone-alpes',
		// Bourgogne-Franche-Comté.
		'21' => 'bourgogne-franche-comte', '25' => 'bourgogne-franche-comte', '39' => 'bourgogne-franche-comte',
		'58' => 'bourgogne-franche-comte', '70' => 'bourgogne-franche-comte', '71' => 'bourgogne-franche-comte',
		'89' => 'bourgogne-franche-comte', '90' => 'bourgogne-franche-comte',
		// Bretagne.
		'22' => 'bretagne', '29' => 'bretagne', '35' => 'bretagne', '56' => 'bretagne',
		// Centre-Val de Loire.
		'18' => 'centre-val-de-loire', '28' => 'centre-val-de-loire', '36' => 'centre-val-de-loire',
		'37' => 'centre-val-de-loire', '41' => 'centre-val-de-loire', '45' => 'centre-val-de-loire',
		// Corse.
		'2a' => 'corse', '2b' => 'corse', '20' => 'corse',
		// Grand Est.
		'08' => 'grand-est', '10' => 'grand-est', '51' => 'grand-est', '52' => 'grand-est',
		'54' => 'grand-est', '55' => 'grand-est', '57' => 'grand-est', '67' => 'grand-est',
		'68' => 'grand-est', '88' => 'grand-est',
		// Hauts-de-France.
		'02' => 'hauts-de-france', '59' => 'hauts-de-france', '60' => 'hauts-de-france',
		'62' => 'hauts-de-france', '80' => 'hauts-de-france',
		// Île-de-France.
		'75' => 'ile-de-france', '77' => 'ile-de-france', '78' => 'ile-de-france', '91' => 'ile-de-france',
		'92' => 'ile-de-france', '93' => 'ile-de-france', '94' => 'ile-de-france', '95' => 'ile-de-france',
		// Normandie.
		'14' => 'normandie', '27' => 'normandie', '50' => 'normandie', '61' => 'normandie', '76' => 'normandie',
		// Nouvelle-Aquitaine.
		'16' => 'nouvelle-aquitaine', '17' => 'nouvelle-aquitaine', '19' => 'nouvelle-aquitaine',
		'23' => 'nouvelle-aquitaine', '24' => 'nouvelle-aquitaine', '33' => 'nouvelle-aquitaine',
		'40' => 'nouvelle-aquitaine', '47' => 'nouvelle-aquitaine', '64' => 'nouvelle-aquitaine',
		'79' => 'nouvelle-aquitaine', '86' => 'nouvelle-aquitaine', '87' => 'nouvelle-aquitaine',
		// Occitanie.
		'09' => 'occitanie', '11' => 'occitanie', '12' => 'occitanie', '30' => 'occitanie',
		'31' => 'occitanie', '32' => 'occitanie', '34' => 'occitanie', '46' => 'occitanie',
		'48' => 'occitanie', '65' => 'occitanie', '66' => 'occitanie', '81' => 'occitanie', '82' => 'occitanie',
		// Pays de la Loire.
		'44' => 'pays-de-la-loire', '49' => 'pays-de-la-loire', '53' => 'pays-de-la-loire',
		'72' => 'pays-de-la-loire', '85' => 'pays-de-la-loire',
		// PACA.
		'04' => 'provence-alpes-cote-d-azur', '05' => 'provence-alpes-cote-d-azur',
		'06' => 'provence-alpes-cote-d-azur', '13' => 'provence-alpes-cote-d-azur',
		'83' => 'provence-alpes-cote-d-azur', '84' => 'provence-alpes-cote-d-azur',
		// DROM.
		'971' => 'guadeloupe', '972' => 'martinique', '973' => 'guyane',
		'974' => 'la-reunion', '976' => 'mayotte',
	];
}

/**
 * Retourne le slug région pour un code postal donné (ou null si inconnu).
 *
 * @param string $code_postal Code postal 5 chiffres.
 * @return string|null
 */
function pcs_directory_region_slug_from_cp( string $code_postal ): ?string {
	$cp = trim( $code_postal );
	if ( '' === $cp ) {
		return null;
	}
	// DROM : codes postaux 971xx, 972xx, etc.
	if ( strlen( $cp ) === 5 && '97' === substr( $cp, 0, 2 ) ) {
		$dept = substr( $cp, 0, 3 );
	} elseif ( strlen( $cp ) === 5 && '98' === substr( $cp, 0, 2 ) ) {
		// 980 Monaco / 984-988 COM : pas dans les régions FR métropolitaines, on skip.
		return null;
	} else {
		$dept = substr( $cp, 0, 2 );
	}
	$map = pcs_directory_dept_to_region_map();
	return $map[ $dept ] ?? null;
}
