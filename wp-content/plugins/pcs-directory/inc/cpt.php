<?php
/**
 * CPT pcs_etablissement : un magasin déco/maison.
 *
 * Slug archive : /annuaire/
 * Slug single  : /annuaire/etablissement/%name%
 *
 * Public, show_in_rest. Supports title/editor/thumbnail/excerpt/custom-fields.
 * 14 meta boxes pour les champs structurés (siret, adresse, geo, contact, etc.).
 *
 * @package PCS_Directory
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Enregistre le CPT pcs_etablissement.
 *
 * @return void
 */
function pcs_directory_register_cpt(): void {
	$labels = [
		'name'                  => __( 'Établissements', 'pluscestsimple' ),
		'singular_name'         => __( 'Établissement', 'pluscestsimple' ),
		'menu_name'             => __( 'Annuaire', 'pluscestsimple' ),
		'name_admin_bar'        => __( 'Établissement', 'pluscestsimple' ),
		'add_new'               => __( 'Ajouter', 'pluscestsimple' ),
		'add_new_item'          => __( 'Ajouter un établissement', 'pluscestsimple' ),
		'new_item'              => __( 'Nouvel établissement', 'pluscestsimple' ),
		'edit_item'             => __( 'Modifier l\'établissement', 'pluscestsimple' ),
		'view_item'             => __( 'Voir l\'établissement', 'pluscestsimple' ),
		'all_items'             => __( 'Tous les établissements', 'pluscestsimple' ),
		'search_items'          => __( 'Rechercher un établissement', 'pluscestsimple' ),
		'not_found'             => __( 'Aucun établissement trouvé.', 'pluscestsimple' ),
		'not_found_in_trash'    => __( 'Aucun établissement dans la corbeille.', 'pluscestsimple' ),
		'archives'              => __( 'Annuaire des magasins', 'pluscestsimple' ),
		'attributes'            => __( 'Attributs de l\'établissement', 'pluscestsimple' ),
		'featured_image'        => __( 'Photo de la devanture', 'pluscestsimple' ),
		'set_featured_image'    => __( 'Définir la photo', 'pluscestsimple' ),
	];

	register_post_type(
		PCS_DIR_CPT,
		[
			'labels'              => $labels,
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_rest'        => true,
			'menu_position'       => 26,
			'menu_icon'           => 'dashicons-store',
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ],
			'has_archive'         => 'annuaire',
			'rewrite'             => [
				'slug'       => 'annuaire/etablissement',
				'with_front' => false,
				'feeds'      => false,
				'pages'      => true,
			],
			'taxonomies'          => [ PCS_DIR_TAX_TYPE, PCS_DIR_TAX_REGION, PCS_DIR_TAX_VILLE ],
		]
	);
}
add_action( 'init', 'pcs_directory_register_cpt', 9 );

/**
 * Liste des meta keys exposées par le CPT, avec leur type et description.
 *
 * Utilisé par : meta boxes admin, REST API, validation à l'import.
 *
 * @return array<string, array{type:string, label:string, single:bool}>
 */
