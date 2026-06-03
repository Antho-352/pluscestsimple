<?php
/**
 * Plugin Name: Annuaire — pluscestsimple
 * Description: Annuaire national des magasins déco/maison pour pluscestsimple.com. CPT pcs_etablissement + taxonomies type/région/ville + import Sirene/BAN/OSM + validation humaine + cron daily refresh + schema LocalBusiness. Démarrage NAF 47.59A/47.59B/47.53Z (déco), extensible plus tard.
 * Version:     1.1.0
 * Author:      Anthony Russo
 * Requires PHP: 8.0
 * Requires at least: 7.0
 * Text Domain: pluscestsimple
 *
 * Plugin destiné au thème classique PHP "pluscestsimple" (v2.0.2+).
 * Convention de préfixe :
 *   - pcs_directory_*       pour les fonctions
 *   - _pcs_etab_*           pour les meta keys
 *   - PCS_DIR_*             pour les constantes
 *   - .pcs-directory-*      pour CSS
 *
 * Aucun import n'est déclenché à l'activation. L'utilisateur lance les imports
 * manuellement via Annuaire > Importer depuis Sirene. Tous les imports passent
 * en statut "draft" et requièrent une validation humaine avant publication.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// ─── Constantes ──────────────────────────────────────────────────────────────

const PCS_DIR_VERSION         = '1.1.0';
const PCS_DIR_CPT             = 'pcs_etablissement';
const PCS_DIR_TAX_TYPE        = 'pcs_etab_type';
const PCS_DIR_TAX_REGION      = 'pcs_etab_region';
const PCS_DIR_TAX_VILLE       = 'pcs_etab_ville';
const PCS_DIR_CRON_HOOK       = 'pcs_directory_refresh';
const PCS_DIR_LOG_OPTION      = 'pcs_directory_import_log';
const PCS_DIR_LOG_MAX_ENTRIES = 20;

// Codes NAF retenus pour la déco/maison (démarrage).
// 47.59A : Commerce de détail de meubles
// 47.59B : Commerce de détail d'autres équipements du foyer
// 47.53Z : Commerce de détail de tapis, moquettes et revêtements de murs et de sols en magasin spécialisé
const PCS_DIR_DEFAULT_NAF = '47.59A,47.59B,47.53Z';

define( 'PCS_DIR_DIR', __DIR__ );
define( 'PCS_DIR_URL', plugin_dir_url( __FILE__ ) );

// ─── Avertissement thème ─────────────────────────────────────────────────────

// Notice admin si le thème attendu n'est pas actif.
add_action( 'admin_notices', function () {
	$theme    = wp_get_theme();
	$expected = [ 'pluscestsimple' ];
	$current  = $theme->get_template();
	if ( ! in_array( $current, $expected, true ) ) {
		echo '<div class="notice notice-warning"><p><strong>Annuaire pluscestsimple</strong> : ce plugin est conçu pour le thème <em>Plus c\'est simple</em>. Il fonctionne avec un autre thème mais le rendu peut être dégradé.</p></div>';
	}
} );

// ─── Chargement des modules ──────────────────────────────────────────────────

require_once PCS_DIR_DIR . '/inc/cpt.php';
require_once PCS_DIR_DIR . '/inc/taxonomies.php';
require_once PCS_DIR_DIR . '/inc/render.php';
require_once PCS_DIR_DIR . '/inc/shortcode.php';
require_once PCS_DIR_DIR . '/inc/seo.php';
require_once PCS_DIR_DIR . '/inc/cron.php';
require_once PCS_DIR_DIR . '/inc/import-sirene.php';
require_once PCS_DIR_DIR . '/inc/import-ban.php';
require_once PCS_DIR_DIR . '/inc/import-osm.php';
require_once PCS_DIR_DIR . '/inc/import-runner.php';
require_once PCS_DIR_DIR . '/inc/import-national.php';

if ( is_admin() ) {
	require_once PCS_DIR_DIR . '/inc/admin-import.php';
	require_once PCS_DIR_DIR . '/inc/admin-validate.php';
}

// ─── Assets frontend ─────────────────────────────────────────────────────────

// On register systématiquement, on enqueue conditionnellement. Le shortcode
// [pcs_directory] (potentiellement posé sur n'importe quelle page) peut alors
// appeler wp_enqueue_style/script('pcs-directory') sans avoir à re-déclarer.
add_action( 'wp_enqueue_scripts', function () {
	wp_register_style(
		'pcs-directory',
		PCS_DIR_URL . 'assets/css/directory.css',
		[],
		PCS_DIR_VERSION
	);
	wp_register_script(
		'pcs-directory',
		PCS_DIR_URL . 'assets/js/directory.js',
		[],
		PCS_DIR_VERSION,
		true
	);

	if ( is_post_type_archive( PCS_DIR_CPT ) || is_singular( PCS_DIR_CPT ) || is_tax( [ PCS_DIR_TAX_TYPE, PCS_DIR_TAX_REGION, PCS_DIR_TAX_VILLE ] ) ) {
		wp_enqueue_style( 'pcs-directory' );
		wp_enqueue_script( 'pcs-directory' );
	}
} );

// ─── Templates : fallback si le thème ne fournit pas les templates ───────────

add_filter( 'template_include', function ( $template ) {
	if ( is_post_type_archive( PCS_DIR_CPT ) || is_tax( [ PCS_DIR_TAX_TYPE, PCS_DIR_TAX_REGION, PCS_DIR_TAX_VILLE ] ) ) {
		$plugin_tpl = PCS_DIR_DIR . '/templates/archive-pcs_etablissement.php';
		// Priorité au template thème si présent.
		$theme_tpl = locate_template( [ 'archive-' . PCS_DIR_CPT . '.php' ] );
		return $theme_tpl ?: $plugin_tpl;
	}
	if ( is_singular( PCS_DIR_CPT ) ) {
		$plugin_tpl = PCS_DIR_DIR . '/templates/single-pcs_etablissement.php';
		$theme_tpl  = locate_template( [ 'single-' . PCS_DIR_CPT . '.php' ] );
		return $theme_tpl ?: $plugin_tpl;
	}
	return $template;
} );

// ─── Activation / désactivation ──────────────────────────────────────────────

/**
 * Hook d'activation : enregistre CPT + taxonomies, seede les régions FR,
 * planifie le cron quotidien, puis flush les permalinks.
 *
 * Aucun import n'est déclenché ici. L'utilisateur lance les imports manuellement.
 *
 * @return void
 */
