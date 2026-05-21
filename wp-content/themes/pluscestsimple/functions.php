<?php
/**
 * Plus c'est simple — bootstrap du thème.
 *
 * Charge les modules /inc/ dans un ordre déterministe. Chaque module est
 * autonome et idempotent (réimport possible sans effets de bord).
 *
 * @package pluscestsimple
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PCS_VERSION', wp_get_theme()->get( 'Version' ) );
define( 'PCS_DIR', get_template_directory() );
define( 'PCS_URI', get_template_directory_uri() );

$pcs_modules = [
	'theme-supports',  // after_setup_theme, image sizes, custom-logo, etc.
	'menus',           // register_nav_menus + fallback wp_nav_menu
	'enqueue',         // CSS / JS front + editor
	'cleanup',         // Bloat removal (emoji, embed, xmlrpc, jQuery front)
	'image',           // Tailles supplémentaires + <picture> AVIF/WebP + alt fallback
	'branding',        // Logo SVG fallback, favicon, apple-touch-icon, manifest
	'seo',             // Meta tags, OG, Twitter, canonical, robots
	'schema',          // JSON-LD : Organization, WebSite, Article, BreadcrumbList
	'breadcrumbs',     // pcs_breadcrumbs() utilisable dans templates
	'security',        // Headers HTTP, anti-enum, security.txt
	'performance',     // Preload fonts, defer, fetchpriority
	'robots',          // robots.txt custom + sitemap declaration
	'reading-time',    // pcs_reading_time() + shortcode
	'structured-data', // Meta boxes FAQ + HowTo (consommé par schema.php)
	'affiliate',       // Auto rel="sponsored nofollow" + disclosure
	'form',            // CPT arw_submission + REST endpoint /pcs/v1/submit
	'cookie-consent',  // Bandeau natif Consent Mode v2
	'legal-defaults',  // Page admin mentions légales + placeholders
	'identity',        // Page admin Identité & Social + bindings filtres
	'view-transitions', // Meta tag + JS minimal pour view transitions
	'category-base',   // Retire /category/ de l'URL des catégories
];

foreach ( $pcs_modules as $pcs_module ) {
	$pcs_file = PCS_DIR . '/inc/' . $pcs_module . '.php';
	if ( file_exists( $pcs_file ) ) {
		require_once $pcs_file;
	}
}
unset( $pcs_modules, $pcs_module, $pcs_file );