function pcs_directory_get_meta_schema(): array {
	return [
		'_pcs_etab_siret'         => [ 'type' => 'string',  'label' => __( 'SIRET (14 chiffres)', 'pluscestsimple' ),       'single' => true ],
		'_pcs_etab_adresse'       => [ 'type' => 'string',  'label' => __( 'Adresse', 'pluscestsimple' ),                   'single' => true ],
		'_pcs_etab_code_postal'   => [ 'type' => 'string',  'label' => __( 'Code postal', 'pluscestsimple' ),               'single' => true ],
		'_pcs_etab_ville'         => [ 'type' => 'string',  'label' => __( 'Ville', 'pluscestsimple' ),                     'single' => true ],
		'_pcs_etab_region'        => [ 'type' => 'string',  'label' => __( 'Région', 'pluscestsimple' ),                    'single' => true ],
		'_pcs_etab_lat'           => [ 'type' => 'number',  'label' => __( 'Latitude', 'pluscestsimple' ),                  'single' => true ],
		'_pcs_etab_lng'           => [ 'type' => 'number',  'label' => __( 'Longitude', 'pluscestsimple' ),                 'single' => true ],
		'_pcs_etab_telephone'     => [ 'type' => 'string',  'label' => __( 'Téléphone', 'pluscestsimple' ),                 'single' => true ],
		'_pcs_etab_site_web'      => [ 'type' => 'string',  'label' => __( 'Site web (URL)', 'pluscestsimple' ),            'single' => true ],
		'_pcs_etab_horaires_text' => [ 'type' => 'string',  'label' => __( 'Horaires (texte libre)', 'pluscestsimple' ),    'single' => true ],
		'_pcs_etab_is_featured'   => [ 'type' => 'boolean', 'label' => __( 'Mise en avant', 'pluscestsimple' ),             'single' => true ],
		'_pcs_etab_sources'       => [ 'type' => 'array',   'label' => __( 'Sources de données', 'pluscestsimple' ),        'single' => true ],
		'_pcs_etab_last_verified' => [ 'type' => 'string',  'label' => __( 'Dernière vérification (Y-m-d)', 'pluscestsimple' ), 'single' => true ],
		'_pcs_etab_naf_code'      => [ 'type' => 'string',  'label' => __( 'Code NAF source', 'pluscestsimple' ),           'single' => true ],
	];
}

/**
 * Enregistre les meta dans REST pour permettre lecture/écriture via l'API.
 *
 * @return void
 */
function pcs_directory_register_meta(): void {
	foreach ( pcs_directory_get_meta_schema() as $key => $def ) {
		register_post_meta(
			PCS_DIR_CPT,
			$key,
			[
				'type'         => 'array' === $def['type'] ? 'array' : $def['type'],
				'single'       => true,
				'show_in_rest' => 'array' === $def['type']
					? [ 'schema' => [ 'type' => 'array', 'items' => [ 'type' => 'string' ] ] ]
					: true,
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			]
		);
	}
}
add_action( 'init', 'pcs_directory_register_meta', 10 );

/**
 * Ajoute la meta box "Données établissement" sur l'écran d'édition.
 *
 * @return void
 */
