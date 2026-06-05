<?php
/**
 * Import JSONL — lit un fichier master.jsonl produit par pcs-annuaire-data.
 *
 * Format entrée (une ligne JSON par boutique) :
 * {
 *   "siret", "siren", "nom", "enseigne", "slug",
 *   "adresse", "code_postal", "ville", "departement", "region",
 *   "lat", "lng", "website", "phone", "opening_hours",
 *   "categorie", "type", "mode", "naf_code",
 *   "is_enseigne" (bool), "public" (bool), "sources" (array)
 * }
 *
 * @package PCS_Directory
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Importe un fichier JSONL dans WordPress.
 *
 * @param string $filepath  Chemin absolu vers master.jsonl.
 * @param bool   $dry_run   Si true, ne crée/modifie rien, compte seulement.
 * @return array{created:int, updated:int, skipped:int, errors:string[]}
 */
function pcs_directory_import_jsonl( string $filepath, bool $dry_run = false ): array {
	$result = [ 'created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => [] ];

	if ( ! file_exists( $filepath ) || ! is_readable( $filepath ) ) {
		$result['errors'][] = "Fichier introuvable ou illisible : {$filepath}";
		return $result;
	}

	$fh = fopen( $filepath, 'r' );
	if ( ! $fh ) {
		$result['errors'][] = "Impossible d'ouvrir : {$filepath}";
		return $result;
	}

	$line_num = 0;

	while ( ( $line = fgets( $fh ) ) !== false ) {
		$line_num++;
		$line = trim( $line );
		if ( '' === $line ) { continue; }

		$data = json_decode( $line, true );
		if ( ! is_array( $data ) ) {
			$result['errors'][] = "Ligne {$line_num} : JSON invalide.";
			continue;
		}

		// Exclure les boutiques marquées non-publiques (designers/pros).
		if ( isset( $data['public'] ) && false === $data['public'] ) {
			$result['skipped']++;
			continue;
		}

		// SIRET obligatoire.
		$siret = sanitize_text_field( (string) ( $data['siret'] ?? '' ) );
		if ( '' === $siret ) {
			$result['errors'][] = "Ligne {$line_num} : SIRET manquant.";
			continue;
		}

		if ( $dry_run ) {
			// En dry-run, on simule sans écrire.
			$existing = pcs_directory_find_post_by_siret( $siret );
			if ( $existing ) {
				$result['updated']++;
			} else {
				$result['created']++;
			}
			continue;
		}

		// Prépare les données.
		$enseigne = sanitize_text_field( (string) ( $data['enseigne'] ?? '' ) );
		$nom      = sanitize_text_field( (string) ( $data['nom'] ?? "Établissement {$siret}" ) );
		$title    = '' !== $enseigne ? $enseigne : $nom;

		$slug     = sanitize_title( (string) ( $data['slug'] ?? $title ) );
		$ville    = sanitize_text_field( (string) ( $data['ville'] ?? '' ) );
		$dept     = sanitize_text_field( (string) ( $data['departement'] ?? '' ) );
		$region   = sanitize_text_field( (string) ( $data['region'] ?? pcs_directory_region_from_dept( $dept ) ) );

		// Cherche un post existant par SIRET.
		$existing_id = pcs_directory_find_post_by_siret( $siret );

		$post_data = [
			'post_type'   => PCS_DIR_CPT,
			'post_status' => 'publish',
			'post_title'  => $title,
			'post_name'   => $slug,
		];

		if ( $existing_id ) {
			$post_data['ID'] = $existing_id;
			$post_id = wp_update_post( $post_data, true );
			if ( is_wp_error( $post_id ) ) {
				$result['errors'][] = "Ligne {$line_num} SIRET {$siret} : " . $post_id->get_error_message();
				continue;
			}
			$result['updated']++;
		} else {
			$post_id = wp_insert_post( $post_data, true );
			if ( is_wp_error( $post_id ) ) {
				$result['errors'][] = "Ligne {$line_num} SIRET {$siret} : " . $post_id->get_error_message();
				continue;
			}
			$result['created']++;
		}

		// ── Meta ──────────────────────────────────────────────────────────────
		$sources_json = wp_json_encode( $data['sources'] ?? [] );
		$meta_map = [
			'_pcs_siret'       => $siret,
			'_pcs_siren'       => sanitize_text_field( (string) ( $data['siren'] ?? '' ) ),
			'_pcs_adresse'     => sanitize_text_field( (string) ( $data['adresse'] ?? '' ) ),
			'_pcs_code_postal' => sanitize_text_field( (string) ( $data['code_postal'] ?? '' ) ),
			'_pcs_lat'         => (float) ( $data['lat'] ?? 0 ),
			'_pcs_lng'         => (float) ( $data['lng'] ?? 0 ),
			'_pcs_website'     => esc_url_raw( (string) ( $data['website'] ?? '' ) ),
			'_pcs_phone'       => sanitize_text_field( (string) ( $data['phone'] ?? '' ) ),
			'_pcs_hours'       => sanitize_textarea_field( (string) ( $data['opening_hours'] ?? '' ) ),
			'_pcs_naf_code'    => sanitize_text_field( (string) ( $data['naf_code'] ?? '' ) ),
			'_pcs_is_enseigne' => ( $data['is_enseigne'] ?? false ) ? '1' : '0',
			'_pcs_public'      => ( $data['public'] ?? true ) ? '1' : '0',
			'_pcs_sources'     => $sources_json,
		];
		foreach ( $meta_map as $key => $val ) {
			update_post_meta( $post_id, $key, $val );
		}

		// ── Taxonomies ────────────────────────────────────────────────────────

		// Catégorie.
		$cat = sanitize_text_field( (string) ( $data['categorie'] ?? '' ) );
		if ( $cat ) {
			pcs_directory_set_term( $post_id, 'pcs_cat', $cat );
		}

		// Type.
		$type = sanitize_text_field( (string) ( $data['type'] ?? '' ) );
		if ( $type ) {
			pcs_directory_set_term( $post_id, 'pcs_type', $type );
		}

		// Mode de vente.
		$mode = sanitize_text_field( (string) ( $data['mode'] ?? 'En boutique' ) );
		pcs_directory_set_term( $post_id, 'pcs_mode', $mode );

		// Département : nom lisible + slug loiret-45 + meta code.
		if ( $dept ) {
			$dept_code = strtolower( $dept );
			$dept_name = pcs_directory_dept_name( $dept_code );
			$dept_slug = sanitize_title( $dept_name ) . '-' . $dept_code;
			pcs_directory_set_term( $post_id, 'pcs_dept', $dept_name, $dept_slug );
			$dterm = get_term_by( 'slug', $dept_slug, 'pcs_dept' );
			if ( $dterm ) {
				update_term_meta( $dterm->term_id, '_pcs_dept_code', $dept_code );
			}
		}

		// Région.
		if ( $region ) {
			pcs_directory_set_term( $post_id, 'pcs_region', $region );
		}

		// Ville.
		if ( $ville ) {
			pcs_directory_set_term( $post_id, 'pcs_ville', ucwords( strtolower( $ville ) ) );
		}
	}

	fclose( $fh );
	return $result;
}

/**
 * Trouve un post pcs_boutique par SIRET. Retourne l'ID ou null.
 */
function pcs_directory_find_post_by_siret( string $siret ): ?int {
	$ids = get_posts( [
		'post_type'      => PCS_DIR_CPT,
		'post_status'    => 'any',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'meta_query'     => [
			[ 'key' => '_pcs_siret', 'value' => $siret ],
		],
	] );
	return ! empty( $ids ) ? (int) $ids[0] : null;
}

/**
 * Assigne un terme à un post, en créant le terme s'il n'existe pas.
 *
 * @param int    $post_id
 * @param string $taxonomy
 * @param string $term_name   Nom du terme.
 * @param string $term_slug   Slug optionnel (si différent du sanitize_title du nom).
 */
function pcs_directory_set_term( int $post_id, string $taxonomy, string $term_name, string $term_slug = '' ): void {
	if ( '' === $term_name ) { return; }

	$slug = '' !== $term_slug ? $term_slug : sanitize_title( $term_name );
	$term = get_term_by( 'slug', $slug, $taxonomy );

	if ( ! $term ) {
		$inserted = wp_insert_term( $term_name, $taxonomy, [ 'slug' => $slug ] );
		if ( is_wp_error( $inserted ) ) { return; }
		$term_id = (int) $inserted['term_id'];
	} else {
		$term_id = (int) $term->term_id;
	}

	wp_set_object_terms( $post_id, $term_id, $taxonomy, false );
}
