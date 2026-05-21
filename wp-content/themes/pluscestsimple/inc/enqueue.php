<?php
/**
 * Enqueue des CSS et JS du thème.
 *
 * Convention : un seul fichier CSS principal (assets/css/theme.css)
 * et un seul fichier JS (assets/js/theme.js) pour minimiser les requêtes.
 * Le critical CSS est inliné dans `inc/performance.php` si présent.
 *
 * @package pluscestsimple
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'wp_enqueue_scripts',
	function () {
		// style.css est requis par WP (header de thème) mais on ne le charge pas réellement —
		// tout le CSS vit dans assets/css/theme.css. On l'enregistre tout de même comme dépendance.
		wp_register_style( 'pluscestsimple-style', get_stylesheet_uri(), [], PCS_VERSION );

		wp_enqueue_style(
			'pluscestsimple-theme',
			PCS_URI . '/assets/css/theme.css',
			[ 'pluscestsimple-style' ],
			PCS_VERSION
		);

		wp_enqueue_script(
			'pluscestsimple-theme',
			PCS_URI . '/assets/js/theme.js',
			[],
			PCS_VERSION,
			[
				'in_footer' => true,
				'strategy'  => 'defer',
			]
		);

		// Comment-reply WP : seulement sur singulier où les commentaires sont ouverts.
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
	},
	5
);
