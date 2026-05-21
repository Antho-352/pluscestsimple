<?php
/**
 * Category base — remove /category/ from category URLs.
 *
 * - Filters category_link so generated URLs drop the base.
 * - Adds rewrite rules so /<slug>/ resolves to the category.
 * - 301 redirects legacy /category/<slug>/ URLs to preserve SEO.
 *
 * Rewrite rules are flushed automatically on theme version bump
 * (see functions.php arw_pulse_tpl_v handler).
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

// 1. Strip /category/ from generated category links.
add_filter( 'category_link', function ( $link ) {
	return str_replace( '/category/', '/', $link );
}, 99 );

// 2. Register rewrite rules for /<slug>/ → category.
add_action( 'init', function () {
	$cats = get_categories( [ 'hide_empty' => false ] );
	if ( empty( $cats ) || is_wp_error( $cats ) ) {
		return;
	}
	foreach ( $cats as $cat ) {
		$slug = $cat->slug;
		// Build full hierarchical path for child categories.
		$path = $slug;
		$parent = $cat->parent;
		while ( $parent ) {
			$p = get_category( $parent );
			if ( ! $p || is_wp_error( $p ) ) { break; }
			$path   = $p->slug . '/' . $path;
			$parent = $p->parent;
		}
		add_rewrite_rule(
			'^' . $path . '/?$',
			'index.php?category_name=' . $path,
			'top'
		);
		add_rewrite_rule(
			'^' . $path . '/page/?([0-9]{1,})/?$',
			'index.php?category_name=' . $path . '&paged=$matches[1]',
			'top'
		);
		add_rewrite_rule(
			'^' . $path . '/feed/(feed|rdf|rss|rss2|atom)/?$',
			'index.php?category_name=' . $path . '&feed=$matches[1]',
			'top'
		);
		add_rewrite_rule(
			'^' . $path . '/(feed|rdf|rss|rss2|atom)/?$',
			'index.php?category_name=' . $path . '&feed=$matches[1]',
			'top'
		);
	}
} );

// 3. 301 redirect legacy /category/<slug>/ → /<slug>/.
add_action( 'template_redirect', function () {
	if ( ! is_category() ) {
		return;
	}
	$req = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	if ( strpos( $req, '/category/' ) === false ) {
		return;
	}
	$cat = get_queried_object();
	if ( ! $cat || empty( $cat->term_id ) ) {
		return;
	}
	$target = get_category_link( $cat->term_id );
	if ( ! $target ) {
		return;
	}
	wp_safe_redirect( $target, 301 );
	exit;
} );

// 4. Flush on category create/edit/delete so new slugs work immediately.
add_action( 'created_category', function () { flush_rewrite_rules( false ); } );
add_action( 'edited_category',  function () { flush_rewrite_rules( false ); } );
add_action( 'delete_category',  function () { flush_rewrite_rules( false ); } );
