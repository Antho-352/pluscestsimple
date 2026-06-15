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

		// Note Google (données tierces, affichées de façon transparente) → étoiles SERP.
		$rating  = (float) get_post_meta( $pid, '_pcs_rating', true );
		$reviews = (int) get_post_meta( $pid, '_pcs_reviews', true );
		if ( $rating > 0 && $reviews > 0 ) {
			$schema['aggregateRating'] = [
				'@type'       => 'AggregateRating',
				'ratingValue' => round( $rating, 1 ),
				'reviewCount' => $reviews,
				'bestRating'  => 5,
				'worstRating' => 1,
			];
		}

	if ( has_post_thumbnail( $pid ) ) {
		$img = wp_get_attachment_image_url( get_post_thumbnail_id( $pid ), 'large' );
		if ( $img ) { $schema['image'] = $img; }
	}

	echo "\n" . '<script type="application/ld+json">'
		. wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
		. "</script>\n";
}, 20 );

// ─── Meta description ─────────────────────────────────────────────────────────
// On ALIMENTE le filtre du thème (couche SEO unique) au lieu d'émettre notre
// propre balise : évite la double <meta name="description"> sur les pages annuaire.
// Bonus : og:description du thème hérite automatiquement de cette valeur.

add_filter( 'pcs_meta_description', function ( $desc_theme ) {
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

	// Sur une page annuaire → on remplace la description générique du thème.
	// Ailleurs → on laisse la valeur du thème intacte.
	return ( '' !== trim( $desc ) ) ? $desc : $desc_theme;
}, 10 );

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
	} elseif ( is_tax( [ 'pcs_ville', 'pcs_dept', 'pcs_region', 'pcs_cat' ] ) ) {
		// Titres descriptifs (mot-clé + zone + compteur) au lieu de « {Terme} – {Site} ».
		$term  = get_queried_object();
		$count = (int) ( $term->count ?? 0 );
		$n     = $count > 1 ? "{$count} boutiques" : "{$count} boutique";
		if ( is_tax( 'pcs_ville' ) ) {
			$title['title'] = "Magasins déco et maison à {$term->name} ({$n})";
		} elseif ( is_tax( 'pcs_dept' ) ) {
			$title['title'] = 'Magasins déco et maison ' . pcs_directory_dept_prep( $term->name ) . " ({$n})";
		} elseif ( is_tax( 'pcs_region' ) ) {
			$title['title'] = "Magasins déco et ameublement en {$term->name} ({$n})";
		} else { // pcs_cat
			$title['title'] = "Magasins {$term->name} en France ({$n})";
		}
		$title['tagline'] = '';
	}
	return $title;
} );
