<?php
/**
 * ARW Pulse — theme bootstrap.
 *
 * @package ARW_Pulse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ARW_PULSE_VERSION', wp_get_theme()->get( 'Version' ) );
define( 'ARW_PULSE_DIR', get_template_directory() );
define( 'ARW_PULSE_URI', get_template_directory_uri() );

add_action( 'after_setup_theme', function () {
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ] );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	// No fixed width/height — let the logo keep its natural aspect ratio,
	// and rely on the wp:site-logo block's "width" prop for display sizing.
	add_theme_support( 'custom-logo', [
		'flex-width'  => true,
		'flex-height' => true,
	] );

	register_nav_menus( [
		'primary' => __( 'Primary menu', 'arw-pulse' ),
		'footer'  => __( 'Footer menu', 'arw-pulse' ),
	] );

	load_theme_textdomain( 'arw-pulse', ARW_PULSE_DIR . '/languages' );
} );

// Auto-reset DB-stored templates/parts on version change so theme files always win.
// Avant le reset, on extrait les refs des blocs navigation (header → primary,
// footer → footer) pour les ré-injecter au render via inc/navigation.php.
// Sans ça, l'utilisateur doit ré-assigner ses menus à chaque mise à jour.
add_action( 'init', function () {
	$opt = 'arw_pulse_tpl_v';
	if ( get_option( $opt ) === ARW_PULSE_VERSION ) {
		return;
	}

	// ─── Préserver les navigation refs avant le delete ───
	$nav_refs = (array) get_option( 'arw_pulse_nav_refs', [ 'primary' => 0, 'footer' => 0 ] );
	$parts    = get_posts( [
		'post_type'      => 'wp_template_part',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'no_found_rows'  => true,
	] );
	foreach ( $parts as $part ) {
		$slot = $part->post_name === 'header' ? 'primary' : ( $part->post_name === 'footer' ? 'footer' : null );
		if ( ! $slot ) { continue; }
		// Regex tolérant : cherche "ref":N à l'intérieur d'un bloc wp:navigation.
		if ( preg_match( '/<!--\s*wp:navigation\b[^>]*?"ref"\s*:\s*(\d+)/', (string) $part->post_content, $m ) ) {
			$ref = (int) $m[1];
			if ( $ref > 0 && get_post_status( $ref ) === 'publish' ) {
				$nav_refs[ $slot ] = $ref;
			}
		}
	}
	update_option( 'arw_pulse_nav_refs', $nav_refs );

	// ─── Reset effectif ───
	foreach ( [ 'wp_template', 'wp_template_part' ] as $type ) {
		$ids = get_posts( [ 'post_type' => $type, 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids' ] );
		foreach ( $ids as $id ) {
			wp_delete_post( $id, true );
		}
	}
	// Flush rewrite rules — picks up new ones (manifest.webmanifest) added by the theme.
	flush_rewrite_rules( false );
	update_option( $opt, ARW_PULSE_VERSION );
} );

// Front styles — inline critical, enqueue the rest.
add_action( 'wp_enqueue_scripts', function () {
	$handle = 'arw-pulse';
	wp_enqueue_style( $handle, ARW_PULSE_URI . '/assets/css/theme.css', [], ARW_PULSE_VERSION );
}, 5 );

// Editor styles.
add_action( 'after_setup_theme', function () {
	add_editor_style( 'assets/css/editor.css' );
} );

// Load modules.
require_once ARW_PULSE_DIR . '/inc/admin-menu.php';
require_once ARW_PULSE_DIR . '/inc/features.php';
require_once ARW_PULSE_DIR . '/inc/homepage-editor.php';
require_once ARW_PULSE_DIR . '/inc/identity.php';
require_once ARW_PULSE_DIR . '/inc/security.php';
require_once ARW_PULSE_DIR . '/inc/cleanup.php';
require_once ARW_PULSE_DIR . '/inc/performance.php';
require_once ARW_PULSE_DIR . '/inc/seo.php';
require_once ARW_PULSE_DIR . '/inc/structured-data.php';
require_once ARW_PULSE_DIR . '/inc/schema.php';
require_once ARW_PULSE_DIR . '/inc/image.php';
require_once ARW_PULSE_DIR . '/inc/affiliate.php';
require_once ARW_PULSE_DIR . '/inc/form.php';
require_once ARW_PULSE_DIR . '/inc/media-kit.php';
require_once ARW_PULSE_DIR . '/inc/legal-defaults.php';
require_once ARW_PULSE_DIR . '/inc/sitemap.php';
require_once ARW_PULSE_DIR . '/inc/cookie-consent.php';
require_once ARW_PULSE_DIR . '/inc/motion.php';
require_once ARW_PULSE_DIR . '/inc/robots.php';
require_once ARW_PULSE_DIR . '/inc/branding.php';
require_once ARW_PULSE_DIR . '/inc/products.php';
require_once ARW_PULSE_DIR . '/inc/title.php';
require_once ARW_PULSE_DIR . '/inc/reading-time.php';
require_once ARW_PULSE_DIR . '/inc/patterns.php';
require_once ARW_PULSE_DIR . '/inc/front-page.php';
require_once ARW_PULSE_DIR . '/inc/category-base.php';
require_once ARW_PULSE_DIR . '/inc/featured-post.php';
require_once ARW_PULSE_DIR . '/inc/navigation.php';
