<?php
/**
 * Page admin : Annuaire > Importer depuis Sirene.
 *
 * Formulaire d'import + logs des 20 dernières exécutions.
 * L'import est SYNCHRONE pour la v1 (un seul utilisateur, batches limités).
 * v2 prévue : passage en async via wp_schedule_single_event + admin-ajax progress.
 *
 * @package PCS_Directory
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Enregistre la page admin sous le menu Annuaire.
 *
 * @return void
 */
function pcs_directory_admin_import_menu(): void {
	add_submenu_page(
		'edit.php?post_type=' . PCS_DIR_CPT,
		__( 'Importer depuis Sirene', 'pluscestsimple' ),
		__( 'Importer depuis Sirene', 'pluscestsimple' ),
		'manage_options',
		'pcs-directory-import',
		'pcs_directory_admin_import_render'
	);
}
add_action( 'admin_menu', 'pcs_directory_admin_import_menu' );

/**
 * Rendu de la page d'import.
 *
 * @return void
 */
function pcs_directory_admin_import_render(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Accès refusé.', 'pluscestsimple' ) );
	}

	$summary = null;
	$error   = null;

	if (
		isset( $_POST['pcs_directory_import_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pcs_directory_import_nonce'] ) ), 'pcs_directory_import' )
	) {
		$args = [
			'naf'         => isset( $_POST['naf'] ) ? sanitize_text_field( wp_unslash( $_POST['naf'] ) ) : PCS_DIR_DEFAULT_NAF,
			'departement' => isset( $_POST['departement'] ) ? sanitize_text_field( wp_unslash( $_POST['departement'] ) ) : 'all',
			'max'         => isset( $_POST['max'] ) ? max( 1, min( 500, (int) $_POST['max'] ) ) : 50,
			'enrich_osm'  => ! empty( $_POST['enrich_osm'] ),
		];

		$summary = pcs_directory_run_import( $args );
		if ( ! empty( $summary['errors'] ) && 0 === $summary['imported'] && 0 === $summary['skipped'] ) {
			$error = implode( ' ; ', $summary['errors'] );
		}
	}

	$log = get_option( PCS_DIR_LOG_OPTION, [] );
	if ( ! is_array( $log ) ) {
		$log = [];
	}

	?>
	<div class="wrap pcs-directory-admin">
		<h1><?php esc_html_e( 'Annuaire — Importer depuis Sirene', 'pluscestsimple' ); ?></h1>

		<p class="description">
			<?php esc_html_e( 'Importe les établissements actifs depuis l\'API Recherche Entreprises (data.gouv.fr). Tous les imports passent en statut "brouillon" et nécessitent une validation manuelle avant publication.', 'pluscestsimple' ); ?>
		</p>

		<?php if ( $error ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $error ); ?></p></div>
		<?php elseif ( $summary ) : ?>
			<div class="notice notice-success">
				<p>
					<?php
					printf(
						/* translators: 1: nb imported, 2: nb skipped, 3: duration sec. */
						esc_html__( 'Import terminé : %1$d importés, %2$d ignorés (déjà en DB), en %3$s s.', 'pluscestsimple' ),
						(int) $summary['imported'],
						(int) $summary['skipped'],
						esc_html( number_format( (float) $summary['duration_s'], 2 ) )
					);
					?>
				</p>
				<?php if ( ! empty( $summary['errors'] ) ) : ?>
					<p><strong><?php esc_html_e( 'Erreurs partielles :', 'pluscestsimple' ); ?></strong></p>
					<ul style="list-style: disc; margin-left: 2em;">
						<?php foreach ( (array) $summary['errors'] as $err ) : ?>
							<li><?php echo esc_html( (string) $err ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
				<p>
					<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=pcs-directory-validate' ) ); ?>">
						<?php esc_html_e( 'Aller à la validation', 'pluscestsimple' ); ?>
					</a>
				</p>
			</div>
		<?php endif; ?>

		<h2><?php esc_html_e( 'Paramètres d\'import', 'pluscestsimple' ); ?></h2>

		<form method="post">
			<?php wp_nonce_field( 'pcs_directory_import', 'pcs_directory_import_nonce' ); ?>

			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><label for="naf"><?php esc_html_e( 'Code(s) NAF', 'pluscestsimple' ); ?></label></th>
						<td>
							<input type="text" id="naf" name="naf" value="<?php echo esc_attr( PCS_DIR_DEFAULT_NAF ); ?>" class="regular-text" />
							<p class="description">
								<?php esc_html_e( 'Séparés par virgules. Défaut : 47.59A (meubles), 47.59B (équipement foyer), 47.53Z (tapis/sols).', 'pluscestsimple' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="departement"><?php esc_html_e( 'Département', 'pluscestsimple' ); ?></label></th>
						<td>
							<input type="text" id="departement" name="departement" value="all" class="small-text" maxlength="3" />
							<p class="description">
								<?php esc_html_e( 'Code à 2 chiffres (ex. 75) ou "all" pour tous les départements.', 'pluscestsimple' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="max"><?php esc_html_e( 'Nombre maximum', 'pluscestsimple' ); ?></label></th>
						<td>
							<input type="number" id="max" name="max" value="50" min="1" max="500" class="small-text" />
							<p class="description">
								<?php esc_html_e( 'Plafonné à 500 par lancement. Démarrer petit (50) pour vérifier la qualité avant de scaler.', 'pluscestsimple' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Enrichissement', 'pluscestsimple' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="enrich_osm" value="1" />
								<?php esc_html_e( 'Enrichir via OpenStreetMap (téléphone, site web, horaires si dispo). Ajoute ~1s par établissement.', 'pluscestsimple' ); ?>
							</label>
						</td>
					</tr>
				</tbody>
			</table>

			<?php
			submit_button(
				__( 'Lancer l\'import', 'pluscestsimple' ),
				'primary',
				'submit',
				true,
				[ 'onclick' => "return confirm('" . esc_js( __( 'Lancer l\'import maintenant ? Cela peut prendre plusieurs minutes (rate-limit 1 req/s).', 'pluscestsimple' ) ) . "');" ]
			);
			?>
		</form>

		<h2><?php esc_html_e( '20 derniers imports', 'pluscestsimple' ); ?></h2>

		<?php if ( empty( $log ) ) : ?>
			<p><em><?php esc_html_e( 'Aucun import lancé pour le moment.', 'pluscestsimple' ); ?></em></p>
		<?php else : ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Date', 'pluscestsimple' ); ?></th>
						<th><?php esc_html_e( 'Utilisateur', 'pluscestsimple' ); ?></th>
						<th><?php esc_html_e( 'Paramètres', 'pluscestsimple' ); ?></th>
						<th><?php esc_html_e( 'Importés', 'pluscestsimple' ); ?></th>
						<th><?php esc_html_e( 'Ignorés', 'pluscestsimple' ); ?></th>
						<th><?php esc_html_e( 'Durée', 'pluscestsimple' ); ?></th>
						<th><?php esc_html_e( 'Erreurs', 'pluscestsimple' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $log as $entry ) : ?>
						<tr>
							<td><?php echo esc_html( (string) ( $entry['timestamp'] ?? '—' ) ); ?></td>
							<td><?php echo esc_html( (string) ( $entry['user'] ?? '—' ) ); ?></td>
							<td>
								<code>
									<?php
									$args = is_array( $entry['args'] ?? null ) ? $entry['args'] : [];
									$parts = [];
									foreach ( $args as $k => $v ) {
										$parts[] = $k . '=' . ( is_scalar( $v ) ? (string) $v : wp_json_encode( $v ) );
									}
									echo esc_html( implode( ' ', $parts ) );
									?>
								</code>
							</td>
							<td><?php echo (int) ( $entry['imported'] ?? 0 ); ?></td>
							<td><?php echo (int) ( $entry['skipped'] ?? 0 ); ?></td>
							<td><?php echo esc_html( (string) ( $entry['duration_s'] ?? 0 ) ); ?> s</td>
							<td>
								<?php
								$errs = is_array( $entry['errors'] ?? null ) ? $entry['errors'] : [];
								if ( empty( $errs ) ) {
									echo '<span style="color:#46b450">OK</span>';
								} else {
									echo '<details><summary>' . esc_html( (string) count( $errs ) ) . '</summary><ul>';
									foreach ( $errs as $e ) {
										echo '<li>' . esc_html( (string) $e ) . '</li>';
									}
									echo '</ul></details>';
								}
								?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
	<?php
}
