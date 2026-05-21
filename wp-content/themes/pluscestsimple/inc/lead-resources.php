<?php
/**
 * Lead Resources — ressources téléchargeables (PDFs, guides) délivrées par email
 * après inscription à un form_type donné.
 *
 * Workflow :
 *   1. Admin crée un CPT pcs_lead_resource avec : titre, fichier PDF, form_type associé
 *   2. Le pattern newsletter / capture (côté thème) a un attribut data-form-type qui matche
 *   3. À chaque soumission, le hook pcs_form_submitted cherche une lead-resource matchante
 *   4. Génère un token signé (24h TTL), envoie un email avec lien de téléchargement
 *   5. REST endpoint /pcs/v1/lead-download?t=TOKEN stream le PDF privé
 *
 * Les PDFs sont stockés dans wp-content/uploads/pcs-resources/private/ (htaccess deny).
 *
 * @package pluscestsimple
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const PCS_LEAD_RESOURCE_CPT  = 'pcs_lead_resource';
const PCS_LEAD_PRIVATE_DIR   = 'pcs-resources/private';
const PCS_LEAD_TOKEN_TTL     = DAY_IN_SECONDS;
const PCS_LEAD_TOKEN_META    = '_pcs_lead_token';
const PCS_LEAD_USED_META     = '_pcs_lead_used';

/* -------------------------------------------------------------------------
 * CPT
 * --------------------------------------------------------------------- */

add_action( 'init', function () {
	register_post_type( PCS_LEAD_RESOURCE_CPT, [
		'labels'              => [
			'name'          => __( 'Ressources lead-magnet', 'pluscestsimple' ),
			'singular_name' => __( 'Ressource', 'pluscestsimple' ),
			'menu_name'     => __( 'Ressources', 'pluscestsimple' ),
			'add_new_item'  => __( 'Nouvelle ressource', 'pluscestsimple' ),
			'edit_item'     => __( 'Modifier la ressource', 'pluscestsimple' ),
		],
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => 'tools.php',
		'show_in_rest'        => false,
		'supports'            => [ 'title', 'editor' ],
		'capability_type'     => 'post',
		'menu_icon'           => 'dashicons-download',
	] );
} );

/* -------------------------------------------------------------------------
 * Meta box : fichier PDF + form_type associé
 * --------------------------------------------------------------------- */

add_action( 'add_meta_boxes_' . PCS_LEAD_RESOURCE_CPT, function () {
	add_meta_box(
		'pcs_lead_resource_meta',
		__( 'Ressource lead-magnet', 'pluscestsimple' ),
		'pcs_lead_resource_meta_box',
		PCS_LEAD_RESOURCE_CPT,
		'normal',
		'high'
	);
} );

function pcs_lead_resource_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'pcs_lead_resource_save', 'pcs_lead_resource_nonce' );
	$pdf_filename = (string) get_post_meta( $post->ID, '_pcs_pdf_filename', true );
	$form_type    = (string) get_post_meta( $post->ID, '_pcs_form_type', true );
	$email_subj   = (string) get_post_meta( $post->ID, '_pcs_email_subject', true );
	$email_body   = (string) get_post_meta( $post->ID, '_pcs_email_body', true );

	echo '<p><label><strong>Nom du fichier PDF :</strong><br>';
	echo '<input type="text" name="pcs_pdf_filename" value="' . esc_attr( $pdf_filename ) . '" class="regular-text" placeholder="tendances-2026.pdf"></label></p>';
	echo '<p class="description">Le fichier doit être uploadé manuellement dans <code>wp-content/uploads/' . esc_html( PCS_LEAD_PRIVATE_DIR ) . '/</code>.</p>';

	echo '<p><label><strong>form_type associé :</strong><br>';
	echo '<input type="text" name="pcs_form_type" value="' . esc_attr( $form_type ) . '" class="regular-text" placeholder="lead-tendances"></label></p>';
	echo '<p class="description">Quand un visiteur soumet un form avec ce <code>form_type</code>, il reçoit ce PDF par email.</p>';

	echo '<p><label><strong>Sujet email :</strong><br>';
	echo '<input type="text" name="pcs_email_subject" value="' . esc_attr( $email_subj ) . '" class="large-text" placeholder="Votre guide — Tendances 2026"></label></p>';

	echo '<p><label><strong>Corps email (HTML autorisé) :</strong><br>';
	echo '<textarea name="pcs_email_body" rows="6" class="large-text">' . esc_textarea( $email_body ) . '</textarea></label></p>';
	echo '<p class="description">Le placeholder <code>{LINK}</code> sera remplacé par le lien de téléchargement signé.</p>';
}

