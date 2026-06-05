<?php
/**
 * Category Redirects — SEO consolidation.
 *
 * Les catégories WP du site ont été créées avec suffixe `-cat` (ex: decoration-cat,
 * decoration-par-piece-cat) pour libérer les URLs `/decoration/`, `/travaux/`, etc.
 * au profit des pages piliers (CMS). Mais WordPress génère malgré tout les archives
 * publiques de ces catégories, ce qui crée du duplicate content avec les pages
 * piliers (qui affichent les mêmes articles via `inc/category-query-filter.php`).
 *
 * Ce fichier consolide tout vers les pages piliers via des redirections 301 :
 *
 *   /decoration-cat/                                  → /decoration/
 *   /decoration-cat/decoration-par-piece-cat/         → /decoration/
 *   /decoration-par-piece-cat/  (qui était en 404)    → /decoration/
 *
 * Effets SEO :
 *   - Plus de duplicate content
 *   - Link juice consolidé vers les pages piliers
 *   - Nettoyage progressif des URLs déjà indexées par Google (deindexation auto via 301)
 *
 * En filet de sécurité, on ajoute aussi `noindex, follow` sur les archives catégorie
 * (si le redirect échoue pour une raison quelconque, l'archive ne sera pas indexée).
 *
 * @package pluscestsimple
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Liste des slugs de piliers (pages CMS) qui servent d'entrée SEO canonique.
 * Source unique : pcs_content_structure() de init-content.php.
 *
 * @return array<string>
 */
function pcs_pilier_slugs(): array {
	if ( ! function_exists( 'pcs_content_structure' ) ) {
		return [ 'decoration', 'travaux', 'jardin', 'architecture', 'lifestyle', 'immobilier' ];
	}
	return array_keys( pcs_content_structure() );
}

/**
 * Trouve le slug de pilier racine pour un slug de catégorie donné.
 * Ex: "decoration-par-piece-cat" → "decoration", "travaux-cat" → "travaux".
 *
 * @param string $cat_slug Slug de la catégorie WP (avec ou sans suffixe -cat).
 * @return string|null Slug du pilier racine, ou null si aucun match.
 */
function pcs_find_pilier_root( string $cat_slug ): ?string {
	// Strip -cat suffix
	$base = $cat_slug;
	if ( str_ends_with( $base, '-cat' ) ) {
		$base = substr( $base, 0, -4 );
	}
	$piliers = pcs_pilier_slugs();
	foreach ( $piliers as $p ) {
		if ( $base === $p || str_starts_with( $base, $p . '-' ) ) {
			return $p;
		}
	}
	return null;
}

/**
 * Page cible d'une catégorie : sous-page éditoriale si elle existe
 * (sous-catégorie → /<pilier>/<sous>/), sinon page pilier racine.
 *
 * @param string $cat_slug Slug catégorie (avec ou sans suffixe -cat).
 * @return WP_Post|null
 */
function pcs_category_target_page( string $cat_slug ): ?WP_Post {
	$base = str_ends_with( $cat_slug, '-cat' ) ? substr( $cat_slug, 0, -4 ) : $cat_slug;

	if ( ! function_exists( 'pcs_content_structure' ) ) {
		$p = get_page_by_path( $base );
		return $p instanceof WP_Post ? $p : null;
	}

	foreach ( pcs_content_structure() as $root => $data ) {
		// Catégorie racine → page pilier.
		if ( $base === $root ) {
			$p = get_page_by_path( $root );
			return $p instanceof WP_Post ? $p : null;
		}
		// Sous-catégorie <pilier>-<sous> → sous-page si elle existe, sinon pilier.
		if ( str_starts_with( $base, $root . '-' ) ) {
			$sub = substr( $base, strlen( $root ) + 1 );
			if ( isset( $data['sub_cats'][ $sub ] ) ) {
				$sub_page = get_page_by_path( $root . '/' . $sub );
				if ( $sub_page instanceof WP_Post ) {
					return $sub_page;
				}
				$p = get_page_by_path( $root ); // fallback racine
				return $p instanceof WP_Post ? $p : null;
			}
		}
	}
	return null;
}

