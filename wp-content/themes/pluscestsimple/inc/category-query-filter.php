<?php
/**
 * Category Query Filter — injecte automatiquement le filtre catégorie dans
 * les blocs Query Loop des pages pilier (Décoration, Travaux, etc.).
 *
 * Contexte :
 * Les pages pilier sont des PAGES WordPress (pas des archives natives).
 * Le bloc Query Loop avec `inherit:true` n'a donc pas de contexte de
 * catégorie à hériter — il sort tous les articles sans filtre. Pour
 * éviter ce problème dans le pattern (où on ne connaît pas le term ID),
 * on intercepte côté serveur via le hook `query_loop_block_query_vars` :
 *
 *   - Si la page courante a un slug correspondant à un pilier connu
 *     (decoration, travaux, jardin, architecture)
 *   - Et que le bloc Query Loop a le namespace `pcs/cat-loop`
 *   → on injecte automatiquement `category_name = <slug>-cat`
 *
 * @package pluscestsimple
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter(
	'query_loop_block_query_vars',
	function ( array $query, $block ) {
		if ( ! is_singular( 'page' ) ) {
			return $query;
		}

		$attrs     = $block->parsed_block['attrs'] ?? [];
		$namespace = $attrs['namespace'] ?? '';

		// On filtre les boucles de notre namespace ET les boucles sans namespace
		// (pages seedées avant l'ajout du namespace → sinon jamais filtrées).
		// On respecte en revanche un namespace tiers explicite.
		if ( '' !== $namespace && 'pcs/cat-loop' !== $namespace ) {
			return $query;
		}

		// Un bloc en mode « hériter » ne doit pas être forcé.
		if ( ! empty( $attrs['query']['inherit'] ) ) {
			return $query;
		}

		// Si un filtre catégorie est DÉJÀ posé sur le bloc (réglage manuel dans
		// l'éditeur), on le respecte et on ne le surcharge pas.
		if ( ! empty( $query['tax_query'] ) || ! empty( $query['category__in'] ) || ! empty( $query['cat'] ) ) {
			return $query;
		}

		$page    = get_queried_object();
		if ( ! $page instanceof WP_Post ) {
			return $query;
		}

		if ( ! function_exists( 'pcs_content_structure' ) ) {
			return $query;
		}
		$structure = pcs_content_structure();

		// Catégorie cible :
		//   - page pilier racine (slug = pilier)        → <pilier>-cat (avec enfants)
		//   - sous-page (parent = pilier, slug = sous)  → <pilier>-<sous>-cat (sans enfants)
		$cat_slug         = '';
		$include_children = true;
		if ( isset( $structure[ $page->post_name ] ) ) {
			$cat_slug         = $page->post_name . '-cat';
			$include_children = true;
		} elseif ( $page->post_parent ) {
			$parent = get_post( $page->post_parent );
			if ( $parent instanceof WP_Post
				&& isset( $structure[ $parent->post_name ]['sub_cats'][ $page->post_name ] ) ) {
				$cat_slug         = $parent->post_name . '-' . $page->post_name . '-cat';
				$include_children = false;
			}
		}
		if ( '' === $cat_slug ) {
			return $query;
		}

		$term = get_term_by( 'slug', $cat_slug, 'category' );
		if ( ! $term instanceof WP_Term ) {
			return $query;
		}

		// Injecte le tax_query category (compatible WP_Query strict, ne casse pas perPage/order).
		$query['tax_query'] = [
			[
				'taxonomy'         => 'category',
				'field'            => 'term_id',
				'terms'            => [ (int) $term->term_id ],
				'include_children' => $include_children,
			],
		];

		return $query;
	},
	10,
	2
);
