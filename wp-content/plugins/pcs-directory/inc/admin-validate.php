<?php
/**
 * Page admin : Annuaire > À valider.
 *
 * Liste tous les pcs_etablissement en draft. Permet :
 *   - édition rapide inline (titre + type + featured)
 *   - bulk action "Valider et publier" → passe les sélectionnés en publish
 *   - bulk action "Rejeter" → trash
 *
 * @package PCS_Directory
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Enregistre la page admin sous le menu Annuaire.
 *
 * @return void
 */
function pcs_directory_admin_validate_menu(): void {
	$count = wp_count_posts( PCS_DIR_CPT );
	$draft = isset( $count->draft ) ? (int) $count->draft : 0;

	$label = sprintf(
		/* translators: %s: badge HTML avec le nombre de brouillons. */
		__( 'À valider %s', 'pluscestsimple' ),
		$draft > 0 ? '<span class="awaiting-mod count-' . (int) $draft . '"><span class="pending-count">' . (int) $draft . '</span></span>' : ''
	);

	add_submenu_page(
		'edit.php?post_type=' . PCS_DIR_CPT,
		__( 'À valider', 'pluscestsimple' ),
		$label,
		'edit_posts',
		'pcs-directory-validate',
		'pcs_directory_admin_validate_render'
	);
}
add_action( 'admin_menu', 'pcs_directory_admin_validate_menu' );

/**
 * Rendu de la page de validation.
 *
 * @return void
 */
