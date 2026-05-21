<?php
/**
 * Theme supports & image sizes.
 *
 * @package pluscestsimple
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'after_setup_theme',
	function () {
		load_theme_textdomain( 'pluscestsimple', PCS_DIR . '/languages' );

		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'editor-styles' );
		add_theme_support( 'wp-block-styles' );
		add_theme_support( 'align-wide' );
		add_theme_support(
			'html5',
			[ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ]
		);
		add_theme_support(
			'custom-logo',
			[
				'flex-width'  => true,
				'flex-height' => true,
			]
		);

		// Image sizes utilisées dans les templates et patterns Gutenberg.
		add_image_size( 'pcs-hero', 1200, 675, true );        // 16:9 — accueil + hero d'article
		add_image_size( 'pcs-card', 800, 600, true );         // 4:3 — cartes d'article
		add_image_size( 'pcs-card-sm', 400, 300, true );      // 4:3 — cartes secondaires
		add_image_size( 'pcs-square', 600, 600, true );       // 1:1 — usages divers
		add_image_size( 'pcs-vertical', 600, 800, false );    // 3:4 — portraits / éditorial

		add_editor_style( 'assets/css/editor.css' );
	}
);
