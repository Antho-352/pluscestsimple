<?php
/**
 * Patterns — enregistrement des catégories.
 *
 * Les patterns eux-mêmes vivent dans /patterns/*.php et sont auto-chargés
 * par WordPress (depuis 6.0). Ce module ne fait qu'enregistrer les catégories
 * d'organisation dans l'inserter Gutenberg.
 *
 * @package pluscestsimple
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'init',
	function () {
		if ( ! function_exists( 'register_block_pattern_category' ) ) {
			return;
		}
		register_block_pattern_category( 'pcs-hero',    [ 'label' => __( 'Plus c\'est simple — Hero', 'pluscestsimple' ) ] );
		register_block_pattern_category( 'pcs-section', [ 'label' => __( 'Plus c\'est simple — Sections', 'pluscestsimple' ) ] );
		register_block_pattern_category( 'pcs-page',    [ 'label' => __( 'Plus c\'est simple — Pages', 'pluscestsimple' ) ] );
		register_block_pattern_category( 'pcs-article', [ 'label' => __( 'Plus c\'est simple — Article', 'pluscestsimple' ) ] );
	}
);
