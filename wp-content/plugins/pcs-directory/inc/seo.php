<?php
/**
 * SEO & schema.org pour les fiches établissement.
 *
 * - Émet un bloc JSON-LD LocalBusiness dans le <head> des single.
 * - Customise le <title> et la meta description (fallback si aucun plugin SEO actif).
 *
 * Le plugin pluscestsimple/Yoast équivalents ne sont PAS supposés présents
 * (cf. CLAUDE.md, "no Yoast/RankMath"). On gère le minimum requis nous-mêmes.
 *
 * @package PCS_Directory
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Émet le JSON-LD LocalBusiness dans le <head> des fiches publish.
 *
 * @return void
 */
function pcs_directory_emit_schema_jsonld(): void {
	if ( ! is_singular( PCS_DIR_CPT ) ) {
		return;
	}
	$pid = (int) get_queried_object_id();
	if ( ! $pid || 'publish' !== get_post_status( $pid ) ) {
		return;
	}

	$schema = pcs_directory_build_local_business_schema( $pid );
	if ( empty( $schema ) ) {
		return;
	}

	echo "\n" . '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
}
add_action( 'wp_head', 'pcs_directory_emit_schema_jsonld', 20 );

/**
 * Construit l'objet schema.org/LocalBusiness pour un établissement.
 *
 * @param int $post_id ID de l'établissement.
 * @return array<string, mixed>
 */
function pcs_directory_build_local_business_schema( int $post_id ): array {
	$nom         = get_the_title( $post_id );
	$adresse     = (string) get_post_meta( $post_id, '_pcs_etab_adresse', true );
	$cp          = (string) get_post_meta( $post_id, '_pcs_etab_code_postal', true );
	$ville       = (string) get_post_meta( $post_id, '_pcs_etab_ville', true );
	$lat         = (float)  get_post_meta( $post_id, '_pcs_etab_lat', true );
	$lng         = (float)  get_post_meta( $post_id, '_pcs_etab_lng', true );
	$tel         = (string) get_post_meta( $post_id, '_pcs_etab_telephone', true );
	$site        = (string) get_post_meta( $post_id, '_pcs_etab_site_web', true );
	$horaires    = (string) get_post_meta( $post_id, '_pcs_etab_horaires_text', true );

	$schema = [
		'@context' => 'https://schema.org',
		'@type'    => 'LocalBusiness',
		'name'     => $nom,
		'url'      => get_permalink( $post_id ),
		'address'  => [
			'@type'           => 'PostalAddress',
			'streetAddress'   => $adresse,
			'postalCode'      => $cp,
			'addressLocality' => $ville,
			'addressCountry'  => 'FR',
		],
	];

	if ( 0.0 !== $lat && 0.0 !== $lng ) {
		$schema['geo'] = [
			'@type'     => 'GeoCoordinates',
			'latitude'  => $lat,
			'longitude' => $lng,
		];
	}
	if ( '' !== $tel ) {
		$schema['telephone'] = $tel;
	}
	if ( '' !== $site ) {
		$schema['sameAs'] = [ $site ];
	}
	if ( '' !== $horaires ) {
		// Format libre — schema.org accepte une string description. Pour un format
		// structuré "Mo-Fr 10:00-19:00", il faudrait parser le texte. v2.
		$schema['openingHours'] = $horaires;
	}
	if ( has_post_thumbnail( $post_id ) ) {
		$img = wp_get_attachment_image_url( get_post_thumbnail_id( $post_id ), 'large' );
		if ( $img ) {
			$schema['image'] = $img;
		}
	}

	return $schema;
}

/**
 * Customise le <title> des fiches établissement.
 *
 * Format : "<nom> — <type> à <ville> | Annuaire pluscestsimple"
 *
 * @param array<string, string> $title Tableau title-parts WordPress.
 * @return array<string, string>
 */
function pcs_directory_filter_title_parts( array $title ): array {
	if ( ! is_singular( PCS_DIR_CPT ) ) {
		return $title;
	}
	$pid    = (int) get_queried_object_id();
	$nom    = get_the_title( $pid );
	$ville  = (string) get_post_meta( $pid, '_pcs_etab_ville', true );
	$type   = '';
	$terms  = get_the_terms( $pid, PCS_DIR_TAX_TYPE );
	if ( is_array( $terms ) && ! empty( $terms ) ) {
		$type = (string) $terms[0]->name;
	}

	$parts = [ $nom ];
	if ( '' !== $type ) {
		$parts[] = $type;
	}
	if ( '' !== $ville ) {
		/* translators: %s: nom de la ville. */
		$parts[] = sprintf( __( 'à %s', 'pluscestsimple' ), $ville );
	}

	$title['title']   = implode( ' — ', $parts );
	$title['site']    = __( 'Annuaire pluscestsimple', 'pluscestsimple' );
	$title['tagline'] = '';
	return $title;
}
add_filter( 'document_title_parts', 'pcs_directory_filter_title_parts' );

/**
 * Émet une meta description : excerpt + ville + type.
 *
 * @return void
 */
function pcs_directory_emit_meta_description(): void {
	if ( ! is_singular( PCS_DIR_CPT ) ) {
		return;
	}
	$pid     = (int) get_queried_object_id();
	$excerpt = get_the_excerpt( $pid );
	$ville   = (string) get_post_meta( $pid, '_pcs_etab_ville', true );

	$type  = '';
	$terms = get_the_terms( $pid, PCS_DIR_TAX_TYPE );
	if ( is_array( $terms ) && ! empty( $terms ) ) {
		$type = (string) $terms[0]->name;
	}

	$parts = array_filter( [ $excerpt, $type, $ville ] );
	$desc  = implode( ' · ', $parts );
	$desc  = wp_strip_all_tags( $desc );
	$desc  = mb_substr( $desc, 0, 160 );

	if ( '' === trim( $desc ) ) {
		return;
	}

	echo "\n" . '<meta name="description" content="' . esc_attr( $desc ) . '" />' . "\n";
}
add_action( 'wp_head', 'pcs_directory_emit_meta_description', 5 );
