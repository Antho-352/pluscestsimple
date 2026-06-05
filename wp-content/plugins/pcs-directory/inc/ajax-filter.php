<?php
/**
 * AJAX filter — endpoint pcs_filter.
 *
 * POST params : nonce, dept, cat, type, mode, page, per_page
 *
 * Retourne JSON :
 * {
 *   success: true,
 *   data: {
 *     posts: [{id, title, url, adresse, code_postal, ville, website, phone, lat, lng, categorie, type, mode, is_enseigne}],
 *     total: N,
 *     pages: N,
 *     markers: [{id, lat, lng, nom, url, ville}]
 *   }
 * }
 *
 * @package PCS_Directory
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'wp_ajax_pcs_filter',        'pcs_directory_ajax_filter' );
add_action( 'wp_ajax_nopriv_pcs_filter', 'pcs_directory_ajax_filter' );

function pcs_directory_ajax_filter(): void {
	// Nonce.
	$nonce = sanitize_text_field( (string) ( $_POST['nonce'] ?? '' ) );
	if ( ! wp_verify_nonce( $nonce, 'pcs_filter_nonce' ) ) {
		wp_send_json_error( [ 'message' => 'Nonce invalide.' ], 403 );
		return;
	}

	$dept     = sanitize_text_field( (string) ( $_POST['dept']     ?? '' ) );
	$cat      = sanitize_text_field( (string) ( $_POST['cat']      ?? '' ) );
	$type     = sanitize_text_field( (string) ( $_POST['type']     ?? '' ) );
	$mode     = sanitize_text_field( (string) ( $_POST['mode']     ?? '' ) );
	$page     = max( 1, (int) ( $_POST['page']     ?? 1 ) );
	$per_page = max( 1, min( 50, (int) ( $_POST['per_page'] ?? 20 ) ) );

	// ── Tax query ─────────────────────────────────────────────────────────────
	$tax_query = [ 'relation' => 'AND' ];

	if ( $dept ) {
		// $dept = slug complet du terme (ex : loiret-45).
		$tax_query[] = [
			'taxonomy' => 'pcs_dept',
			'field'    => 'slug',
			'terms'    => $dept,
		];
	}
	if ( $cat ) {
		$tax_query[] = [
			'taxonomy' => 'pcs_cat',
			'field'    => 'slug',
			'terms'    => $cat,
		];
	}
	if ( $type ) {
		$tax_query[] = [
			'taxonomy' => 'pcs_type',
			'field'    => 'slug',
			'terms'    => $type,
		];
	}
	if ( $mode ) {
		$tax_query[] = [
			'taxonomy' => 'pcs_mode',
			'field'    => 'slug',
			'terms'    => $mode,
		];
	}

	// ── Query principale ──────────────────────────────────────────────────────
	$args = [
		'post_type'      => PCS_DIR_CPT,
		'post_status'    => 'publish',
		'posts_per_page' => $per_page,
		'paged'          => $page,
		'meta_query'     => [
			[ 'key' => '_pcs_public', 'value' => '1' ],
		],
		'orderby'        => 'title',
		'order'          => 'ASC',
	];

	if ( count( $tax_query ) > 1 ) {
		$args['tax_query'] = $tax_query;
	}

	$query = new WP_Query( $args );
	$posts = [];

	foreach ( $query->posts as $post ) {
		$pid   = $post->ID;
		$cats  = get_the_terms( $pid, 'pcs_cat' );
		$types = get_the_terms( $pid, 'pcs_type' );
		$modes = get_the_terms( $pid, 'pcs_mode' );
		$vils  = get_the_terms( $pid, 'pcs_ville' );

		$posts[] = [
			'id'          => $pid,
			'title'       => get_the_title( $pid ),
			'url'         => get_permalink( $pid ),
			'adresse'     => get_post_meta( $pid, '_pcs_adresse', true ),
			'code_postal' => get_post_meta( $pid, '_pcs_code_postal', true ),
			'ville'       => ( is_array( $vils ) && $vils ) ? $vils[0]->name : '',
			'website'     => get_post_meta( $pid, '_pcs_website', true ),
			'phone'       => get_post_meta( $pid, '_pcs_phone', true ),
			'lat'         => (float) get_post_meta( $pid, '_pcs_lat', true ),
			'lng'         => (float) get_post_meta( $pid, '_pcs_lng', true ),
			'categorie'   => ( is_array( $cats ) && $cats ) ? $cats[0]->name : '',
			'type'        => ( is_array( $types ) && $types ) ? $types[0]->name : '',
			'mode'        => ( is_array( $modes ) && $modes ) ? $modes[0]->name : '',
			'is_enseigne' => (bool) get_post_meta( $pid, '_pcs_is_enseigne', true ),
			'rating'      => get_post_meta( $pid, '_pcs_rating', true ),
			'reviews'     => get_post_meta( $pid, '_pcs_reviews', true ),
		];
	}

	// ── Markers pour la carte ────────────────────────────────────────────────
	$marker_tax_query = count( $tax_query ) > 1 ? array_slice( $tax_query, 1 ) : [];
	$markers_json     = pcs_directory_get_map_markers_json( $marker_tax_query, 500 );
	$markers          = json_decode( $markers_json, true ) ?? [];

	wp_send_json_success( [
		'posts'   => $posts,
		'total'   => (int) $query->found_posts,
		'pages'   => (int) $query->max_num_pages,
		'markers' => $markers,
	] );
}
