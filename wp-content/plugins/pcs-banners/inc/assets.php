<?php
/**
 * Enqueue CSS conditionnel : on n'ajoute le fichier que si au moins
 * une bannière a été rendue dans la page courante.
 *
 * Stratégie : on enregistre le handle au wp_enqueue_scripts (priorité haute)
 * mais on l'enqueue effectivement au wp_footer une fois qu'on sait si une
 * bannière a été rendue.
 *
 * Comme le CSS est minimal (~50 lignes) et que charger en footer n'est pas
 * idéal pour le FOUC, on a un deuxième mécanisme : on enqueue dès qu'on
 * détecte un bloc pcs/banner-slot dans le contenu via has_block(). Pour les
 * shortcodes ou les appels directs depuis le thème, on tombe sur le fallback
 * footer.
 *
 * @package PCS_Banners
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Enregistre le handle CSS (sans l'enqueue).
 *
 * @return void
 */
function pcs_banner_register_assets(): void {
	wp_register_style(
		'pcs-banners',
		PCS_BANNER_URL . 'assets/css/banners.css',
		[],
		PCS_BANNER_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'pcs_banner_register_assets', 5 );

/**
 * Enqueue préventif si le contenu courant contient le bloc pcs/banner-slot.
 * Détection légère via has_block (parsing fait par le core, mis en cache).
 *
 * @return void
 */
function pcs_banner_maybe_enqueue_for_block(): void {
	if ( is_admin() ) {
		return;
	}
	if ( ! is_singular() ) {
		// Sur les listes (home, archive…), on ne peut pas savoir → on s'appuie
		// sur le fallback footer.
		return;
	}
	$post = get_post();
	if ( ! $post instanceof WP_Post ) {
		return;
	}
	if ( function_exists( 'has_block' ) && has_block( 'pcs/banner-slot', $post ) ) {
		wp_enqueue_style( 'pcs-banners' );
	}
	// Détection shortcode aussi (utile dans content patterns).
	if ( function_exists( 'has_shortcode' ) && has_shortcode( $post->post_content, 'pcs_banner' ) ) {
		wp_enqueue_style( 'pcs-banners' );
	}
}
add_action( 'wp_enqueue_scripts', 'pcs_banner_maybe_enqueue_for_block', 20 );

/**
 * Fallback : si une bannière a été rendue (ex : appel direct depuis le thème)
 * et que le CSS n'a pas encore été enqueue, on l'injecte inline au footer
 * pour éviter le FOUC sur les requêtes suivantes.
 *
 * @return void
 */
function pcs_banner_footer_fallback(): void {
	if ( ! pcs_banner_has_been_rendered() ) {
		return;
	}
	if ( wp_style_is( 'pcs-banners', 'enqueued' ) || wp_style_is( 'pcs-banners', 'done' ) ) {
		return;
	}

	// On injecte inline le CSS (lecture du fichier) — évite un round-trip HTTP
	// pour cette page et garantit que les bannières sont stylées même si
	// has_block() n'a pas matché (appel programmatique depuis le thème).
	$css_path = PCS_BANNER_DIR . '/assets/css/banners.css';
	if ( file_exists( $css_path ) ) {
		$css = (string) file_get_contents( $css_path );
		if ( '' !== $css ) {
			printf(
				'<style id="pcs-banners-inline">%s</style>',
				wp_strip_all_tags( $css )
			);
		}
	}
}
add_action( 'wp_footer', 'pcs_banner_footer_fallback', 99 );
