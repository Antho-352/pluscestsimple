<?php
/**
 * Pattern registration + categories.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'init', function () {
	register_block_pattern_category( 'arw-home', [ 'label' => __( 'ARW / Accueil', 'arw-pulse' ) ] );
	register_block_pattern_category( 'arw-magazine', [ 'label' => __( 'ARW / Magazine', 'arw-pulse' ) ] );
	register_block_pattern_category( 'arw-affiliate', [ 'label' => __( 'ARW / Affiliation', 'arw-pulse' ) ] );
	register_block_pattern_category( 'arw-legal', [ 'label' => __( 'ARW / Légal', 'arw-pulse' ) ] );
	register_block_pattern_category( 'arw-marketing', [ 'label' => __( 'ARW / Marketing', 'arw-pulse' ) ] );
	register_block_pattern_category( 'arw-press', [ 'label' => __( 'ARW / Kit média', 'arw-pulse' ) ] );
} );
