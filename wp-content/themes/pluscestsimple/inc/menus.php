<?php
/**
 * Menus de navigation.
 *
 * Deux emplacements : primary (header) et footer. L'utilisateur les
 * compose et les assigne depuis Apparence → Menus. Pas de surcouche
 * PHP : `wp_nav_menu()` natif, l'assignation des menus aux emplacements
 * survit aux mises à jour du thème via les `theme_mods`.
 *
 * @package pluscestsimple
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'after_setup_theme',
	function () {
		register_nav_menus(
			[
				'primary' => __( 'Menu principal (header)', 'pluscestsimple' ),
				'footer'  => __( 'Menu pied de page', 'pluscestsimple' ),
			]
		);
	}
);

/**
 * Rend le menu principal du header avec un fallback si aucun menu n'est assigné.
 */
function pcs_primary_menu(): void {
	wp_nav_menu(
		[
			'theme_location'  => 'primary',
			'container'       => 'nav',
			'container_class' => 'pcs-nav pcs-nav--primary',
			'menu_class'      => 'pcs-nav__list',
			'fallback_cb'     => 'pcs_menu_fallback',
			'depth'           => 2,
			'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
		]
	);
}

/**
 * Rend le menu du footer avec un fallback si aucun menu n'est assigné.
 */
function pcs_footer_menu(): void {
	wp_nav_menu(
		[
			'theme_location'  => 'footer',
			'container'       => 'nav',
			'container_class' => 'pcs-nav pcs-nav--footer',
			'menu_class'      => 'pcs-nav__list',
			'fallback_cb'     => 'pcs_menu_fallback_footer',
			'depth'           => 1,
			'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
		]
	);
}

/**
 * Fallback du menu principal : liste des catégories + Contact.
 */
function pcs_menu_fallback(): void {
	echo '<nav class="pcs-nav pcs-nav--primary"><ul class="pcs-nav__list">';
	$cats = get_terms(
		[
			'taxonomy'   => 'category',
			'hide_empty' => true,
			'number'     => 6,
		]
	);
	if ( ! is_wp_error( $cats ) ) {
		foreach ( $cats as $cat ) {
			printf(
				'<li class="menu-item"><a href="%s">%s</a></li>',
				esc_url( get_term_link( $cat ) ),
				esc_html( $cat->name )
			);
		}
	}
	$contact = get_page_by_path( 'contact' );
	if ( $contact ) {
		printf(
			'<li class="menu-item"><a href="%s">%s</a></li>',
			esc_url( get_permalink( $contact ) ),
			esc_html__( 'Contact', 'pluscestsimple' )
		);
	}
	echo '</ul></nav>';
}

/**
 * Fallback du menu footer : pages légales.
 */
function pcs_menu_fallback_footer(): void {
	echo '<nav class="pcs-nav pcs-nav--footer"><ul class="pcs-nav__list">';
	foreach ( [ 'mentions-legales' => 'Mentions légales', 'contact' => 'Contact', 'plan-du-site' => 'Plan du site' ] as $slug => $label ) {
		$page = get_page_by_path( $slug );
		if ( $page ) {
			printf(
				'<li class="menu-item"><a href="%s">%s</a></li>',
				esc_url( get_permalink( $page ) ),
				esc_html( $label )
			);
		}
	}
	echo '</ul></nav>';
}
