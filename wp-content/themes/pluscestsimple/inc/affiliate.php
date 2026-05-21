<?php
/**
 * Affiliation helpers.
 * - Auto rel="sponsored nofollow noopener" on outgoing links tagged as affiliate
 * - Auto-insert FTC-style disclosure at top of post when it contains affiliate links
 * - UTM helper for outbound CTA blocks
 *
 * @package pluscestsimple
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Filter the post content to process affiliate links.
 * Conventions:
 *   - Any <a> with class "is-affiliate" or data-affiliate="1" gets rel="sponsored nofollow noopener" and target="_blank".
 *   - All external links get rel="noopener" minimum.
 */
add_filter( 'the_content', function ( $content ) {
	if ( ! is_singular() || empty( $content ) ) { return $content; }

	$has_affiliate = false;
	$site_host     = wp_parse_url( home_url(), PHP_URL_HOST );

	$content = preg_replace_callback( '#<a\s+([^>]+)>#i', function ( $m ) use ( &$has_affiliate, $site_host ) {
		$attrs_str = $m[1];

		// Parse href and class quickly.
		preg_match( '/href="([^"]+)"/i', $attrs_str, $h );
		preg_match( '/class="([^"]*)"/i', $attrs_str, $c );
		$href  = $h[1] ?? '';
		$class = $c[1] ?? '';

		$host       = $href ? wp_parse_url( $href, PHP_URL_HOST ) : '';
		$is_ext     = $host && $host !== $site_host;
		$is_affil   = strpos( $class, 'is-affiliate' ) !== false || strpos( $attrs_str, 'data-affiliate="1"' ) !== false;

		if ( ! $is_ext && ! $is_affil ) {
			return $m[0];
		}

		$rel_parts = [ 'noopener' ];
		if ( $is_affil ) {
			$rel_parts[] = 'sponsored';
			$rel_parts[] = 'nofollow';
			$has_affiliate = true;
		} elseif ( $is_ext ) {
			// Leave dofollow for regular external (editorial choice).
		}

		// Merge with existing rel.
		if ( preg_match( '/rel="([^"]*)"/i', $attrs_str, $r ) ) {
			$existing  = preg_split( '/\s+/', $r[1] );
			$rel_parts = array_unique( array_merge( $existing, $rel_parts ) );
			$attrs_str = preg_replace( '/rel="[^"]*"/i', 'rel="' . esc_attr( implode( ' ', $rel_parts ) ) . '"', $attrs_str );
		} else {
			$attrs_str .= ' rel="' . esc_attr( implode( ' ', $rel_parts ) ) . '"';
		}

		// target=_blank for affiliate, optional for external.
		if ( $is_affil && strpos( $attrs_str, 'target=' ) === false ) {
			$attrs_str .= ' target="_blank"';
		}

		return '<a ' . trim( $attrs_str ) . '>';
	}, $content );

	// Inject disclosure at top if affiliate links detected and not already shown via pattern.
	if ( $has_affiliate && apply_filters( 'pcs_auto_disclosure', true ) && strpos( $content, 'pcs-disclosure' ) === false ) {
		$disclosure = pcs_disclosure_markup();
		$content    = $disclosure . $content;
	}

	return $content;
}, 15 );

/**
 * Disclosure markup (FTC + FR compliant).
 */
function pcs_disclosure_markup() {
	$text = apply_filters(
		'pcs_disclosure_text',
		__( 'Cet article contient des liens d\'affiliation. Si vous achetez via ces liens, nous pouvons percevoir une commission sans coût supplémentaire pour vous. Cela nous aide à maintenir le site.', 'pluscestsimple' )
	);
	return '<aside class="pcs-disclosure" role="note">' . esc_html( $text ) . '</aside>';
}

/**
 * Append UTM to a URL. Usage in templates or patterns.
 */
function pcs_utm( $url, $campaign = '', $medium = 'affiliate' ) {
	$params = [
		'utm_source'   => sanitize_title( wp_parse_url( home_url(), PHP_URL_HOST ) ),
		'utm_medium'   => sanitize_key( $medium ),
		'utm_campaign' => sanitize_title( $campaign ?: get_post_field( 'post_name' ) ),
	];
	return add_query_arg( $params, $url );
}
