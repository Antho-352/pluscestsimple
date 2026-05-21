<?php
/**
 * Title tag & custom meta title.
 * - Consistent templates per page type.
 * - Optional per-post override via `_arw_meta_title` meta (edited through the
 *   Post sidebar panel; REST-exposed).
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

// _arw_meta_title post meta is registered in inc/seo.php (centralised).

add_filter( 'pre_get_document_title', function ( $title ) {
	$site    = get_bloginfo( 'name' );
	$tagline = get_bloginfo( 'description' );

	if ( is_singular() ) {
		$pid      = get_queried_object_id();
		$override = get_post_meta( $pid, '_arw_meta_title', true );
		// Fallback to Yoast / RankMath if present.
		// Audit 2026-05-14 (F6) : patterns avec limite explicite [^%]{1,40} pour éviter
		// backtracking catastrophique sur postmeta importé depuis source non-fiable.
		if ( ! $override ) {
			$yoast = (string) get_post_meta( $pid, '_yoast_wpseo_title', true );
			// Strip Yoast template tags (%%sitename%%, %%title%%, etc.).
			if ( $yoast ) {
				$yoast = preg_replace( '/%%[^%]{1,40}%%/', '', $yoast );
				$yoast = trim( preg_replace( '/\s+/', ' ', (string) $yoast ) );
				$yoast = trim( $yoast, ' —-|·' );
				if ( $yoast ) { $override = $yoast; }
			}
		}
		if ( ! $override ) {
			$rm = (string) get_post_meta( $pid, 'rank_math_title', true );
			if ( $rm ) {
				$rm = preg_replace( '/%[^%]{1,40}%/', '', $rm );
				$rm = trim( preg_replace( '/\s+/', ' ', (string) $rm ) );
				$override = trim( $rm, ' —-|·' );
			}
		}
		if ( $override ) { return $override; }
		return sprintf( '%s — %s', get_the_title(), $site );
	}
	if ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		return sprintf( '%s — %s', $term->name, $site );
	}
	if ( is_author() ) {
		return sprintf( '%s — %s', get_queried_object()->display_name, $site );
	}
	if ( is_home() ) {
		return sprintf( '%s — %s', __( 'Articles', 'arw-pulse' ), $site );
	}
	if ( is_search() ) {
		return sprintf( '%s « %s » — %s', __( 'Résultats pour', 'arw-pulse' ), get_search_query(), $site );
	}
	if ( is_404() ) {
		return sprintf( '%s — %s', __( '404 — Page introuvable', 'arw-pulse' ), $site );
	}
	// Front page.
	return $tagline ? sprintf( '%s — %s', $site, $tagline ) : $site;
}, 20 );
