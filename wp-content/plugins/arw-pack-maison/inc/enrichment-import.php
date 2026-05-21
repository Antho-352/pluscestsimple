<?php
/**
 * Enrichment v2 importer.
 *
 * Lit le fichier `assets/data/enrichment.json` livré avec le pack et applique
 * les 5 sections enrichies (diagnostic, cost, pro_order, alternatives_long,
 * common_mistakes) à chaque règle correspondante.
 *
 * Matching : par sanitize_title(rule_title) → post_name. Idempotent — si une
 * règle a déjà ses méta enrichis, on les met à jour (ou skip si l'option
 * "écraser" est décochée).
 *
 * Une admin page sous Compatibilimètre → Enrichissement.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

const ARW_MAISON_ENRICH_OPT  = 'arw_maison_enrich_imported_at';
const ARW_MAISON_ENRICH_FILE = 'assets/data/enrichment.json';

const ARW_MAISON_ENRICH_FIELDS = [
	'diagnostic'        => '_arw_rule_diagnostic',
	'cost'              => '_arw_rule_cost',
	'pro_order'         => '_arw_rule_pro_order',
	'alternatives_long' => '_arw_rule_alternatives_long',
	'common_mistakes'   => '_arw_rule_common_mistakes',
];

// ─── Admin menu ──────────────────────────────────────────────────────────────

add_action( 'admin_menu', function () {
	$parent = 'edit.php?post_type=' . ARW_MAISON_RULE_CPT;
	add_submenu_page(
		$parent,
		__( 'Enrichissement v2', 'arw-maison' ),
		__( 'Enrichissement', 'arw-maison' ),
		'manage_options',
		'arw-maison-enrichment',
		'arw_maison_enrichment_admin_page'
	);
}, 33 );

function arw_maison_enrichment_file_path(): string {
	return ARW_MAISON_DIR . '/' . ARW_MAISON_ENRICH_FILE;
}

function arw_maison_enrichment_load(): array {
	$path = arw_maison_enrichment_file_path();
	if ( ! file_exists( $path ) ) { return []; }
	$raw = file_get_contents( $path );
	if ( ! $raw ) { return []; }
	$data = json_decode( $raw, true );
	if ( ! is_array( $data ) || empty( $data['rules'] ) || ! is_array( $data['rules'] ) ) { return []; }
	return $data['rules'];
}

/**
 * Run the importer.
 *
 * @param bool $overwrite If true, écrase les valeurs existantes. Sinon, ne
 *                        remplit que les méta vides.
 * @param bool $dry_run   Si true, ne touche pas la DB, retourne juste le rapport.
 * @return array Rapport: ['total','matched','updated','skipped','not_found','errors']
 */
function arw_maison_enrichment_run( bool $overwrite = false, bool $dry_run = false ): array {
	$rules    = arw_maison_enrichment_load();
	$report   = [
		'total'           => count( $rules ),
		'matched'         => 0,
		'updated'         => 0,
		'skipped'         => 0,
		'not_found'       => 0,
		'errors'          => 0,
		'missing_titles'  => [],
	];

	if ( empty( $rules ) ) { return $report; }

	foreach ( $rules as $rule ) {
		$title = (string) ( $rule['title'] ?? '' );
		if ( ! $title ) { $report['errors']++; continue; }

		// Match by post_name (= sanitize_title of title at creation).
		$slug = sanitize_title( $title );

		$matches = get_posts( [
			'post_type'      => ARW_MAISON_RULE_CPT,
			'name'           => $slug,
			'posts_per_page' => 1,
			'post_status'    => 'any',
			'no_found_rows'  => true,
			'fields'         => 'ids',
		] );

		if ( empty( $matches ) ) {
			// Fallback: exact title search (rare case if post_name was edited manually).
			$matches = get_posts( [
				'post_type'      => ARW_MAISON_RULE_CPT,
				'title'          => $title,
				'posts_per_page' => 1,
				'post_status'    => 'any',
				'no_found_rows'  => true,
				'fields'         => 'ids',
			] );
		}

		if ( empty( $matches ) ) {
			$report['not_found']++;
			$report['missing_titles'][] = $title;
			continue;
		}

		$post_id = (int) $matches[0];
		$report['matched']++;

		$any_changed = false;
		foreach ( ARW_MAISON_ENRICH_FIELDS as $json_key => $meta_key ) {
			$value = isset( $rule[ $json_key ] ) ? trim( (string) $rule[ $json_key ] ) : '';
			if ( ! $value ) { continue; }

			$existing = (string) get_post_meta( $post_id, $meta_key, true );
			if ( $existing && ! $overwrite ) { continue; }
			if ( $existing === $value ) { continue; }

			if ( ! $dry_run ) {
				update_post_meta( $post_id, $meta_key, sanitize_textarea_field( $value ) );
			}
			$any_changed = true;
		}

		if ( $any_changed ) {
			$report['updated']++;
		} else {
			$report['skipped']++;
		}
	}

	if ( ! $dry_run ) {
		update_option( ARW_MAISON_ENRICH_OPT, gmdate( 'c' ) );
	}

	return $report;
}

