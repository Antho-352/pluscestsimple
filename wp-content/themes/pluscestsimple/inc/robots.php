<?php
/**
 * robots.txt customization.
 * WP core generates a minimal robots.txt on /robots.txt (virtual).
 * We add: sitemap declaration, explicit CSS/JS allow, hygienic disallows.
 *
 * @package pluscestsimple
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Priority 999 = runs last → our output wins over any plugin that appends (e.g. Content-Signal).
add_filter( 'robots_txt', function ( $output, $public ) {
	if ( ! $public ) {
		return $output;
	}

	$sitemaps = [ home_url( '/wp-sitemap.xml' ) ];
	$sitemaps = apply_filters( 'pcs_robots_sitemaps', $sitemaps );

	$lines = [
		'User-agent: *',
		'Allow: /',
		'',
		'# Keep rendering-critical resources crawlable',
		'Allow: /wp-content/uploads/',
		'Allow: /wp-content/themes/',
		'Allow: /wp-includes/*.js',
		'Allow: /wp-includes/*.css',
		'Allow: /wp-content/*.js',
		'Allow: /wp-content/*.css',
		'',
		'# Noise & attack surface',
		'Disallow: /wp-admin/',
		'Disallow: /wp-login.php',
		'Disallow: /xmlrpc.php',
		'Disallow: /?s=',
		'Disallow: /search/',
		'Disallow: /*?*preview=true',
		'Disallow: /*?*p=',
		'Disallow: /*?*replytocom=',
		'Allow: /wp-admin/admin-ajax.php',
		'',
		'# Pagination des blocs Query Loop (?query-{id}-page=) : pages synthétiques',
		'# sans valeur SEO (canonical déjà vers la base) → on évite le gaspillage',
		'# de budget de crawl sur des combinaisons quasi infinies.',
		'Disallow: /*?query-',
		'Disallow: /*&query-',
		'',
	];

	// Filter pour permettre aux extensions d'ajouter des Disallow ciblés.
	$extra = (array) apply_filters( 'pcs_robots_disallow', [] );
	if ( $extra ) {
		$lines[] = '# Site-specific disallow';
		foreach ( $extra as $rule ) {
			$rule = ltrim( (string) $rule );
			if ( $rule === '' ) { continue; }
			$lines[] = ( str_starts_with( $rule, 'Disallow:' ) || str_starts_with( $rule, 'Allow:' ) )
				? $rule
				: 'Disallow: ' . $rule;
		}
		$lines[] = '';
	}

	foreach ( $sitemaps as $sm ) {
		$lines[] = 'Sitemap: ' . esc_url_raw( $sm );
	}

	return implode( "\n", $lines ) . "\n";
}, 999, 2 );