add_action( 'save_post_' . PCS_LEAD_RESOURCE_CPT, function ( int $post_id ): void {
	if ( ! isset( $_POST['pcs_lead_resource_nonce'] ) || ! wp_verify_nonce( $_POST['pcs_lead_resource_nonce'], 'pcs_lead_resource_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	foreach ( [ 'pdf_filename', 'form_type', 'email_subject', 'email_body' ] as $field ) {
		$value = isset( $_POST[ 'pcs_' . $field ] ) ? wp_unslash( $_POST[ 'pcs_' . $field ] ) : '';
		if ( $field === 'email_body' ) {
			update_post_meta( $post_id, '_pcs_' . $field, wp_kses_post( $value ) );
		} else {
			update_post_meta( $post_id, '_pcs_' . $field, sanitize_text_field( $value ) );
		}
	}
} );

/* -------------------------------------------------------------------------
 * Helper : trouver la ressource matchant un form_type
 * --------------------------------------------------------------------- */

function pcs_lead_resource_find( string $form_type ): ?WP_Post {
	$posts = get_posts( [
		'post_type'      => PCS_LEAD_RESOURCE_CPT,
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'meta_key'       => '_pcs_form_type',
		'meta_value'     => $form_type,
		'no_found_rows'  => true,
	] );
	return $posts[0] ?? null;
}

/* -------------------------------------------------------------------------
 * Dossier privé : créer + .htaccess deny
 * --------------------------------------------------------------------- */

function pcs_lead_private_dir(): string {
	$uploads = wp_get_upload_dir();
	$dir     = trailingslashit( $uploads['basedir'] ) . PCS_LEAD_PRIVATE_DIR;
	if ( ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );
		$htaccess = $dir . '/.htaccess';
		if ( ! file_exists( $htaccess ) ) {
			file_put_contents( $htaccess, "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nOrder deny,allow\nDeny from all\n</IfModule>\n" );
		}
		$index = $dir . '/index.php';
		if ( ! file_exists( $index ) ) {
			file_put_contents( $index, "<?php // silence is golden\n" );
		}
	}
	return $dir;
}

/* -------------------------------------------------------------------------
 * Hook sur soumission form → matche un form_type → envoie le PDF
 * --------------------------------------------------------------------- */

add_action( 'pcs_form_submitted', function ( int $submission_id, array $req ): void {
	$form_type = (string) ( $req['form_type'] ?? '' );
	if ( $form_type === '' ) {
		return;
	}
	$resource = pcs_lead_resource_find( $form_type );
	if ( ! $resource ) {
		return;
	}
	$pdf_filename = (string) get_post_meta( $resource->ID, '_pcs_pdf_filename', true );
	$pdf_path     = pcs_lead_private_dir() . '/' . $pdf_filename;
	if ( ! $pdf_filename || ! file_exists( $pdf_path ) ) {
		return;
	}

	// Génère un token signé.
	$token = wp_generate_password( 32, false );
	update_post_meta( $submission_id, PCS_LEAD_TOKEN_META, $token );
	update_post_meta( $submission_id, '_pcs_lead_resource_id', $resource->ID );

	$link = add_query_arg( [
		't' => $token,
	], rest_url( 'pcs/v1/lead-download' ) );

	$to      = (string) ( $req['email'] ?? '' );
	if ( ! is_email( $to ) ) {
		return;
	}
	$subject = (string) get_post_meta( $resource->ID, '_pcs_email_subject', true ) ?: sprintf( __( 'Votre ressource — %s', 'pluscestsimple' ), $resource->post_title );
	$body    = (string) get_post_meta( $resource->ID, '_pcs_email_body', true );
	if ( $body === '' ) {
		$body = '<p>Bonjour,</p><p>Voici votre ressource :</p><p><a href="{LINK}">Télécharger</a></p><p>Le lien expire dans 24 heures.</p>';
	}
	$body = str_replace( '{LINK}', esc_url( $link ), $body );

	wp_mail( $to, $subject, $body, [ 'Content-Type: text/html; charset=UTF-8' ] );
}, 10, 2 );

