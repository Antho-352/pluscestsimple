<?php
/**
 * SEO : meta description + title overrides pour les pages de règle.
 *
 * Stratégie : la meta description = "Verdict : ..." + 130 premiers chars de l'explication.
 * Cela donne un snippet Google directement actionnable, sans cliquer.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

// ─── Meta description for rule pages ─────────────────────────────────────────

add_filter( 'arw_pulse_meta_description', function ( $desc ) {
	if ( ! is_singular( ARW_MAISON_RULE_CPT ) ) { return $desc; }

	$post_id     = get_the_ID();
	$verdict     = (string) get_post_meta( $post_id, '_arw_rule_verdict', true );
	$explanation = (string) get_post_meta( $post_id, '_arw_rule_explanation', true );

	if ( ! $verdict ) { return $desc; }

	$label = arw_maison_verdict_label( $verdict );

	$snippet = trim( wp_strip_all_tags( $explanation ) );
	if ( mb_strlen( $snippet ) > 130 ) {
		$snippet = mb_substr( $snippet, 0, 127 ) . '…';
	}

	return $label . ' — ' . $snippet;
} );

// ─── Title prefix : add the verdict in <title> ───────────────────────────────

add_filter( 'pre_get_document_title', function ( $title ) {
	if ( ! is_singular( ARW_MAISON_RULE_CPT ) ) { return $title; }

	$post_id = get_the_ID();
	$verdict = (string) get_post_meta( $post_id, '_arw_rule_verdict', true );
	if ( ! $verdict ) { return $title; }

	$rule_title = get_the_title( $post_id );
	$label      = arw_maison_verdict_label( $verdict );
	$site       = get_bloginfo( 'name' );

	// Format : "Parquet sur PC ? — Sous conditions | Plus c'est simple"
	return $rule_title . ' — ' . $label . ' | ' . $site;
}, 99 );

// ─── Taxonomies catégories compat : title + meta description optimisés ──────
// Avant : "Catégorie : Sols - Site" + meta vide (générique WP).
// Après : "Sols : règles & conseils Compatibilimètre — Site" + description ciblée.

add_filter( 'pre_get_document_title', function ( $title ) {
	if ( ! is_tax( ARW_MAISON_CATEGORY_TAX ) ) { return $title; }
	$term = get_queried_object();
	if ( ! $term || is_wp_error( $term ) ) { return $title; }
	return sprintf( '%s : règles & conseils Compatibilimètre | %s', $term->name, get_bloginfo( 'name' ) );
}, 99 );

add_filter( 'arw_pulse_meta_description', function ( $desc ) {
	if ( ! is_tax( ARW_MAISON_CATEGORY_TAX ) ) { return $desc; }
	$term = get_queried_object();
	if ( ! $term || is_wp_error( $term ) ) { return $desc; }
	if ( ! empty( $term->description ) ) {
		return wp_strip_all_tags( $term->description );
	}
	return sprintf(
		'Toutes les règles « %s » du Compatibilimètre : compatible, sous conditions, à éviter ou interdit. Décisions argumentées avant chantier.',
		$term->name
	);
} );
