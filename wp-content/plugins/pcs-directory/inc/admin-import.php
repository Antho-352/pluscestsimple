<?php
/**
 * Page admin : Annuaire > Importer depuis Sirene.
 *
 * Deux modes :
 *   - Import ciblé (synchrone) : un département / un plafond, pour tester la qualité.
 *   - Import national complet (asynchrone) : parcourt les 101 départements en
 *     tâche de fond, résumable, sans limite de 500 (cf. inc/import-national.php).
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

	// ─── Handlers import national ───────────────────────────────────────────
	$nat_notice = '';
	if (
		isset( $_POST['pcs_national_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pcs_national_nonce'] ) ), 'pcs_national' )
	) {
		$nat_action = isset( $_POST['nat_action'] ) ? sanitize_key( wp_unslash( $_POST['nat_action'] ) ) : '';
		switch ( $nat_action ) {
			case 'start':
				$nat_naf    = isset( $_POST['nat_naf'] ) ? sanitize_text_field( wp_unslash( $_POST['nat_naf'] ) ) : PCS_DIR_DEFAULT_NAF;
				$nat_enrich = ! empty( $_POST['nat_enrich_osm'] );
				pcs_directory_national_start( $nat_naf, $nat_enrich );
				$nat_notice = __( 'Import national démarré. Il tourne en tâche de fond — tu peux fermer cette page, il continue.', 'pluscestsimple' );
				break;
			case 'pause':
				pcs_directory_national_stop( false );
				$nat_notice = __( 'Import mis en pause. Tu peux le reprendre quand tu veux.', 'pluscestsimple' );
				break;
			case 'resume':
				pcs_directory_national_resume();
				$nat_notice = __( 'Import repris.', 'pluscestsimple' );
				break;
			case 'reset':
				pcs_directory_national_stop( true );
				$nat_notice = __( 'État réinitialisé.', 'pluscestsimple' );
				break;
		}
	}
	$nat_state = pcs_directory_national_get_state();

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

		<?php if ( $nat_notice ) : ?>
			<div class="notice notice-info"><p><?php echo esc_html( $nat_notice ); ?></p></div>
		<?php endif; ?>

		<?php
		$nat_running = ( 'running' === $nat_state['status'] );
		$nat_paused  = ( 'paused' === $nat_state['status'] );
		$nat_done    = ( 'done' === $nat_state['status'] );
		$nat_active  = $nat_running || $nat_paused;
		?>
		<div id="pcs-national" style="background:#fff;border:1px solid #c3c4c7;border-left:4px solid #1f3a2e;padding:20px;margin:20px 0;max-width:900px">
			<h2 style="margin-top:0"><?php esc_html_e( '🇫🇷 Import national complet', 'pluscestsimple' ); ?></h2>
			<p class="description" style="max-width:760px">
				<?php esc_html_e( 'Parcourt automatiquement les 101 départements (le seul moyen de dépasser le plafond de 10 000 résultats de l\'API). Tourne en tâche de fond, résumable. Compte plusieurs heures pour la France entière — tu peux fermer la page, ça continue.', 'pluscestsimple' ); ?>
			</p>

			<!-- Barre de progression (remplie en JS) -->
			<div id="pcs-nat-progress" style="<?php echo $nat_active || $nat_done ? '' : 'display:none'; ?>margin:16px 0">
				<div style="background:#e2e4e7;border-radius:4px;overflow:hidden;height:22px">
					<div id="pcs-nat-bar" style="background:#1f3a2e;height:100%;width:<?php echo (int) ( $nat_state['total_depts'] ? round( $nat_state['done_depts'] / $nat_state['total_depts'] * 100 ) : 0 ); ?>%;transition:width .4s"></div>
				</div>
				<p id="pcs-nat-stats" style="margin:8px 0 0;font-size:13px;color:#3c434a">
					<?php esc_html_e( 'Chargement du statut…', 'pluscestsimple' ); ?>
				</p>
			</div>

			<form method="post" style="margin-top:12px">
				<?php wp_nonce_field( 'pcs_national', 'pcs_national_nonce' ); ?>

				<?php if ( ! $nat_active ) : ?>
					<table class="form-table" role="presentation" style="margin-top:0">
						<tr>
							<th scope="row" style="width:160px"><label for="nat_naf"><?php esc_html_e( 'Code(s) NAF', 'pluscestsimple' ); ?></label></th>
							<td><input type="text" id="nat_naf" name="nat_naf" value="<?php echo esc_attr( PCS_DIR_DEFAULT_NAF ); ?>" class="regular-text" /></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Enrichissement', 'pluscestsimple' ); ?></th>
							<td><label><input type="checkbox" name="nat_enrich_osm" value="1" /> <?php esc_html_e( 'OSM (déconseillé en national — très lent, à faire en passe séparée).', 'pluscestsimple' ); ?></label></td>
						</tr>
					</table>
					<button type="submit" name="nat_action" value="start" class="button button-primary"
						onclick="return confirm('<?php echo esc_js( __( 'Démarrer l\'import national complet ? Plusieurs heures en tâche de fond.', 'pluscestsimple' ) ); ?>');">
						<?php esc_html_e( 'Démarrer l\'import national', 'pluscestsimple' ); ?>
					</button>
				<?php else : ?>
					<?php if ( $nat_running ) : ?>
						<button type="submit" name="nat_action" value="pause" class="button"><?php esc_html_e( '⏸ Mettre en pause', 'pluscestsimple' ); ?></button>
					<?php else : ?>
						<button type="submit" name="nat_action" value="resume" class="button button-primary"><?php esc_html_e( '▶ Reprendre', 'pluscestsimple' ); ?></button>
					<?php endif; ?>
					<button type="submit" name="nat_action" value="reset" class="button button-link-delete"
						onclick="return confirm('<?php echo esc_js( __( 'Réinitialiser l\'état de l\'import ? (n\'efface pas les magasins déjà importés)', 'pluscestsimple' ) ); ?>');">
						<?php esc_html_e( 'Réinitialiser', 'pluscestsimple' ); ?>
					</button>
				<?php endif; ?>
			</form>
		</div>

		<script>
		(function(){
			var bar = document.getElementById('pcs-nat-bar');
			var stats = document.getElementById('pcs-nat-stats');
			var wrap = document.getElementById('pcs-nat-progress');
			if (!stats) return;
			var ajaxurl = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';
			var nonce = '<?php echo esc_js( wp_create_nonce( 'pcs_national_status' ) ); ?>';
			function fmt(n){ return new Intl.NumberFormat('fr-FR').format(n); }
			function poll(){
				var body = new URLSearchParams();
				body.set('action','pcs_national_status');
				body.set('nonce',nonce);
				fetch(ajaxurl,{method:'POST',credentials:'same-origin',body:body})
					.then(function(r){return r.json();})
					.then(function(j){
						if(!j||!j.success){return;}
						var d=j.data;
						if(d.status==='idle'){ wrap.style.display='none'; return; }
						wrap.style.display='';
						if(bar){ bar.style.width=(d.pct||0)+'%'; }
						var label;
						if(d.status==='done'){
							label='✅ Terminé — '+fmt(d.imported)+' magasins importés, '+fmt(d.skipped)+' ignorés (doublons), '+fmt(d.errors)+' erreurs · '+d.elapsed_min+' min.';
							bar.style.background='#46b450';
						} else if(d.status==='paused'){
							label='⏸ En pause — '+fmt(d.imported)+' importés · département '+d.done_depts+'/'+d.total_depts+'.';
						} else {
							label='⏳ En cours — dép. '+(d.current_dept||'…')+' (page '+d.current_page+') · '
								+d.done_depts+'/'+d.total_depts+' départements · '
								+fmt(d.imported)+' importés, '+fmt(d.skipped)+' ignorés · '+d.elapsed_min+' min.';
						}
						if(d.last_error){ label+=' ⚠ '+d.last_error; }
						stats.textContent=label;
						if(d.status==='running'){ setTimeout(poll, 4000); }
						else if(d.status==='paused'){ setTimeout(poll, 8000); }
					})
					.catch(function(){ setTimeout(poll, 8000); });
			}
			<?php if ( $nat_active || $nat_done ) : ?>poll();<?php endif; ?>
		})();
		</script>

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

		<h2><?php esc_html_e( 'Import ciblé (test qualité)', 'pluscestsimple' ); ?></h2>
		<p class="description" style="max-width:760px">
			<?php esc_html_e( 'Pour tester un département ou un NAF précis avant de lancer le national. Plafonné à 500 par lancement. Pour la France entière, utilise l\'import national ci-dessus.', 'pluscestsimple' ); ?>
		</p>

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
