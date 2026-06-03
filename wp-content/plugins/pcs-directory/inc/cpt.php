<?php
/**
 * CPT pcs_boutique — magasin déco/maison.
 *
 * Archive : /annuaire/
 * Single  : /annuaire/boutique/{slug}
 *
 * Meta keys (préfixe _pcs_) :
 *   _pcs_siret, _pcs_siren, _pcs_adresse, _pcs_code_postal,
 *   _pcs_lat, _pcs_lng, _pcs_website, _pcs_phone, _pcs_hours,
 *   _pcs_naf_code, _pcs_is_enseigne, _pcs_public, _pcs_sources
 *
 * @package PCS_Directory
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// ─── Enregistrement CPT ───────────────────────────────────────────────────────

function pcs_directory_register_cpt(): void {
	$labels = [
		'name'               => 'Boutiques',
		'singular_name'      => 'Boutique',
		'menu_name'          => 'Annuaire',
		'add_new'            => 'Ajouter',
		'add_new_item'       => 'Ajouter une boutique',
		'edit_item'          => 'Modifier la boutique',
		'view_item'          => 'Voir la boutique',
		'all_items'          => 'Toutes les boutiques',
		'search_items'       => 'Rechercher',
		'not_found'          => 'Aucune boutique trouvée.',
		'not_found_in_trash' => 'Aucune boutique dans la corbeille.',
		'archives'           => 'Annuaire des magasins',
	];

	register_post_type(
		PCS_DIR_CPT,
		[
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => true,
			'show_in_rest'       => true,
			'menu_position'      => 26,
			'menu_icon'          => 'dashicons-store',
			'capability_type'    => 'post',
			'hierarchical'       => false,
			'supports'           => [ 'title', 'editor', 'thumbnail', 'custom-fields' ],
			'has_archive'        => 'annuaire',
			'rewrite'            => [
				'slug'       => 'annuaire/boutique',
				'with_front' => false,
				'pages'      => true,
			],
			'taxonomies'         => [ 'pcs_cat', 'pcs_type', 'pcs_mode', 'pcs_dept', 'pcs_region', 'pcs_ville' ],
		]
	);
}
add_action( 'init', 'pcs_directory_register_cpt', 9 );

// ─── Meta schema ──────────────────────────────────────────────────────────────

function pcs_directory_get_meta_schema(): array {
	return [
		'_pcs_siret'       => [ 'type' => 'string',  'label' => 'SIRET' ],
		'_pcs_siren'       => [ 'type' => 'string',  'label' => 'SIREN' ],
		'_pcs_adresse'     => [ 'type' => 'string',  'label' => 'Adresse' ],
		'_pcs_code_postal' => [ 'type' => 'string',  'label' => 'Code postal' ],
		'_pcs_lat'         => [ 'type' => 'number',  'label' => 'Latitude' ],
		'_pcs_lng'         => [ 'type' => 'number',  'label' => 'Longitude' ],
		'_pcs_website'     => [ 'type' => 'string',  'label' => 'Site web' ],
		'_pcs_phone'       => [ 'type' => 'string',  'label' => 'Téléphone' ],
		'_pcs_hours'       => [ 'type' => 'string',  'label' => 'Horaires (OSM)' ],
		'_pcs_naf_code'    => [ 'type' => 'string',  'label' => 'Code NAF' ],
		'_pcs_is_enseigne' => [ 'type' => 'boolean', 'label' => 'Grande enseigne' ],
		'_pcs_public'      => [ 'type' => 'boolean', 'label' => 'Public (visible)' ],
		'_pcs_sources'     => [ 'type' => 'string',  'label' => 'Sources (JSON)' ],
	];
}

// Enregistre les meta dans REST.
add_action( 'init', function () {
	foreach ( pcs_directory_get_meta_schema() as $key => $def ) {
		register_post_meta(
			PCS_DIR_CPT,
			$key,
			[
				'type'          => $def['type'],
				'single'        => true,
				'show_in_rest'  => true,
				'auth_callback' => fn() => current_user_can( 'edit_posts' ),
			]
		);
	}
}, 10 );

// ─── Meta box admin ───────────────────────────────────────────────────────────

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'pcs_boutique_data',
		'Données boutique',
		'pcs_directory_render_meta_box',
		PCS_DIR_CPT,
		'normal',
		'high'
	);
} );

function pcs_directory_render_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'pcs_boutique_save_meta', 'pcs_boutique_nonce' );
	$schema = pcs_directory_get_meta_schema();
	echo '<table class="form-table"><tbody>';
	foreach ( $schema as $key => $def ) {
		$value = get_post_meta( $post->ID, $key, true );
		$id    = esc_attr( $key );
		echo '<tr>';
		echo '<th scope="row"><label for="' . $id . '">' . esc_html( $def['label'] ) . '</label></th>';
		echo '<td>';
		if ( in_array( $key, [ '_pcs_is_enseigne', '_pcs_public' ], true ) ) {
			printf(
				'<label><input type="checkbox" id="%s" name="%s" value="1" %s /></label>',
				$id,
				$id,
				checked( $value, '1', false )
			);
		} elseif ( $key === '_pcs_hours' ) {
			printf(
				'<textarea id="%s" name="%s" rows="3" class="large-text">%s</textarea>',
				$id,
				$id,
				esc_textarea( (string) $value )
			);
		} elseif ( $key === '_pcs_website' ) {
			printf(
				'<input type="url" id="%s" name="%s" value="%s" class="regular-text" />',
				$id,
				$id,
				esc_attr( (string) $value )
			);
		} elseif ( in_array( $key, [ '_pcs_lat', '_pcs_lng' ], true ) ) {
			printf(
				'<input type="number" step="0.000001" id="%s" name="%s" value="%s" class="regular-text" />',
				$id,
				$id,
				esc_attr( (string) $value )
			);
		} else {
			printf(
				'<input type="text" id="%s" name="%s" value="%s" class="regular-text" />',
				$id,
				$id,
				esc_attr( (string) $value )
			);
		}
		echo '</td></tr>';
	}
	echo '</tbody></table>';
}

add_action( 'save_post_' . PCS_DIR_CPT, function ( int $post_id ): void {
	if ( ! isset( $_POST['pcs_boutique_nonce'] ) ) { return; }
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pcs_boutique_nonce'] ) ), 'pcs_boutique_save_meta' ) ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }

	foreach ( pcs_directory_get_meta_schema() as $key => $def ) {
		if ( in_array( $key, [ '_pcs_is_enseigne', '_pcs_public' ], true ) ) {
			update_post_meta( $post_id, $key, isset( $_POST[ $key ] ) ? '1' : '0' );
			continue;
		}
		if ( ! isset( $_POST[ $key ] ) ) { continue; }
		$raw = wp_unslash( $_POST[ $key ] );
		match ( $key ) {
			'_pcs_website'     => update_post_meta( $post_id, $key, esc_url_raw( (string) $raw ) ),
			'_pcs_hours'       => update_post_meta( $post_id, $key, sanitize_textarea_field( (string) $raw ) ),
			'_pcs_lat',
			'_pcs_lng'         => update_post_meta( $post_id, $key, (float) $raw ),
			default            => update_post_meta( $post_id, $key, sanitize_text_field( (string) $raw ) ),
		};
	}
} );

// ─── Colonnes admin ───────────────────────────────────────────────────────────

add_filter( 'manage_' . PCS_DIR_CPT . '_posts_columns', function ( array $cols ): array {
	$new = [];
	foreach ( $cols as $k => $v ) {
		$new[ $k ] = $v;
		if ( 'title' === $k ) {
			$new['pcs_siret']   = 'SIRET';
			$new['pcs_ville']   = 'Ville';
			$new['pcs_contact'] = 'Contact';
			$new['pcs_sources'] = 'Sources';
		}
	}
	return $new;
} );

add_action( 'manage_' . PCS_DIR_CPT . '_posts_custom_column', function ( string $col, int $post_id ): void {
	switch ( $col ) {
		case 'pcs_siret':
			echo esc_html( get_post_meta( $post_id, '_pcs_siret', true ) ?: '—' );
			break;
		case 'pcs_ville':
			echo esc_html( get_post_meta( $post_id, '_pcs_code_postal', true ) . ' ' . get_post_meta( $post_id, '_pcs_adresse', true ) );
			break;
		case 'pcs_contact':
			$site  = get_post_meta( $post_id, '_pcs_website', true ) ? '🌐' : '';
			$phone = get_post_meta( $post_id, '_pcs_phone', true ) ? '📞' : '';
			$hours = get_post_meta( $post_id, '_pcs_hours', true ) ? '🕐' : '';
			echo esc_html( $site . $phone . $hours ?: '—' );
			break;
		case 'pcs_sources':
			$src = get_post_meta( $post_id, '_pcs_sources', true );
			$arr = is_string( $src ) ? json_decode( $src, true ) : [];
			echo esc_html( is_array( $arr ) ? implode( ', ', $arr ) : '—' );
			break;
	}
}, 10, 2 );

// ─── Helper : markers JSON pour la carte ─────────────────────────────────────

/**
 * Retourne un JSON array des marqueurs carte pour une requête donnée.
 *
 * @param array $tax_query  tax_query WP_Query optionnelle (filtre par terme).
 * @param int   $limit      Nombre max de marqueurs (défaut 500).
 */
function pcs_directory_get_map_markers_json( array $tax_query = [], int $limit = 500 ): string {
	$args = [
		'post_type'      => PCS_DIR_CPT,
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'meta_query'     => [
			'relation' => 'AND',
			[ 'key' => '_pcs_public', 'value' => '1' ],
			[ 'key' => '_pcs_lat',    'compare' => 'EXISTS' ],
			[ 'key' => '_pcs_lng',    'compare' => 'EXISTS' ],
		],
	];
	if ( $tax_query ) {
		$args['tax_query'] = $tax_query;
	}

	$ids     = get_posts( $args );
	$markers = [];
	foreach ( $ids as $id ) {
		$lat = (float) get_post_meta( $id, '_pcs_lat', true );
		$lng = (float) get_post_meta( $id, '_pcs_lng', true );
		if ( ! $lat || ! $lng ) { continue; }
		$markers[] = [
			'id'    => $id,
			'lat'   => $lat,
			'lng'   => $lng,
			'nom'   => get_the_title( $id ),
			'url'   => get_permalink( $id ),
			'ville' => get_post_meta( $id, '_pcs_code_postal', true ) . ' ' . get_post_meta( $id, '_pcs_adresse', true ),
		];
	}
	return (string) wp_json_encode( $markers );
}