// ─── Admin page render ───────────────────────────────────────────────────────

function arw_maison_enrichment_admin_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Accès refusé.' ); }

	$message = '';
	$report  = null;

	if ( isset( $_POST['arw_enrich_nonce'] ) && wp_verify_nonce( $_POST['arw_enrich_nonce'], 'arw_enrich_run' ) ) {
		$overwrite = ! empty( $_POST['overwrite'] );
		$dry_run   = isset( $_POST['action'] ) && $_POST['action'] === 'dry_run';
		$report    = arw_maison_enrichment_run( $overwrite, $dry_run );
		$message   = $dry_run
			? __( 'Simulation terminée — rien n\'a été écrit.', 'arw-maison' )
			: __( 'Import terminé.', 'arw-maison' );
	}

	$file_path   = arw_maison_enrichment_file_path();
	$file_exists = file_exists( $file_path );
	$file_size   = $file_exists ? size_format( filesize( $file_path ) ) : '—';
	$rules       = arw_maison_enrichment_load();
	$rule_count  = count( $rules );
	$last_run    = get_option( ARW_MAISON_ENRICH_OPT, '' );

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Enrichissement v2 — Compatibilimètre', 'arw-maison' ); ?></h1>

		<p style="max-width:780px;color:#5b5648">
			<?php esc_html_e( 'Importe les 5 sections enrichies (diagnostic, coût, qui appeler, alternatives détaillées, erreurs fréquentes) générées par IA pour chaque règle. Le matching se fait par titre exact. Idempotent — tu peux relancer plusieurs fois sans risque.', 'arw-maison' ); ?>
		</p>

		<?php if ( $message ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>
		<?php endif; ?>

		<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;max-width:1100px;margin-top:20px">

			<!-- ─── Importer ─────────────────────────────────────── -->
			<div style="background:#fff;padding:24px;border:1px solid #c3c4c7">

				<h2 style="margin-top:0"><?php esc_html_e( 'Lancer l\'import', 'arw-maison' ); ?></h2>

				<?php if ( ! $file_exists ) : ?>
					<div class="notice notice-error inline" style="margin:0 0 16px"><p>
						<?php esc_html_e( 'Fichier enrichment.json introuvable.', 'arw-maison' ); ?>
						<code><?php echo esc_html( ARW_MAISON_ENRICH_FILE ); ?></code>
					</p></div>
				<?php else : ?>

					<form method="post">
						<?php wp_nonce_field( 'arw_enrich_run', 'arw_enrich_nonce' ); ?>

						<p>
							<label>
								<input type="checkbox" name="overwrite" value="1">
								<?php esc_html_e( 'Écraser les valeurs existantes', 'arw-maison' ); ?>
							</label>
							<br>
							<span style="font-size:12px;color:#6b7280">
								<?php esc_html_e( 'Décoché : ne remplit que les méta vides (recommandé pour le 1er import).', 'arw-maison' ); ?>
							</span>
						</p>

						<p style="margin-top:20px">
							<button type="submit" name="action" value="dry_run" class="button">
								<?php esc_html_e( 'Simulation (dry-run)', 'arw-maison' ); ?>
							</button>
							<button type="submit" name="action" value="run" class="button button-primary" style="margin-left:8px">
								<?php esc_html_e( 'Importer pour de bon', 'arw-maison' ); ?>
							</button>
						</p>
					</form>

				<?php endif; ?>

				<?php if ( $report ) : ?>
					<h3 style="margin-top:32px"><?php esc_html_e( 'Rapport', 'arw-maison' ); ?></h3>
					<table class="widefat striped" style="max-width:520px">
						<tr><th><?php esc_html_e( 'Règles dans le JSON', 'arw-maison' ); ?></th><td><?php echo (int) $report['total']; ?></td></tr>
						<tr><th><?php esc_html_e( 'Trouvées en DB', 'arw-maison' ); ?></th><td style="color:#1f3a2e"><?php echo (int) $report['matched']; ?></td></tr>
						<tr><th><?php esc_html_e( 'Mises à jour', 'arw-maison' ); ?></th><td style="color:#1f3a2e;font-weight:600"><?php echo (int) $report['updated']; ?></td></tr>
						<tr><th><?php esc_html_e( 'Sautées (déjà remplies)', 'arw-maison' ); ?></th><td><?php echo (int) $report['skipped']; ?></td></tr>
						<tr><th><?php esc_html_e( 'Non trouvées', 'arw-maison' ); ?></th><td style="color:<?php echo $report['not_found'] ? '#d63638' : '#5b5648'; ?>"><?php echo (int) $report['not_found']; ?></td></tr>
					</table>

					<?php if ( ! empty( $report['missing_titles'] ) ) : ?>
						<details style="margin-top:12px">
							<summary style="cursor:pointer;color:#d63638"><?php printf( esc_html__( '%d règles non trouvées en base (cliquer pour voir)', 'arw-maison' ), count( $report['missing_titles'] ) ); ?></summary>
							<ul style="margin:8px 0 0 20px;color:#5b5648;font-size:13px">
								<?php foreach ( $report['missing_titles'] as $t ) : ?>
									<li><?php echo esc_html( $t ); ?></li>
								<?php endforeach; ?>
							</ul>
						</details>
					<?php endif; ?>
				<?php endif; ?>

			</div>

			<!-- ─── Sidebar : status ─────────────────────────────── -->
			<div>
				<div style="background:#fff;padding:20px;border:1px solid #c3c4c7;margin-bottom:16px">
					<h3 style="margin-top:0"><?php esc_html_e( 'Fichier source', 'arw-maison' ); ?></h3>
					<p style="margin:0 0 4px"><?php echo $file_exists ? '<span style="color:#1f3a2e">✓ présent</span>' : '<span style="color:#d63638">⚠ absent</span>'; ?></p>
					<p style="margin:0 0 4px;font-size:13px;color:#5b5648"><?php esc_html_e( 'Taille', 'arw-maison' ); ?> : <?php echo esc_html( $file_size ); ?></p>
					<p style="margin:0 0 4px;font-size:13px;color:#5b5648"><?php esc_html_e( 'Règles', 'arw-maison' ); ?> : <strong><?php echo (int) $rule_count; ?></strong></p>
				</div>

				<div style="background:#fff;padding:20px;border:1px solid #c3c4c7">
					<h3 style="margin-top:0"><?php esc_html_e( 'Dernier import', 'arw-maison' ); ?></h3>
					<p style="margin:0;font-size:13px;color:#5b5648">
						<?php echo $last_run ? esc_html( wp_date( 'd/m/Y H:i', strtotime( $last_run ) ) ) : esc_html__( 'jamais lancé', 'arw-maison' ); ?>
					</p>
				</div>
			</div>

		</div>
	</div>
	<?php
}
