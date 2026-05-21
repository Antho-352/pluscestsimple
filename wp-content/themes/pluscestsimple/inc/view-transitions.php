<?php
/**
 * View Transitions — meta tag opt-in pour les transitions inter-pages.
 *
 * Activable/désactivable via le filter `pcs_view_transitions`.
 * Pour la transition visuelle CSS, voir assets/css/theme.css (règle @view-transition).
 *
 * @package pluscestsimple
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'wp_head',
	function () {
		if ( apply_filters( 'pcs_view_transitions', true ) ) {
			echo '<meta name="view-transition" content="same-origin">' . "\n";
		}
	},
	2
);
