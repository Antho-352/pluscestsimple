<?php
/**
 * Géocodage via la Base Adresse Nationale (BAN).
 *
 * - Pas d'authentification requise.
 * - Endpoint : GET https://api-adresse.data.gouv.fr/search/?q=...&limit=1
 * - Rate-limit : 50 req/s/IP. On reste très en dessous.
 * - Doc : https://adresse.data.gouv.fr/api-doc/adresse
 *
 * @package PCS_Directory
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Géocode une adresse via l'API BAN.
 *
 * @param string $address Adresse en clair (peut inclure CP + ville).
 * @return array{lat:float, lng:float, address_formatted:string, score:float, city:string, postcode:string}|null
 *         Null si l'API n'a renvoyé aucun résultat exploitable.
 */
function pcs_directory_geocode_ban( string $address ): ?array {
	$q = trim( $address );
	if ( '' === $q ) {
		return null;
	}

	$url = add_query_arg(
		[
			'q'     => $q,
			'limit' => 1,
		],
		'https://api-adresse.data.gouv.fr/search/'
	);

	$response = wp_remote_get(
		$url,
		[
			'timeout'    => 15,
			'user-agent' => 'pcs-directory/' . PCS_DIR_VERSION . ' (+https://pluscestsimple.com)',
			'headers'    => [ 'Accept' => 'application/json' ],
		]
	);

	if ( is_wp_error( $response ) ) {
		return null;
	}
	$code = (int) wp_remote_retrieve_response_code( $response );
	if ( $code < 200 || $code >= 300 ) {
		return null;
	}
	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( ! is_array( $data ) || empty( $data['features'] ) || ! is_array( $data['features'] ) ) {
		return null;
	}

	$f = $data['features'][0];
	if ( ! is_array( $f ) || empty( $f['geometry']['coordinates'] ) ) {
		return null;
	}

	// GeoJSON : [lng, lat].
	$coords = $f['geometry']['coordinates'];
	$lng    = (float) ( $coords[0] ?? 0 );
	$lat    = (float) ( $coords[1] ?? 0 );

	$props = is_array( $f['properties'] ?? null ) ? $f['properties'] : [];

	return [
		'lat'               => $lat,
		'lng'               => $lng,
		'address_formatted' => (string) ( $props['label'] ?? '' ),
		'score'             => (float) ( $props['score'] ?? 0 ),
		'city'              => (string) ( $props['city'] ?? '' ),
		'postcode'          => (string) ( $props['postcode'] ?? '' ),
	];
}