/* -------------------------------------------------------------------------
 * REST endpoint : /pcs/v1/lead-download?t=TOKEN
 * --------------------------------------------------------------------- */

add_action( 'rest_api_init', function () {
	register_rest_route( 'pcs/v1', '/lead-download', [
		'methods'             => 'GET',
		'callback'            => 'pcs_lead_download_stream',
		'permission_callback' => '__return_true',
		'args'                => [
			't' => [
				'required' => true,
				'type'     => 'string',
			],
		],
	] );
} );

function pcs_lead_download_stream( WP_REST_Request $req ) {
	$token = (string) $req->get_param( 't' );
	if ( strlen( $token ) !== 32 ) {
		return new WP_Error( 'invalid', __( 'Token invalide', 'pluscestsimple' ), [ 'status' => 400 ] );
	}

	$submissions = get_posts( [
		'post_type'      => defined( 'PCS_SUBMISSION_CPT' ) ? PCS_SUBMISSION_CPT : 'arw_submission',
		'post_status'    => 'any',
		'posts_per_page' => 1,
		'meta_key'       => PCS_LEAD_TOKEN_META,
		'meta_value'     => $token,
		'no_found_rows'  => true,
	] );
	$submission = $submissions[0] ?? null;
	if ( ! $submission ) {
		return new WP_Error( 'notfound', __( 'Lien expiré ou invalide', 'pluscestsimple' ), [ 'status' => 404 ] );
	}

	// Vérifie TTL.
	$age = time() - strtotime( $submission->post_date_gmt . ' UTC' );
	if ( $age > PCS_LEAD_TOKEN_TTL ) {
		return new WP_Error( 'expired', __( 'Lien expiré', 'pluscestsimple' ), [ 'status' => 410 ] );
	}

	$resource_id = (int) get_post_meta( $submission->ID, '_pcs_lead_resource_id', true );
	if ( ! $resource_id ) {
		return new WP_Error( 'invalid', __( 'Ressource introuvable', 'pluscestsimple' ), [ 'status' => 404 ] );
	}
	$pdf_filename = (string) get_post_meta( $resource_id, '_pcs_pdf_filename', true );
	$pdf_path     = pcs_lead_private_dir() . '/' . $pdf_filename;
	if ( ! $pdf_filename || ! file_exists( $pdf_path ) ) {
		return new WP_Error( 'missing', __( 'Fichier manquant', 'pluscestsimple' ), [ 'status' => 404 ] );
	}

	// Marque le token comme utilisé (mais autorise re-téléchargement dans la fenêtre TTL).
	update_post_meta( $submission->ID, PCS_LEAD_USED_META, gmdate( 'Y-m-d H:i:s' ) );

	header( 'Content-Type: application/pdf' );
	header( 'Content-Disposition: attachment; filename="' . basename( $pdf_filename ) . '"' );
	header( 'Content-Length: ' . filesize( $pdf_path ) );
	header( 'Cache-Control: no-cache, no-store, must-revalidate' );
	readfile( $pdf_path );
	exit;
}

/* -------------------------------------------------------------------------
 * Création du dossier privé à l'activation (premier init)
 * --------------------------------------------------------------------- */

add_action( 'init', function () {
	if ( get_option( 'pcs_lead_dir_created' ) ) {
		return;
	}
	pcs_lead_private_dir();
	update_option( 'pcs_lead_dir_created', 1 );
}, 50 );
