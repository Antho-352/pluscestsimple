<?php
/**
 * Native form system.
 * - Custom Post Type `arw_submission` stores every submission (admin UI for free).
 * - REST endpoint POST /wp-json/arw/v1/submit handles captures.
 * - Honeypot + rate-limit + nonce.
 * - Sends email to admin via wp_mail(); optional CC to custom recipient.
 * - One form, one block pattern; differentiated by "form_type" param (contact, newsletter, quote, …).
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

const ARW_PULSE_SUBMISSION_CPT = 'arw_submission';

// Register CPT.
add_action( 'init', function () {
	register_post_type( ARW_PULSE_SUBMISSION_CPT, [
		'labels' => [
			'name'               => __( 'Soumissions', 'arw-pulse' ),
			'singular_name'      => __( 'Soumission', 'arw-pulse' ),
			'menu_name'          => __( 'Soumissions', 'arw-pulse' ),
			'search_items'       => __( 'Rechercher', 'arw-pulse' ),
			'not_found'          => __( 'Aucune soumission', 'arw-pulse' ),
			'not_found_in_trash' => __( 'Aucune soumission dans la corbeille', 'arw-pulse' ),
		],
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => defined( 'ARW_PULSE_ADMIN_SLUG' ) ? ARW_PULSE_ADMIN_SLUG : true,
		'show_in_admin_bar'   => false,
		'menu_icon'           => 'dashicons-email-alt',
		// PII : restreindre lecture/écriture à manage_options uniquement.
		// Authors/Contributors ne doivent pas voir les emails/téléphones soumis.
		'capability_type'     => 'page',
		'capabilities'        => [
			'edit_post'           => 'manage_options',
			'read_post'           => 'manage_options',
			'delete_post'         => 'manage_options',
			'edit_posts'          => 'manage_options',
			'edit_others_posts'   => 'manage_options',
			'delete_posts'        => 'manage_options',
			'publish_posts'       => 'manage_options',
			'read_private_posts'  => 'manage_options',
			'create_posts'        => 'do_not_allow',
		],
		'map_meta_cap'        => false,
		'supports'            => [ 'title', 'custom-fields' ],
		'has_archive'         => false,
		'rewrite'             => false,
		'exclude_from_search' => true,
	] );
} );

// Admin columns.
add_filter( 'manage_' . ARW_PULSE_SUBMISSION_CPT . '_posts_columns', function ( $cols ) {
	return [
		'cb'       => $cols['cb'],
		'title'    => __( 'Sujet', 'arw-pulse' ),
		'type'     => __( 'Type', 'arw-pulse' ),
		'email'    => __( 'Email', 'arw-pulse' ),
		'page'     => __( 'Source', 'arw-pulse' ),
		'date'     => __( 'Date', 'arw-pulse' ),
	];
} );
add_action( 'manage_' . ARW_PULSE_SUBMISSION_CPT . '_posts_custom_column', function ( $col, $post_id ) {
	switch ( $col ) {
		case 'type':
			echo esc_html( get_post_meta( $post_id, 'form_type', true ) );
			break;
		case 'email':
			echo esc_html( get_post_meta( $post_id, 'email', true ) );
			break;
		case 'page':
			$u = get_post_meta( $post_id, 'source_url', true );
			if ( $u ) {
				printf( '<a href="%s" target="_blank" rel="noopener">%s</a>', esc_url( $u ), esc_html( wp_parse_url( $u, PHP_URL_PATH ) ) );
			}
			break;
	}
}, 10, 2 );

// REST endpoint.
add_action( 'rest_api_init', function () {
	register_rest_route( 'arw/v1', '/submit', [
		'methods'             => 'POST',
		'callback'            => 'arw_pulse_handle_submission',
		'permission_callback' => 'arw_pulse_form_permission',
		'args'                => [
			'form_type' => [ 'required' => true, 'sanitize_callback' => 'sanitize_key' ],
			'name'      => [ 'required' => false, 'sanitize_callback' => 'sanitize_text_field' ],
			'email'     => [ 'required' => true, 'sanitize_callback' => 'sanitize_email' ],
			'phone'     => [ 'required' => false, 'sanitize_callback' => 'sanitize_text_field' ],
			'subject'   => [ 'required' => false, 'sanitize_callback' => 'sanitize_text_field' ],
			'message'   => [ 'required' => false, 'sanitize_callback' => 'sanitize_textarea_field' ],
			'company'   => [ 'required' => false, 'sanitize_callback' => 'sanitize_text_field' ],
			'hp'        => [ 'required' => false ],
			'nonce'     => [ 'required' => true ],
			'ts'        => [ 'required' => false, 'sanitize_callback' => 'absint' ],
			'tsig'      => [ 'required' => false, 'sanitize_callback' => 'sanitize_text_field' ],
			'source'    => [ 'required' => false, 'sanitize_callback' => 'esc_url_raw' ],
		],
	] );
} );

/**
 * Permission callback : exige Origin OU Referer aligné sur l'hôte du site.
 * Le nonce wp_rest est vérifié dans le callback métier — mais comme il est exposé
 * en clair sur toute page front, on bloque les soumissions cross-origin ici.
 */
