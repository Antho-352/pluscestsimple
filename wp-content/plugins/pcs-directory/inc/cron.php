<?php
/**
 * Cron quotidien : refresh des établissements publiés contre l'API Sirene.
 *
 * Stratégie :
 *   - Tâche pcs_directory_refresh planifiée daily.
 *   - À chaque tick : re-check les 20 établissements publiés les plus anciens
 *     (par date last_verified) — évite de saturer l'API en une fois.
 *   - Diff détecté (nom, adresse, code postal, statut) → repasse en draft.
 *   - Établissement cessé (etat_administratif != 'A') → trash.
 *
 * @package PCS_Directory
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Planifie la tâche cron si elle n'est pas déjà active.
 *
 * @return void
 */
function pcs_directory_schedule_cron(): void {
	if ( ! wp_next_scheduled( PCS_DIR_CRON_HOOK ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', PCS_DIR_CRON_HOOK );
	}
}

/**
 * Handler du cron : re-vérifie un batch de 20 établissements publiés.
 *
 * @return void
 */
function pcs_directory_run_refresh_cron(): void {
	$q = new WP_Query( [
		'post_type'              => PCS_DIR_CPT,
		'post_status'            => 'publish',
		'posts_per_page'         => 20,
		'orderby'                => 'meta_value',
		'meta_key'               => '_pcs_etab_last_verified', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'order'                  => 'ASC',
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
	] );

	if ( ! $q->have_posts() ) {
		return;
	}

	$processed = 0;
	$updated   = 0;
	$trashed   = 0;

	foreach ( $q->posts as $post ) {
		$processed++;
		$pid   = (int) $post->ID;
		$siret = (string) get_post_meta( $pid, '_pcs_etab_siret', true );
		if ( 14 !== strlen( $siret ) ) {
			continue;
		}

		// On interroge Sirene pour ce SIRET précis (filtre 'q' = SIRET fonctionne).
		$response = wp_remote_get(
			add_query_arg(
				[ 'q' => $siret, 'per_page' => 1 ],
				'https://recherche-entreprises.api.gouv.fr/search'
			),
			[
				'timeout'    => 15,
				'user-agent' => 'pcs-directory/' . PCS_DIR_VERSION . ' (+https://pluscestsimple.com)',
				'headers'    => [ 'Accept' => 'application/json' ],
			]
		);
		// Rate-limit serveur.
		sleep( 1 );

		if ( is_wp_error( $response ) ) {
			continue;
		}
		$code = (int) wp_remote_retrieve_response_code( $response );
		if ( $code < 200 || $code >= 300 ) {
			continue;
		}
		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		// SIRET introuvable → trash automatique.
		if ( ! is_array( $data ) || empty( $data['results'] ) ) {
			wp_trash_post( $pid );
			$trashed++;
			continue;
		}

		// On parcourt les results pour matcher exactement le SIRET.
		$matched = null;
		foreach ( (array) $data['results'] as $entreprise ) {
			$siege = $entreprise['siege'] ?? null;
			if ( is_array( $siege ) && ( $siege['siret'] ?? '' ) === $siret ) {
				$matched = pcs_directory_normalize_sirene_record( $entreprise, $siege );
				break;
			}
			if ( ! empty( $entreprise['matching_etablissements'] ) && is_array( $entreprise['matching_etablissements'] ) ) {
				foreach ( $entreprise['matching_etablissements'] as $etab ) {
					if ( is_array( $etab ) && ( $etab['siret'] ?? '' ) === $siret ) {
						$matched = pcs_directory_normalize_sirene_record( $entreprise, $etab );
						break 2;
					}
				}
			}
		}

		if ( null === $matched ) {
			wp_trash_post( $pid );
			$trashed++;
			continue;
		}

		// Cessé ?
		if ( isset( $matched['etat'] ) && 'A' !== $matched['etat'] ) {
			wp_trash_post( $pid );
			$trashed++;
			continue;
		}

		// Diff sur champs sensibles → repasse en draft.
		$current = [
			'nom'         => get_the_title( $pid ),
			'adresse'     => (string) get_post_meta( $pid, '_pcs_etab_adresse', true ),
			'code_postal' => (string) get_post_meta( $pid, '_pcs_etab_code_postal', true ),
			'ville'       => (string) get_post_meta( $pid, '_pcs_etab_ville', true ),
		];
		$drift = (
			pcs_directory_loose_diff( $current['nom'],         (string) $matched['nom'] )
			|| pcs_directory_loose_diff( $current['adresse'],  (string) $matched['adresse'] )
			|| trim( $current['code_postal'] ) !== trim( (string) $matched['code_postal'] )
			|| pcs_directory_loose_diff( $current['ville'],    (string) $matched['ville'] )
		);

		if ( $drift ) {
			wp_update_post( [
				'ID'          => $pid,
				'post_status' => 'draft',
				'post_title'  => (string) $matched['nom'],
			] );
			update_post_meta( $pid, '_pcs_etab_adresse',     (string) $matched['adresse'] );
			update_post_meta( $pid, '_pcs_etab_code_postal', (string) $matched['code_postal'] );
			update_post_meta( $pid, '_pcs_etab_ville',       (string) $matched['ville'] );
			$updated++;
		}

		// Dans tous les cas (même pas de drift), on met à jour last_verified.
		update_post_meta( $pid, '_pcs_etab_last_verified', current_time( 'Y-m-d' ) );
	}

	// Journalise la passe cron dans le même log que les imports manuels.
	pcs_directory_log_import_run(
		[ 'cron' => 'refresh' ],
		[
			'imported'   => $updated,
			'skipped'    => $processed - $updated - $trashed,
			'errors'     => $trashed > 0 ? [ sprintf( '%d établissements mis en corbeille (cessés ou introuvables)', $trashed ) ] : [],
			'duration_s' => 0.0,
		]
	);
}
add_action( PCS_DIR_CRON_HOOK, 'pcs_directory_run_refresh_cron' );

/**
 * Comparaison souple entre 2 strings : insensible à la casse, espaces et accents.
 *
 * Retourne true si les valeurs sont DIFFÉRENTES après normalisation.
 *
 * @param string $a Première valeur.
 * @param string $b Seconde valeur.
 * @return bool
 */
function pcs_directory_loose_diff( string $a, string $b ): bool {
	$normalize = static function ( string $s ): string {
		$s = remove_accents( $s );
		$s = strtolower( $s );
		$s = preg_replace( '/\s+/u', ' ', $s ) ?? $s;
		return trim( $s );
	};
	return $normalize( $a ) !== $normalize( $b );
}