function pcs_directory_add_meta_box(): void {
	add_meta_box(
		'pcs_etab_data',
		__( 'Données établissement', 'pluscestsimple' ),
		'pcs_directory_render_meta_box',
		PCS_DIR_CPT,
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'pcs_directory_add_meta_box' );

/**
 * Rendu de la meta box d'édition (14 champs structurés).
 *
 * @param WP_Post $post Post courant.
 * @return void
 */
function pcs_directory_render_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'pcs_directory_save_meta', 'pcs_directory_meta_nonce' );
	$schema = pcs_directory_get_meta_schema();
	echo '<table class="form-table"><tbody>';
	foreach ( $schema as $key => $def ) {
		$value = get_post_meta( $post->ID, $key, true );
		$id    = esc_attr( $key );
		echo '<tr>';
		echo '<th scope="row"><label for="' . $id . '">' . esc_html( $def['label'] ) . '</label></th>';
		echo '<td>';
		switch ( $key ) {
			case '_pcs_etab_horaires_text':
				printf(
					'<textarea id="%s" name="%s" rows="4" class="large-text">%s</textarea>',
					$id,
					$id,
					esc_textarea( (string) $value )
				);
				break;
			case '_pcs_etab_is_featured':
				printf(
					'<label><input type="checkbox" id="%s" name="%s" value="1" %s /> %s</label>',
					$id,
					$id,
					checked( $value, '1', false ),
					esc_html__( 'Afficher en tête de liste avec badge "Vérifié"', 'pluscestsimple' )
				);
				break;
			case '_pcs_etab_sources':
				$arr = is_array( $value ) ? $value : [];
				printf(
					'<input type="text" id="%s" name="%s" value="%s" class="regular-text" />',
					$id,
					$id,
					esc_attr( implode( ',', $arr ) )
				);
				echo '<p class="description">' . esc_html__( 'Séparées par virgules : sirene, ban, osm.', 'pluscestsimple' ) . '</p>';
				break;
			case '_pcs_etab_site_web':
				printf(
					'<input type="url" id="%s" name="%s" value="%s" class="regular-text" />',
					$id,
					$id,
					esc_attr( (string) $value )
				);
				break;
			case '_pcs_etab_lat':
			case '_pcs_etab_lng':
				printf(
					'<input type="number" step="0.000001" id="%s" name="%s" value="%s" class="regular-text" />',
					$id,
					$id,
					esc_attr( (string) $value )
				);
				break;
			default:
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

/**
 * Sauvegarde des meta à l'enregistrement du post.
 *
 * @param int $post_id ID du post sauvegardé.
 * @return void
 */
function pcs_directory_save_meta( int $post_id ): void {
	// Nonce.
	if ( ! isset( $_POST['pcs_directory_meta_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pcs_directory_meta_nonce'] ) ), 'pcs_directory_save_meta' ) ) {
		return;
	}
	// Permissions.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	// Autosave.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	// Type.
	if ( get_post_type( $post_id ) !== PCS_DIR_CPT ) {
		return;
	}

	$schema = pcs_directory_get_meta_schema();
	foreach ( $schema as $key => $def ) {
		if ( '_pcs_etab_is_featured' === $key ) {
			$val = isset( $_POST[ $key ] ) ? '1' : '0';
			update_post_meta( $post_id, $key, $val );
			continue;
		}
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}
		$raw = wp_unslash( $_POST[ $key ] );
		switch ( $key ) {
			case '_pcs_etab_sources':
				$parts = array_filter( array_map( 'trim', explode( ',', (string) $raw ) ) );
				update_post_meta( $post_id, $key, array_values( $parts ) );
				break;
			case '_pcs_etab_horaires_text':
				update_post_meta( $post_id, $key, sanitize_textarea_field( (string) $raw ) );
				break;
			case '_pcs_etab_site_web':
				update_post_meta( $post_id, $key, esc_url_raw( (string) $raw ) );
				break;
			case '_pcs_etab_lat':
			case '_pcs_etab_lng':
				update_post_meta( $post_id, $key, (float) $raw );
				break;
			default:
				update_post_meta( $post_id, $key, sanitize_text_field( (string) $raw ) );
		}
	}
}
add_action( 'save_post_' . PCS_DIR_CPT, 'pcs_directory_save_meta' );

/**
 * Colonnes admin custom dans la liste des établissements.
 *
 * @param array<string, string> $cols Colonnes par défaut.
 * @return array<string, string>
 */
function pcs_directory_admin_columns( array $cols ): array {
	$new = [];
	foreach ( $cols as $k => $v ) {
		$new[ $k ] = $v;
		if ( 'title' === $k ) {
			$new['pcs_siret']    = __( 'SIRET', 'pluscestsimple' );
			$new['pcs_ville']    = __( 'Ville', 'pluscestsimple' );
			$new['pcs_sources']  = __( 'Sources', 'pluscestsimple' );
			$new['pcs_featured'] = __( 'Featured', 'pluscestsimple' );
		}
	}
	return $new;
}
add_filter( 'manage_' . PCS_DIR_CPT . '_posts_columns', 'pcs_directory_admin_columns' );

/**
 * Contenu des colonnes admin custom.
 *
 * @param string $col     Nom de la colonne.
 * @param int    $post_id ID du post.
 * @return void
 */
function pcs_directory_admin_column_content( string $col, int $post_id ): void {
	switch ( $col ) {
		case 'pcs_siret':
			echo esc_html( get_post_meta( $post_id, '_pcs_etab_siret', true ) ?: '—' );
			break;
		case 'pcs_ville':
			echo esc_html( get_post_meta( $post_id, '_pcs_etab_ville', true ) ?: '—' );
			break;
		case 'pcs_sources':
			$src = get_post_meta( $post_id, '_pcs_etab_sources', true );
			echo esc_html( is_array( $src ) ? implode( ', ', $src ) : '—' );
			break;
		case 'pcs_featured':
			echo get_post_meta( $post_id, '_pcs_etab_is_featured', true ) === '1' ? '★' : '';
			break;
	}
}
add_action( 'manage_' . PCS_DIR_CPT . '_posts_custom_column', 'pcs_directory_admin_column_content', 10, 2 );