function arw_pulse_form_permission( WP_REST_Request $req ) {
	$origin  = (string) $req->get_header( 'origin' );
	$referer = (string) $req->get_header( 'referer' );
	if ( $origin === '' && $referer === '' ) {
		return new WP_Error( 'forbidden', 'Cross-origin refusé.', [ 'status' => 403 ] );
	}
	$site_host    = (string) wp_parse_url( home_url(), PHP_URL_HOST );
	$origin_host  = $origin  !== '' ? (string) wp_parse_url( $origin,  PHP_URL_HOST ) : '';
	$referer_host = $referer !== '' ? (string) wp_parse_url( $referer, PHP_URL_HOST ) : '';
	if ( strcasecmp( $origin_host, $site_host ) !== 0 && strcasecmp( $referer_host, $site_host ) !== 0 ) {
		return new WP_Error( 'forbidden', 'Cross-origin refusé.', [ 'status' => 403 ] );
	}
	return true;
}

function arw_pulse_handle_submission( WP_REST_Request $req ) {
	// Honeypot.
	if ( ! empty( $req['hp'] ) ) {
		return new WP_REST_Response( [ 'ok' => true ], 200 );
	}

	// Nonce — accepts either wp_rest (REST standard, works for anonymous) or our custom nonce.
	$nonce_ok = false;
	if ( ! empty( $req['nonce'] ) ) {
		if ( wp_verify_nonce( $req['nonce'], 'wp_rest' ) || wp_verify_nonce( $req['nonce'], 'arw_pulse_form' ) ) {
			$nonce_ok = true;
		}
	}
	// X-WP-Nonce header fallback.
	$header_nonce = $req->get_header( 'x_wp_nonce' );
	if ( ! $nonce_ok && $header_nonce && wp_verify_nonce( $header_nonce, 'wp_rest' ) ) {
		$nonce_ok = true;
	}
	if ( ! $nonce_ok ) {
		return new WP_REST_Response( [ 'ok' => false, 'error' => 'invalid_nonce' ], 403 );
	}

	// Anti-spam : token horodaté HMAC-SHA256 signé côté serveur lors du rendu du form.
	// Refuse les submits sous 3 secondes (bot trop rapide) et au-dessus de 30 minutes (formulaire trop vieux).
	// Bypass pour admins connectés (preview, tests).
	if ( ! ( is_user_logged_in() && current_user_can( 'manage_options' ) ) ) {
		$ts    = (int) ( $req['ts'] ?? 0 );
		$tsig  = (string) ( $req['tsig'] ?? '' );
		if ( ! $ts || ! $tsig ) {
			return new WP_REST_Response( [ 'ok' => false, 'error' => 'missing_timestamp' ], 403 );
		}
		$expected = hash_hmac( 'sha256', (string) $ts, wp_salt( 'nonce' ) );
		if ( ! hash_equals( $expected, $tsig ) ) {
			return new WP_REST_Response( [ 'ok' => false, 'error' => 'invalid_timestamp' ], 403 );
		}
		$delta = time() - $ts;
		if ( $delta < 3 ) {
			return new WP_REST_Response( [ 'ok' => false, 'error' => 'too_fast' ], 429 );
		}
		if ( $delta > 30 * MINUTE_IN_SECONDS ) {
			return new WP_REST_Response( [ 'ok' => false, 'error' => 'form_expired' ], 410 );
		}
		// Anti-replay : un tsig capturé ne peut être consommé qu'une seule fois.
		// Clé courte (32 chars du tsig) + TTL = fenêtre max d'usage (30min).
		$replay_key = 'arw_tsig_' . substr( $tsig, 0, 32 );
		if ( get_transient( $replay_key ) ) {
			return new WP_REST_Response( [ 'ok' => false, 'error' => 'token_replay' ], 409 );
		}
		set_transient( $replay_key, 1, 30 * MINUTE_IN_SECONDS );
	}

	// Rate limit (per IP, 10 submissions / 10min). Bypass for logged-in admins.
	// Uses CDN-aware IP detection (HTTP_CF_CONNECTING_IP / X-Forwarded-For fallback REMOTE_ADDR)
	// and atomic increment via wp_cache_incr when object cache present.
	$skip_rl = is_user_logged_in() && current_user_can( 'manage_options' );
	$max_per_window = (int) apply_filters( 'arw_pulse_form_rate_limit_max', 10 );
	$ip = arw_pulse_client_ip();
	if ( ! $skip_rl ) {
		$key = 'arw_rl_' . md5( $ip );
		$hit = arw_pulse_rate_hit( $key, 10 * MINUTE_IN_SECONDS );
		if ( $hit > $max_per_window ) {
			return new WP_REST_Response( [ 'ok' => false, 'error' => 'rate_limited', 'retry_after' => 600 ], 429 );
		}
	}

	// Validate email.
	if ( ! is_email( $req['email'] ) ) {
		return new WP_REST_Response( [ 'ok' => false, 'error' => 'invalid_email' ], 400 );
	}

	$form_type = $req['form_type'];
	$allowed   = apply_filters( 'arw_pulse_form_types', [ 'contact', 'newsletter', 'quote', 'press', 'affiliate-inquiry' ] );
	if ( ! in_array( $form_type, $allowed, true ) ) {
		return new WP_REST_Response( [ 'ok' => false, 'error' => 'invalid_form_type' ], 400 );
	}

	// Store as CPT.
	$subject = $req['subject'] ?: sprintf( '[%s] %s', $form_type, $req['email'] );
	$post_id = wp_insert_post( [
		'post_type'   => ARW_PULSE_SUBMISSION_CPT,
		'post_status' => 'publish',
		'post_title'  => wp_strip_all_tags( $subject ),
		'post_content'=> (string) $req['message'],
	] );

	if ( is_wp_error( $post_id ) ) {
		return new WP_REST_Response( [ 'ok' => false, 'error' => 'storage_failed' ], 500 );
	}

	foreach ( [ 'form_type', 'name', 'email', 'phone', 'company', 'source' ] as $k ) {
		if ( isset( $req[ $k ] ) && '' !== $req[ $k ] ) {
			update_post_meta( $post_id, $k, $req[ $k ] );
		}
	}
	// RGPD : on stocke un hash HMAC-SHA256 de l'IP (pseudonymisation), pas l'IP brute.
	// Permet déduplication / anti-fraude sans exposition de l'IP en clair.
	update_post_meta( $post_id, 'ip_hash', arw_pulse_hash_ip( $ip ) );
	update_post_meta( $post_id, 'ua', isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( (string) $_SERVER['HTTP_USER_AGENT'] ) ) : '' );

	// Email notification.
	$to      = apply_filters( 'arw_pulse_form_to', get_option( 'admin_email' ), $form_type );
	$subject = sprintf( '[%s] %s — %s', get_bloginfo( 'name' ), $form_type, $req['email'] );
	$body    = "Type: {$form_type}\n";
	foreach ( [ 'name', 'email', 'phone', 'company', 'source' ] as $k ) {
		$v = $req[ $k ] ?? '';
		if ( $v ) { $body .= ucfirst( $k ) . ": {$v}\n"; }
	}
	$body   .= "\n" . ( $req['message'] ?? '' );
	// Strip CR/LF from email before injecting into header (defence in depth).
	$safe_from = function_exists( 'arw_pulse_safe_email_header' )
		? arw_pulse_safe_email_header( $req['email'] )
		: preg_replace( '/[\r\n]/', '', sanitize_email( (string) $req['email'] ) );
	$headers = $safe_from ? [ 'Reply-To: ' . $safe_from ] : [];

	wp_mail( $to, $subject, $body, $headers );

	do_action( 'arw_pulse_form_submitted', $post_id, $req );

	$redirect = apply_filters( 'arw_pulse_form_redirect', "/merci-{$form_type}/", $form_type );

	return new WP_REST_Response( [ 'ok' => true, 'redirect' => $redirect ], 200 );
}

