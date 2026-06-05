<?php
/**
 * Admin — Annuaire > Importer JSONL.
 *
 * Deux modes :
 *   - Upload fichier (depuis ton ordinateur)
 *   - Chemin serveur (si le fichier est déjà sur le serveur)
 *
 * @package PCS_Directory
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// ─── Menu ─────────────────────────────────────────────────────────────────────

add_action( 'admin_menu', function (): void {
	add_submenu_page(
		'edit.php?post_type=' . PCS_DIR_CPT,
		'Importer JSONL',
		'Importer JSONL',
		'manage_options',
		'pcs-directory-import',
		'pcs_directory_admin_import_render'
	);
} );

// ─── Rendu ────────────────────────────────────────────────────────────────────

function pcs_directory_admin_import_render(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Accès refusé.' );
	}

	$result  = null;
	$error   = '';
	$dry_run = false;

	if (
		isset( $_POST['pcs_import_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pcs_import_nonce'] ) ), 'pcs_import' )
	) {
		$dry_run = ! empty( $_POST['dry_run'] );

		// ── Mode 1 : upload fichier ────────────────────────────────────────────
		$filepath = '';
		if ( ! empty( $_FILES['jsonl_file']['tmp_name'] ) ) {
			$upload = $_FILES['jsonl_file'];

			if ( $upload['error'] !== UPLOAD_ERR_OK ) {
				$error = 'Erreur upload : code ' . (int) $upload['error'];
			} elseif ( $upload['size'] === 0 ) {
				$error = 'Fichier uploadé vide.';
			} else {
				$filepath = $upload['tmp_name'];
			}

		// ── Mode 2 : chemin serveur ────────────────────────────────────────────
		} elseif ( ! empty( $_POST['jsonl_path'] ) ) {
			$filepath = sanitize_text_field( wp_unslash( $_POST['jsonl_path'] ) );
			if ( ! file_exists( $filepath ) ) {
				$error = "Fichier introuvable sur le serveur : {$filepath}";
				$filepath = '';
			}
		} else {
			$error = 'Aucun fichier fourni.';
		}

		if ( '' !== $filepath && '' === $error ) {
			set_time_limit( 300 );
			$result = pcs_directory_import_jsonl( $filepath, $dry_run );
		}
	}

	?>
	<div class="wrap pcs-directory-admin">
		<h1>Annuaire — Importer JSONL</h1>

		<p class="description" style="max-width:700px">
			Importe un fichier <code>master.jsonl</code> produit par le pipeline <strong>pcs-annuaire-data</strong>.
			L'import est idempotent (mise à jour si le SIRET existe déjà).
			Les boutiques <code>"public": false</code> sont automatiquement ignorées.
		</p>

		<?php if ( $error ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $error ); ?></p></div>
		<?php endif; ?>

		<?php if ( $result !== null ) : ?>
			<div class="notice notice-<?php echo empty( $result['errors'] ) ? 'success' : 'warning'; ?>">
				<p>
					<?php if ( $dry_run ) : ?><strong>[DRY RUN — rien n'a été modifié]</strong> <?php endif; ?>
					Créés : <strong><?php echo (int) $result['created']; ?></strong> &nbsp;|&nbsp;
					Mis à jour : <strong><?php echo (int) $result['updated']; ?></strong> &nbsp;|&nbsp;
					Ignorés (non-publics) : <strong><?php echo (int) $result['skipped']; ?></strong> &nbsp;|&nbsp;
					Retirés (hors-sujet/fermés) : <strong><?php echo (int) ( $result['removed'] ?? 0 ); ?></strong>
					<?php if ( $result['errors'] ) : ?>
						&nbsp;|&nbsp; Erreurs : <strong><?php echo count( $result['errors'] ); ?></strong>
					<?php endif; ?>
				</p>
				<?php if ( $result['errors'] ) : ?>
					<details>
						<summary><?php echo count( $result['errors'] ); ?> erreur(s)</summary>
						<ul style="margin-left:1.5em;list-style:disc">
							<?php foreach ( $result['errors'] as $err ) : ?>
								<li><?php echo esc_html( $err ); ?></li>
							<?php endforeach; ?>
						</ul>
					</details>
				<?php endif; ?>
			</div>
			<?php if ( $result !== null && ! $dry_run ) :
				update_option( 'pcs_directory_last_import', current_time( 'mysql' ) );
			endif; ?>
		<?php endif; ?>

		<form method="post" enctype="multipart/form-data" style="margin-top:1.5rem">
			<?php wp_nonce_field( 'pcs_import', 'pcs_import_nonce' ); ?>

			<table class="form-table" role="presentation">

				<tr>
					<th scope="row"><label for="jsonl_file">📂 Uploader le fichier JSONL</label></th>
					<td>
						<input type="file" id="jsonl_file" name="jsonl_file" accept=".jsonl,.json" style="font-size:1rem" />
						<p class="description">
							Depuis ton ordinateur : sélectionne <code>master.jsonl</code> et clique sur Lancer.
							<strong>Méthode recommandée.</strong>
						</p>
					</td>
				</tr>

				<tr>
					<td colspan="2">
						<details>
							<summary style="cursor:pointer;color:#666;font-size:.9rem">
								Ou spécifier un chemin sur le serveur (si le fichier est déjà hébergé)
							</summary>
							<div style="margin-top:.75rem">
								<input
									type="text"
									id="jsonl_path"
									name="jsonl_path"
									value=""
									class="large-text"
									placeholder="/chemin/absolu/sur/le/serveur/master.jsonl"
								/>
							</div>
						</details>
					</td>
				</tr>

				<tr>
					<th scope="row">Options</th>
					<td>
						<label>
							<input type="checkbox" name="dry_run" value="1" <?php checked( ! empty( $_POST['dry_run'] ) ); ?> />
							<strong>Dry run</strong> — simuler sans écrire en base (recommandé avant le premier import)
						</label>
					</td>
				</tr>

			</table>

			<?php submit_button( 'Lancer l\'import', 'primary' ); ?>
		</form>

		<hr />

		<h2>État actuel</h2>
		<table class="widefat" style="max-width:500px">
			<tr>
				<th>Boutiques publiées</th>
				<td><strong><?php echo (int) wp_count_posts( PCS_DIR_CPT )->publish; ?></strong></td>
			</tr>
			<tr>
				<th>Dernière mise à jour</th>
				<td><?php echo esc_html( get_option( 'pcs_directory_last_import', '—' ) ); ?></td>
			</tr>
		</table>
	</div>
	<?php
}
