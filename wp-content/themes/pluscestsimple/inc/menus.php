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
				'social'  => __( 'Réseaux sociaux (footer)', 'pluscestsimple' ),
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
	$etre_publie = get_page_by_path( 'travailler-avec-nous' );
	if ( $etre_publie ) {
		printf(
			'<li class="menu-item"><a href="%s">%s</a></li>',
			esc_url( get_permalink( $etre_publie ) ),
			esc_html__( 'Être publié', 'pluscestsimple' )
		);
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
 * Rendu des icônes réseaux sociaux (footer).
 *
 * L'utilisateur ajoute ses liens depuis Apparence → Menus → "Réseaux sociaux (footer)".
 * Le nom de chaque item de menu peut être quelconque ; l'icône est détectée
 * automatiquement depuis le domaine de l'URL (facebook.com → icône FB, pinterest → icône Pinterest).
 */
function pcs_social_menu(): void {
	if ( ! has_nav_menu( 'social' ) ) {
		return;
	}

	$items = wp_get_nav_menu_items( get_nav_menu_locations()['social'] ?? 0 );
	if ( empty( $items ) ) {
		return;
	}

	echo '<nav class="pcs-social-nav" aria-label="' . esc_attr__( 'Réseaux sociaux', 'pluscestsimple' ) . '">';
	echo '<ul class="pcs-social-nav__list">';

	foreach ( $items as $item ) {
		$url  = esc_url( $item->url );
		$icon = pcs_social_icon( $item->url );
		$label = esc_html( $item->title );
		printf(
			'<li><a href="%s" class="pcs-social-nav__link" target="_blank" rel="noopener noreferrer" aria-label="%s">%s</a></li>',
			$url,
			$label,
			$icon
		);
	}

	echo '</ul></nav>';
}

/**
 * Retourne le SVG correspondant au réseau social détecté depuis l'URL.
 * Fallback : icône lien générique.
 *
 * @param string $url L'URL du lien de menu.
 * @return string SVG inline (escaped).
 */
function pcs_social_icon( string $url ): string {
	$host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );

	if ( str_contains( $host, 'pinterest' ) ) {
		// Pinterest — P stylisé officiel
		return '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 0C5.373 0 0 5.373 0 12c0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738a.36.36 0 0 1 .083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.632-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z"/></svg>';
	}

	if ( str_contains( $host, 'facebook' ) || str_contains( $host, 'fb.com' ) ) {
		// Facebook — f dans un cercle
		return '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.413c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/></svg>';
	}

	if ( str_contains( $host, 'instagram' ) ) {
		return '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/></svg>';
	}

	// Fallback générique
	return '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>';
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
