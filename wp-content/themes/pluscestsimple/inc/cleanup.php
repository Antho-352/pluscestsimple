<?php
/**
 * Strip WordPress bloat that hurts perf or SEO on a modern FSE theme.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Emoji script + styles — irrelevant on modern browsers.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

// Head cleanup.
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'feed_links_extra', 3 );
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
remove_action( 'wp_head', 'rest_output_link_wp_head' );
remove_action( 'template_redirect', 'rest_output_link_header', 11 );

// NOTE: we keep ?ver=X query strings — they are how WordPress busts caches
// when the theme version bumps. Stripping them means CDN / proxy caches
// serve stale CSS after updates. Left in on purpose.

// Dequeue styles + scripts we don't need on the frontend.
add_action( 'wp_enqueue_scripts', function () {
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'classic-theme-styles' );

	if ( ! is_admin() && ! is_user_logged_in() ) {
		wp_deregister_script( 'jquery' );
		wp_dequeue_script( 'wp-embed' );     // oEmbed consumer JS — not needed.
	}
}, 100 );

// Remove block supports / duotone SVG injected in <body> — pure overhead.
remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
remove_action( 'wp_footer',    'wp_global_styles_render_svg_filters' );

// Disable comments feature globally (reactivate per-site if needed).
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );
add_action( 'admin_menu', function () {
	remove_menu_page( 'edit-comments.php' );
} );
add_action( 'init', function () {
	if ( is_admin_bar_showing() ) {
		remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60 );
	}
} );

// Disable XML-RPC.
add_filter( 'xmlrpc_enabled', '__return_false' );
remove_action( 'wp_head', 'rsd_link' );

// Disable REST for anonymous users on sensitive endpoints (keeps /wp/v2 for editor).
add_filter( 'rest_endpoints', function ( $endpoints ) {
	if ( ! is_user_logged_in() && isset( $endpoints['/wp/v2/users'] ) ) {
		unset( $endpoints['/wp/v2/users'] );
	}
	if ( ! is_user_logged_in() && isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) {
		unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
	}
	return $endpoints;
} );

// Remove default feeds links we don't need (keep main feed only).
remove_action( 'wp_head', 'feed_links', 2 );
add_action( 'wp_head', function () {
	printf(
		'<link rel="alternate" type="application/rss+xml" title="%s" href="%s">' . "\n",
		esc_attr( get_bloginfo( 'name' ) . ' » Feed' ),
		esc_url( get_bloginfo( 'rss2_url' ) )
	);
}, 2 );
