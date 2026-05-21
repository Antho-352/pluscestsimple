<?php
/**
 * Fil d'Ariane visuel — fonction autonome `pcs_breadcrumbs()` à appeler dans
 * les templates (single.php, page.php, category.php, archive.php, search.php,
 * 404.php).
 *
 * Rend `<nav class="pcs-breadcrumbs" aria-label="Fil d'Ariane">…</nav>` avec un
 * marquage microdata Schema.org BreadcrumbList. La hiérarchie de fil est calculée
 * via `pcs_get_breadcrumbs()` (définie dans inc/schema.php) — elle reste la
 * source de vérité, partagée entre le rendu visuel et le JSON-LD.
 *
 * Séparateur : `<span class="pcs-breadcrumbs__sep" aria-hidden="true">/</span>`
 *
 * Le dernier item (page courante) est rendu en `<span aria-current="page">`,
 * les items précédents en `<a>`. Microdata `itemListElement` posé sur chaque
 * crumb (position 1-based).
 *
 * @package pluscestsimple
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Rend le fil d'Ariane HTML. Echo direct (consommé par les templates).
 * Aucun rendu sur la home (fil non pertinent).
 *
 * @param array{
 *     show_on_front?: bool,   Force le rendu sur la home si true. Défaut : false.
 *     separator?: string,     Caractère de séparation visuel. Défaut : '/'.
 * } $args
 */
function pcs_breadcrumbs( array $args = [] ): void {
	$args = wp_parse_args( $args, [
		'show_on_front' => false,
		'separator'     => '/',
	] );

	if ( is_front_page() && ! $args['show_on_front'] ) {
		return;
	}

	$crumbs = function_exists( 'pcs_get_breadcrumbs' ) ? pcs_get_breadcrumbs() : [];
	if ( empty( $crumbs ) ) {
		return;
	}

	$total = count( $crumbs );
	$sep   = '<span class="pcs-breadcrumbs__sep" aria-hidden="true">' . esc_html( $args['separator'] ) . '</span>';

	echo '<nav class="pcs-breadcrumbs" aria-label="' . esc_attr__( 'Fil d\'Ariane', 'pluscestsimple' ) . '">';
	echo '<ol class="pcs-breadcrumbs__list" itemscope itemtype="https://schema.org/BreadcrumbList">';

	$pos = 1;
	foreach ( $crumbs as $i => $crumb ) {
		$is_last = ( $i === $total - 1 );
		$name    = (string) ( $crumb['name'] ?? '' );
		$url     = (string) ( $crumb['url']  ?? '' );

		echo '<li class="pcs-breadcrumbs__item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
		if ( ! $is_last && $url ) {
			printf(
				'<a class="pcs-breadcrumbs__link" itemprop="item" href="%s"><span itemprop="name">%s</span></a>',
				esc_url( $url ),
				esc_html( $name )
			);
		} else {
			printf(
				'<span class="pcs-breadcrumbs__current" itemprop="name" aria-current="page">%s</span>',
				esc_html( $name )
			);
		}
		printf( '<meta itemprop="position" content="%d">', (int) $pos );
		echo '</li>';

		if ( ! $is_last ) {
			echo $sep; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — static markup.
		}

		$pos++;
	}

	echo '</ol>';
	echo '</nav>';
}
