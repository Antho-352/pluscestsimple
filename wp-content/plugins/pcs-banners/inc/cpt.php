<?php
/**
 * CPT pcs_banner + meta boxes (URL cible, période, type, libellé).
 *
 * Champs meta :
 *  - _pcs_banner_url        (string)  URL cliquable de la bannière
 *  - _pcs_banner_start_date (string)  datetime-local (YYYY-MM-DDTHH:MM)
 *  - _pcs_banner_end_date   (string)  datetime-local
 *  - _pcs_banner_type       (string)  display | sponsored | affilie
 *  - _pcs_banner_label      (string)  libellé personnalisé (sinon auto selon type)
 *
 * @package PCS_Banners
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Enregistre le CPT pcs_banner (non public, admin uniquement).
 *
 * @return void
 */
function pcs_banner_register_cpt(): void {
	$labels = [
		'name'                  => __( 'Bannières', 'pluscestsimple' ),
		'singular_name'         => __( 'Bannière', 'pluscestsimple' ),
		'menu_name'             => __( 'Bannières', 'pluscestsimple' ),
		'name_admin_bar'        => __( 'Bannière', 'pluscestsimple' ),
		'add_new'               => __( 'Ajouter', 'pluscestsimple' ),
		'add_new_item'          => __( 'Ajouter une bannière', 'pluscestsimple' ),
		'edit_item'             => __( 'Modifier la bannière', 'pluscestsimple' ),
		'new_item'              => __( 'Nouvelle bannière', 'pluscestsimple' ),
		'view_item'             => __( 'Voir la bannière', 'pluscestsimple' ),
		'search_items'          => __( 'Rechercher une bannière', 'pluscestsimple' ),
		'not_found'             => __( 'Aucune bannière trouvée.', 'pluscestsimple' ),
		'not_found_in_trash'    => __( 'Aucune bannière dans la corbeille.', 'pluscestsimple' ),
		'featured_image'        => __( 'Image de la bannière', 'pluscestsimple' ),
		'set_featured_image'    => __( 'Définir l\'image', 'pluscestsimple' ),
		'remove_featured_image' => __( 'Retirer l\'image', 'pluscestsimple' ),
		'use_featured_image'    => __( 'Utiliser comme image', 'pluscestsimple' ),
	];

	register_post_type(
		PCS_BANNER_CPT,
		[
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_rest'       => true, // requis pour Gutenberg.
			'menu_position'      => 26,
			'menu_icon'          => 'dashicons-megaphone',
			'capability_type'    => 'post',
			'hierarchical'       => false,
			'supports'           => [ 'title', 'thumbnail', 'revisions' ],
			'has_archive'        => false,
			'rewrite'            => false,
			'taxonomies'         => [ PCS_BANNER_SLOT_TAX ],
		]
	);
}
add_action( 'init', 'pcs_banner_register_cpt', 10 );

// ─── Meta box ───────────────────────────────────────────────────────────────

/**
 * Ajoute la meta box "Paramètres de la bannière" sur l'écran d'édition du CPT.
 *
 * @return void
 */
