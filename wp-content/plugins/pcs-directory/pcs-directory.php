<?php
/**
 * Plugin Name: Annuaire — pluscestsimple
 * Description: Annuaire des magasins déco/maison. CPT pcs_boutique + 6 taxonomies + import JSONL + carte Leaflet + filtres AJAX.
 * Version:     2.2.1
 * Author:      Anthony Russo
 * Requires PHP: 8.0
 * Requires at least: 7.0
 * Tested up to: 7.0
 * Text Domain: pcs-directory
 *
 * Conventions v2 :
 *   - pcs_directory_*   fonctions
 *   - _pcs_*            meta keys
 *   - PCS_DIR_*         constantes
 *   - .pcs-*            CSS
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// ─── Constantes ───────────────────────────────────────────────────────────────

const PCS_DIR_VERSION = '2.2.1';
const PCS_DIR_CPT     = 'pcs_boutique';

define( 'PCS_DIR_DIR', __DIR__ );
define( 'PCS_DIR_URL', plugin_dir_url( __FILE__ ) );

// ─── Modules ──────────────────────────────────────────────────────────────────

require_once PCS_DIR_DIR . '/inc/cpt.php';
require_once PCS_DIR_DIR . '/inc/taxonomies.php';
require_once PCS_DIR_DIR . '/inc/helpers.php';
require_once PCS_DIR_DIR . '/inc/import-jsonl.php';
require_once PCS_DIR_DIR . '/inc/ajax-filter.php';
require_once PCS_DIR_DIR . '/inc/seo.php';

if ( is_admin() ) {
	require_once PCS_DIR_DIR . '/inc/admin-import.php';
	require_once PCS_DIR_DIR . '/inc/admin-stats.php';
	require_once PCS_DIR_DIR . '/inc/term-meta.php';
}

// ─── Assets frontend ──────────────────────────────────────────────────────────

add_action( 'wp_enqueue_scripts', function () {
	$on_directory = is_post_type_archive( PCS_DIR_CPT )
		|| is_singular( PCS_DIR_CPT )
		|| is_tax( [ 'pcs_cat', 'pcs_type', 'pcs_mode', 'pcs_dept', 'pcs_region', 'pcs_ville' ] );

	if ( ! $on_directory ) {
		return;
	}

	wp_enqueue_style( 'leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', [], '1.9.4' );
	wp_enqueue_script( 'leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', [], '1.9.4', true );
	wp_enqueue_style( 'pcs-directory', PCS_DIR_URL . 'assets/css/directory.css', [], PCS_DIR_VERSION );
	wp_enqueue_script(
		'pcs-directory',
		PCS_DIR_URL . 'assets/js/directory.js',
		[ 'leaflet' ],
		PCS_DIR_VERSION,
		true
	);
	wp_localize_script( 'pcs-directory', 'pcsDir', [
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'pcs_filter_nonce' ),
	] );
} );

// ─── Template loader ──────────────────────────────────────────────────────────

add_filter( 'template_include', function ( string $template ): string {
	// Archive / page mère annuaire.
	if ( is_post_type_archive( PCS_DIR_CPT ) ) {
		$t = PCS_DIR_DIR . '/templates/archive-pcs_boutique.php';
		return file_exists( $t ) ? $t : $template;
	}

	// Fiche boutique.
	if ( is_singular( PCS_DIR_CPT ) ) {
		$t = PCS_DIR_DIR . '/templates/single-pcs_boutique.php';
		return file_exists( $t ) ? $t : $template;
	}

	// Taxonomies — chaque taxonomy a son template dédié.
	$tax_templates = [
		'pcs_dept'   => 'taxonomy-pcs_dept.php',
		'pcs_region' => 'taxonomy-pcs_region.php',
		'pcs_ville'  => 'taxonomy-pcs_ville.php',
		'pcs_cat'    => 'taxonomy-pcs_cat.php',
		'pcs_type'   => 'taxonomy-pcs_type.php',
		'pcs_mode'   => 'taxonomy-pcs_mode.php',
	];
	foreach ( $tax_templates as $tax => $tpl_file ) {
		if ( is_tax( $tax ) ) {
			$t = PCS_DIR_DIR . '/templates/' . $tpl_file;
			// Fallback sur taxonomy-pcs_cat si template spécifique absent.
			if ( ! file_exists( $t ) ) {
				$t = PCS_DIR_DIR . '/templates/taxonomy-pcs_cat.php';
			}
			return file_exists( $t ) ? $t : $template;
		}
	}

	return $template;
} );

// ─── Activation ───────────────────────────────────────────────────────────────

register_activation_hook( __FILE__, function () {
	// Enregistre CPT + taxonomies pour que les rewrite rules soient créées.
	pcs_directory_register_cpt();
	pcs_directory_register_taxonomies();
	flush_rewrite_rules();
} );

register_deactivation_hook( __FILE__, function () {
	flush_rewrite_rules();
} );

// Version-bump : flush rewrite rules une seule fois.
add_action( 'init', function () {
	if ( get_option( 'pcs_directory_v' ) !== PCS_DIR_VERSION ) {
		flush_rewrite_rules( false );
		update_option( 'pcs_directory_v', PCS_DIR_VERSION );
	}
}, 99 );
