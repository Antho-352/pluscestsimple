<?php
/**
 * Plugin Name: Compatibilimètre — pluscestsimple
 * Description: Outil Compatibilimètre pour pluscestsimple.com : CPT arw_compat_rule (201 règles techniques DTU/normes), pages SEO long-tail auto, lead-magnet, sync Brevo (à venir). Conçu pour le thème pluscestsimple v2.0+.
 * Version:     1.0.0
 * Author:      Anthony Russo
 * Requires PHP: 8.0
 *
 * Drop this folder in wp-content/plugins/ and activate via Plugins admin.
 * Le CPT slug `arw_compat_rule` est conservé pour ne pas casser la DB des règles
 * existantes. Les filtres exposés au thème sont préfixés pcs_ (pluscestsimple v2.0+).
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// ─── Constants ───────────────────────────────────────────────────────────────

const ARW_MAISON_VERSION = '1.0.0';

define( 'ARW_MAISON_DIR', __DIR__ );
define( 'ARW_MAISON_URL', plugin_dir_url( __FILE__ ) );

const ARW_MAISON_RULE_CPT     = 'arw_compat_rule';
const ARW_MAISON_CATEGORY_TAX = 'arw_compat_category';

// ─── Boot ─────────────────────────────────────────────────────────────────────

// Admin warning si le thème attendu n'est pas actif.
add_action( 'admin_notices', function () {
	$theme    = wp_get_theme();
	$expected = [ 'pluscestsimple', 'arw-pulse' ]; // arw-pulse temporairement toléré pendant la migration.
	$current  = $theme->get_template();
	if ( ! in_array( $current, $expected, true ) ) {
		echo '<div class="notice notice-warning"><p><strong>Compatibilimètre</strong> : ce plugin est conçu pour le thème <em>Plus c\'est simple</em>. Le rendu peut être dégradé avec un autre thème (fonts non préchargées, breadcrumbs incomplets).</p></div>';
	}
} );

// Load modules.
require_once ARW_MAISON_DIR . '/inc/taxonomies.php';
require_once ARW_MAISON_DIR . '/inc/cpt-compat-rule.php';
require_once ARW_MAISON_DIR . '/inc/csv-import.php';
require_once ARW_MAISON_DIR . '/inc/json-export.php';
require_once ARW_MAISON_DIR . '/inc/page-bootstrap.php';
require_once ARW_MAISON_DIR . '/inc/compat-display.php';
require_once ARW_MAISON_DIR . '/inc/schema-compat.php';
require_once ARW_MAISON_DIR . '/inc/seo-compat.php';
require_once ARW_MAISON_DIR . '/inc/lead-magnet.php';
require_once ARW_MAISON_DIR . '/inc/lead-magnet-admin.php';
require_once ARW_MAISON_DIR . '/inc/home-content.php';
require_once ARW_MAISON_DIR . '/inc/home-pattern.php';
require_once ARW_MAISON_DIR . '/inc/enrichment-import.php';

// ─── Theme integration : align font preload with the pluscestsimple theme ──
//
// Le thème pluscestsimple déclare Inter (body) + Fraunces (display). On
// override la liste de preload pour ne charger que ces deux fonts.

add_filter( 'pcs_preload_fonts', function ( $fonts ) {
	$theme_url = get_template_directory_uri();
	return [
		$theme_url . '/assets/fonts/inter-var.woff2',
		$theme_url . '/assets/fonts/fraunces-var.woff2',
	];
} );

// ─── Frontend assets (registered, enqueued conditionally by shortcodes) ──────

add_action( 'wp_enqueue_scripts', function () {
	wp_register_style(
		'arw-maison-compat',
		ARW_MAISON_URL . 'assets/css/compat.css',
		[],
		ARW_MAISON_VERSION
	);
	wp_register_script(
		'arw-maison-compat',
		ARW_MAISON_URL . 'assets/js/compat.js',
		[],
		ARW_MAISON_VERSION,
		true
	);
} );

// ─── Register block templates from /templates/ (plugin) ──────────────────────

add_action( 'init', function () {
	if ( ! function_exists( 'register_block_template' ) ) { return; }

	// Single rule — slug matches WP template hierarchy → auto-applied for arw_compat_rule.
	$single = ARW_MAISON_DIR . '/templates/single-arw_compat_rule.html';
	if ( file_exists( $single ) ) {
		register_block_template( 'arw-pack-maison//single-arw_compat_rule', [
			'title'       => __( 'Règle Compatibilimètre', 'arw-maison' ),
			'description' => __( 'Template fiche de règle : verdict + explication + alternatives + règles liées.', 'arw-maison' ),
			'content'     => file_get_contents( $single ),
			'post_types'  => [ ARW_MAISON_RULE_CPT ],
		] );
	}
}, 20 );

// ─── Activation : flush rewrite rules ────────────────────────────────────────

register_activation_hook( __FILE__, function () {
	if ( function_exists( 'arw_maison_register_taxonomies' ) ) {
		arw_maison_register_taxonomies();
	}
	if ( function_exists( 'arw_maison_register_rule_cpt' ) ) {
		arw_maison_register_rule_cpt();
	}
	if ( function_exists( 'arw_maison_ensure_index_page' ) ) {
		arw_maison_ensure_index_page();
	}
	if ( function_exists( 'arw_maison_lead_install_default_pdf' ) ) {
		arw_maison_lead_install_default_pdf();
	}
	flush_rewrite_rules();
} );

register_deactivation_hook( __FILE__, function () {
	wp_clear_scheduled_hook( 'arw_maison_lead_cleanup' );
	flush_rewrite_rules();
} );

// ─── Version-bump hook : flush rewrite rules once on upgrade ────────────────

add_action( 'init', function () {
	$stored = get_option( 'arw_maison_v' );
	if ( $stored !== ARW_MAISON_VERSION ) {
		flush_rewrite_rules( false );
		update_option( 'arw_maison_v', ARW_MAISON_VERSION );
	}
}, 99 );
