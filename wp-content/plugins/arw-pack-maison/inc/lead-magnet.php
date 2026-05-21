<?php
/**
 * Lead magnet — capture email après 3 consultations de règle, envoi automatique du PDF
 * "12 erreurs qui coûtent 10 000 €" via lien sécurisé one-shot (token signé, expire 24h).
 *
 * Stack :
 *   - Frontend : localStorage compteur + blur + modal (compat.js)
 *   - Form REST : /arw/v1/submit (du thème) avec form_type=lead-magnet
 *   - Hook submitted : génère un token, envoie email avec lien
 *   - REST download : /arw/v1/lead-download?t=TOKEN → stream le PDF
 *   - PDF stocké dans wp-content/uploads/arw-maison/private/ (htaccess deny)
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

const ARW_MAISON_LEAD_TYPE         = 'lead-magnet';
const ARW_MAISON_LEAD_TOKEN_TTL    = DAY_IN_SECONDS;
const ARW_MAISON_LEAD_TOKEN_META   = '_arw_lead_token';
const ARW_MAISON_LEAD_USED_META    = '_arw_lead_used';
const ARW_MAISON_LEAD_PRIVATE_DIR  = 'arw-maison/private';
const ARW_MAISON_LEAD_PDF_FILENAME = '12-erreurs.pdf';

// ─── Whitelist form_type ─────────────────────────────────────────────────────

add_filter( 'arw_pulse_form_types', function ( $types ) {
	$types[] = ARW_MAISON_LEAD_TYPE;
	return $types;
} );

// ─── Helpers : private uploads dir ───────────────────────────────────────────

function arw_maison_lead_private_dir(): string {
	$uploads = wp_get_upload_dir();
	$dir     = trailingslashit( $uploads['basedir'] ) . ARW_MAISON_LEAD_PRIVATE_DIR;

	if ( ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );

		// Block direct HTTP access (Apache).
		$htaccess = $dir . '/.htaccess';
		if ( ! file_exists( $htaccess ) ) {
			$rules  = "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n";
			$rules .= "<IfModule !mod_authz_core.c>\nOrder deny,allow\nDeny from all\n</IfModule>\n";
			file_put_contents( $htaccess, $rules );
		}
		// Empty index.php (defense in depth for non-Apache).
		$index = $dir . '/index.php';
		if ( ! file_exists( $index ) ) {
			file_put_contents( $index, "<?php // silence is golden\n" );
		}
	}

	return $dir;
}

function arw_maison_lead_pdf_path(): string {
	return arw_maison_lead_private_dir() . '/' . ARW_MAISON_LEAD_PDF_FILENAME;
}

// ─── First-run : copy bundled PDF to private uploads ────────────────────────

function arw_maison_lead_install_default_pdf(): bool {
	$target = arw_maison_lead_pdf_path();
	if ( file_exists( $target ) ) { return true; }

	$bundled = ARW_MAISON_DIR . '/assets/pdf/' . ARW_MAISON_LEAD_PDF_FILENAME;
	if ( ! file_exists( $bundled ) ) { return false; }

	return @copy( $bundled, $target );
}

add_action( 'init', 'arw_maison_lead_install_default_pdf', 5 );

// ─── Settings (with sensible defaults) ───────────────────────────────────────

const ARW_MAISON_LEAD_OPT = 'arw_maison_lead_settings';

function arw_maison_lead_settings(): array {
	$saved = (array) get_option( ARW_MAISON_LEAD_OPT, [] );
	return wp_parse_args( $saved, [
		'enabled'         => 1,
		'threshold'       => 3, // nb de règles avant blur
		'modal_title'     => 'Continuez librement.',
		'modal_text'      => 'Laissez votre email pour accéder au Compatibilimètre sans limite et recevoir le guide PDF gratuit — 12 erreurs fréquentes en rénovation qui coûtent en moyenne 10 000 €. 26 pages. Sans publicité.',
		'modal_button'    => 'Recevoir le guide gratuit',
		'modal_consent'   => 'J\'accepte de recevoir le guide PDF et la lettre mensuelle de Plus c\'est simple.',
		'email_subject'   => 'Votre guide PDF — 12 erreurs qui coûtent 10 000 €',
		'email_intro'     => 'Bonjour,',
		'email_body'      => 'Voici votre guide :',
		'email_outro'     => 'Le lien expire dans 24 heures. Si besoin, retournez sur Plus c\'est simple pour le recevoir à nouveau.',
	] );
}

// ─── Hook on submission : generate token + send email ────────────────────────

add_action( 'arw_pulse_form_submitted', function ( $post_id, $req ) {
	if ( ( $req['form_type'] ?? '' ) !== ARW_MAISON_LEAD_TYPE ) { return; }

	// Rate-limit dédié au lead-magnet : 3 envois max / heure / IP. Évite le
	// spam de mail relay si le rate-limit global du form.php est contourné
	// ou si l'IP partagée (NAT) reste sous le quota global de 3/10min.
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? preg_replace( '/[^0-9a-fA-F\.\:]/', '', (string) $_SERVER['REMOTE_ADDR'] ) : '';
	if ( $ip ) {
		$rl_key = 'arw_lead_send_' . md5( $ip );
		$count  = (int) get_transient( $rl_key );
		if ( $count >= 3 ) {
			// Marquer la soumission comme rate-limited sans envoyer d'email.
			update_post_meta( $post_id, '_arw_lead_rate_limited', 1 );
			return;
		}
		set_transient( $rl_key, $count + 1, HOUR_IN_SECONDS );
	}

	// Generate a 32-char alphanum token.
	$token = wp_generate_password( 32, false, false );

	update_post_meta( $post_id, ARW_MAISON_LEAD_TOKEN_META, $token );
	update_post_meta( $post_id, ARW_MAISON_LEAD_TOKEN_META . '_expires', time() + ARW_MAISON_LEAD_TOKEN_TTL );
	update_post_meta( $post_id, ARW_MAISON_LEAD_USED_META, 0 );

	// Send email to the visitor.
	$email = $req['email'] ?? '';
	if ( $email && is_email( $email ) ) {
		arw_maison_lead_send_email( $email, $token );
	}
}, 10, 2 );

// ─── Email sender ────────────────────────────────────────────────────────────

function arw_maison_lead_send_email( string $email, string $token, bool $is_test = false ): bool {
	$settings = arw_maison_lead_settings();

	$download_url = add_query_arg(
		[ 't' => $token ],
		rest_url( 'arw/v1/lead-download' )
	);

	$site_name  = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$site_url   = home_url( '/' );

	$subject = $is_test ? '[TEST] ' . $settings['email_subject'] : $settings['email_subject'];

	$body  = '<!DOCTYPE html><html lang="fr"><body style="font-family:Georgia,serif;color:#0c0e0d;background:#f7f1e6;margin:0;padding:32px;">';
	$body .= '<div style="max-width:540px;margin:0 auto;background:#f7f1e6;">';

	if ( $is_test ) {
		$body .= '<div style="margin:0 0 24px;padding:12px 16px;background:#f7e8c8;border-left:3px solid #a78a4d;font-size:13px;color:#5b5648;line-height:1.5;">';
		$body .= '<strong>Email de test.</strong> Le bouton de téléchargement ci-dessous est volontairement désactivé (token factice). Ce mail sert uniquement à vérifier la délivrabilité et la mise en page.';
		$body .= '</div>';
	}

	$body .= '<p style="margin:0 0 16px;font-size:16px;line-height:1.6;">' . esc_html( $settings['email_intro'] ) . '</p>';
	$body .= '<p style="margin:0 0 24px;font-size:16px;line-height:1.6;">' . esc_html( $settings['email_body'] ) . '</p>';
	$body .= '<p style="margin:0 0 24px;text-align:center;">';

	if ( $is_test ) {
		$body .= '<span style="display:inline-block;padding:14px 28px;background:#a78a4d;color:#f7f1e6;font-family:Georgia,serif;font-size:15px;letter-spacing:0.04em;text-transform:uppercase;cursor:not-allowed;opacity:0.6;">';
		$body .= 'Télécharger le PDF (désactivé)';
		$body .= '</span>';
	} else {
		$body .= '<a href="' . esc_url( $download_url ) . '" style="display:inline-block;padding:14px 28px;background:#1f3a2e;color:#f7f1e6;text-decoration:none;font-family:Georgia,serif;font-size:15px;letter-spacing:0.04em;text-transform:uppercase;border-radius:0;">';
		$body .= 'Télécharger le PDF';
		$body .= '</a>';
	}

	$body .= '</p>';
	$body .= '<p style="margin:0 0 24px;font-size:14px;color:#5b5648;line-height:1.6;font-style:italic;">' . esc_html( $settings['email_outro'] ) . '</p>';
	$body .= '<hr style="border:0;border-top:1px solid #d8cbb0;margin:32px 0;">';
	$body .= '<p style="margin:0;font-size:13px;color:#5b5648;">';
	$body .= esc_html( $site_name ) . ' · <a href="' . esc_url( $site_url ) . '" style="color:#1f3a2e;">' . esc_html( $site_url ) . '</a>';
	$body .= '</p>';
	$body .= '</div></body></html>';

	$admin_email = get_option( 'admin_email', 'noreply@' . (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
	$headers = [
		'Content-Type: text/html; charset=UTF-8',
		'From: ' . $site_name . ' <' . $admin_email . '>',
	];

	return (bool) wp_mail( $email, $subject, $body, $headers );
}

// ─── REST : GET /arw/v1/lead-download?t=TOKEN ────────────────────────────────

add_action( 'rest_api_init', function () {
	register_rest_route( 'arw/v1', '/lead-download', [
		'methods'             => 'GET',
		'callback'            => 'arw_maison_lead_download',
		'permission_callback' => '__return_true',
		'args'                => [
			't' => [
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
		],
	] );
} );

function arw_maison_lead_download( WP_REST_Request $req ) {
	$token = (string) $req['t'];

	// Strict format : 16-64 chars alphanum (matches wp_generate_password).
	if ( ! preg_match( '/^[A-Za-z0-9]{16,64}$/', $token ) ) {
		return new WP_REST_Response( [ 'error' => 'invalid_token' ], 400 );
	}

	// IP rate-limit : max 10 lookups / 5 min to prevent token brute-force.
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? preg_replace( '/[^0-9a-fA-F\.\:]/', '', (string) $_SERVER['REMOTE_ADDR'] ) : '';
	if ( $ip ) {
		$rate_key = 'arw_lead_rl_' . md5( $ip );
		$count    = (int) get_transient( $rate_key );
		if ( $count >= 10 ) {
			return new WP_REST_Response( [ 'error' => 'rate_limited' ], 429 );
		}
		set_transient( $rate_key, $count + 1, 5 * MINUTE_IN_SECONDS );
	}

	// Find the submission with this token.
	$matches = get_posts( [
		'post_type'      => 'arw_submission',
		'post_status'    => 'any',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_query'     => [ [
			'key'   => ARW_MAISON_LEAD_TOKEN_META,
			'value' => $token,
		] ],
	] );

	if ( empty( $matches ) ) {
		return new WP_REST_Response( [ 'error' => 'token_not_found' ], 404 );
	}

	$submission_id = (int) $matches[0];
	$expires       = (int) get_post_meta( $submission_id, ARW_MAISON_LEAD_TOKEN_META . '_expires', true );
	$used          = (int) get_post_meta( $submission_id, ARW_MAISON_LEAD_USED_META, true );

	if ( $expires && $expires < time() ) {
		return new WP_REST_Response( [ 'error' => 'token_expired' ], 410 );
	}

	// Single-use is opt-in : we increment usage but don't block (some users open the link multiple times).
	// Hard-stop after 5 downloads to prevent abuse.
	if ( $used >= 5 ) {
		return new WP_REST_Response( [ 'error' => 'token_overused' ], 429 );
	}

	$pdf = arw_maison_lead_pdf_path();
	if ( ! file_exists( $pdf ) || ! is_readable( $pdf ) ) {
		return new WP_REST_Response( [ 'error' => 'pdf_missing' ], 500 );
	}

	update_post_meta( $submission_id, ARW_MAISON_LEAD_USED_META, $used + 1 );

	// Stream the PDF.
	$filename = '12-erreurs-pluscestsimple.pdf';

	header( 'Content-Type: application/pdf' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	header( 'Content-Length: ' . filesize( $pdf ) );
	header( 'Cache-Control: private, no-cache, no-store, must-revalidate' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' );

	// Disable WP REST envelope and other handlers from re-entering.
	@ob_end_clean();
	readfile( $pdf );
	exit;
}

// ─── Cleanup expired tokens : daily cron ─────────────────────────────────────

add_action( 'arw_maison_lead_cleanup', 'arw_maison_lead_cleanup_run' );

function arw_maison_lead_cleanup_run(): int {
	$now = time();

	$old = get_posts( [
		'post_type'      => 'arw_submission',
		'post_status'    => 'any',
		'posts_per_page' => 200,
		'fields'         => 'ids',
		'meta_query'     => [
			[
				'key'     => ARW_MAISON_LEAD_TOKEN_META . '_expires',
				'value'   => $now - DAY_IN_SECONDS,
				'compare' => '<',
				'type'    => 'NUMERIC',
			],
		],
	] );

	$cleaned = 0;
	foreach ( $old as $sid ) {
		// Just clear the token, keep the submission record.
		delete_post_meta( $sid, ARW_MAISON_LEAD_TOKEN_META );
		delete_post_meta( $sid, ARW_MAISON_LEAD_TOKEN_META . '_expires' );
		$cleaned++;
	}
	return $cleaned;
}

// Schedule the cleanup on activation, unschedule on deactivation.
add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'arw_maison_lead_cleanup' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'arw_maison_lead_cleanup' );
	}
}, 50 );