/**
 * Enqueue a tiny inline JS handler for the form pattern (submits via fetch, handles redirect).
 */
add_action( 'wp_footer', function () {
	if ( ! apply_filters( 'arw_pulse_needs_form_js', true ) ) { return; }
	$nonce = wp_create_nonce( 'wp_rest' );
	$url   = esc_url_raw( rest_url( 'arw/v1/submit' ) );
	$ts    = time();
	$tsig  = hash_hmac( 'sha256', (string) $ts, wp_salt( 'nonce' ) );
	?>
<script>
(function(){
  document.addEventListener('submit', function(e){
    var f = e.target;
    if (!f.matches('form[data-arw-form]')) return;
    e.preventDefault();
    var btn = f.querySelector('[type=submit]');
    if (btn) { btn.disabled = true; btn.dataset._t = btn.textContent; btn.textContent = 'Envoi…'; }
    var fd = new FormData(f);
    fd.append('nonce', '<?php echo esc_js( $nonce ); ?>');
    fd.append('ts',    '<?php echo esc_js( (string) $ts ); ?>');
    fd.append('tsig',  '<?php echo esc_js( $tsig ); ?>');
    fd.append('source', window.location.href);
    var payload = {};
    fd.forEach(function(v,k){ payload[k] = v; });
    fetch('<?php echo esc_js( $url ); ?>', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': '<?php echo esc_js( $nonce ); ?>'
      },
      body: JSON.stringify(payload)
    }).then(function(r){ return r.json(); }).then(function(j){
      if (j && j.ok) {
        window.location.href = j.redirect || '/merci/';
      } else {
        if (btn) { btn.disabled = false; btn.textContent = btn.dataset._t || 'Envoyer'; }
        var err = f.querySelector('[data-arw-form-error]');
        if (err) { err.textContent = (j && j.error) ? j.error : 'Erreur'; err.hidden = false; }
      }
    }).catch(function(){
      if (btn) { btn.disabled = false; btn.textContent = btn.dataset._t || 'Envoyer'; }
    });
  });
})();
</script>
	<?php
}, 100 );

