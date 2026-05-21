<?php
/**
 * Import depuis l'API Recherche Entreprises (recherche-entreprises.api.gouv.fr).
 *
 * - Pas d'authentification requise.
 * - Rate limit raisonnable côté serveur (~7 req/s) : on respecte avec sleep(1) côté runner.
 * - Endpoint : GET /search?activite_principale=...&departement=...&page=...&per_page=...
 * - Doc : https://recherche-entreprises.api.gouv.fr/docs/
 *
 * @package PCS_Directory
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Cherche des établissements via l'API Recherche Entreprises.
 *
 * @param array{
 *   naf?: string,           // CSV : '47.59A,47.59B'
 *   departement?: string,   // '75' ou 'all'
 *   limite?: int,           // max résultats à retourner (cap = 250 / appel)
 *   page?: int,             // pagination
 * } $args
 * @return array{etablissements: array<int, array<string, mixed>>, total: int, error: ?string}
 */
function pcs_directory_import_sirene( array $args ): array {
	$naf         = isset( $args['naf'] ) ? sanitize_text_field( (string) $args['naf'] ) : PCS_DIR_DEFAULT_NAF;
	$departement = isset( $args['departement'] ) ? sanitize_text_field( (string) $args['departement'] ) : 'all';
	$limite      = isset( $args['limite'] ) ? max( 1, min( 250, (int) $args['limite'] ) ) : 50;
	$page        = isset( $args['page'] ) ? max( 1, (int) $args['page'] ) : 1;

	// L'API accepte un seul activite_principale par requête mais supporte plusieurs
	// codes séparés par virgule sur certains endpoints. On envoie tel quel : la valeur
	// CSV est documentée comme acceptable pour le paramètre activite_principale.
	$query = [
		'activite_principale' => $naf,
		'per_page'            => $limite,
		'page'                => $page,
		// Exclure les entreprises cessées (statut administratif "A" = Active).
		'etat_administratif'  => 'A',
	];
	if ( 'all' !== $departement && '' !== $departement ) {
		$query['departement'] = $departement;
	}

	$url = add_query_arg( $query, 'https://recherche-entreprises.api.gouv.fr/search' );

	$response = wp_remote_get(
		$url,
		[
			'timeout'    => 20,
			'user-agent' => 'pcs-directory/' . PCS_DIR_VERSION . ' (+https://pluscestsimple.com)',
			'headers'    => [
				'Accept' => 'application/json',
			],
		]
	);

	if ( is_wp_error( $response ) ) {
		return [
			'etablissements' => [],
			'total'          => 0,
			'error'          => 'sirene: ' . $response->get_error_message(),
		];
	}
	$code = (int) wp_remote_retrieve_response_code( $response );
	if ( $code < 200 || $code >= 300 ) {
		return [
			'etablissements' => [],
			'total'          => 0,
			'error'          => 'sirene: HTTP ' . $code,
		];
	}
	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body, true );
	if ( ! is_array( $data ) || ! isset( $data['results'] ) ) {
		return [
			'etablissements' => [],
			'total'          => 0,
			'error'          => 'sirene: réponse JSON invalide',
		];
	}

	$normalized = [];
	foreach ( (array) $data['results'] as $entreprise ) {
		$siege = $entreprise['siege'] ?? [];
		if ( ! is_array( $siege ) || empty( $siege['siret'] ) ) {
			continue;
		}
		$normalized[] = pcs_directory_normalize_sirene_record( $entreprise, $siege );

		// L'API renvoie aussi matching_etablissements (plusieurs établissements
		// par entreprise). On les ajoute si dispo.
		if ( ! empty( $entreprise['matching_etablissements'] ) && is_array( $entreprise['matching_etablissements'] ) ) {
			foreach ( $entreprise['matching_etablissements'] as $etab ) {
				if ( ! is_array( $etab ) || empty( $etab['siret'] ) ) {
					continue;
				}
				// Évite de re-pousser le siège.
				if ( ! empty( $siege['siret'] ) && $etab['siret'] === $siege['siret'] ) {
					continue;
				}
				$normalized[] = pcs_directory_normalize_sirene_record( $entreprise, $etab );
			}
		}
	}

	return [
		'etablissements' => $normalized,
		'total'          => (int) ( $data['total_results'] ?? count( $normalized ) ),
		'error'          => null,
	];
}

/**
 * Normalise un établissement Sirene en structure interne stable.
 *
 * @param array<string, mixed> $entreprise Bloc parent (raison sociale, NAF, etc.).
 * @param array<string, mixed> $etab       Bloc établissement (siret, adresse, geo).
 * @return array<string, mixed>
 */
function pcs_directory_normalize_sirene_record( array $entreprise, array $etab ): array {
	// Nom : nom_complet > denomination > nom + prenom.
	$nom = $entreprise['nom_complet']
		?? $entreprise['denomination']
		?? trim( ( $entreprise['prenom_1'] ?? '' ) . ' ' . ( $entreprise['nom'] ?? '' ) );
	$nom = is_string( $nom ) ? trim( $nom ) : '';
	if ( '' === $nom ) {
		$nom = $etab['enseigne_1'] ?? $etab['nom_commercial'] ?? 'Établissement ' . ( $etab['siret'] ?? '' );
	}

	// Adresse : on construit à partir de adresse complète si fournie, sinon composants.
	$adresse_parts = array_filter( [
		$etab['numero_voie']        ?? null,
		$etab['indice_repetition']  ?? null,
		$etab['type_voie']          ?? null,
		$etab['libelle_voie']       ?? null,
	] );
	$adresse = $etab['adresse'] ?? implode( ' ', array_map( 'strval', $adresse_parts ) );

	$cp    = (string) ( $etab['code_postal'] ?? '' );
	$ville = (string) ( $etab['libelle_commune'] ?? $etab['commune'] ?? '' );

	$lat = isset( $etab['latitude'] ) ? (float) $etab['latitude'] : null;
	$lng = isset( $etab['longitude'] ) ? (float) $etab['longitude'] : null;

	$naf = (string) ( $etab['activite_principale'] ?? $entreprise['activite_principale'] ?? '' );

	return [
		'siret'        => (string) $etab['siret'],
		'nom'          => (string) $nom,
		'enseigne'     => (string) ( $etab['enseigne_1'] ?? '' ),
		'adresse'      => trim( (string) $adresse ),
		'code_postal'  => $cp,
		'ville'        => $ville,
		'lat'          => $lat,
		'lng'          => $lng,
		'naf_code'     => $naf,
		'etat'         => (string) ( $etab['etat_administratif'] ?? 'A' ),
	];
}
