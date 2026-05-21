<?php
/**
 * Crée automatiquement la page `/compatibilimetre/` au premier chargement
 * si elle n'existe pas. La page contient le shortcode [arw_compatibilimetre]
 * (UI de recherche + filtres + résultats).
 *
 * Idempotent : marque un flag dans options pour ne pas re-créer si la page
 * a été supprimée volontairement par l'admin.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

const ARW_MAISON_INDEX_PAGE_OPT  = 'arw_maison_index_page_id';
const ARW_MAISON_INDEX_PAGE_SLUG = 'compatibilimetre';

add_action( 'init', 'arw_maison_ensure_index_page', 30 );

function arw_maison_ensure_index_page(): void {
	// Skip during install/upgrade hooks where wpdb may be unavailable.
	if ( ! function_exists( 'get_page_by_path' ) ) { return; }

	$page_id = (int) get_option( ARW_MAISON_INDEX_PAGE_OPT, 0 );

	// If the recorded ID still points to a valid published page, nothing to do.
	if ( $page_id && get_post_status( $page_id ) === 'publish' ) {
		return;
	}

	// Check if a page with that slug already exists (avoid duplicates).
	$existing = get_page_by_path( ARW_MAISON_INDEX_PAGE_SLUG, OBJECT, 'page' );
	if ( $existing instanceof WP_Post && $existing->post_status === 'publish' ) {
		update_option( ARW_MAISON_INDEX_PAGE_OPT, (int) $existing->ID );
		return;
	}

	// Create the page.
	$new_id = wp_insert_post( [
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'post_title'     => __( 'Compatibilimètre', 'arw-maison' ),
		'post_name'      => ARW_MAISON_INDEX_PAGE_SLUG,
		'post_content'   => '<!-- wp:shortcode -->[arw_compatibilimetre]<!-- /wp:shortcode -->',
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
	], true );

	if ( ! is_wp_error( $new_id ) && $new_id ) {
		update_option( ARW_MAISON_INDEX_PAGE_OPT, (int) $new_id );
		// Flush rewrite rules so the new page slug is recognized immediately.
		flush_rewrite_rules( false );
	}
}

// ─── Force a "page" template (no theme front-page conflict) ──────────────────

// On the index page, suggest a wide template if available; otherwise the theme
// default page template will work fine since the shortcode controls layout.

// ─── Body class for styling hooks ────────────────────────────────────────────

add_filter( 'body_class', function ( $classes ) {
	if ( is_singular( ARW_MAISON_RULE_CPT ) ) {
		$classes[] = 'arw-compat-rule';
		$v = (string) get_post_meta( get_the_ID(), '_arw_rule_verdict', true );
		if ( $v ) { $classes[] = 'arw-verdict-' . $v; }
	}
	$page_id = (int) get_option( ARW_MAISON_INDEX_PAGE_OPT, 0 );
	if ( $page_id && is_page( $page_id ) ) {
		$classes[] = 'arw-compat-index';
	}
	return $classes;
} );
