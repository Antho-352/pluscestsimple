<?php
/**
 * Import national complet — asynchrone, résumable, partitionné par département.
 *
 * Pourquoi ce module :
 * L'API Recherche Entreprises impose deux murs durs :
 *   1. per_page ≤ 25
 *   2. page × per_page ≤ 10 000  → on ne peut JAMAIS accéder au-delà des
 *      10 000 premiers résultats d'une requête donnée.
 * Une requête nationale unique plafonne donc à 10 000 magasins, alors que la
 * France en compte bien plus. Solution : partitionner par département (101
 * partitions), chacune restant largement sous 10 000, et paginer chaque
 * département à fond. Dédup globale par SIRET.
 *
 * Comme l'ensemble représente des dizaines de milliers d'établissements (et
 * autant d'appels API espacés de 1 s), l'import ne peut pas tenir dans une
 * requête admin. On le découpe en "ticks" :
 *   - chaque tick traite une fenêtre de temps (~20 s) de pages
 *   - l'état est persisté en option après chaque tick → reprise automatique
 *   - le tick se replanifie lui-même (cron single-event) ET se propulse via
 *     un loopback admin-ajax non bloquant → tourne sans dépendre du trafic.
 *
 * @package PCS_Directory
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

const PCS_DIR_NAT_STATE_OPT  = 'pcs_directory_national_state';
const PCS_DIR_NAT_SECRET_OPT = 'pcs_directory_national_secret';
const PCS_DIR_NAT_TICK_HOOK  = 'pcs_directory_national_tick';
const PCS_DIR_NAT_TIME_BUDGET = 20;   // secondes max par tick.
const PCS_DIR_NAT_PER_PAGE    = 25;   // plafond API.

/**
 * Liste des 101 départements français (métropole + Corse + DOM).
 *
 * @return string[]
 */
function pcs_directory_national_departements(): array {
	$depts = [];
	for ( $i = 1; $i <= 95; $i++ ) {
		if ( 20 === $i ) { continue; } // 20 → 2A/2B.
		$depts[] = str_pad( (string) $i, 2, '0', STR_PAD_LEFT );
	}
	$depts[] = '2A';
	$depts[] = '2B';
	foreach ( [ '971', '972', '973', '974', '976' ] as $dom ) {
		$depts[] = $dom;
	}
	return $depts;
}

/**
 * État par défaut (import à l'arrêt).
 *
 * @return array<string, mixed>
 */
function pcs_directory_national_default_state(): array {
	return [
		'status'       => 'idle', // idle | running | paused | done
		'naf'          => PCS_DIR_DEFAULT_NAF,
		'enrich_osm'   => false,
		'queue'        => [],
		'current_dept' => '',
		'current_page' => 1,
		'dept_total'   => 0,
		'imported'     => 0,
		'skipped'      => 0,
		'errors'       => 0,
		'done_depts'   => 0,
		'total_depts'  => 0,
		'last_error'   => '',
		'started_at'   => 0,
		'last_tick'    => 0,
	];
}

/**
 * Récupère l'état courant (fusionné avec les défauts).
 *
 * @return array<string, mixed>
 */
function pcs_directory_national_get_state(): array {
	$saved = get_option( PCS_DIR_NAT_STATE_OPT, [] );
	if ( ! is_array( $saved ) ) { $saved = []; }
	return array_merge( pcs_directory_national_default_state(), $saved );
}

/**
 * Persiste l'état.
 *
 * @param array<string, mixed> $state État.
 * @return void
 */
function pcs_directory_national_save_state( array $state ): void {
	$state['last_tick'] = time();
	update_option( PCS_DIR_NAT_STATE_OPT, $state, false );
}

/**
 * Démarre un import national complet.
 *
 * @param string $naf        Codes NAF CSV.
 * @param bool   $enrich_osm Enrichissement OSM (déconseillé en bulk).
 * @return array<string, mixed> Nouvel état.
 */
function pcs_directory_national_start( string $naf, bool $enrich_osm = false ): array {
	$depts = pcs_directory_national_departements();
	$state = pcs_directory_national_default_state();
	$state['status']      = 'running';
	$state['naf']         = $naf ?: PCS_DIR_DEFAULT_NAF;
	$state['enrich_osm']  = $enrich_osm;
	$state['queue']       = $depts;
	$state['total_depts'] = count( $depts );
	$state['started_at']  = time();
	pcs_directory_national_save_state( $state );

	// Secret pour autoriser le loopback non authentifié.
	update_option( PCS_DIR_NAT_SECRET_OPT, wp_generate_password( 32, false, false ), false );

	pcs_directory_national_propel();
	return $state;
}

/**
 * Met en pause / arrête l'import (conserve l'état pour reprise).
 *
 * @param bool $reset Si true, réinitialise complètement l'état.
 * @return void
 */
