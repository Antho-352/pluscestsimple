<?php
/**
 * Enrichissement via OpenStreetMap (Overpass API).
 *
 * - Pas d'authentification requise.
 * - POST overpass-api.de/api/interpreter
 * - Rate-limit Overpass : 2 requêtes "lourdes" simultanées max par IP.
 *   On évite avec sleep(1) côté runner et timeout court.
 * - Doc : https://wiki.openstreetmap.org/wiki/Overpass_API
 *
 * Attribution OBLIGATOIRE : © OpenStreetMap contributors, ODbL.
 * (rendue dans templates/single-pcs_etablissement.php)
 *
 * @package PCS_Directory
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Enrichit un établissement via une recherche Overpass autour de ses coordonnées.
 *
 * Cherche un node OSM tagué shop=* (ou amenity=marketplace) dont le name match
 * approximativement le nom de l'établissement, dans un rayon de 200m.
 *
 * @param float  $lat  Latitude (WGS84).
 * @param float  $lng  Longitude (WGS84).
 * @param string $name Nom de l'établissement (pour matcher OSM:name).
 * @return array{website:?string, phone:?string, opening_hours:?string}
 */
function pcs_directory_enrich_osm( float $lat, float $lng, string $name ): array {
	$default = [
		'website'       => null,
		'phone'         => null,
		'opening_hours' => null,
	];

	if ( 0.0 === $lat || 0.0 === $lng ) {
		return $default;
	}
	$name = trim( $name );
	if ( '' === $name ) {
		return $default;
	}

	// Échappe les caractères regex Overpass dans le nom.
	$escaped = pcs_directory_overpass_escape( $name );

	// Requête Overpass QL :
	//   - around:200,lat,lng → rayon 200m
	//   - shop=* ou amenity=marketplace : commerces
	//   - name~"...",i : match case-insensitive
	//   - out tags : on ne récupère que les tags (pas la géométrie)
	$ql = sprintf(
		'[out:json][timeout:15];(node["shop"]["name"~"%s",i](around:200,%s,%s);node["amenity"="marketplace"]["name"~"%s",i](around:200,%s,%s););out tags 5;',
		$escaped,
		number_format( $lat, 6, '.', '' ),
		number_format( $lng, 6, '.', '' ),
		$escaped,
		number_format( $lat, 6, '.', '' ),
		number_format( $lng, 6, '.', '' )
	);

	$response = wp_remote_post(
		'https://overpass-api.de/api/interpreter',
		[
			'timeout'    => 20,
			'user-agent' => 'pcs-directory/' . PCS_DIR_VERSION . ' (+https://pluscestsimple.com)',
			'headers'    => [
				'Content-Type' => 'application/x-www-form-urlencoded',
				'Accept'       => 'application/json',
			],
			'body'       => 'data=' . rawurlencode( $ql ),
		]
	);

	if ( is_wp_error( $response ) ) {
		return $default;
	}
	$code = (int) wp_remote_retrieve_response_code( $response );
	if ( $code < 200 || $code >= 300 ) {
		return $default;
	}
	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( ! is_array( $data ) || empty( $data['elements'] ) || ! is_array( $data['elements'] ) ) {
		return $default;
	}

	// Premier élément match retenu (Overpass renvoie déjà les plus proches d'abord
	// pour around:, mais l'ordre n'est pas strict — on prend le premier avec tags utiles).
	foreach ( $data['elements'] as $element ) {
		if ( ! is_array( $element ) || empty( $element['tags'] ) || ! is_array( $element['tags'] ) ) {
			continue;
		}
		$tags = $element['tags'];
		return [
			'website'       => isset( $tags['website'] )       ? (string) $tags['website']       : ( isset( $tags['contact:website'] ) ? (string) $tags['contact:website'] : null ),
			'phone'         => isset( $tags['phone'] )         ? (string) $tags['phone']         : ( isset( $tags['contact:phone'] )   ? (string) $tags['contact:phone']   : null ),
			'opening_hours' => isset( $tags['opening_hours'] ) ? (string) $tags['opening_hours'] : null,
		];
	}

	return $default;
}

/**
 * Échappe un nom pour l'inclure dans une regex Overpass QL.
 *
 * Overpass utilise des regex POSIX. On échappe les méta-caractères et on
 * remplace les espaces par \s+ pour tolérer la variation de typographie.
 *
 * @param string $name Nom brut.
 * @return string
 */
function pcs_directory_overpass_escape( string $name ): string {
	// Échappe les guillemets doubles + backslashes pour la chaîne Overpass.
	$name = str_replace( [ '\\', '"' ], [ '\\\\', '\\"' ], $name );
	// Échappe les méta-caractères regex courants.
	$name = preg_replace( '/([.+*?\[\](){}|^$])/u', '\\\\$1', $name ) ?? $name;
	// Espaces → \s+ (tolère typographies multiples).
	$name = preg_replace( '/\s+/u', '\\\\s+', $name ) ?? $name;
	return $name;
}
