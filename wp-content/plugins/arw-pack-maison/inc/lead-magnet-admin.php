<?php
/**
 * Admin settings page : "Lead magnet"
 *
 * Permet à l'admin de :
 *  - Activer/désactiver le lead magnet
 *  - Régler le seuil de blur (par défaut : 3 règles consultées)
 *  - Customiser le texte du modal et de l'email
 *  - Téléverser un PDF différent (override le PDF par défaut)
 *  - Voir le statut du PDF actuel + stats soumissions
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

const ARW_MAISON_LEAD_CAP = 'manage_options';

add_action( 'admin_menu', function () {
	$parent = defined( 'ARW_PULSE_ADMIN_SLUG' ) ? ARW_PULSE_ADMIN_SLUG : 'edit.php?post_type=' . ARW_MAISON_RULE_CPT;
	add_submenu_page(
		$parent,
		__( 'Lead magnet — Compatibilimètre', 'arw-maison' ),
		__( 'Lead magnet', 'arw-maison' ),
		ARW_MAISON_LEAD_CAP,
		'arw-maison-lead',
		'arw_maison_lead_admin_page'
	);
}, 32 );

function arw_maison_lead_admin_page(): void {
	if ( ! current_user_can( ARW_MAISON_LEAD_CAP ) ) { wp_die( __( 'Accès refusé.', 'arw-maison' ) ); }

	$message = '';
	$error   = '';

	// ─── Save settings ─────────────────────────────────────────────────────
	if ( isset( $_POST['arw_lead_settings_nonce'] ) && wp_verify_nonce( $_POST['arw_lead_settings_nonce'], 'arw_lead_settings' ) ) {
		$current = arw_maison_lead_settings();
		$new = [
			'enabled'       => empty( $_POST['enabled'] ) ? 0 : 1,
			'threshold'     => max( 1, min( 10, (int) ( $_POST['threshold'] ?? 3 ) ) ),
			'modal_title'   => sanitize_text_field( wp_unslash( $_POST['modal_title']   ?? $current['modal_title'] ) ),
			'modal_text'    => sanitize_textarea_field( wp_unslash( $_POST['modal_text']  ?? $current['modal_text'] ) ),
			'modal_button'  => sanitize_text_field( wp_unslash( $_POST['modal_button']  ?? $current['modal_button'] ) ),
			'modal_consent' => sanitize_textarea_field( wp_unslash( $_POST['modal_consent'] ?? $current['modal_consent'] ) ),
			'email_subject' => sanitize_text_field( wp_unslash( $_POST['email_subject'] ?? $current['email_subject'] ) ),
			'email_intro'   => sanitize_text_field( wp_unslash( $_POST['email_intro']   ?? $current['email_intro'] ) ),
			'email_body'    => sanitize_textarea_field( wp_unslash( $_POST['email_body'] ?? $current['email_body'] ) ),
			'email_outro'   => sanitize_textarea_field( wp_unslash( $_POST['email_outro'] ?? $current['email_outro'] ) ),
		];
		update_option( ARW_MAISON_LEAD_OPT, $new );
		$message = __( 'Réglages sauvegardés.', 'arw-maison' );
	}

	// ─── Handle PDF upload ─────────────────────────────────────────────────
	if ( isset( $_POST['arw_lead_pdf_nonce'] ) && wp_verify_nonce( $_POST['arw_lead_pdf_nonce'], 'arw_lead_pdf_upload' ) ) {
		if ( ! empty( $_FILES['arw_lead_pdf']['tmp_name'] ) && $_FILES['arw_lead_pdf']['error'] === UPLOAD_ERR_OK ) {
			$tmp      = $_FILES['arw_lead_pdf']['tmp_name'];
			$name     = (string) ( $_FILES['arw_lead_pdf']['name'] ?? '' );
			// Triple validation : magic bytes + filename ext + WP filetype check.
			// mime_content_type seul peut être trompé par un PDF polyglotte (PHP en queue).
			$mime_magic = function_exists( 'mime_content_type' ) ? mime_content_type( $tmp ) : '';
			$ft         = wp_check_filetype_and_ext( $tmp, $name, [ 'pdf' => 'application/pdf' ] );
			$is_pdf     = $mime_magic === 'application/pdf'
				&& ( $ft['type'] ?? '' ) === 'application/pdf'
				&& ( $ft['ext'] ?? '' ) === 'pdf';

			if ( $is_pdf && filesize( $tmp ) <= 5 * MB_IN_BYTES ) {
				$target = arw_maison_lead_pdf_path();
				if ( @move_uploaded_file( $tmp, $target ) ) {
					$message = __( 'PDF mis à jour.', 'arw-maison' );
				} else {
					$error = __( 'Échec de la copie. Vérifie les permissions du dossier uploads.', 'arw-maison' );
				}
			} else {
				$error = __( 'Le fichier doit être un PDF valide de moins de 5 MB.', 'arw-maison' );
			}
		} else {
			$error = __( 'Aucun fichier reçu.', 'arw-maison' );
		}
	}

	// ─── Handle test email ─────────────────────────────────────────────────
	if ( isset( $_POST['arw_lead_test_nonce'] ) && wp_verify_nonce( $_POST['arw_lead_test_nonce'], 'arw_lead_test_email' ) ) {
		$test_email = sanitize_email( wp_unslash( $_POST['test_email'] ?? '' ) );
		if ( $test_email ) {
			// Rate-limit : max 2 emails de test / heure / user. Évite d'utiliser
			// le site comme mail relay (admin trust full mais hygiène + audit-trail).
			$rl_key = 'arw_lead_test_' . get_current_user_id();
			$count  = (int) get_transient( $rl_key );
			if ( $count >= 2 ) {
				$error = __( 'Limite de 2 emails de test par heure atteinte. Réessayez plus tard.', 'arw-maison' );
			} else {
				set_transient( $rl_key, $count + 1, HOUR_IN_SECONDS );
				$test_token = 'TEST' . wp_generate_password( 28, false, false );
				$ok         = arw_maison_lead_send_email( $test_email, $test_token, true );
				$message    = $ok
					? sprintf( __( 'Email de test envoyé à %s. Vérifie la mise en page + délivrabilité. Le lien de téléchargement est volontairement désactivé pour ce test.', 'arw-maison' ), esc_html( $test_email ) )
					: __( 'Échec de l\'envoi. Vérifie la config SMTP / wp_mail.', 'arw-maison' );
				if ( ! $ok ) { $error = $message; $message = ''; }
			}
		}
	}

	$settings = arw_maison_lead_settings();
	$pdf_path = arw_maison_lead_pdf_path();
	$pdf_ok   = file_exists( $pdf_path );
	$pdf_size = $pdf_ok ? size_format( filesize( $pdf_path ) ) : '—';
	$pdf_date = $pdf_ok ? wp_date( 'd/m/Y H:i', filemtime( $pdf_path ) ) : '—';

	// Stats : count submissions of type lead-magnet.
	$count_query = new WP_Query( [
		'post_type'      => 'arw_submission',
		'post_status'    => 'any',
		'posts_per_page' => 1,
		'meta_query'     => [ [ 'key' => 'form_type', 'value' => ARW_MAISON_LEAD_TYPE ] ],
		'no_found_rows'  => false,
	] );
	$total_submissions = (int) $count_query->found_posts;
	wp_reset_postdata();

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Lead magnet — Compatibilimètre', 'arw-maison' ); ?></h1>

		<?php if ( $message ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php echo wp_kses_post( $message ); ?></p></div>
		<?php endif; ?>
		<?php if ( $error ) : ?>
			<div class="notice notice-error is-dismissible"><p><?php echo esc_html( $error ); ?></p></div>
		<?php endif; ?>

		<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;max-width:1200px;margin-top:20px">

			<!-- ─── Settings ──────────────────────────────────────────────── -->
			<form method="post" style="background:#fff;padding:24px;border:1px solid #c3c4c7">
				<?php wp_nonce_field( 'arw_lead_settings', 'arw_lead_settings_nonce' ); ?>

				<h2 style="margin-top:0"><?php esc_html_e( 'Comportement', 'arw-maison' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th><label for="enabled"><?php esc_html_e( 'Lead magnet actif', 'arw-maison' ); ?></label></th>
						<td>
							<label><input type="checkbox" name="enabled" id="enabled" value="1" <?php checked( $settings['enabled'], 1 ); ?>>
								<?php esc_html_e( 'Activer le blur + modal après seuil de consultations', 'arw-maison' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th><label for="threshold"><?php esc_html_e( 'Seuil de déclenchement', 'arw-maison' ); ?></label></th>
						<td>
							<input type="number" name="threshold" id="threshold" value="<?php echo (int) $settings['threshold']; ?>" min="1" max="10" style="width:80px">
							<p class="description"><?php esc_html_e( 'Nombre de règles consultées avant que le blur n\'apparaisse (1-10).', 'arw-maison' ); ?></p>
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Modal de capture', 'arw-maison' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th><label for="modal_title"><?php esc_html_e( 'Titre', 'arw-maison' ); ?></label></th>
						<td><input type="text" name="modal_title" id="modal_title" value="<?php echo esc_attr( $settings['modal_title'] ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th><label for="modal_text"><?php esc_html_e( 'Texte', 'arw-maison' ); ?></label></th>
						<td><textarea name="modal_text" id="modal_text" rows="3" class="large-text"><?php echo esc_textarea( $settings['modal_text'] ); ?></textarea></td>
					</tr>
					<tr>
						<th><label for="modal_button"><?php esc_html_e( 'Texte du bouton', 'arw-maison' ); ?></label></th>
						<td><input type="text" name="modal_button" id="modal_button" value="<?php echo esc_attr( $settings['modal_button'] ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th><label for="modal_consent"><?php esc_html_e( 'Mention RGPD', 'arw-maison' ); ?></label></th>
						<td><textarea name="modal_consent" id="modal_consent" rows="2" class="large-text"><?php echo esc_textarea( $settings['modal_consent'] ); ?></textarea></td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Email envoyé au visiteur', 'arw-maison' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th><label for="email_subject"><?php esc_html_e( 'Sujet', 'arw-maison' ); ?></label></th>
						<td><input type="text" name="email_subject" id="email_subject" value="<?php echo esc_attr( $settings['email_subject'] ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th><label for="email_intro"><?php esc_html_e( 'Introduction', 'arw-maison' ); ?></label></th>
						<td><input type="text" name="email_intro" id="email_intro" value="<?php echo esc_attr( $settings['email_intro'] ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th><label for="email_body"><?php esc_html_e( 'Corps', 'arw-maison' ); ?></label></th>
						<td><textarea name="email_body" id="email_body" rows="2" class="large-text"><?php echo esc_textarea( $settings['email_body'] ); ?></textarea></td>
					</tr>
					<tr>
						<th><label for="email_outro"><?php esc_html_e( 'Conclusion', 'arw-maison' ); ?></label></th>
						<td><textarea name="email_outro" id="email_outro" rows="2" class="large-text"><?php echo esc_textarea( $settings['email_outro'] ); ?></textarea></td>
					</tr>
				</table>

				<p>
					<button type="submit" class="button button-primary"><?php esc_html_e( 'Enregistrer les réglages', 'arw-maison' ); ?></button>
				</p>
			</form>

			<!-- ─── Sidebar : PDF + stats + test ──────────────────────────── -->
			<div>
				<div style="background:#fff;padding:20px;border:1px solid #c3c4c7;margin-bottom:16px">
					<h3 style="margin-top:0"><?php esc_html_e( 'PDF actuel', 'arw-maison' ); ?></h3>
					<p style="margin:0 0 4px"><?php echo $pdf_ok ? '<span style="color:#1f3a2e">✓ PDF présent</span>' : '<span style="color:#d63638">⚠ PDF absent</span>'; ?></p>
					<p style="margin:0 0 4px;font-size:13px;color:#5b5648"><?php esc_html_e( 'Taille', 'arw-maison' ); ?> : <?php echo esc_html( $pdf_size ); ?></p>
					<p style="margin:0 0 16px;font-size:13px;color:#5b5648"><?php esc_html_e( 'Modifié', 'arw-maison' ); ?> : <?php echo esc_html( $pdf_date ); ?></p>

					<form method="post" enctype="multipart/form-data">
						<?php wp_nonce_field( 'arw_lead_pdf_upload', 'arw_lead_pdf_nonce' ); ?>
						<p style="margin:0 0 8px"><strong><?php esc_html_e( 'Remplacer le PDF', 'arw-maison' ); ?></strong></p>
						<input type="file" name="arw_lead_pdf" accept="application/pdf" required>
						<p style="margin:8px 0 12px;font-size:12px;color:#5b5648"><?php esc_html_e( 'Max 5 MB. Type PDF uniquement.', 'arw-maison' ); ?></p>
						<button type="submit" class="button"><?php esc_html_e( 'Téléverser', 'arw-maison' ); ?></button>
					</form>
				</div>

				<div style="background:#fff;padding:20px;border:1px solid #c3c4c7;margin-bottom:16px">
					<h3 style="margin-top:0"><?php esc_html_e( 'Statistiques', 'arw-maison' ); ?></h3>
					<p style="margin:0;font-size:32px;font-weight:300;font-family:Georgia,serif;line-height:1"><?php echo number_format_i18n( $total_submissions ); ?></p>
					<p style="margin:4px 0 0;font-size:13px;color:#5b5648;letter-spacing:0.06em;text-transform:uppercase"><?php esc_html_e( 'Captures email totales', 'arw-maison' ); ?></p>
				</div>

				<div style="background:#fff;padding:20px;border:1px solid #c3c4c7">
					<h3 style="margin-top:0"><?php esc_html_e( 'Tester l\'email', 'arw-maison' ); ?></h3>
					<form method="post">
						<?php wp_nonce_field( 'arw_lead_test_email', 'arw_lead_test_nonce' ); ?>
						<input type="email" name="test_email" placeholder="ton.email@exemple.fr" class="regular-text" required style="margin-bottom:8px">
						<button type="submit" class="button"><?php esc_html_e( 'Envoyer un test', 'arw-maison' ); ?></button>
					</form>
				</div>
			</div>

		</div>
	</div>
	<?php
}
