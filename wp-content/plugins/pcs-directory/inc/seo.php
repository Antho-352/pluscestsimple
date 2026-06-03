<?php
/**
 * SEO — meta descriptions + schema.org LocalBusiness.
 *
 * Pas de Yoast/RankMath. On gère le minimum requis en natif.
 *
 * @package PCS_Directory
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// ─── JSON-LD LocalBusiness (single boutique) ───────────────────────────────────

add_action( 'wp_head', function (): void {
	if ( ! is_singular( PCS_DIR_CPT ) ) { return; }

	$pid = (int) get_queried_object_id();
	if ( ! $pid || 'publish' !== get_post_status( $pid ) ) { return; }

	$nom      = get_the_title( $pid );
	$adresse  = (string) get_post_meta( $pid, '_pcs_adresse', true );
	$cp       = (string) get_post_meta( $pid, '_pcs_code_postal', true );
	$ville    = '';
	// Extraire ville depuis taxonomie pcs_ville.
	$ville_terms = get_the_terms( $pid, 'pcs_ville' );
	if ( is_array( $ville_terms ) && $ville_terms ) {
		$ville = $ville_terms[0]->name;
	}
	$lat     = (float) get_post_meta( $pid, '_pcs_lat', true );
	$lng     = (float) get_post_meta( $pid, '_pcs_lng', true );
	$tel     = (string) get_post_meta( $pid, '_pcs_phone', true );
	$site    = (string) get_post_meta( $pid, '_pcs_website', true );
	$horaires = (string) get_post_meta( $pid, '_pcs_hours', true );

	$schema = [
		'@context' => 'https://schema.org',
		'@type'    => 'LocalBusiness',
		'name'     => $nom,
		'url'      => get_permalink( $pid ),
		'address'  => [
			'@type'           => 'PostalAddress',
			'streetAddress'   => $adresse,
			'postalCode'      => $cp,
			'addressLocality' => $ville,
			'addressCountry'  => 'FR',
		],
	];

	if ( $lat && $lng ) {
		$schema['geo'] = [
			'@type'     => 'GeoCoordinates',
			'latitude'  => $lat,
			'longitude' => $lng,
		];
	}
	if ( $tel ) { $schema['telephone'] = $tel; }
	if ( $site ) { $schema['sameAs'] = [ $site ]; }
	if ( $horaires ) { $schema['openingHours'] = $horaires; }

	if ( has_post_thumbnail( $pid ) ) {
		$img = wp_get_attachment_image_url( get_post_thumbnail_id( $pid ), 'large' );
		if ( $img ) { $schema['image'] = $img; }
	}

	echo "\n" . '<script type="application/ld+json">'
		. wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
		. "</script>\n";
}, 20 );

// ─── Meta description ─────────────────────────────────────────────────────────

add_action( 'wp_head', function (): void {
	$desc = '';

	// Fiche boutique.
	if ( is_singular( PCS_DIR_CPT ) ) {
		$pid   = (int) get_queried_object_id();
		$nom   = get_the_title( $pid );
		$types = get_the_terms( $pid, 'pcs_type' );
		$cats  = get_the_terms( $pid, 'pcs_cat' );
		$villes = get_the_terms( $pid, 'pcs_ville' );
		$type  = ( is_array( $types ) && $types ) ? $types[0]->name : '';
		$cat   = ( is_array( $cats ) && $cats ) ? $cats[0]->name : '';
		$ville = ( is_array( $villes ) && $villes ) ? $villes[0]->name : '';
		$parts = array_filter( [ $type, $cat ] );
		$desc  = $nom
			. ( $parts ? ' — ' . implode( ', ', $parts ) : '' )
			. ( $ville ? " à {$ville}" : '' )
			. '. Adresse, horaires, téléphone et site web.';

	// Département.
	} elseif ( is_tax( 'pcs_dept' ) ) {
		$term  = get_queried_object();
		$count = $term->count ?? 0;
		$name  = $term->name ?? '';
		$desc  = "Découvrez {$count} magasins de décoration et ameublement dans le département {$name}. Meubles, déco, luminaires, cuisines...";

	// Région.
	} elseif ( is_tax( 'pcs_region' ) ) {
		$term  = get_queried_object();
		$count = $term->count ?? 0;
		$name  = $term->name ?? '';
		$desc  = "Trouvez les meilleurs magasins de déco et maison en {$name}. {$count} boutiques référencées sur pluscestsimple.com.";

	// Ville.
	} elseif ( is_tax( 'pcs_ville' ) ) {
		$term  = get_queried_object();
		$count = $term->count ?? 0;
		$name  = $term->name ?? '';
		$desc  = "Magasins de décoration et ameublement à {$name} — {$count} boutiques référencées. Adresses, horaires, sites web.";

	// Catégorie.
	} elseif ( is_tax( 'pcs_cat' ) ) {
		$term  = get_queried_object();
		$count = $term->count ?? 0;
		$name  = $term->name ?? '';
		$desc  = "{$count} magasins {$name} en France. Comparez les enseignes, trouvez le magasin le plus proche de chez vous.";

	// Archive page mère.
	} elseif ( is_post_type_archive( PCS_DIR_CPT ) ) {
		$desc = 'Annuaire complet des magasins de décoration et ameublement en France. Meubles, déco, luminaires, cuisines — trouvez une boutique près de chez vous.';
	}

	$desc = wp_strip_all_tags( $desc );
	$desc = mb_substr( $desc, 0, 160 );

	if ( '' !== trim( $desc ) ) {
		echo "\n" . '<meta name="description" content="' . esc_attr( $desc ) . '" />' . "\n";
	}
}, 5 );

// ─── Titre document ──────────────────────────────────────────────────────────

add_filter( 'document_title_parts', function ( array $title ): array {
	if ( is_singular( PCS_DIR_CPT ) ) {
		$pid    = (int) get_queried_object_id();
		$nom    = get_the_title( $pid );
		$villes = get_the_terms( $pid, 'pcs_ville' );
		$ville  = ( is_array( $villes ) && $villes ) ? $villes[0]->name : '';
		$title['title']   = $nom . ( $ville ? " — {$ville}" : '' );
		$title['tagline'] = '';
	} elseif ( is_post_type_archive( PCS_DIR_CPT ) ) {
		$title['title']   = 'Annuaire magasins déco et maison en France';
		$title['tagline'] = '';
	}
	return $title;
} );
