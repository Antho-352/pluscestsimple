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

		$publish_now = ! empty( $_POST['publish_now'] );

		if ( '' !== $filepath && '' === $error ) {
			set_time_limit( 300 );
			$result = pcs_directory_import_jsonl( $filepath, $dry_run, $publish_now );
		}
	}

	// ── Purge totale (repartir de zéro) ────────────────────────────────────────
	$purged = null;
	if (
		isset( $_POST['pcs_purge_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pcs_purge_nonce'] ) ), 'pcs_purge' )
		&& ! empty( $_POST['pcs_purge_confirm'] )
	) {
		$purged = pcs_directory_purge_all();
	}

	// ── Publication par lots (drip) ─────────────────────────────────────────────
	$published_batch = null;
	if (
		isset( $_POST['pcs_publish_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pcs_publish_nonce'] ) ), 'pcs_publish' )
	) {
		$batch_n = isset( $_POST['batch_size'] ) ? max( 1, min( 1000, (int) $_POST['batch_size'] ) ) : 100;
		set_time_limit( 300 );
		$published_batch = pcs_directory_publish_batch( $batch_n );
	}

	$status_counts = pcs_directory_count_by_status();

	?>
	<div class="wrap pcs-directory-admin">
		<h1>Annuaire — Importer JSONL</h1>

		<p class="description" style="max-width:740px">
			Importe un fichier <code>master.jsonl</code> produit par le pipeline <strong>pcs-annuaire-data</strong>.
			L'import est idempotent (mise à jour si le place_id/SIRET existe déjà).
			Par défaut, les nouvelles boutiques arrivent en <strong>brouillon</strong> — tu les publies ensuite
			par lots de 100 (section « Publication par lots » plus bas) pour ne pas créer toutes les pages d'un coup.
		</p>

		<?php if ( $error ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $error ); ?></p></div>
		<?php endif; ?>

		<?php if ( $published_batch !== null ) : ?>
			<div class="notice notice-success"><p><strong><?php echo (int) $published_batch; ?></strong> boutiques publiées. Leurs pages (boutique + villes/départements concernés) apparaîtront dans le sitemap.</p></div>
		<?php endif; ?>

		<?php if ( $purged !== null ) : ?>
			<div class="notice notice-success"><p><strong><?php echo (int) $purged; ?></strong> boutiques supprimées. L'annuaire est vide, prêt pour un import propre.</p></div>
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

			<p class="description" style="margin:.4rem 0 1rem">
				<label>
					<input type="checkbox" name="publish_now" value="1" <?php checked( ! empty( $_POST['publish_now'] ) ); ?> />
					<strong>Publier immédiatement</strong> (sinon : brouillon, à publier par lots de 100 ci-dessous)
				</label>
			</p>
			<?php submit_button( 'Lancer l\'import', 'primary' ); ?>
		</form>

		<hr />

		<h2>📦 Publication par lots (drip)</h2>
		<p class="description" style="max-width:740px">
			Pour ne pas créer toutes les pages d'un coup (mauvais signal Google), publie les boutiques
			progressivement — par exemple <strong>100 tous les 3-4 jours</strong>. Seules les boutiques
			publiées apparaissent sur le site et dans le sitemap.
		</p>

		<table class="widefat striped" style="max-width:500px;margin-bottom:1rem">
			<tr>
				<th>✅ Publiées (en ligne)</th>
				<td><strong style="font-size:1.2em"><?php echo (int) $status_counts['publish']; ?></strong></td>
			</tr>
			<tr>
				<th>📝 En brouillon (en attente)</th>
				<td><strong style="font-size:1.2em"><?php echo (int) $status_counts['draft']; ?></strong></td>
			</tr>
			<tr>
				<th>Dernier import</th>
				<td><?php echo esc_html( get_option( 'pcs_directory_last_import', '—' ) ); ?></td>
			</tr>
		</table>

		<?php if ( $status_counts['draft'] > 0 ) : ?>
			<form method="post">
				<?php wp_nonce_field( 'pcs_publish', 'pcs_publish_nonce' ); ?>
				<label>Taille du lot :
					<input type="number" name="batch_size" value="100" min="1" max="1000" class="small-text" />
				</label>
				<?php submit_button( 'Publier le prochain lot', 'primary', 'submit', false, [
					'onclick' => "return confirm('Publier ce lot de boutiques maintenant ?');",
				] ); ?>
				<span class="description">— publie les <?php echo (int) min( 100, $status_counts['draft'] ); ?> plus anciennes en brouillon.</span>
			</form>
		<?php else : ?>
			<p><em>Aucune boutique en brouillon. Importe un fichier (sans « publier immédiatement ») pour en mettre en file.</em></p>
		<?php endif; ?>

		<hr />

		<h2 style="color:#b32d2e">Zone dangereuse — repartir de zéro</h2>
		<p class="description" style="max-width:700px">
			Supprime <strong>toutes</strong> les boutiques de l'annuaire (irréversible).
			À utiliser avant un import 100% Google pour ne pas mélanger avec l'ancienne base SIRENE.
		</p>
		<form method="post" onsubmit="return confirm('Supprimer DÉFINITIVEMENT toutes les boutiques de l\'annuaire ?');">
			<?php wp_nonce_field( 'pcs_purge', 'pcs_purge_nonce' ); ?>
			<label><input type="checkbox" name="pcs_purge_confirm" value="1" /> Je confirme vouloir tout supprimer</label><br><br>
			<button type="submit" class="button button-link-delete">Supprimer toutes les boutiques</button>
		</form>
	</div>
	<?php
}

/**
 * Supprime tous les posts pcs_boutique (force delete).
 *
 * @return int Nombre de boutiques supprimées.
 */
function pcs_directory_purge_all(): int {
	set_time_limit( 600 );
	$total = 0;
	do {
		$ids = get_posts( [
			'post_type'      => PCS_DIR_CPT,
			'post_status'    => 'any',
			'posts_per_page' => 200,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		] );
		foreach ( $ids as $id ) {
			wp_delete_post( $id, true );
			$total++;
		}
	} while ( ! empty( $ids ) );
	return $total;
}