/**
 * Redirige les archives catégorie WP (parents + enfants) vers leur page
 * (sous-page éditoriale ou pilier racine) en 301.
 */
add_action( 'template_redirect', function () {
	if ( is_admin() || wp_doing_ajax() ) {
		return;
	}
	if ( ! is_category() ) {
		return;
	}
	$cat = get_queried_object();
	if ( ! $cat instanceof WP_Term ) {
		return;
	}
	if ( ! pcs_find_pilier_root( $cat->slug ) ) {
		return; // pas une catégorie pilier → on laisse passer
	}
	$page = pcs_category_target_page( $cat->slug );
	if ( ! $page instanceof WP_Post ) {
		return;
	}
	wp_safe_redirect( get_permalink( $page ), 301 );
	exit;
}, 1 ); // priorité haute pour passer AVANT les autres hooks template_redirect

/**
 * Redirige les URLs "flat" 404 qui ressemblent à des slugs de sous-catégorie
 * (ex: /travaux-par-piece-cat/) vers la page pilier correspondante.
 *
 * Ces URLs n'existent pas dans WP (les vraies sous-catégories sont nestées sous
 * leur parent) mais ont pu être indexées par Google ou existent dans la nature.
 */
add_action( 'template_redirect', function () {
	if ( is_admin() || wp_doing_ajax() ) {
		return;
	}
	if ( ! is_404() ) {
		return;
	}
	$path = trim( wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) ?? '', '/' );
	if ( '' === $path ) {
		return;
	}
	// On ne traite que les URLs 1-segment qui finissent par -cat
	$segments = explode( '/', $path );
	if ( count( $segments ) !== 1 ) {
		return;
	}
	$segment = sanitize_title( $segments[0] );
	if ( ! str_ends_with( $segment, '-cat' ) ) {
		return;
	}
	if ( ! pcs_find_pilier_root( $segment ) ) {
		return;
	}
	$page = pcs_category_target_page( $segment );
	if ( ! $page instanceof WP_Post ) {
		return;
	}
	wp_safe_redirect( get_permalink( $page ), 301 );
	exit;
}, 1 );

/**
 * Filet de sécurité : si pour une raison quelconque une archive catégorie est
 * rendue (pas redirigée), on force le `noindex, follow`. Hook sur le filtre
 * `pcs_robots_meta_index` exposé par inc/seo.php.
 */
add_filter( 'pcs_robots_meta_index', function ( $robots ) {
	if ( is_category() ) {
		$cat = get_queried_object();
		if ( $cat instanceof WP_Term && pcs_find_pilier_root( $cat->slug ) ) {
			return 'noindex, follow';
		}
	}
	return $robots;
} );

/**
 * Exclut les catégories piliers (`-cat`) du sitemap XML WP core (wp-sitemap.xml).
 * Sans ça, Google découvre les URLs via le sitemap puis se prend nos 301
 * → ce serait correct mais sale. Mieux : ne pas les déclarer du tout.
 *
 * Les pages piliers (`/decoration/`, etc.) sont auto-incluses dans le sitemap
 * via le type `page`, donc Google trouve les bonnes URLs.
 */
add_filter( 'wp_sitemaps_taxonomies_query_args', function ( $args, $taxonomy ) {
	if ( 'category' !== $taxonomy ) {
		return $args;
	}
	$exclude_ids = [];
	$cats = get_categories( [ 'hide_empty' => false ] );
	foreach ( $cats as $c ) {
		if ( pcs_find_pilier_root( $c->slug ) ) {
			$exclude_ids[] = (int) $c->term_id;
		}
	}
	if ( ! empty( $exclude_ids ) ) {
		$args['exclude'] = isset( $args['exclude'] ) && is_array( $args['exclude'] )
			? array_merge( $args['exclude'], $exclude_ids )
			: $exclude_ids;
	}
	return $args;
}, 10, 2 );
