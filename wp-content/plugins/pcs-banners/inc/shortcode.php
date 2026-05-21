<?php
/**
 * Shortcode [pcs_banner slot="homepage-mid"] — équivalent du bloc Gutenberg.
 * Pratique pour les patterns, les widgets, ou le contenu legacy.
 *
 * @package PCS_Banners
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Callback du shortcode [pcs_banner].
 *
 * @param array<string, mixed>|string $atts Attributs.
 * @return string HTML rendu.
 */
function pcs_banner_shortcode_callback( $atts ): string {
	$atts = shortcode_atts(
		[
			'slot' => 'homepage-mid',
		],
		is_array( $atts ) ? $atts : [],
		'pcs_banner'
	);

	$slot = sanitize_key( (string) $atts['slot'] );
	if ( '' === $slot ) {
		return '';
	}

	return pcs_banner_render( $slot );
}
add_shortcode( 'pcs_banner', 'pcs_banner_shortcode_callback' );