function pcs_directory_activate(): void {
	if ( function_exists( 'pcs_directory_register_cpt' ) ) {
		pcs_directory_register_cpt();
	}
	if ( function_exists( 'pcs_directory_register_taxonomies' ) ) {
		pcs_directory_register_taxonomies();
	}
	if ( function_exists( 'pcs_directory_seed_default_types' ) ) {
		pcs_directory_seed_default_types();
	}
	if ( function_exists( 'pcs_directory_seed_default_regions' ) ) {
		pcs_directory_seed_default_regions();
	}
	if ( function_exists( 'pcs_directory_schedule_cron' ) ) {
		pcs_directory_schedule_cron();
	}
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'pcs_directory_activate' );

/**
 * Hook de désactivation : déprogramme le cron, flush les permalinks.
 *
 * On ne supprime PAS les données (CPT, taxonomies, options) pour éviter
 * toute perte accidentelle. La désinstallation propre passera par un
 * uninstall.php dédié si besoin.
 *
 * @return void
 */
function pcs_directory_deactivate(): void {
	wp_clear_scheduled_hook( PCS_DIR_CRON_HOOK );
	if ( defined( 'PCS_DIR_NAT_TICK_HOOK' ) ) {
		wp_clear_scheduled_hook( PCS_DIR_NAT_TICK_HOOK );
	}
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'pcs_directory_deactivate' );

// ─── Version-bump : flush rewrite rules une fois sur upgrade ────────────────

add_action( 'init', function () {
	$stored = get_option( 'pcs_directory_v' );
	if ( $stored !== PCS_DIR_VERSION ) {
		flush_rewrite_rules( false );
		update_option( 'pcs_directory_v', PCS_DIR_VERSION );
	}
}, 99 );
