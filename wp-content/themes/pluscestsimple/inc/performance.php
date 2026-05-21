<?php
/**
 * Performance primitives.
 * - LCP preload + fetchpriority for singular AND archives
 * - Font preload
 * - Critical CSS inline
 * - Render-blocking resource removal
 * - Archive image sizes optimisation
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

// ─── LCP: singular posts ──────────────────────────────────────────────────────

add_action( 'wp_head', function () {
	if ( ! is_singular() ) { return; }
	$thumb_id = get_post_thumbnail_id();
	if ( ! $thumb_id ) { return; }
	$src = wp_get_attachment_image_src( $thumb_id, 'large' );
	if ( ! $src ) { return; }
	printf(
		'<link rel="preload" as="image" href="%s" fetchpriority="high">' . "\n",
		esc_url( $src[0] )
	);
}, 3 );

add_filter( 'wp_get_attachment_image_attributes', function ( $attr, $attachment ) {
	static $done = false;
	if ( ! $done && is_singular() ) {
		$attr['fetchpriority'] = 'high';
		$attr['loading']       = 'eager';
		$done                  = true;
	}
	return $attr;
}, 10, 2 );

// ─── LCP: archive / category pages ───────────────────────────────────────────

add_action( 'wp_head', function () {
	if ( ! ( is_archive() || is_home() || is_search() ) ) { return; }

	// Peek at the first post in the main query without side effects.
	global $wp_query;
	$posts = $wp_query->posts ?? [];
	if ( empty( $posts ) ) { return; }

	$first_id    = absint( $posts[0]->ID ?? 0 );
	$thumb_id    = $first_id ? get_post_thumbnail_id( $first_id ) : 0;
	if ( ! $thumb_id ) { return; }

	// Use arw-card (800×450) — matches card display size.
	$src = wp_get_attachment_image_src( $thumb_id, 'arw-card' )
		?: wp_get_attachment_image_src( $thumb_id, 'large' );
	if ( ! $src ) { return; }

	printf(
		'<link rel="preload" as="image" href="%s" fetchpriority="high" imagesrcset="%s" imagesizes="(max-width:600px) 100vw, 400px">' . "\n",
		esc_url( $src[0] ),
		esc_attr( wp_get_attachment_image_srcset( $thumb_id, 'arw-card' ) ?: '' )
	);
}, 3 );

// Set fetchpriority=high on first archive card image.
add_filter( 'wp_get_attachment_image_attributes', function ( $attr, $attachment ) {
	static $archive_done = false;
	if ( ! $archive_done && ( is_archive() || is_home() || is_search() ) ) {
		$attr['fetchpriority'] = 'high';
		$attr['loading']       = 'eager';
		$archive_done          = true;
	}
	return $attr;
}, 10, 2 );

// ─── Fonts preload ────────────────────────────────────────────────────────────

add_action( 'wp_head', function () {
	$fonts = [];
	foreach ( [ 'inter-var', 'space-grotesk-var' ] as $name ) {
		$path = ARW_PULSE_DIR . '/assets/fonts/' . $name . '.woff2';
		if ( file_exists( $path ) ) {
			$fonts[] = ARW_PULSE_URI . '/assets/fonts/' . $name . '.woff2';
		}
	}
	$fonts = apply_filters( 'arw_pulse_preload_fonts', $fonts );
	foreach ( $fonts as $font ) {
		printf(
			'<link rel="preload" as="font" type="font/woff2" href="%s" crossorigin>' . "\n",
			esc_url( $font )
		);
	}
}, 4 );

// ─── Critical CSS inline ──────────────────────────────────────────────────────

add_action( 'wp_head', function () {
	$path = ARW_PULSE_DIR . '/assets/css/critical.css';
	if ( ! file_exists( $path ) ) { return; }
	$css = file_get_contents( $path );
	if ( $css ) {
		// Defense in depth : strip </style> au cas où le fichier serait pollué
		// (admin compromis modifie critical.css → injection HTML qui casse le contexte).
		$css = str_ireplace( '</style', '', (string) $css );
		echo '<style id="arw-pulse-critical">' . $css . '</style>' . "\n";
	}
}, 5 );

// ─── Render-blocking: defer non-critical scripts ──────────────────────────────

add_filter( 'script_loader_tag', function ( $tag, $handle ) {
	$defer = apply_filters( 'arw_pulse_defer_scripts', [
		'wp-embed',
	] );
	if ( in_array( $handle, $defer, true ) ) {
		return str_replace( '<script ', '<script defer ', $tag );
	}
	return $tag;
}, 10, 2 );

// ─── Archive image: proper sizes attribute ────────────────────────────────────
// Ensures browser downloads the right srcset variant for card-sized images.

add_filter( 'wp_calculate_image_sizes', function ( $sizes, $size, $image_src, $image_meta, $attachment_id ) {
	if ( is_archive() || is_home() || is_search() ) {
		return '(max-width: 600px) 100vw, (max-width: 900px) 50vw, 400px';
	}
	return $sizes;
}, 10, 5 );

// ─── Native lazy-loading (belt-and-suspenders) ────────────────────────────────

add_filter( 'wp_lazy_loading_enabled', '__return_true' );

// ─── DNS prefetch ─────────────────────────────────────────────────────────────

add_filter( 'wp_resource_hints', function ( $hints, $relation_type ) {
	if ( 'dns-prefetch' === $relation_type ) {
		$hints = array_merge( $hints, apply_filters( 'arw_pulse_dns_prefetch', [] ) );
	}
	return $hints;
}, 10, 2 );

// ─── .htaccess : cache headers (Expires + Cache-Control) ─────────────────────
// Écrit nos règles entre des markers pour ne pas casser le bloc WordPress.
// Tourne une fois par version (admin uniquement, pas de hit fs sur requêtes front).

const ARW_PULSE_HTACCESS_VERSION = '1';

add_action( 'admin_init', function () {
	$stored = get_option( 'arw_pulse_htaccess_cache_v' );
	if ( $stored === ARW_PULSE_HTACCESS_VERSION ) { return; }
	if ( ! function_exists( 'insert_with_markers' ) ) {
		require_once ABSPATH . 'wp-admin/includes/misc.php';
	}
	if ( arw_pulse_write_cache_htaccess() ) {
		update_option( 'arw_pulse_htaccess_cache_v', ARW_PULSE_HTACCESS_VERSION );
	}
} );

function arw_pulse_write_cache_htaccess(): bool {
	if ( ! function_exists( 'get_home_path' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}
	$home_path = function_exists( 'get_home_path' ) ? get_home_path() : ABSPATH;
	$htaccess  = $home_path . '.htaccess';

	if ( ! file_exists( $htaccess ) || ! is_writable( $htaccess ) ) {
		return false;
	}

	$rules = [
		'<IfModule mod_expires.c>',
		'ExpiresActive On',
		'ExpiresByType text/css                "access plus 1 year"',
		'ExpiresByType application/javascript  "access plus 1 year"',
		'ExpiresByType image/jpeg              "access plus 1 year"',
		'ExpiresByType image/png               "access plus 1 year"',
		'ExpiresByType image/svg+xml           "access plus 1 year"',
		'ExpiresByType image/webp              "access plus 1 year"',
		'ExpiresByType image/avif              "access plus 1 year"',
		'ExpiresByType font/woff2              "access plus 1 year"',
		'ExpiresByType application/pdf         "access plus 1 month"',
		'</IfModule>',
		'',
		'<IfModule mod_headers.c>',
		'<FilesMatch "\.(css|js|woff2|jpg|jpeg|png|webp|avif|svg)$">',
		'Header set Cache-Control "public, max-age=31536000, immutable"',
		'</FilesMatch>',
		'</IfModule>',
	];

	return (bool) insert_with_markers( $htaccess, 'ARW Pulse Cache', $rules );
}

// ─── .htaccess : hardening (block install.php, wp-config exposure) ───────────
// install.php est servi 200 OK même après install — surface d'info disclosure
// (version WP leakée). On bloque dur via .htaccess.

const ARW_PULSE_HTACCESS_HARDEN_VERSION = '1';

add_action( 'admin_init', function () {
	$stored = get_option( 'arw_pulse_htaccess_harden_v' );
	if ( $stored === ARW_PULSE_HTACCESS_HARDEN_VERSION ) { return; }
	if ( ! function_exists( 'insert_with_markers' ) ) {
		require_once ABSPATH . 'wp-admin/includes/misc.php';
	}
	if ( arw_pulse_write_harden_htaccess() ) {
		update_option( 'arw_pulse_htaccess_harden_v', ARW_PULSE_HTACCESS_HARDEN_VERSION );
	}
} );

function arw_pulse_write_harden_htaccess(): bool {
	if ( ! function_exists( 'get_home_path' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}
	$home_path = function_exists( 'get_home_path' ) ? get_home_path() : ABSPATH;
	$htaccess  = $home_path . '.htaccess';
	if ( ! file_exists( $htaccess ) || ! is_writable( $htaccess ) ) {
		return false;
	}
	$rules = [
		'# Bloque /wp-admin/install.php (info disclosure version WP, useless après install)',
		'<FilesMatch "^install\.php$">',
		'Require all denied',
		'</FilesMatch>',
		'',
		'# Empêche l\'accès direct aux fichiers sensibles à la racine',
		'<FilesMatch "^(wp-config\.php|wp-config-sample\.php|readme\.html|license\.txt|\.htaccess|\.htpasswd)$">',
		'Require all denied',
		'</FilesMatch>',
		'',
		'# Bloque les patterns de backup courants',
		'<FilesMatch "\.(bak|backup|old|orig|save|swp|swo|tmp|log|sql|sql\.gz|tar|tar\.gz|zip)$">',
		'Require all denied',
		'</FilesMatch>',
	];
	return (bool) insert_with_markers( $htaccess, 'ARW Pulse Hardening', $rules );
}

// Failsafe PHP-level : si .htaccess pas writable ou ignoré par le serveur,
// on bloque install.php avant qu'il ne rende son HTML (qui leak la version WP via les CSS).
// `init` priority 0 fire après le chargement du thème mais avant que install.php ne rende sa page.
add_action( 'init', function () {
	$req = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	if ( $req && preg_match( '#/wp-admin/install\.php(?:[/?]|$)#', $req ) ) {
		// WP est déjà installé si cette option existe (siteurl est seedée à l'install).
		if ( get_option( 'siteurl' ) ) {
			status_header( 403 );
			nocache_headers();
			exit( 'Forbidden' );
		}
	}
}, 0 );
