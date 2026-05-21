<?php
/**
 * Reading time (cached as post meta on save).
 *
 * @package pluscestsimple
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function pcs_word_count( string $text ): int {
	// UTF-8-safe token count: matches runs of letters/numbers across all scripts,
	// handles French accents, CJK words, etc. str_word_count() was byte-oriented
	// and under-counted words with accented chars.
	if ( preg_match_all( '/[\p{L}\p{N}]+/u', $text, $m ) ) {
		return count( $m[0] );
	}
	return 0;
}

function pcs_reading_time( $post_id = null ) {
	$post_id = $post_id ?: get_the_ID();
	$cached  = get_post_meta( $post_id, '_pcs_reading_time', true );
	if ( '' !== $cached ) { return (int) $cached; }

	$post = get_post( $post_id );
	if ( ! $post ) { return 0; }
	$words = pcs_word_count( wp_strip_all_tags( $post->post_content ) );
	$wpm   = (int) apply_filters( 'pcs_wpm', 220 );
	$mins  = max( 1, (int) round( $words / max( 1, $wpm ) ) );

	update_post_meta( $post_id, '_pcs_reading_time', $mins );
	update_post_meta( $post_id, '_pcs_word_count', $words );
	return $mins;
}

// Invalidate on save.
add_action( 'save_post', function ( $post_id ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) { return; }
	delete_post_meta( $post_id, '_pcs_reading_time' );
	delete_post_meta( $post_id, '_pcs_word_count' );
} );

// ─── Shortcodes for post-meta template part ───────────────────────────────────

add_shortcode( 'pcs_reading_time', function () {
	if ( ! is_singular( 'post' ) ) { return ''; }
	$mins = pcs_reading_time( get_the_ID() );
	if ( ! $mins ) { return ''; }
	return sprintf(
		'<span class="pcs-meta-item pcs-meta-item--reading"><span aria-hidden="true">•</span> %d&nbsp;min de lecture</span>',
		(int) $mins
	);
} );

/**
 * Display "Mis à jour le X" only when post was modified >= 1 day after publication.
 * Avoids showing meaningless "updated today" stamps on freshly-published posts.
 */
add_shortcode( 'pcs_updated', function () {
	if ( ! is_singular( 'post' ) ) { return ''; }
	$post = get_queried_object();
	$pub  = (int) get_post_time( 'U', true, $post );
	$mod  = (int) get_post_modified_time( 'U', true, $post );
	if ( ( $mod - $pub ) < DAY_IN_SECONDS ) { return ''; }
	return sprintf(
		'<span class="pcs-meta-item pcs-meta-item--updated"><span aria-hidden="true">•</span> Mis à jour le <time datetime="%s">%s</time></span>',
		esc_attr( get_the_modified_date( 'c', $post ) ),
		esc_html( get_the_modified_date( 'j F Y', $post ) )
	);
} );
