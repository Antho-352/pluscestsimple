<?php
/**
 * Piliers retirés — resserrage de l'angle (2026-06).
 *
 * immobilier + lifestyle ont été retirés de pcs_content_structure(). Ce module
 * gère proprement l'existant (Opus : migrer AVANT, ne rien casser des positionnées) :
 *   - 301 des pages piliers retirées + leurs archives catégorie → cible
 *   - triage des articles :
 *       · renonce-t3-fissures-structurelles → travaux/gros-oeuvre (sujet structurel)
 *       · autres articles immobilier (finance) → noindex (conservés, désindexés, 0 clic)
 *       · articles lifestyle → recatégorisés sous decoration/rangement-organisation
 *
 * Idempotent (gated PCS_VERSION). On ne SUPPRIME aucun article (réversible).
 *
 * @package pluscestsimple
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// ─── Redirections 301 (priorité 0 : avant tous les autres template_redirect) ────

add_action( 'template_redirect', function () {
	if ( is_admin() || wp_doing_ajax() ) { return; }

	// Pages piliers retirées (et leurs sous-pages) + petits-budgets.
	if ( is_page() ) {
		$page = get_queried_object();
		if ( ! $page instanceof WP_Post ) { return; }
		$anc = $page;
		while ( $anc->post_parent ) {
			$parent = get_post( $anc->post_parent );
			if ( ! $parent instanceof WP_Post ) { break; }
			$anc = $parent;
		}
		$root = $anc->post_name;
		if ( 'immobilier' === $root ) { wp_safe_redirect( home_url( '/' ), 301 ); exit; }
		if ( 'lifestyle' === $root )  { wp_safe_redirect( home_url( '/decoration/' ), 301 ); exit; }
		// petits-budgets : la page directe OU toute sous-page éventuelle (via ancêtres).
		$pb = ( 'petits-budgets' === $page->post_name );
		if ( ! $pb ) {
			foreach ( get_post_ancestors( $page ) as $anc_id ) {
				if ( 'petits-budgets' === get_post_field( 'post_name', $anc_id ) ) { $pb = true; break; }
			}
		}
		if ( $pb ) { wp_safe_redirect( home_url( '/decoration/' ), 301 ); exit; }
	}

	// Archives catégories retirées.
	if ( is_category() ) {
		$cat = get_queried_object();
		if ( $cat instanceof WP_Term ) {
			if ( preg_match( '/^immobilier(-|$)/', $cat->slug ) ) { wp_safe_redirect( home_url( '/' ), 301 ); exit; }
			if ( preg_match( '/^lifestyle(-|$)/', $cat->slug ) )  { wp_safe_redirect( home_url( '/decoration/' ), 301 ); exit; }
			if ( preg_match( '/^decoration-petits-budgets(-|$)/', $cat->slug ) ) { wp_safe_redirect( home_url( '/decoration/' ), 301 ); exit; }
		}
	}
}, 0 );

// ─── Triage des articles (une seule fois) ──────────────────────────────────────

add_action( 'init', function () {
	if ( get_option( 'pcs_retired_migrated' ) === PCS_VERSION ) { return; }

	// 1. Fissures T3 (structurel) → travaux/gros-oeuvre.
	$fissure_id = 0;
	$fissure = get_posts( [ 'name' => 'renonce-t3-fissures-structurelles', 'post_type' => 'post', 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids' ] );
	$travaux_go = get_term_by( 'slug', 'travaux-gros-oeuvre-cat', 'category' );
	if ( $fissure && $travaux_go instanceof WP_Term ) {
		$fissure_id = (int) $fissure[0];
		wp_set_post_categories( $fissure_id, [ (int) $travaux_go->term_id ] );
	}

	// 2. Articles immobilier restants → noindex (conservés).
	$immo = get_term_by( 'slug', 'immobilier-cat', 'category' );
	if ( $immo instanceof WP_Term ) {
		$ids = get_posts( [ 'post_type' => 'post', 'posts_per_page' => -1, 'cat' => (int) $immo->term_id, 'fields' => 'ids', 'post_status' => 'any', 'suppress_filters' => true ] );
		foreach ( $ids as $pid ) {
			if ( (int) $pid === $fissure_id ) { continue; }
			update_post_meta( (int) $pid, '_pcs_noindex', '1' );
		}
	}

	// 3. Articles lifestyle → decoration/rangement-organisation.
	$life = get_term_by( 'slug', 'lifestyle-cat', 'category' );
	$rang = get_term_by( 'slug', 'decoration-rangement-organisation-cat', 'category' );
	if ( $life instanceof WP_Term && $rang instanceof WP_Term ) {
		$ids = get_posts( [ 'post_type' => 'post', 'posts_per_page' => -1, 'cat' => (int) $life->term_id, 'fields' => 'ids', 'post_status' => 'any', 'suppress_filters' => true ] );
		foreach ( $ids as $pid ) {
			wp_set_post_categories( (int) $pid, [ (int) $rang->term_id ] );
		}
	}

	update_option( 'pcs_retired_migrated', PCS_VERSION );
}, 100 ); // APRÈS init-content (prio 99) qui crée decoration-rangement-organisation-cat,
          // sinon la recatégorisation lifestyle→rangement échoue silencieusement (H1).
