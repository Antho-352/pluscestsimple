<?php
/**
 * Plugin Name: Bannières — pluscestsimple
 * Description: Gestion des bannières publicitaires (display / sponsorisé / affilié) pour pluscestsimple.com. CPT pcs_banner + taxonomy pcs_banner_slot + bloc Gutenberg + shortcode. Cache transient 5 min, rel auto, étiquettes auto.
 * Version:     1.0.0
 * Author:      Anthony Russo
 * Requires PHP: 8.0
 * Requires at least: 7.0
 * Text Domain: pluscestsimple
 *
 * Plugin destiné au thème classique PHP "pluscestsimple" (v2.0.2+).
 * Convention de préfixe : pcs_ pour les fonctions/constantes,
 * _pcs_banner_ pour les meta keys, .pcs-banner-* pour CSS.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// ─── Constantes ──────────────────────────────────────────────────────────────

const PCS_BANNER_VERSION       = '1.0.0';
const PCS_BANNER_CPT           = 'pcs_banner';
const PCS_BANNER_SLOT_TAX      = 'pcs_banner_slot';
const PCS_BANNER_CACHE_PREFIX  = 'pcs_banner_';
const PCS_BANNER_CACHE_TTL     = 300; // 5 minutes (5 * MINUTE_IN_SECONDS).

define( 'PCS_BANNER_DIR', __DIR__ );
define( 'PCS_BANNER_URL', plugin_dir_url( __FILE__ ) );

// ─── Avertissement thème ─────────────────────────────────────────────────────

// Notice admin si le thème attendu n'est pas actif.
add_action( 'admin_notices', function () {
	$theme    = wp_get_theme();
	$expected = [ 'pluscestsimple' ];
	$current  = $theme->get_template();
	if ( ! in_array( $current, $expected, true ) ) {
		echo '<div class="notice notice-warning"><p><strong>Bannières pluscestsimple</strong> : ce plugin est conçu pour le thème <em>Plus c\'est simple</em>. Il fonctionne avec un autre thème mais le rendu peut être dégradé.</p></div>';
	}
} );

// ─── Chargement des modules ──────────────────────────────────────────────────

require_once PCS_BANNER_DIR . '/inc/taxonomies.php';
require_once PCS_BANNER_DIR . '/inc/cpt.php';
require_once PCS_BANNER_DIR . '/inc/render.php';
require_once PCS_BANNER_DIR . '/inc/block.php';
require_once PCS_BANNER_DIR . '/inc/shortcode.php';
require_once PCS_BANNER_DIR . '/inc/admin-list.php';
require_once PCS_BANNER_DIR . '/inc/assets.php';

// ─── Activation / désactivation ──────────────────────────────────────────────

/**
 * Hook d'activation : enregistre CPT + taxo puis flush les permalinks.
 *
 * @return void
 */
function pcs_banner_activate(): void {
	// Les fonctions register sont déjà accrochées à 'init',
	// mais l'activation passe par un cycle de chargement distinct.
	if ( function_exists( 'pcs_banner_register_taxonomy' ) ) {
		pcs_banner_register_taxonomy();
	}
	if ( function_exists( 'pcs_banner_register_cpt' ) ) {
		pcs_banner_register_cpt();
	}
	if ( function_exists( 'pcs_banner_seed_default_slots' ) ) {
		pcs_banner_seed_default_slots();
	}
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'pcs_banner_activate' );

/**
 * Hook de désactivation : flush les permalinks et purge le cache transient.
 *
 * @return void
 */
function pcs_banner_deactivate(): void {
	if ( function_exists( 'pcs_banner_flush_all_cache' ) ) {
		pcs_banner_flush_all_cache();
	}
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'pcs_banner_deactivate' );