function pcs_directory_admin_validate_render(): void {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'Accès refusé.', 'pluscestsimple' ) );
	}

	$notice = pcs_directory_admin_validate_handle_post();

	$paged = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1;

	$query = new WP_Query( [
		'post_type'      => PCS_DIR_CPT,
		'post_status'    => 'draft',
		'posts_per_page' => 30,
		'paged'          => $paged,
		'orderby'        => 'date',
		'order'          => 'DESC',
	] );

	$total_pages = (int) $query->max_num_pages;

	?>
	<div class="wrap pcs-directory-admin">
		<h1><?php esc_html_e( 'Annuaire — Établissements à valider', 'pluscestsimple' ); ?></h1>

		<?php if ( $notice ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $notice ); ?></p></div>
		<?php endif; ?>

		<p class="description">
			<?php esc_html_e( 'Tous les imports passent ici en brouillon. Vérifiez le nom, ajoutez le type, choisissez "Valider et publier" pour les rendre publics, ou "Rejeter" pour mettre à la corbeille.', 'pluscestsimple' ); ?>
		</p>

		<?php if ( ! $query->have_posts() ) : ?>
			<p><em><?php esc_html_e( 'Aucun établissement en attente de validation.', 'pluscestsimple' ); ?></em></p>
		<?php else : ?>
			<form method="post">
				<?php wp_nonce_field( 'pcs_directory_validate', 'pcs_directory_validate_nonce' ); ?>

				<div class="tablenav top">
					<div class="alignleft actions bulkactions">
						<label for="bulk-action-top" class="screen-reader-text"><?php esc_html_e( 'Action groupée', 'pluscestsimple' ); ?></label>
						<select name="bulk_action" id="bulk-action-top">
							<option value=""><?php esc_html_e( 'Action groupée', 'pluscestsimple' ); ?></option>
							<option value="publish"><?php esc_html_e( 'Valider et publier', 'pluscestsimple' ); ?></option>
							<option value="trash"><?php esc_html_e( 'Rejeter (corbeille)', 'pluscestsimple' ); ?></option>
						</select>
						<?php submit_button( __( 'Appliquer', 'pluscestsimple' ), '', '', false ); ?>
					</div>
				</div>

				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<td class="manage-column column-cb check-column">
								<input type="checkbox" id="cb-select-all" onclick="document.querySelectorAll('input[name=\\'ids[]\\']').forEach(c=>c.checked=this.checked);" />
							</td>
							<th><?php esc_html_e( 'Aperçu', 'pluscestsimple' ); ?></th>
							<th><?php esc_html_e( 'Nom', 'pluscestsimple' ); ?></th>
							<th><?php esc_html_e( 'SIRET', 'pluscestsimple' ); ?></th>
							<th><?php esc_html_e( 'Type', 'pluscestsimple' ); ?></th>
							<th><?php esc_html_e( 'Ville', 'pluscestsimple' ); ?></th>
							<th><?php esc_html_e( 'Sources', 'pluscestsimple' ); ?></th>
							<th><?php esc_html_e( 'Importé le', 'pluscestsimple' ); ?></th>
							<th><?php esc_html_e( 'Featured', 'pluscestsimple' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'pluscestsimple' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php while ( $query->have_posts() ) :
							$query->the_post();
							$pid       = (int) get_the_ID();
							$siret     = get_post_meta( $pid, '_pcs_etab_siret', true );
							$ville     = get_post_meta( $pid, '_pcs_etab_ville', true );
							$sources   = get_post_meta( $pid, '_pcs_etab_sources', true );
							$featured  = get_post_meta( $pid, '_pcs_etab_is_featured', true );
							$thumb     = get_the_post_thumbnail( $pid, [ 60, 60 ] );
							$types     = wp_get_object_terms( $pid, PCS_DIR_TAX_TYPE, [ 'fields' => 'id=>name' ] );
							$type_ids  = is_array( $types ) && ! is_wp_error( $types ) ? array_keys( $types ) : [];
							?>
							<tr>
								<th scope="row" class="check-column">
									<input type="checkbox" name="ids[]" value="<?php echo esc_attr( (string) $pid ); ?>" />
								</th>
								<td>
									<?php
									if ( $thumb ) {
										echo $thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									} else {
										echo '<a href="' . esc_url( get_edit_post_link( $pid ) ) . '">' . esc_html__( 'Ajouter', 'pluscestsimple' ) . '</a>';
									}
									?>
								</td>
								<td>
									<input type="text" name="titles[<?php echo esc_attr( (string) $pid ); ?>]" value="<?php echo esc_attr( get_the_title() ); ?>" class="regular-text" />
								</td>
								<td><code><?php echo esc_html( (string) $siret ); ?></code></td>
								<td>
									<select name="types[<?php echo esc_attr( (string) $pid ); ?>]">
										<option value=""><?php esc_html_e( '— Choisir —', 'pluscestsimple' ); ?></option>
										<?php
										$all_types = get_terms( [ 'taxonomy' => PCS_DIR_TAX_TYPE, 'hide_empty' => false ] );
										if ( is_array( $all_types ) ) {
											foreach ( $all_types as $t ) {
												printf(
													'<option value="%d"%s>%s</option>',
													(int) $t->term_id,
													selected( in_array( (int) $t->term_id, $type_ids, true ), true, false ),
													esc_html( $t->name )
												);
											}
										}
										?>
									</select>
								</td>
								<td><?php echo esc_html( (string) $ville ); ?></td>
								<td>
									<?php
									echo esc_html(
										is_array( $sources ) ? implode( ', ', $sources ) : '—'
									);
									?>
								</td>
								<td><?php echo esc_html( get_the_date( 'Y-m-d H:i' ) ); ?></td>
								<td>
									<input type="checkbox" name="featured[<?php echo esc_attr( (string) $pid ); ?>]" value="1" <?php checked( $featured, '1' ); ?> />
								</td>
								<td>
									<a href="<?php echo esc_url( get_edit_post_link( $pid ) ); ?>"><?php esc_html_e( 'Éditer', 'pluscestsimple' ); ?></a>
									<?php
									$preview = get_preview_post_link( $pid );
									if ( $preview ) {
										echo ' · <a href="' . esc_url( $preview ) . '" target="_blank" rel="noopener">' . esc_html__( 'Aperçu', 'pluscestsimple' ) . '</a>';
									}
									?>
								</td>
							</tr>
						<?php endwhile; ?>
					</tbody>
				</table>

				<div class="tablenav bottom">
					<div class="alignleft actions">
						<?php submit_button( __( 'Enregistrer les modifications inline', 'pluscestsimple' ), 'secondary', 'save_inline', false ); ?>
					</div>
					<?php if ( $total_pages > 1 ) : ?>
						<div class="tablenav-pages">
							<?php
							echo paginate_links( [ // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								'base'    => add_query_arg( 'paged', '%#%' ),
								'format'  => '',
								'current' => $paged,
								'total'   => $total_pages,
							] );
							?>
						</div>
					<?php endif; ?>
				</div>
			</form>
		<?php endif; wp_reset_postdata(); ?>
	</div>
	<?php
}

/**
 * Gère les soumissions POST de la page de validation.
 *
 * @return string|null Message de feedback, ou null si rien à faire.
 */
