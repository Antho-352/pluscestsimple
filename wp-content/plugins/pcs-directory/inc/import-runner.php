<?php
/**
 * Orchestrateur du pipeline d'import.
 *
 * Pour chaque résultat Sirene :
 *   1. Dédup par SIRET (skip si déjà en DB).
 *   2. Géocodage BAN si Sirene n'a pas de coordonnées exploitables.
 *   3. Enrichissement OSM (optionnel) → website / phone / opening_hours.
 *   4. wp_insert_post( post_status='draft' ) avec toutes les metas.
 *
 * Rate-limit : sleep(1) entre appels API, pause(5) entre batches de 50.
 * Tous les imports passent en draft → validation humaine OBLIGATOIRE.
 *
 * @package PCS_Directory
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Lance le pipeline d'import complet à partir de paramètres utilisateur.
 *
 * @param array{
 *   naf?: string,
 *   departement?: string,
 *   max?: int,
 *   enrich_osm?: bool,
 * } $args
 * @return array{imported:int, skipped:int, errors:array<int, string>, duration_s:float}
 */
function pcs_directory_run_import( array $args ): array {
	$started_at = microtime( true );
	$result     = [
		'imported'   => 0,
		'skipped'    => 0,
		'errors'     => [],
		'duration_s' => 0.0,
	];

	$naf         = isset( $args['naf'] ) ? sanitize_text_field( (string) $args['naf'] ) : PCS_DIR_DEFAULT_NAF;
	$departement = isset( $args['departement'] ) ? sanitize_text_field( (string) $args['departement'] ) : 'all';
	$max         = isset( $args['max'] ) ? max( 1, min( 500, (int) $args['max'] ) ) : 50;
	$enrich_osm  = ! empty( $args['enrich_osm'] );

	// Augmente le timeout PHP pour le batch (peut prendre quelques minutes).
	@set_time_limit( 600 );

	$per_page = 50;
	$page     = 1;
	$fetched  = 0;

	while ( $fetched < $max ) {
		$batch_size = min( $per_page, $max - $fetched );
		$sirene     = pcs_directory_import_sirene( [
			'naf'         => $naf,
			'departement' => $departement,
			'limite'      => $batch_size,
			'page'        => $page,
		] );

		if ( ! empty( $sirene['error'] ) ) {
			$result['errors'][] = $sirene['error'];
			break;
		}
		if ( empty( $sirene['etablissements'] ) ) {
			break; // plus de résultats.
		}

		foreach ( $sirene['etablissements'] as $record ) {
			$fetched++;

			$siret = (string) ( $record['siret'] ?? '' );
			if ( 14 !== strlen( $siret ) ) {
				$result['errors'][] = sprintf( 'SIRET invalide : %s', esc_html( $siret ) );
				$result['skipped']++;
				continue;
			}

			// Dédup.
			if ( pcs_directory_post_exists_by_siret( $siret ) ) {
				$result['skipped']++;
				continue;
			}

			// Géocodage si manquant.
			$lat = isset( $record['lat'] ) ? (float) $record['lat'] : 0.0;
			$lng = isset( $record['lng'] ) ? (float) $record['lng'] : 0.0;
			if ( 0.0 === $lat || 0.0 === $lng ) {
				$full_address = trim( sprintf(
					'%s %s %s',
					(string) ( $record['adresse'] ?? '' ),
					(string) ( $record['code_postal'] ?? '' ),
					(string) ( $record['ville'] ?? '' )
				) );
				if ( '' !== $full_address ) {
					sleep( 1 ); // rate-limit.
					$geo = pcs_directory_geocode_ban( $full_address );
					if ( null !== $geo ) {
						$lat = $geo['lat'];
						$lng = $geo['lng'];
					}
				}
			}

			$sources = [ 'sirene' ];
			if ( 0.0 !== $lat && 0.0 !== $lng ) {
				$sources[] = 'ban';
			}

			$osm = [
				'website'       => null,
				'phone'         => null,
				'opening_hours' => null,
			];
			if ( $enrich_osm && 0.0 !== $lat && 0.0 !== $lng ) {
				sleep( 1 ); // rate-limit Overpass.
				$osm = pcs_directory_enrich_osm( $lat, $lng, (string) $record['nom'] );
				if ( $osm['website'] || $osm['phone'] || $osm['opening_hours'] ) {
					$sources[] = 'osm';
				}
			}

			$post_id = pcs_directory_insert_etablissement( $record, $lat, $lng, $sources, $osm );
			if ( is_wp_error( $post_id ) || ! $post_id ) {
				$result['errors'][] = sprintf(
					'wp_insert_post a échoué pour SIRET %s : %s',
					esc_html( $siret ),
					is_wp_error( $post_id ) ? esc_html( $post_id->get_error_message() ) : 'unknown'
				);
				continue;
			}
			$result['imported']++;
		}

		$page++;
		// Pause inter-batch.
		if ( $fetched < $max ) {
			sleep( 5 );
		}
	}

	$result['duration_s'] = round( microtime( true ) - $started_at, 2 );

	pcs_directory_log_import_run( $args, $result );

	return $result;
}

/**
 * Vérifie qu'un établissement avec ce SIRET existe déjà (tous statuts confondus).
 *
 * @param string $siret SIRET 14 chiffres.
 * @return bool
 */