function pcs_directory_national_stop( bool $reset = false ): void {
	wp_clear_scheduled_hook( PCS_DIR_NAT_TICK_HOOK );
	if ( $reset ) {
		update_option( PCS_DIR_NAT_STATE_OPT, pcs_directory_national_default_state(), false );
		return;
	}
	$state = pcs_directory_national_get_state();
	if ( 'running' === $state['status'] ) {
		$state['status'] = 'paused';
		pcs_directory_national_save_state( $state );
	}
}

/**
 * Reprend un import en pause.
 *
 * @return void
 */
function pcs_directory_national_resume(): void {
	$state = pcs_directory_national_get_state();
	if ( in_array( $state['status'], [ 'paused', 'running' ], true )
		&& ( ! empty( $state['queue'] ) || '' !== $state['current_dept'] ) ) {
		$state['status'] = 'running';
		pcs_directory_national_save_state( $state );
		pcs_directory_national_propel();
	}
}

/**
 * Propulse le prochain tick : cron single-event + loopback non bloquant.
 *
 * @return void
 */
function pcs_directory_national_propel(): void {
	if ( ! wp_next_scheduled( PCS_DIR_NAT_TICK_HOOK ) ) {
		wp_schedule_single_event( time() + 5, PCS_DIR_NAT_TICK_HOOK );
	}
	$secret = (string) get_option( PCS_DIR_NAT_SECRET_OPT, '' );
	if ( '' === $secret ) { return; }
	// Loopback non bloquant : déclenche le tick immédiatement sans attendre le cron.
	wp_remote_post(
		admin_url( 'admin-ajax.php' ),
		[
			'timeout'   => 0.01,
			'blocking'  => false,
			'sslverify' => false,
			'body'      => [
				'action' => 'pcs_national_tick',
				'key'    => $secret,
			],
		]
	);
}

/**
 * Cœur : exécute un tick d'import dans une fenêtre de temps bornée.
 *
 * @return void
 */
function pcs_directory_national_tick(): void {
	$state = pcs_directory_national_get_state();
	if ( 'running' !== $state['status'] ) {
		return;
	}

	// Verrou anti-réentrance : un seul tick à la fois.
	if ( get_transient( 'pcs_dir_nat_lock' ) ) {
		return;
	}
	set_transient( 'pcs_dir_nat_lock', 1, 120 );

	@set_time_limit( 120 );
	$start_ts = microtime( true );

	while ( ( microtime( true ) - $start_ts ) < PCS_DIR_NAT_TIME_BUDGET ) {

		// Sélectionne le département courant.
		if ( '' === $state['current_dept'] ) {
			if ( empty( $state['queue'] ) ) {
				$state['status'] = 'done';
				break;
			}
			$state['current_dept'] = (string) array_shift( $state['queue'] );
			$state['current_page'] = 1;
			$state['dept_total']   = pcs_directory_national_dept_total( $state['naf'], $state['current_dept'] );
		}

		// Garde-fou mur 10 000 (ne devrait pas arriver au grain département).
		if ( $state['current_page'] * PCS_DIR_NAT_PER_PAGE > 10000 ) {
			$state['current_dept'] = '';
			$state['done_depts']++;
			continue;
		}

		$sirene = pcs_directory_import_sirene( [
			'naf'         => $state['naf'],
			'departement' => $state['current_dept'],
			'limite'      => PCS_DIR_NAT_PER_PAGE,
			'page'        => $state['current_page'],
		] );

		if ( ! empty( $sirene['error'] ) ) {
			$state['errors']++;
			$state['last_error'] = (string) $sirene['error'];
			// On saute ce département pour ne pas bloquer tout l'import.
			$state['current_dept'] = '';
			$state['done_depts']++;
			continue;
		}

		$records = $sirene['etablissements'] ?? [];
		if ( empty( $records ) ) {
			// Département épuisé.
			$state['current_dept'] = '';
			$state['done_depts']++;
			continue;
		}

		foreach ( $records as $record ) {
			$outcome = pcs_directory_national_process_record( $record, (bool) $state['enrich_osm'] );
			if ( 'imported' === $outcome ) {
				$state['imported']++;
			} elseif ( 'error' === $outcome ) {
				$state['errors']++;
			} else {
				$state['skipped']++;
			}
		}

		// Page suivante ; si on a reçu moins qu'une page pleine → département fini.
		if ( count( $records ) < PCS_DIR_NAT_PER_PAGE ) {
			$state['current_dept'] = '';
			$state['done_depts']++;
		} else {
			$state['current_page']++;
		}

		// Rate-limit API entre pages.
		sleep( 1 );
	}

	pcs_directory_national_save_state( $state );
	delete_transient( 'pcs_dir_nat_lock' );

	// Suite ou fin ?
	if ( 'running' === $state['status'] && ( ! empty( $state['queue'] ) || '' !== $state['current_dept'] ) ) {
		pcs_directory_national_propel();
	} else {
		if ( 'done' === $state['status'] ) {
			pcs_directory_log_import_run(
				[ 'national' => $state['naf'], 'depts' => $state['total_depts'] ],
				[
					'imported'   => (int) $state['imported'],
					'skipped'    => (int) $state['skipped'],
					'errors'     => $state['errors'] > 0 ? [ sprintf( '%d erreurs/ignorés API', (int) $state['errors'] ) ] : [],
					'duration_s' => round( time() - (int) $state['started_at'], 1 ),
				]
			);
		}
		wp_clear_scheduled_hook( PCS_DIR_NAT_TICK_HOOK );
	}
}
add_action( PCS_DIR_NAT_TICK_HOOK, 'pcs_directory_national_tick' );

