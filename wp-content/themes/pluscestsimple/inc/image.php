<?php
/**
 * Image helpers: register sizes (additional to theme-supports.php),
 * generate <picture> with AVIF/WebP fallback when available.
 *
 * Les tailles principales du thème (pcs-hero, pcs-card, pcs-card-sm,
 * pcs-square, pcs-vertical) sont déclarées dans theme-supports.php.
 * On ajoute ici les variantes legacy `pcs-*` étendues.
 *
 * @package pluscestsimple
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'after_setup_theme', function () {
	set_post_thumbnail_size( 1200, 630, true ); // OG default.
	// Tailles supplémentaires non couvertes par theme-supports.php.
	add_image_size( 'pcs-hero-xl', 1600, 900, true );
	add_image_size( 'pcs-card-wide', 800, 450, true );
} );

/**
 * Alt text fallback — when alt is empty, use (in order):
 *   1. Featured image → post title of the post displaying it
 *   2. Attachment caption (post_excerpt)
 *   3. Attachment title
 * This prevents SEO-invisible images and improves accessibility.
 */
add_filter( 'wp_get_attachment_image_attributes', function ( $attr, $attachment ) {
	if ( ! empty( $attr['alt'] ) ) { return $attr; }

	$fallback = '';
	if ( is_singular() && get_post_thumbnail_id() === (int) $attachment->ID ) {
		$fallback = get_the_title();
	}
	if ( ! $fallback ) { $fallback = $attachment->post_excerpt; }
	if ( ! $fallback ) { $fallback = $attachment->post_title; }

	$attr['alt'] = sanitize_text_field( (string) $fallback );
	return $attr;
}, 20, 2 );

/**
 * Return <picture> markup with AVIF + WebP fallback when sibling files exist.
 * Looks for /uploads/.../image.jpg → image.avif / image.webp next to it.
 */
function pcs_picture( $attachment_id, $size = 'large', $attrs = [] ) {
	$src = wp_get_attachment_image_src( $attachment_id, $size );
	if ( ! $src ) { return ''; }
	$url    = $src[0];
	$width  = $src[1];
	$height = $src[2];

	$alt     = trim( (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) );
	$loading = $attrs['loading'] ?? 'lazy';
	$fpri    = $attrs['fetchpriority'] ?? 'auto';
	$classes = $attrs['class'] ?? '';
	$sizes   = $attrs['sizes'] ?? '(max-width: 768px) 100vw, 720px';

	// Build <source> tags if sibling files exist.
	$avif = preg_replace( '/\.(jpe?g|png)$/i', '.avif', $url );
	$webp = preg_replace( '/\.(jpe?g|png)$/i', '.webp', $url );
	$base_dir = wp_get_upload_dir()['basedir'];
	$base_url = wp_get_upload_dir()['baseurl'];
	$avif_path = str_replace( $base_url, $base_dir, $avif );
	$webp_path = str_replace( $base_url, $base_dir, $webp );

	$out  = '<picture>';
	if ( file_exists( $avif_path ) && $avif !== $url ) {
		$out .= sprintf( '<source type="image/avif" srcset="%s">', esc_url( $avif ) );
	}
	if ( file_exists( $webp_path ) && $webp !== $url ) {
		$out .= sprintf( '<source type="image/webp" srcset="%s">', esc_url( $webp ) );
	}
	$out .= sprintf(
		'<img src="%s" alt="%s" width="%d" height="%d" loading="%s" fetchpriority="%s" decoding="async"%s sizes="%s">',
		esc_url( $url ),
		esc_attr( $alt ),
		(int) $width,
		(int) $height,
		esc_attr( $loading ),
		esc_attr( $fpri ),
		$classes ? ' class="' . esc_attr( $classes ) . '"' : '',
		esc_attr( $sizes )
	);
	$out .= '</picture>';
	return $out;
}

// ─── Auto-wrap featured images en <picture> avec sources AVIF/WebP ─────────
// Si des fichiers .avif/.webp existent à côté du .jpg/.png original, on émet
// un <picture> avec sources progressives — sinon retombe sur le <img> standard.
// Les .avif/.webp peuvent être générés par un plugin de conversion ou un
// script CLI au moment de l'upload média.

add_filter( 'post_thumbnail_html', function ( $html, $post_id, $thumbnail_id, $size, $attr ) {
	if ( ! $thumbnail_id || is_admin() ) { return $html; }
	$src = wp_get_attachment_image_src( $thumbnail_id, $size );
	if ( ! $src ) { return $html; }
	$url = $src[0];
	$base_dir = wp_get_upload_dir()['basedir'];
	$base_url = wp_get_upload_dir()['baseurl'];
	$avif_path = str_replace( $base_url, $base_dir, preg_replace( '/\.(jpe?g|png)$/i', '.avif', $url ) );
	$webp_path = str_replace( $base_url, $base_dir, preg_replace( '/\.(jpe?g|png)$/i', '.webp', $url ) );
	if ( ! file_exists( $avif_path ) && ! file_exists( $webp_path ) ) {
		return $html;
	}
	// Au moins un format moderne dispo : régénère via picture().
	$attrs = [
		'loading'       => $attr['loading']       ?? 'lazy',
		'fetchpriority' => $attr['fetchpriority'] ?? ( is_singular() ? 'high' : 'auto' ),
		'class'         => $attr['class']         ?? '',
		'sizes'         => $attr['sizes']         ?? '(max-width: 768px) 100vw, 720px',
	];
	return pcs_picture( $thumbnail_id, $size, $attrs );
}, 10, 5 );
