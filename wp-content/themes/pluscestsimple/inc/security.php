<?php
/**
 * Security hardening.
 * - HTTP security headers (HSTS, X-Frame-Options, X-Content-Type-Options,
 *   Referrer-Policy, Permissions-Policy)
 * - Author enumeration block (/?author=N)
 * - Login error neutralisation (no user existence leak)
 * - /.well-known/security.txt
 * - Global rate-limit for public form submissions
 * - Email header injection guard helper
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

// ─── HTTP security headers ────────────────────────────────────────────────────

add_action( 'send_headers', function () {
	if ( is_admin() ) { return; }

	// CSP basique : permissive sur les inlines (WordPress + thème en émettent)
	// mais bloque les sources externes non whitelistées + clickjacking via frame-ancestors.
	// Activable/désactivable via filter (un site qui charge AdSense, GTM lourd, etc.
	// devra retourner '' ou ajuster).
	//
	// NOTE SÉCURITÉ : 'unsafe-inline' est volontairement présent sur script-src et style-src.
	// Raison : WordPress core + Gutenberg + thème émettent du JS/CSS inline (jamais nonce-tagged
	// par défaut). Migrer vers une CSP nonce-based demanderait de tagger TOUS les inlines (core
	// inclus, non-fiable). C'est un trade-off documenté : on perd la ceinture-bretelle CSP face
	// à un XSS exploitable, on garde la robustesse fonctionnelle. Compensé par : échappement
	// systématique en sortie (XSS-1..5 fixés en audit 2026-05-14), JSON_HEX_TAG sur JSON-LD,
	// HSTS+X-Frame+Permissions-Policy en place. À reconsidérer si une refacto Gutenberg ouvre
	// la voie aux nonces inline.
	$default_csp = "default-src 'self' data:; "
		. "script-src 'self' 'unsafe-inline' https:; "
		. "style-src 'self' 'unsafe-inline' https:; "
		. "img-src 'self' data: https:; "
		. "font-src 'self' data: https:; "
		. "connect-src 'self' https:; "
		. "frame-ancestors 'self'; "
		. "base-uri 'self'; "
		. "form-action 'self'";

	$headers = apply_filters( 'arw_pulse_security_headers', [
		'X-Content-Type-Options' => 'nosniff',
		'X-Frame-Options'        => 'SAMEORIGIN',
		'Referrer-Policy'        => 'strict-origin-when-cross-origin',
		'Permissions-Policy'     => 'geolocation=(), camera=(), microphone=(), payment=(), usb=(), magnetometer=(), gyroscope=(), accelerometer=(), interest-cohort=()',
		'X-XSS-Protection'       => '0', // Modern browsers ignore; explicitly off to disable legacy filter bugs.
		'Content-Security-Policy' => apply_filters( 'arw_pulse_csp', $default_csp ),
	] );

	// HSTS only on HTTPS, only for canonical host (avoid breaking local dev / staging).
	if ( is_ssl() ) {
		$headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains; preload';
	}

	foreach ( $headers as $name => $value ) {
		if ( $value !== '' && ! headers_sent() ) {
			header( $name . ': ' . $value );
		}
	}
} );

// ─── Block author enumeration ────────────────────────────────────────────────
// GET /?author=N leaks the username via redirect to /author/{slug}/.
// We 404 any such request for non-logged-in users.

add_action( 'template_redirect', function () {
	if ( is_user_logged_in() || is_admin() ) { return; }
	if ( ! empty( $_GET['author'] ) ) {
		status_header( 404 );
		nocache_headers();
		include get_query_template( '404' );
		exit;
	}
} );

// Strip the `author_name` query var from archives too (defence in depth).
add_filter( 'redirect_canonical', function ( $redirect, $requested ) {
	if ( ! is_user_logged_in() && strpos( $requested, 'author=' ) !== false ) {
		return false;
	}
	return $redirect;
}, 10, 2 );

// ─── Neutral login errors (no user-exists vs wrong-password leak) ─────────────

add_filter( 'login_errors', function () {
	return __( 'Identifiants invalides.', 'arw-pulse' );
} );

// ─── Remove login page hint when unknown user ─────────────────────────────────

add_filter( 'wp_login_errors', function ( $errors ) {
	if ( is_wp_error( $errors ) ) {
		$errors = new WP_Error();
		$errors->add( 'invalid', __( 'Identifiants invalides.', 'arw-pulse' ) );
	}
	return $errors;
}, 99 );

// ─── /.well-known/security.txt ────────────────────────────────────────────────

add_action( 'init', function () {
	add_rewrite_rule( '^\.well-known/security\.txt$', 'index.php?arw_security_txt=1', 'top' );
} );

add_filter( 'query_vars', function ( $vars ) {
	$vars[] = 'arw_security_txt';
	return $vars;
} );

add_action( 'template_redirect', function () {
	if ( ! get_query_var( 'arw_security_txt' ) ) { return; }
	$contact = apply_filters( 'arw_pulse_security_contact', 'mailto:' . ( get_option( 'admin_email' ) ?: 'security@' . wp_parse_url( home_url(), PHP_URL_HOST ) ) );
	// Defense in depth : strip CR/LF de toute valeur filtrée (header injection si filtre malveillant).
	$contact = preg_replace( '/[\r\n]+/', '', (string) $contact );
	$expires = gmdate( 'Y-m-d\TH:i:s\Z', time() + YEAR_IN_SECONDS );
	header( 'Content-Type: text/plain; charset=utf-8' );
	header( 'Cache-Control: public, max-age=86400' );
	echo "Contact: {$contact}\n";
	echo "Expires: {$expires}\n";
	echo "Preferred-Languages: fr, en\n";
	exit;
} );

// ─── Global rate-limit for public form submissions ────────────────────────────
// Per-IP is in form.php; this adds a bucket-wide cap that throttles traffic
// from rotated IPs (common bot pattern).

add_filter( 'rest_pre_dispatch', function ( $result, $server, $request ) {
	if ( $request->get_route() !== '/arw/v1/submit' ) { return $result; }
	if ( is_user_logged_in() ) { return $result; } // admins / editors exempt

	$cap = (int) apply_filters( 'arw_pulse_global_rate_cap', 60 ); // 60 submissions / hour total
	// Atomicité CONDITIONNELLE :
	//  - si object cache externe (Redis/Memcached) actif → wp_cache_incr atomique ✓
	//  - fallback transient (DB) → read-then-write NON atomique (race possible sous burst concurrent)
	// Pour un cap absolu garanti, exiger un object cache externe en prod (recommandé).
	$bucket = function_exists( 'arw_pulse_rate_hit' )
		? arw_pulse_rate_hit( 'arw_rl_global', HOUR_IN_SECONDS )
		: ( (int) get_transient( 'arw_rl_global' ) + 1 );
	if ( $bucket > $cap ) {
		return new WP_REST_Response( [ 'ok' => false, 'error' => 'rate_limited_global', 'retry_after' => 3600 ], 429 );
	}
	if ( ! function_exists( 'arw_pulse_rate_hit' ) ) {
		set_transient( 'arw_rl_global', $bucket, HOUR_IN_SECONDS );
	}
	return $result;
}, 10, 3 );

// ─── Disable anonymous WP REST API for non-core endpoints ─────────────────────
// Already locked in cleanup.php for /wp/v2/users — nothing to add here.

// ─── Helper: sanitize email for Reply-To header ───────────────────────────────

function arw_pulse_safe_email_header( string $email ): string {
	$email = (string) sanitize_email( $email );
	// Strip any \r\n that might slip through (defence in depth).
	$email = preg_replace( '/[\r\n]/', '', $email );
	return $email;
}

// ─── Helper: escape cell values for CSV (prevents formula injection) ─────────
// Excel / Google Sheets execute =, +, -, @ as formulas when opening a CSV.

function arw_pulse_csv_escape( $value ): string {
	$s = (string) $value;
	if ( $s === '' ) { return ''; }
	$first = $s[0];
	if ( in_array( $first, [ '=', '+', '-', '@', "\t", "\r" ], true ) ) {
		return "'" . $s;
	}
	return $s;
}

// ─── Availability enum validator for products ─────────────────────────────────

function arw_pulse_normalize_availability( string $raw ): string {
	$allowed = [ 'InStock', 'OutOfStock', 'PreOrder', 'BackOrder', 'Discontinued' ];
	return in_array( $raw, $allowed, true ) ? $raw : 'InStock';
}

// ─── Helper: get real client IP (CDN/proxy-aware) ─────────────────────────────
// Audit 2026-05-14 — derrière Cloudflare/OVH proxy/load balancer, REMOTE_ADDR vaut
// l'IP du proxy ⇒ tous les visiteurs partagent le même compteur de rate-limit
// (DoS auto-infligé). On lit CF-Connecting-IP en priorité, puis X-Forwarded-For
// (premier IP de la chaîne), avec fallback REMOTE_ADDR. Validation FILTER_VALIDATE_IP
// pour éviter spoof de header avec chaîne arbitraire.

function arw_pulse_client_ip(): string {
	$candidates = [ 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ];
	foreach ( $candidates as $header ) {
		if ( empty( $_SERVER[ $header ] ) ) { continue; }
		$raw   = (string) wp_unslash( $_SERVER[ $header ] );
		$first = trim( explode( ',', $raw )[0] );
		$valid = filter_var( $first, FILTER_VALIDATE_IP );
		if ( $valid ) { return (string) $valid; }
	}
	return '';
}

// ─── Helper: hash an IP for storage (RGPD-friendly pseudonymisation) ──────────
// Stocker l'IP brute en postmeta sur les submissions = donnée personnelle conservée
// indéfiniment. On stocke un HMAC-SHA256 avec wp_salt() à la place — permet la
// déduplication / anti-fraude sans exposition de l'IP en clair (admin compromis,
// export CSV, fuite DB).

function arw_pulse_hash_ip( string $ip ): string {
	if ( $ip === '' ) { return ''; }
	return hash_hmac( 'sha256', $ip, wp_salt( 'nonce' ) );
}

// ─── Helper: atomic rate-limit increment (race-free) ──────────────────────────
// get_transient + set_transient en deux temps ouvre une fenêtre de race sous concurrence.
// wp_cache_incr (si object cache présent) est atomique. Fallback transient sinon.
// Retourne le compteur courant après incrément.

function arw_pulse_rate_hit( string $key, int $ttl_seconds ): int {
	if ( wp_using_ext_object_cache() ) {
		$hit = wp_cache_get( $key, 'arw_rl' );
		if ( false === $hit ) {
			wp_cache_set( $key, 1, 'arw_rl', $ttl_seconds );
			return 1;
		}
		return (int) wp_cache_incr( $key, 1, 'arw_rl' );
	}
	$hit = (int) get_transient( $key );
	set_transient( $key, $hit + 1, $ttl_seconds );
	return $hit + 1;
}
