<?php
/**
 * Branding — logo + favicon defaults.
 *
 * - Logo: inline SVG with site name + signature mark (speed lines + wheel).
 *   Used when no custom logo is uploaded via Site Identity.
 * - Favicon: static SVG from /assets/images/favicon.svg, output in <head> when
 *   no Site Icon is set in Customizer.
 *
 * Both fallbacks are replaced the moment the user uploads a real logo / icon
 * via Apparence → Personnaliser → Identité du site.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Inline SVG used as default logo.
 * Uses currentColor so it adapts to dark/light header (urban skin = white on black).
 */
function arw_pulse_default_logo_svg() {
	$name   = mb_strtoupper( wp_strip_all_tags( get_bloginfo( 'name' ) ), 'UTF-8' );
	$name   = trim( $name );
	if ( '' === $name ) { $name = 'SITE'; }

	// Rough SVG width estimate based on char count (monospace-ish).
	$char_w  = 13; // px per char at font-size 22 with Space Grotesk bold
	$text_w  = max( 60, strlen( $name ) * $char_w );
	$total_w = 42 + $text_w + 4; // mark + padding + text + margin

	$svg = sprintf(
		'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %1$d 40" role="img" aria-label="%2$s" fill="none">' .
		'<path d="M2 12 H10" stroke="#ffd600" stroke-width="2.5" stroke-linecap="round"/>' .
		'<path d="M2 20 H14" stroke="#ffd600" stroke-width="2.5" stroke-linecap="round"/>' .
		'<path d="M2 28 H10" stroke="#ffd600" stroke-width="2.5" stroke-linecap="round"/>' .
		'<circle cx="28" cy="20" r="8" stroke="currentColor" stroke-width="2.5"/>' .
		'<circle cx="28" cy="20" r="1.8" fill="currentColor"/>' .
		'<text x="42" y="28" font-family="\'Space Grotesk\', system-ui, -apple-system, \'Segoe UI\', Arial, sans-serif" font-weight="700" font-size="22" letter-spacing="-0.3" fill="currentColor">%3$s</text>' .
		'</svg>',
		(int) $total_w,
		esc_attr( $name ),
		esc_html( $name )
	);

	return $svg;
}

/**
 * Replace the core/site-logo block output when no custom logo is uploaded.
 */
add_filter( 'render_block_core/site-logo', function ( $content, $block ) {
	if ( has_custom_logo() ) {
		return $content;
	}
	return sprintf(
		'<div class="wp-block-site-logo arw-default-logo"><a href="%s" class="custom-logo-link" rel="home" aria-label="%s">%s</a></div>',
		esc_url( home_url( '/' ) ),
		esc_attr( get_bloginfo( 'name' ) ),
		arw_pulse_default_logo_svg()
	);
}, 10, 2 );

/**
 * Fallback favicon — SVG. Output only when Site Icon isn't set.
 */
add_action( 'wp_head', function () {
	if ( has_site_icon() ) { return; }
	$favicon = ARW_PULSE_DIR . '/assets/images/favicon.svg';
	if ( file_exists( $favicon ) ) {
		printf(
			'<link rel="icon" href="%s" type="image/svg+xml">' . "\n",
			esc_url( ARW_PULSE_URI . '/assets/images/favicon.svg' )
		);
	}
	// PNG fallback for older browsers + apple-touch-icon.
	$png = ARW_PULSE_DIR . '/assets/images/favicon-512.png';
	if ( file_exists( $png ) ) {
		printf(
			'<link rel="icon" type="image/png" sizes="512x512" href="%s">' . "\n",
			esc_url( ARW_PULSE_URI . '/assets/images/favicon-512.png' )
		);
		printf(
			'<link rel="apple-touch-icon" sizes="512x512" href="%s">' . "\n",
			esc_url( ARW_PULSE_URI . '/assets/images/favicon-512.png' )
		);
	}
}, 2 );

/**
 * Apple touch icon from Site Icon when set (overrides the PNG fallback).
 */
add_action( 'wp_head', function () {
	if ( ! has_site_icon() ) { return; }
	$icon_id = (int) get_option( 'site_icon' );
	$src = wp_get_attachment_image_src( $icon_id, [ 180, 180 ] );
	if ( $src ) {
		printf(
			'<link rel="apple-touch-icon" sizes="180x180" href="%s">' . "\n",
			esc_url( $src[0] )
		);
	}
}, 2 );

/**
 * Web app manifest — virtual route /manifest.webmanifest.
 * Improves mobile install + basic PWA signal; also referenced in <link>.
 */
add_action( 'init', function () {
	add_rewrite_rule( '^manifest\.webmanifest$', 'index.php?arw_manifest=1', 'top' );
} );

add_filter( 'query_vars', function ( $vars ) {
	$vars[] = 'arw_manifest';
	return $vars;
} );

add_action( 'template_redirect', function () {
	if ( ! get_query_var( 'arw_manifest' ) ) { return; }
	$icon_id  = (int) get_option( 'site_icon' );
	$icon_url = '';
	if ( $icon_id ) {
		// Defense in depth : valider que l'attachement pointe bien dans uploads basedir,
		// même si admin compromis a uploadé hors arborescence WP.
		$file = get_attached_file( $icon_id );
		$base = wp_get_upload_dir();
		$base = isset( $base['basedir'] ) ? realpath( $base['basedir'] ) : '';
		$real = $file ? realpath( $file ) : '';
		if ( $real && $base && strpos( $real, $base ) === 0 ) {
			$src      = wp_get_attachment_image_src( $icon_id, 'full' );
			$icon_url = $src[0] ?? '';
		}
	}
	if ( ! $icon_url && file_exists( ARW_PULSE_DIR . '/assets/images/favicon-512.png' ) ) {
		$icon_url = ARW_PULSE_URI . '/assets/images/favicon-512.png';
	}
	$manifest = [
		'name'             => get_bloginfo( 'name' ),
		'short_name'       => mb_substr( get_bloginfo( 'name' ), 0, 12 ),
		'start_url'        => home_url( '/' ),
		'display'          => 'standalone',
		'background_color' => '#0a0a0a',
		'theme_color'      => '#0a0a0a',
		'icons'            => $icon_url ? [
			[ 'src' => $icon_url, 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable' ],
		] : [],
	];
	header( 'Content-Type: application/manifest+json; charset=utf-8' );
	header( 'Cache-Control: public, max-age=86400' );
	echo wp_json_encode( $manifest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP );
	exit;
} );

add_action( 'wp_head', function () {
	printf( '<link rel="manifest" href="%s">' . "\n", esc_url( home_url( '/manifest.webmanifest' ) ) );
	printf( '<meta name="theme-color" content="%s">' . "\n", '#0a0a0a' );
}, 2 );
