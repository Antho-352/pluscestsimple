<?php
/**
 * Shortcode [pcs_directory] : embarque l'archive filtrable dans n'importe quelle page.
 *
 * Attributs :
 *   - type    : slug d'un type (filtre pré-appliqué)
 *   - region  : slug d'une région
 *   - ville   : slug d'une ville
 *   - limit   : nombre max d'établissements (défaut 12)
 *   - filters : 'yes' (défaut) ou 'no' pour cacher la barre de filtres
 *
 * Exemple : [pcs_directory type="meubles" region="ile-de-france" limit="9"]
 *
 * @package PCS_Directory
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Handler du shortcode [pcs_directory].
 *
 * @param array<string, string>|string $atts Attributs.
 * @return string HTML.
 */
function pcs_directory_shortcode_handler( $atts ): string {
	$atts = shortcode_atts(
		[
			'type'    => '',
			'region'  => '',
			'ville'   => '',
			'limit'   => '12',
			'filters' => 'yes',
		],
		is_array( $atts ) ? $atts : [],
		'pcs_directory'
	);

	$tax_query = [];
	if ( ! empty( $atts['type'] ) ) {
		$tax_query[] = [
			'taxonomy' => PCS_DIR_TAX_TYPE,
			'field'    => 'slug',
			'terms'    => [ sanitize_text_field( $atts['type'] ) ],
		];
	}
	if ( ! empty( $atts['region'] ) ) {
		$tax_query[] = [
			'taxonomy' => PCS_DIR_TAX_REGION,
			'field'    => 'slug',
			'terms'    => [ sanitize_text_field( $atts['region'] ) ],
		];
	}
	if ( ! empty( $atts['ville'] ) ) {
		$tax_query[] = [
			'taxonomy' => PCS_DIR_TAX_VILLE,
			'field'    => 'slug',
			'terms'    => [ sanitize_text_field( $atts['ville'] ) ],
		];
	}
	if ( count( $tax_query ) > 1 ) {
		$tax_query['relation'] = 'AND';
	}

	$q = new WP_Query( [
		'post_type'      => PCS_DIR_CPT,
		'post_status'    => 'publish',
		'posts_per_page' => max( 1, min( 60, (int) $atts['limit'] ) ),
		'tax_query'      => $tax_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		'meta_key'       => '_pcs_etab_is_featured', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'orderby'        => [ 'meta_value_num' => 'DESC', 'date' => 'DESC' ],
		'no_found_rows'  => true,
	] );

	// Force le chargement des assets (le shortcode peut être posé hors archive).
	wp_enqueue_style( 'pcs-directory' );
	wp_enqueue_script( 'pcs-directory' );

	ob_start();
	echo '<section class="pcs-directory pcs-directory--shortcode">';

	if ( 'yes' === $atts['filters'] ) {
		echo pcs_directory_render_filters(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	if ( ! $q->have_posts() ) {
		echo '<p class="pcs-directory__empty">' . esc_html__( 'Aucun établissement à afficher.', 'pluscestsimple' ) . '</p>';
	} else {
		echo '<div class="pcs-directory__grid">';
		foreach ( $q->posts as $post ) {
			echo pcs_directory_render_card( (int) $post->ID ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '</div>';
	}

	echo '</section>';
	wp_reset_postdata();
	return (string) ob_get_clean();
}
add_shortcode( 'pcs_directory', 'pcs_directory_shortcode_handler' );