function pcs_directory_admin_validate_handle_post(): ?string {
	if ( empty( $_POST['pcs_directory_validate_nonce'] ) ) {
		return null;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pcs_directory_validate_nonce'] ) ), 'pcs_directory_validate' ) ) {
		return null;
	}
	if ( ! current_user_can( 'edit_posts' ) ) {
		return null;
	}

	// 1) Enregistrement inline (titres + type + featured) pour TOUS les rows visibles.
	$titles   = isset( $_POST['titles'] )   && is_array( $_POST['titles'] )   ? wp_unslash( $_POST['titles'] )   : [];
	$types    = isset( $_POST['types'] )    && is_array( $_POST['types'] )    ? wp_unslash( $_POST['types'] )    : [];
	$featured = isset( $_POST['featured'] ) && is_array( $_POST['featured'] ) ? wp_unslash( $_POST['featured'] ) : [];

	$inline_updates = 0;
	foreach ( $titles as $pid => $new_title ) {
		$pid       = (int) $pid;
		$new_title = sanitize_text_field( (string) $new_title );
		$post      = get_post( $pid );
		if ( ! $post || PCS_DIR_CPT !== $post->post_type ) {
			continue;
		}
		if ( ! current_user_can( 'edit_post', $pid ) ) {
			continue;
		}
		$changed = false;
		if ( $new_title !== $post->post_title && '' !== $new_title ) {
			wp_update_post( [ 'ID' => $pid, 'post_title' => $new_title ] );
			$changed = true;
		}
		if ( isset( $types[ $pid ] ) ) {
			$type_id = (int) $types[ $pid ];
			if ( $type_id > 0 ) {
				wp_set_post_terms( $pid, [ $type_id ], PCS_DIR_TAX_TYPE, false );
				$changed = true;
			}
		}
		$want_featured = isset( $featured[ $pid ] ) ? '1' : '0';
		if ( $want_featured !== (string) get_post_meta( $pid, '_pcs_etab_is_featured', true ) ) {
			update_post_meta( $pid, '_pcs_etab_is_featured', $want_featured );
			$changed = true;
		}
		if ( $changed ) {
			$inline_updates++;
		}
	}

	// 2) Bulk action si demandée.
	$bulk = isset( $_POST['bulk_action'] ) ? sanitize_key( wp_unslash( $_POST['bulk_action'] ) ) : '';
	$ids  = isset( $_POST['ids'] ) && is_array( $_POST['ids'] ) ? array_map( 'absint', wp_unslash( $_POST['ids'] ) ) : [];
	$ids  = array_filter( $ids );

	$bulk_count = 0;
	if ( ! empty( $bulk ) && ! empty( $ids ) ) {
		foreach ( $ids as $pid ) {
			$post = get_post( $pid );
			if ( ! $post || PCS_DIR_CPT !== $post->post_type ) {
				continue;
			}
			if ( ! current_user_can( 'edit_post', $pid ) ) {
				continue;
			}
			if ( 'publish' === $bulk ) {
				wp_update_post( [ 'ID' => $pid, 'post_status' => 'publish' ] );
				update_post_meta( $pid, '_pcs_etab_last_verified', current_time( 'Y-m-d' ) );
				$bulk_count++;
			} elseif ( 'trash' === $bulk ) {
				wp_trash_post( $pid );
				$bulk_count++;
			}
		}
	}

	$messages = [];
	if ( $inline_updates > 0 ) {
		$messages[] = sprintf(
			/* translators: %d: nb rows updated inline. */
			_n( '%d ligne mise à jour.', '%d lignes mises à jour.', $inline_updates, 'pluscestsimple' ),
			$inline_updates
		);
	}
	if ( $bulk_count > 0 ) {
		if ( 'publish' === $bulk ) {
			$messages[] = sprintf(
				/* translators: %d: nb published. */
				_n( '%d établissement publié.', '%d établissements publiés.', $bulk_count, 'pluscestsimple' ),
				$bulk_count
			);
		} elseif ( 'trash' === $bulk ) {
			$messages[] = sprintf(
				/* translators: %d: nb trashed. */
				_n( '%d établissement rejeté.', '%d établissements rejetés.', $bulk_count, 'pluscestsimple' ),
				$bulk_count
			);
		}
	}

	return $messages ? implode( ' ', $messages ) : null;
}