/**
 * Total d'établissements pour (naf, département) — sert à la barre de progression.
 *
 * @param string $naf  Codes NAF CSV.
 * @param string $dept Code département.
 * @return int
 */
function pcs_directory_national_dept_total( string $naf, string $dept ): int {
	$probe = pcs_directory_import_sirene( [
		'naf'         => $naf,
		'departement' => $dept,
		'limite'      => 1,
		'page'        => 1,
	] );
	return (int) ( $probe['total'] ?? 0 );
}

/**
 * Traite un établissement Sirene : dédup, géocodage, enrichissement, insert.
 * Réutilise les primitives du runner pour rester DRY.
 *
 * @param array<string, mixed> $record     Enregistrement normalisé.
 * @param bool                 $enrich_osm Enrichissement OSM.
 * @return string 'imported' | 'skipped' | 'error'
 */
function pcs_directory_national_process_record( array $record, bool $enrich_osm ): string {
	$siret = (string) ( $record['siret'] ?? '' );
	if ( 14 !== strlen( $siret ) ) {
		return 'error';
	}
	if ( pcs_directory_post_exists_by_siret( $siret ) ) {
		return 'skipped';
	}

	$lat = isset( $record['lat'] ) ? (float) $record['lat'] : 0.0;
	$lng = isset( $record['lng'] ) ? (float) $record['lng'] : 0.0;

	// Géocodage BAN seulement si l'API n'a pas fourni de coordonnées.
	if ( 0.0 === $lat || 0.0 === $lng ) {
		$full_address = trim( sprintf(
			'%s %s %s',
			(string) ( $record['adresse'] ?? '' ),
			(string) ( $record['code_postal'] ?? '' ),
			(string) ( $record['ville'] ?? '' )
		) );
		if ( '' !== $full_address ) {
			sleep( 1 );
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

	$osm = [ 'website' => null, 'phone' => null, 'opening_hours' => null ];
	if ( $enrich_osm && 0.0 !== $lat && 0.0 !== $lng ) {
		sleep( 1 );
		$osm = pcs_directory_enrich_osm( $lat, $lng, (string) $record['nom'] );
		if ( $osm['website'] || $osm['phone'] || $osm['opening_hours'] ) {
			$sources[] = 'osm';
		}
	}

	$post_id = pcs_directory_insert_etablissement( $record, $lat, $lng, $sources, $osm );
	if ( is_wp_error( $post_id ) || ! $post_id ) {
		return 'error';
	}
	return 'imported';
}

// ─── AJAX : loopback tick (non authentifié, protégé par secret) ─────────────

add_action( 'wp_ajax_nopriv_pcs_national_tick', 'pcs_directory_national_ajax_tick' );
add_action( 'wp_ajax_pcs_national_tick', 'pcs_directory_national_ajax_tick' );

function pcs_directory_national_ajax_tick(): void {
	$key    = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
	$secret = (string) get_option( PCS_DIR_NAT_SECRET_OPT, '' );
	if ( '' === $secret || ! hash_equals( $secret, $key ) ) {
		wp_die( '', '', [ 'response' => 403 ] );
	}
	ignore_user_abort( true );
	pcs_directory_national_tick();
	wp_die( 'ok' );
}

// ─── AJAX : statut JSON pour la barre de progression admin ──────────────────

add_action( 'wp_ajax_pcs_national_status', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( [], 403 );
	}
	check_ajax_referer( 'pcs_national_status', 'nonce' );
	$state = pcs_directory_national_get_state();
	$total_est = 0;
	// Estimation grossière de progression : départements traités / total.
	$pct = $state['total_depts'] > 0
		? min( 100, (int) round( ( $state['done_depts'] / $state['total_depts'] ) * 100 ) )
		: 0;
	wp_send_json_success( [
		'status'       => $state['status'],
		'imported'     => (int) $state['imported'],
		'skipped'      => (int) $state['skipped'],
		'errors'       => (int) $state['errors'],
		'current_dept' => (string) $state['current_dept'],
		'current_page' => (int) $state['current_page'],
		'dept_total'   => (int) $state['dept_total'],
		'done_depts'   => (int) $state['done_depts'],
		'total_depts'  => (int) $state['total_depts'],
		'pct'          => $pct,
		'last_error'   => (string) $state['last_error'],
		'elapsed_min'  => $state['started_at'] ? round( ( time() - (int) $state['started_at'] ) / 60, 1 ) : 0,
	] );
} );
