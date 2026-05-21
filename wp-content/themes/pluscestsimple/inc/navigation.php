<?php
/**
 * Navigation — préservation des assignations menu primary/footer à travers
 * les resets de templates/template_parts au bump de version.
 *
 * Pourquoi :
 * Le thème supprime tous les wp_template_part en DB à chaque bump de version
 * (cf. functions.php) pour que les fichiers du ZIP soient autoritaires. Mais
 * l'attribut `ref` du bloc wp:navigation (qui pointe vers le wp_navigation post
 * contenant le menu) est stocké dans le wp_template_part → perdu au reset.
 *
 * Solution :
 * 1. Avant le reset, functions.php extrait les refs et les sauvegarde dans
 *    l'option `arw_pulse_nav_refs` = [ 'primary' => N, 'footer' => N ]
 * 2. Les fichiers parts/header.html et parts/footer.html portent les classes
 *    `arw-nav-primary` / `arw-nav-footer` sur leur bloc wp:navigation
 * 3. Ce filter intercepte le rendu de chaque core/navigation sans `ref`,
 *    détecte le className marker, et injecte le ref depuis l'option.
 *
 * Résultat : l'assignation des menus survit indéfiniment aux mises à jour.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_filter( 'render_block_data', function ( $parsed_block ) {
	if ( ( $parsed_block['blockName'] ?? '' ) !== 'core/navigation' ) {
		return $parsed_block;
	}
	// Si un ref est déjà présent (utilisateur l'a remis manuellement), on respecte.
	if ( ! empty( $parsed_block['attrs']['ref'] ) ) {
		return $parsed_block;
	}

	$class = (string) ( $parsed_block['attrs']['className'] ?? '' );
	if ( $class === '' ) { return $parsed_block; }

	$refs = (array) get_option( 'arw_pulse_nav_refs', [] );

	if ( strpos( $class, 'arw-nav-primary' ) !== false && ! empty( $refs['primary'] ) ) {
		$ref = (int) $refs['primary'];
		if ( get_post_status( $ref ) === 'publish' ) {
			$parsed_block['attrs']['ref'] = $ref;
		}
	} elseif ( strpos( $class, 'arw-nav-footer' ) !== false && ! empty( $refs['footer'] ) ) {
		$ref = (int) $refs['footer'];
		if ( get_post_status( $ref ) === 'publish' ) {
			$parsed_block['attrs']['ref'] = $ref;
		}
	}

	return $parsed_block;
} );