function pcs_banner_add_meta_boxes(): void {
	add_meta_box(
		'pcs_banner_settings',
		__( 'Paramètres de la bannière', 'pluscestsimple' ),
		'pcs_banner_render_meta_box',
		PCS_BANNER_CPT,
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'pcs_banner_add_meta_boxes' );

/**
 * Affiche le HTML de la meta box (URL cible, dates, type, libellé).
 *
 * @param WP_Post $post Post courant.
 * @return void
 */
function pcs_banner_render_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'pcs_banner_save_meta', 'pcs_banner_meta_nonce' );

	$url        = (string) get_post_meta( $post->ID, '_pcs_banner_url', true );
	$start_date = (string) get_post_meta( $post->ID, '_pcs_banner_start_date', true );
	$end_date   = (string) get_post_meta( $post->ID, '_pcs_banner_end_date', true );
	$type       = (string) get_post_meta( $post->ID, '_pcs_banner_type', true );
	$label      = (string) get_post_meta( $post->ID, '_pcs_banner_label', true );

	if ( empty( $type ) ) {
		$type = 'display';
	}

	$types = [
		'display'   => __( 'Display (publicité classique)', 'pluscestsimple' ),
		'sponsored' => __( 'Sponsorisé', 'pluscestsimple' ),
		'affilie'   => __( 'Affilié / Partenariat', 'pluscestsimple' ),
	];

	?>
	<style>
		.pcs-banner-mb table { width: 100%; }
		.pcs-banner-mb th { width: 200px; text-align: left; padding: 12px 8px; vertical-align: top; }
		.pcs-banner-mb td { padding: 12px 8px; }
		.pcs-banner-mb input[type="url"],
		.pcs-banner-mb input[type="text"],
		.pcs-banner-mb input[type="datetime-local"],
		.pcs-banner-mb select { width: 100%; max-width: 480px; }
		.pcs-banner-mb .description { color: #666; font-size: 12px; margin-top: 4px; }
	</style>
	<div class="pcs-banner-mb">
		<table>
			<tr>
				<th><label for="pcs_banner_url"><?php esc_html_e( 'URL cible', 'pluscestsimple' ); ?></label></th>
				<td>
					<input type="url" id="pcs_banner_url" name="pcs_banner_url"
						value="<?php echo esc_attr( $url ); ?>"
						placeholder="https://example.com" />
					<p class="description"><?php esc_html_e( 'URL ouverte au clic sur la bannière (nouvelle fenêtre).', 'pluscestsimple' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="pcs_banner_type"><?php esc_html_e( 'Type', 'pluscestsimple' ); ?></label></th>
				<td>
					<select id="pcs_banner_type" name="pcs_banner_type">
						<?php foreach ( $types as $val => $name ) : ?>
							<option value="<?php echo esc_attr( $val ); ?>"
								<?php selected( $type, $val ); ?>><?php echo esc_html( $name ); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php esc_html_e( 'Détermine le rel=sponsored nofollow et l\'étiquette automatique au-dessus de l\'image.', 'pluscestsimple' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="pcs_banner_label"><?php esc_html_e( 'Libellé (optionnel)', 'pluscestsimple' ); ?></label></th>
				<td>
					<input type="text" id="pcs_banner_label" name="pcs_banner_label"
						value="<?php echo esc_attr( $label ); ?>"
						placeholder="Sponsorisé / En partenariat / Publicité" />
					<p class="description"><?php esc_html_e( 'Laissez vide pour utiliser le libellé par défaut selon le type (Sponsorisé / En partenariat). Type "display" : aucune étiquette par défaut.', 'pluscestsimple' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="pcs_banner_start_date"><?php esc_html_e( 'Date de début', 'pluscestsimple' ); ?></label></th>
				<td>
					<input type="datetime-local" id="pcs_banner_start_date" name="pcs_banner_start_date"
						value="<?php echo esc_attr( $start_date ); ?>" />
					<p class="description"><?php esc_html_e( 'Laissez vide pour une diffusion immédiate.', 'pluscestsimple' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="pcs_banner_end_date"><?php esc_html_e( 'Date de fin', 'pluscestsimple' ); ?></label></th>
				<td>
					<input type="datetime-local" id="pcs_banner_end_date" name="pcs_banner_end_date"
						value="<?php echo esc_attr( $end_date ); ?>" />
					<p class="description"><?php esc_html_e( 'Laissez vide pour une diffusion sans date d\'expiration.', 'pluscestsimple' ); ?></p>
				</td>
			</tr>
		</table>
	</div>
	<?php
}

/**
 * Sauvegarde des meta de la bannière.
 *
 * @param int $post_id ID du post.
 * @return void
 */
function pcs_banner_save_meta( int $post_id ): void {
	// Sécurité standard.
	if ( ! isset( $_POST['pcs_banner_meta_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pcs_banner_meta_nonce'] ) ), 'pcs_banner_save_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( get_post_type( $post_id ) !== PCS_BANNER_CPT ) {
		return;
	}

	// URL.
	if ( isset( $_POST['pcs_banner_url'] ) ) {
		$url = esc_url_raw( wp_unslash( $_POST['pcs_banner_url'] ) );
		update_post_meta( $post_id, '_pcs_banner_url', $url );
	}

	// Type — whitelist.
	if ( isset( $_POST['pcs_banner_type'] ) ) {
		$type     = sanitize_key( wp_unslash( $_POST['pcs_banner_type'] ) );
		$allowed  = [ 'display', 'sponsored', 'affilie' ];
		$safe     = in_array( $type, $allowed, true ) ? $type : 'display';
		update_post_meta( $post_id, '_pcs_banner_type', $safe );
	}

	// Libellé.
	if ( isset( $_POST['pcs_banner_label'] ) ) {
		$label = sanitize_text_field( wp_unslash( $_POST['pcs_banner_label'] ) );
		update_post_meta( $post_id, '_pcs_banner_label', $label );
	}

	// Dates : format datetime-local YYYY-MM-DDTHH:MM (HTML5).
	foreach ( [ 'start_date', 'end_date' ] as $key ) {
		$field = 'pcs_banner_' . $key;
		if ( isset( $_POST[ $field ] ) ) {
			$val  = sanitize_text_field( wp_unslash( $_POST[ $field ] ) );
			// Validation simple : datetime-local respecte ce pattern, sinon on stocke vide.
			if ( '' !== $val && ! preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/', $val ) ) {
				$val = '';
			}
			update_post_meta( $post_id, '_pcs_banner_' . $key, $val );
		}
	}
}
add_action( 'save_post', 'pcs_banner_save_meta' );

// ─── Invalidation cache ──────────────────────────────────────────────────────

/**
 * Invalide tous les caches transient de bannières (tous slots confondus).
 *
 * @return void
 */
function pcs_banner_flush_all_cache(): void {
	$slots = pcs_banner_get_all_slots();
	foreach ( array_keys( $slots ) as $slug ) {
		delete_transient( PCS_BANNER_CACHE_PREFIX . $slug );
	}
}

/**
 * Invalide le cache à chaque save_post du CPT.
 *
 * @return void
 */
function pcs_banner_on_save_flush(): void {
	pcs_banner_flush_all_cache();
}
add_action( 'save_post_' . PCS_BANNER_CPT, 'pcs_banner_on_save_flush' );
add_action( 'delete_post', function ( $post_id ) {
	if ( get_post_type( $post_id ) === PCS_BANNER_CPT ) {
		pcs_banner_flush_all_cache();
	}
} );
