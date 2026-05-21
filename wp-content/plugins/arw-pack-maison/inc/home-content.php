<?php
/**
 * Pré-remplit les contenus Hero + Manifesto de la page d'accueil au premier
 * lancement du pack, en utilisant les textes validés pour pluscestsimple.com.
 *
 * Logique : on ne touche aux options que si elles sont vides ET si on n'a
 * jamais seedé. Une fois seedées, l'admin peut éditer librement via
 * Réglages → Contenu Accueil — nos valeurs sont devenues le point de départ,
 * pas une contrainte.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

const ARW_MAISON_HOME_SEEDED_OPT = 'arw_maison_home_seeded';

add_action( 'init', 'arw_maison_seed_home_content', 25 );

function arw_maison_seed_home_content(): void {
	if ( get_option( ARW_MAISON_HOME_SEEDED_OPT ) ) { return; }

	$hero_existing = get_option( 'arw_hp_hero', [] );
	$mani_existing = get_option( 'arw_hp_manifesto', [] );

	if ( empty( $hero_existing ) || ! is_array( $hero_existing ) || ! array_filter( $hero_existing ) ) {
		update_option( 'arw_hp_hero', arw_maison_default_hero() );
	}

	if ( empty( $mani_existing ) || ! is_array( $mani_existing ) || ! array_filter( $mani_existing ) ) {
		update_option( 'arw_hp_manifesto', arw_maison_default_manifesto() );
	}

	update_option( ARW_MAISON_HOME_SEEDED_OPT, gmdate( 'c' ) );
}

function arw_maison_default_hero(): array {
	return [
		'eyebrow'             => 'Le bon réflexe avant chaque chantier',
		'date_text'           => '',
		'title_html'          => 'Refaire sa maison sans<br>se tromper.',
		'description'         => 'Avant chaque chantier, deux ou trois décisions changent tout. On vous aide à les reconnaître, en une question.',
		'cta_primary_label'   => 'Tester mon projet',
		'cta_primary_url'     => home_url( '/compatibilimetre/' ),
		'cta_secondary_label' => 'Le carnet',
		'cta_secondary_url'   => home_url( '/blog/' ),
		'image_url'           => '',
		'image_id'            => 0,
		'image_alt'           => '',
		'image_width'         => 0,
		'image_height'        => 0,
		'meta_left'           => 'Plus c\'est simple',
		'meta_right'          => 'Édition 2026',
	];
}

function arw_maison_default_manifesto(): array {
	return [
		'eyebrow'   => 'Pourquoi Plus c\'est simple',
		'text_html' => 'Internet déborde de tendances. Les magazines vendent des canapés. Les artisans facturent leurs erreurs.<br><br>' .
		               '<em>Plus c\'est simple</em> part d\'un constat froid : <mark>80 % des regrets de chantier viennent de 20 % des décisions</mark> — mal posées, mal informées, mal anticipées.<br><br>' .
		               'Notre travail consiste à isoler ces 20 % et à les rendre lisibles. Aucun catalogue d\'inspiration. Aucun classement. Juste les vrais arbitrages, expliqués sans détour, par catégorie.',
		'sig_name'  => 'Plus c\'est simple',
		'sig_loc'   => 'pluscestsimple.com',
	];
}
