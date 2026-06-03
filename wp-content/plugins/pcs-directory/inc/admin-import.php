<?php
/**
 * Admin — Annuaire > Importer JSONL.
 *
 * Formulaire de chemin fichier + option dry-run.
 * Affiche un rapport ligne par ligne après import.
 *
 * @package PCS_Directory
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// ─── Menu ─────────────────────────────────────────────────────────────────────

add_action( 'admin_menu', function (): void {
	// Menu parent = post_type pcs_boutique.
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
		$filepath = sanitize_text_field( wp_unslash( $_POST['jsonl_path'] ?? '' ) );
		$dry_run  = ! empty( $_POST['dry_run'] );

		if ( '' === $filepath ) {
			$error = 'Chemin de fichier vide.';
		} elseif ( ! file_exists( $filepath ) ) {
			$error = "Fichier introuvable : {$filepath}";
		} else {
			set_time_limit( 300 );
			$result = pcs_directory_import_jsonl( $filepath, $dry_run );
		}
	}

	?>
	<div class="wrap pcs-directory-admin">
		<h1>Annuaire — Importer JSONL</h1>

		<p class="description" style="max-width:700px">
			Importe un fichier <code>master.jsonl</code> produit par le pipeline <strong>pcs-annuaire-data</strong>.
			Chaque ligne est un établissement. L'import est idempotent (mise à jour si le SIRET existe déjà).
			Les boutiques <code>"public": false</code> (designers/pros) sont automatiquement ignorées.
		</p>

		<?php if ( $error ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $error ); ?></p></div>
		<?php endif; ?>

		<?php if ( $result !== null ) : ?>
			<div class="notice notice-<?php echo empty( $result['errors'] ) ? 'success' : 'warning'; ?>">
				<p>
					<?php echo $dry_run ? '<strong>[DRY RUN — rien n\'a été modifié]</strong> ' : ''; ?>
					Créés : <strong><?php echo (int) $result['created']; ?></strong> &nbsp;|&nbsp;
					Mis à jour : <strong><?php echo (int) $result['updated']; ?></strong> &nbsp;|&nbsp;
					Ignorés (non-publics) : <strong><?php echo (int) $result['skipped']; ?></strong>
					<?php if ( $result['errors'] ) : ?>
						&nbsp;|&nbsp; Erreurs : <strong><?php echo count( $result['errors'] ); ?></strong>
					<?php endif; ?>
				</p>
				<?php if ( $result['errors'] ) : ?>
					<details>
						<summary><?php echo count( $result['errors'] ); ?> erreur(s) — cliquer pour détails</summary>
						<ul style="margin-left:1.5em;list-style:disc">
							<?php foreach ( $result['errors'] as $err ) : ?>
								<li><?php echo esc_html( $err ); ?></li>
							<?php endforeach; ?>
						</ul>
					</details>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<form method="post" style="margin-top:1.5rem">
			<?php wp_nonce_field( 'pcs_import', 'pcs_import_nonce' ); ?>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<label for="jsonl_path">Chemin absolu du fichier JSONL</label>
					</th>
					<td>
						<input
							type="text"
							id="jsonl_path"
							name="jsonl_path"
							value="<?php echo esc_attr( $_POST['jsonl_path'] ?? '/Users/anthonyrusso/pcs-annuaire-data/data/loiret/master.jsonl' ); ?>"
							class="large-text"
							placeholder="/chemin/absolu/vers/master.jsonl"
						/>
						<p class="description">
							Exemple : <code>/Users/anthonyrusso/pcs-annuaire-data/data/loiret/master.jsonl</code>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">Options</th>
					<td>
						<label>
							<input type="checkbox" name="dry_run" value="1" <?php checked( isset( $_POST['dry_run'] ) ); ?> />
							<strong>Dry run</strong> — simuler sans écrire en base
						</label>
					</td>
				</tr>
			</table>

			<?php submit_button( 'Lancer l\'import', 'primary', 'submit', true, [
				'onclick' => "return confirm('Lancer l\\'import JSONL ? Cela peut prendre plusieurs minutes pour un gros fichier.');",
			] ); ?>
		</form>

		<hr />

		<h2>Informations</h2>
		<table class="widefat" style="max-width:600px">
			<tr>
				<th>Boutiques publiées</th>
				<td>
					<?php echo (int) wp_count_posts( PCS_DIR_CPT )->publish; ?>
				</td>
			</tr>
			<tr>
				<th>Dernière mise à jour</th>
				<td><?php echo esc_html( get_option( 'pcs_directory_last_import', '—' ) ); ?></td>
			</tr>
		</table>

		<?php if ( $result !== null && ! $dry_run ) : ?>
			<?php update_option( 'pcs_directory_last_import', current_time( 'mysql' ) ); ?>
		<?php endif; ?>
	</div>
	<?php
}
