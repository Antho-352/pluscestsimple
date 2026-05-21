<?php
/**
 * Category base — remove /category/ from category URLs.
 *
 * Stratégie D1 :
 * - Le slug interne de la catégorie est suffixé (ex: `decoration-cat`) pour ne
 *   PAS entrer en collision avec une page WP du même topic (slug `decoration`).
 * - Le préfixe /category/ est retiré de toutes les URLs canoniques générées.
 * - Si une page WP existe avec un slug correspondant au slug de la catégorie
 *   sans le suffixe `-cat` (ex: page « decoration » pour cat « decoration-cat »),
 *   l'archive de catégorie redirige 301 vers la page (la page joue le rôle de
 *   landing/hub éditorial). Sinon, l'archive est rendue normalement.
 * - Le suffixe attendu est filtrable via `pcs_category_slug_suffix`.
 * - Le slug cible de redirect est filtrable via `pcs_category_landing_page_slug`
 *   (recevant le slug de la cat + le terme — peut retourner un slug arbitraire
 *   pour découpler cat slug et page slug).
 *
 * Implémentation :
 * 1. Filtre `category_link` pour drop /category/ des URLs émises par WP.
 * 2. Rewrite rules pour résoudre /<slug>/ → archive de catégorie.
 * 3. Redirect 301 sur /category/<slug>/ legacy → URL canonique.
 * 4. Redirect 301 sur l'archive vers la landing page WP si elle existe.
 * 5. Flush rewrite rules sur création/édition/suppression de catégorie.
 *
 * @package pluscestsimple
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

// 4. Stratégie D1 — redirect 301 de l'archive vers la page WP « landing » si elle existe.
// Exemple : archive `/decoration-cat/` → page WP `/decoration/`.
add_action( 'template_redirect', function () {
	if ( ! is_category() ) {
		return;
	}
	$cat = get_queried_object();
	if ( ! $cat || empty( $cat->slug ) ) {
		return;
	}

	// Suffixe attendu sur le slug catégorie pour éviter collision avec les pages.
	$suffix = (string) apply_filters( 'pcs_category_slug_suffix', '-cat', $cat );

	// Déduit le slug de la page cible : on retire le suffixe du slug catégorie.
	// Le filtre `pcs_category_landing_page_slug` permet de découpler complètement
	// (retourner un slug arbitraire, ou une chaîne vide pour ne pas rediriger).
	$default_page_slug = $suffix && str_ends_with( $cat->slug, $suffix )
		? substr( $cat->slug, 0, -strlen( $suffix ) )
		: '';
	$page_slug = (string) apply_filters( 'pcs_category_landing_page_slug', $default_page_slug, $cat );
	if ( '' === $page_slug ) {
		return;
	}

	$page = get_page_by_path( $page_slug );
	if ( ! $page || 'publish' !== $page->post_status ) {
		return;
	}

	// Évite la boucle infinie si la page elle-même a le slug de l'archive courante.
	$target = get_permalink( $page );
	$current = home_url( add_query_arg( null, null ) );
	if ( $target === $current ) {
		return;
	}

	wp_safe_redirect( $target, 301 );
	exit;
} );

// 5. Flush on category create/edit/delete so new slugs work immediately.
add_action( 'created_category', function () { flush_rewrite_rules( false ); } );
add_action( 'edited_category',  function () { flush_rewrite_rules( false ); } );
add_action( 'delete_category',  function () { flush_rewrite_rules( false ); } );