/**
 * CSV export action for submissions.
 */
add_action( 'admin_post_arw_export_submissions', function () {
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Nope' ); }
	check_admin_referer( 'arw_export_submissions' );

	$posts = get_posts( [
		'post_type'   => ARW_PULSE_SUBMISSION_CPT,
		'numberposts' => -1,
		'orderby'     => 'date',
		'order'       => 'DESC',
	] );

	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=submissions-' . date( 'Y-m-d' ) . '.csv' );

	$out = fopen( 'php://output', 'w' );
	$esc = function_exists( 'arw_pulse_csv_escape' )
		? 'arw_pulse_csv_escape'
		: function ( $v ) { return (string) $v; };
	fputcsv( $out, array_map( $esc, [ 'Date', 'Type', 'Name', 'Email', 'Phone', 'Company', 'Source', 'Subject', 'Message' ] ) );
	foreach ( $posts as $p ) {
		fputcsv( $out, array_map( $esc, [
			get_the_date( 'Y-m-d H:i', $p ),
			get_post_meta( $p->ID, 'form_type', true ),
			get_post_meta( $p->ID, 'name', true ),
			get_post_meta( $p->ID, 'email', true ),
			get_post_meta( $p->ID, 'phone', true ),
			get_post_meta( $p->ID, 'company', true ),
			get_post_meta( $p->ID, 'source', true ),
			$p->post_title,
			$p->post_content,
		] ) );
	}
	fclose( $out );
	exit;
} );

// Admin page action link.
add_action( 'admin_notices', function () {
	$screen = get_current_screen();
	if ( ! $screen || $screen->post_type !== ARW_PULSE_SUBMISSION_CPT ) { return; }
	$url = wp_nonce_url( admin_url( 'admin-post.php?action=arw_export_submissions' ), 'arw_export_submissions' );
	printf( '<div class="notice notice-info"><p><a class="button" href="%s">%s</a></p></div>', esc_url( $url ), esc_html__( 'Exporter en CSV', 'arw-pulse' ) );
} );