function pcs_directory_post_exists_by_siret( string $siret ): bool {
	$q = new WP_Query( [
		'post_type'              => PCS_DIR_CPT,
		'post_status'            => 'any',
		'posts_per_page'         => 1,
		'fields'                 => 'ids',
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
		'meta_query'             => [
			[
				'key'     => '_pcs_etab_siret',
				'value'   => $siret,
				'compare' => '=',
			],
		],
	] );
	return $q->have_posts();
}

/**
 * Insère un établissement en draft avec metas et taxonomies.
 *
 * @param array<string, mixed>       $record  Données Sirene normalisées.
 * @param float                      $lat     Latitude finale (Sirene ou BAN).
 * @param float                      $lng     Longitude finale.
 * @param array<int, string>         $sources Sources cumulées.
 * @param array{website:?string, phone:?string, opening_hours:?string} $osm Enrichissement OSM (peut être vide).
 * @return int|WP_Error ID du post créé, ou WP_Error en cas d'échec.
 */
function pcs_directory_insert_etablissement( array $record, float $lat, float $lng, array $sources, array $osm ) {
	$nom         = (string) ( $record['nom'] ?? '' );
	$siret       = (string) ( $record['siret'] ?? '' );
	$adresse     = (string) ( $record['adresse'] ?? '' );
	$code_postal = (string) ( $record['code_postal'] ?? '' );
	$ville       = (string) ( $record['ville'] ?? '' );
	$naf         = (string) ( $record['naf_code'] ?? '' );

	$region_slug = pcs_directory_region_slug_from_cp( $code_postal );

	// Excerpt minimal (le rédacteur le complétera en validation).
	$excerpt = trim( sprintf(
		/* translators: 1: nom, 2: ville. */
		__( '%1$s — magasin situé à %2$s.', 'pluscestsimple' ),
		$nom,
		$ville !== '' ? $ville : __( 'France', 'pluscestsimple' )
	) );

	$post_id = wp_insert_post( [
		'post_type'    => PCS_DIR_CPT,
		'post_status'  => 'draft', // JAMAIS publish auto.
		'post_title'   => $nom,
		'post_excerpt' => $excerpt,
		'post_content' => '',
		'meta_input'   => [
			'_pcs_etab_siret'         => $siret,
			'_pcs_etab_adresse'       => $adresse,
			'_pcs_etab_code_postal'   => $code_postal,
			'_pcs_etab_ville'         => $ville,
			'_pcs_etab_region'        => $region_slug ?? '',
			'_pcs_etab_lat'           => $lat,
			'_pcs_etab_lng'           => $lng,
			'_pcs_etab_telephone'     => (string) ( $osm['phone'] ?? '' ),
			'_pcs_etab_site_web'      => (string) ( $osm['website'] ?? '' ),
			'_pcs_etab_horaires_text' => (string) ( $osm['opening_hours'] ?? '' ),
			'_pcs_etab_is_featured'   => '0',
			'_pcs_etab_sources'       => array_values( array_unique( $sources ) ),
			'_pcs_etab_last_verified' => current_time( 'Y-m-d' ),
			'_pcs_etab_naf_code'      => $naf,
		],
	], true );

	if ( is_wp_error( $post_id ) ) {
		return $post_id;
	}

	// Taxonomies : région (si déterminable) + ville (créée si manquante).
	if ( $region_slug ) {
		$term = get_term_by( 'slug', $region_slug, PCS_DIR_TAX_REGION );
		if ( $term && ! is_wp_error( $term ) ) {
			wp_set_post_terms( $post_id, [ $term->term_id ], PCS_DIR_TAX_REGION, false );
		}
	}
	if ( '' !== $ville ) {
		wp_set_post_terms( $post_id, [ $ville ], PCS_DIR_TAX_VILLE, false );
	}
	// Type : on ne devine pas. C'est au validateur humain de classer (sauf si NAF
	// match exactement un type. v2 : mapping NAF→type configurable).

	return (int) $post_id;
}

/**
 * Journalise une exécution d'import dans l'option pcs_directory_import_log.
 *
 * Conserve les PCS_DIR_LOG_MAX_ENTRIES dernières entrées.
 *
 * @param array<string, mixed>                                                       $args   Paramètres d'entrée.
 * @param array{imported:int, skipped:int, errors:array<int, string>, duration_s:float} $result Résultat.
 * @return void
 */
function pcs_directory_log_import_run( array $args, array $result ): void {
	$log = get_option( PCS_DIR_LOG_OPTION, [] );
	if ( ! is_array( $log ) ) {
		$log = [];
	}
	array_unshift( $log, [
		'timestamp'   => current_time( 'mysql' ),
		'user'        => function_exists( 'wp_get_current_user' ) ? (string) wp_get_current_user()->user_login : 'system',
		'args'        => $args,
		'imported'    => (int) $result['imported'],
		'skipped'     => (int) $result['skipped'],
		'errors'      => array_slice( (array) $result['errors'], 0, 10 ),
		'duration_s'  => (float) $result['duration_s'],
	] );
	$log = array_slice( $log, 0, PCS_DIR_LOG_MAX_ENTRIES );
	update_option( PCS_DIR_LOG_OPTION, $log, false );
}
