<?php
/**
 * JSON-LD pour les pages de règle du Compatibilimètre.
 *
 * Type émis : `QAPage` + `Question` (avec `acceptedAnswer` ou `suggestedAnswer`
 * selon le verdict). Schema.org QAPage est le bon match pour une page question→réponse.
 *
 * Indexation Google : QAPage est éligible aux rich results "Q&A".
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'wp_head', function () {
	if ( ! is_singular( ARW_MAISON_RULE_CPT ) ) { return; }

	$post_id = get_the_ID();
	if ( ! $post_id ) { return; }

	$verdict      = (string) get_post_meta( $post_id, '_arw_rule_verdict', true );
	$explanation  = (string) get_post_meta( $post_id, '_arw_rule_explanation', true );
	$alternatives = (string) get_post_meta( $post_id, '_arw_rule_alternatives', true );

	if ( ! $verdict || ! $explanation ) { return; }

	$label = arw_maison_verdict_label( $verdict );

	// Build the answer text : verdict label + explanation + alternatives.
	$answer = $label . '. ' . $explanation;
	if ( $alternatives ) {
		$answer .= ' Alternatives : ' . $alternatives;
	}

	// Authoritative answer = compatible / forbidden (binary).
	// Suggested answer = conditional / discouraged (nuanced).
	$is_authoritative = in_array( $verdict, [ 'compatible', 'forbidden' ], true );

	$payload = [
		'@context'    => 'https://schema.org',
		'@type'       => 'QAPage',
		'mainEntity'  => [
			'@type'        => 'Question',
			'name'         => get_the_title( $post_id ),
			'text'         => get_the_title( $post_id ),
			'answerCount'  => 1,
			'dateCreated'  => get_the_date( 'c', $post_id ),
			'author'       => [
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
				'url'   => home_url( '/' ),
			],
			( $is_authoritative ? 'acceptedAnswer' : 'suggestedAnswer' ) => [
				'@type'        => 'Answer',
				'text'         => $answer,
				'dateCreated'  => get_the_date( 'c', $post_id ),
				'upvoteCount'  => 1,
				'url'          => get_permalink( $post_id ),
				'author'       => [
					'@type' => 'Organization',
					'name'  => get_bloginfo( 'name' ),
				],
			],
		],
	];

	echo "\n<script type=\"application/ld+json\">" . wp_json_encode( $payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . "</script>\n";
}, 25 );

// ─── Breadcrumbs integration with the theme ──────────────────────────────────

add_filter( 'pcs_breadcrumbs', function ( $crumbs ) {
	if ( ! is_singular( ARW_MAISON_RULE_CPT ) ) { return $crumbs; }

	$post_id = get_the_ID();
	$home    = home_url( '/' );

	$new = [
		[ 'name' => __( 'Accueil',          'arw-maison' ), 'url' => $home ],
		[ 'name' => __( 'Compatibilimètre', 'arw-maison' ), 'url' => home_url( '/compatibilimetre/' ) ],
	];

	// Catégorie principale.
	$cats = wp_get_post_terms( $post_id, ARW_MAISON_CATEGORY_TAX );
	if ( ! is_wp_error( $cats ) && ! empty( $cats ) ) {
		$cat = $cats[0];
		$new[] = [ 'name' => $cat->name, 'url' => get_term_link( $cat ) ];
	}

	// Page courante : self-URL (Schema.org BreadcrumbList exige une URL valide
	// si le champ "item" est émis, même pour le dernier crumb).
	$new[] = [ 'name' => get_the_title( $post_id ), 'url' => get_permalink( $post_id ) ];

	return $new;
} );
