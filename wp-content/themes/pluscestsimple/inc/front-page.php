<?php
/**
 * Front-page content dispatcher.
 *
 * Le fichier /templates/front-page.html du thème ne contient qu'un header, un <main>,
 * un shortcode [arw_pulse_front_page], et un footer. Ce shortcode renvoie le layout
 * par défaut (citymoto-style magazine). Un pack de niche (outdoor, craft, B2B…)
 * peut l'override simplement en ré-enregistrant le shortcode après le thème.
 *
 * Extension via filtre : `arw_pulse_front_page_markup` reçoit la string de blocs
 * à rendre. Priorité standard 10 pour le thème, 20+ pour les packs.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function arw_pulse_default_front_markup(): string {
	return <<<BLOCKS
<!-- wp:pattern {"slug":"arw-pulse/hero-front"} /-->

<!-- wp:pattern {"slug":"arw-pulse/categories-marquee"} /-->

<!-- wp:pattern {"slug":"arw-pulse/hero-featured"} /-->

<!-- wp:shortcode -->[arw_essential]<!-- /wp:shortcode -->

<!-- wp:pattern {"slug":"arw-pulse/dossiers-deep"} /-->

<!-- wp:pattern {"slug":"arw-pulse/manifesto"} /-->

<!-- wp:pattern {"slug":"arw-pulse/comparatif-cover"} /-->

<!-- wp:pattern {"slug":"arw-pulse/grid-magazine"} /-->

<!-- wp:pattern {"slug":"arw-pulse/partners-marquee"} /-->

<!-- wp:pattern {"slug":"arw-pulse/newsletter-inline"} /-->
BLOCKS;
}

add_shortcode( 'arw_pulse_front_page', function () {
	$markup = apply_filters( 'arw_pulse_front_page_markup', arw_pulse_default_front_markup() );
	// do_blocks rend les blocs (incluant wp:shortcode qui extrait le shortcode).
	// do_shortcode re-traite les shortcodes restants (WordPress ne le fait pas en cascade).
	return do_shortcode( do_blocks( (string) $markup ) );
} );
