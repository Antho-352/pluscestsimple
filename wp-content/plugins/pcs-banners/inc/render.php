<?php
/**
 * Sélection + rendu HTML d'une bannière pour un slot donné.
 *
 * Logique :
 *  1. Lit le cache transient pcs_banner_<slot> (TTL 5 min).
 *  2. Sinon : query du CPT pcs_banner avec term du slot, status publish,
 *     date courante entre start_date et end_date (dates vides = toujours actif).
 *  3. Random pick parmi les actifs (rotation simple).
 *  4. Cache du résultat (payload data, pas le HTML).
 *  5. Construit le HTML : étiquette (si type != display) + image + lien.
 *
 * @package PCS_Banners
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Indique si au moins une bannière a été rendue dans la page courante.
 * Utilisé par inc/assets.php pour enqueue CSS conditionnellement.
 *
 * @return bool
 */
function pcs_banner_has_been_rendered(): bool {
	return ! empty( $GLOBALS['pcs_banner_rendered'] );
}

/**
 * Marque qu'au moins une bannière a été rendue (pour enqueue CSS).
 *
 * @return void
 */
function pcs_banner_mark_rendered(): void {
	$GLOBALS['pcs_banner_rendered'] = true;
}

/**
 * Sélectionne et retourne la "payload" d'une bannière active pour un slot,
 * ou null si aucune bannière active.
 *
 * Payload :
 *  - id        (int)
 *  - title     (string)  titre interne
 *  - image     (string)  URL image full size
 *  - image_w   (int)
 *  - image_h   (int)
 *  - alt       (string)
 *  - url       (string)  URL cible
 *  - type      (string)  display | sponsored | affilie
 *  - label     (string)  étiquette résolue (peut être vide)
 *
 * @param string $slot Slug du slot (term de la taxonomy pcs_banner_slot).
 * @return array<string, mixed>|null
 */
function pcs_banner_pick( string $slot ): ?array {
	if ( '' === $slot ) {
		return null;
	}

	// Cache transient.
	$cache_key = PCS_BANNER_CACHE_PREFIX . $slot;
	$cached    = get_transient( $cache_key );
	if ( false !== $cached ) {
		// Sentinelle "aucune bannière" stockée comme tableau vide → on retourne null.
		return is_array( $cached ) && ! empty( $cached ) ? $cached : null;
	}

	$now = current_time( 'Y-m-d\TH:i' ); // Format identique au datetime-local stocké.

	// Query : on récupère tous les candidats du slot puis on filtre PHP-side
	// (les comparaisons de dates sur des meta string sont fragiles en SQL).
	$query = new WP_Query(
		[
			'post_type'              => PCS_BANNER_CPT,
			'post_status'            => 'publish',
			'posts_per_page'         => 50,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
			'tax_query'              => [
				[
					'taxonomy' => PCS_BANNER_SLOT_TAX,
					'field'    => 'slug',
					'terms'    => $slot,
				],
			],
		]
	);

	$active_ids = [];
	foreach ( $query->posts as $post_id ) {
		$start = (string) get_post_meta( $post_id, '_pcs_banner_start_date', true );
		$end   = (string) get_post_meta( $post_id, '_pcs_banner_end_date', true );

		// Date début : si fournie, doit être <= now.
		if ( '' !== $start && $start > $now ) {
			continue;
		}
		// Date fin : si fournie, doit être >= now.
		if ( '' !== $end && $end < $now ) {
			continue;
		}
		$active_ids[] = (int) $post_id;
	}

	if ( empty( $active_ids ) ) {
		// Cache "aucun résultat" pour éviter la requête répétée pendant le TTL.
		set_transient( $cache_key, [], PCS_BANNER_CACHE_TTL );
		return null;
	}

	// Rotation aléatoire simple.
	$picked_id = $active_ids[ array_rand( $active_ids ) ];

	$payload = pcs_banner_build_payload( $picked_id );

	if ( null === $payload ) {
		set_transient( $cache_key, [], PCS_BANNER_CACHE_TTL );
		return null;
	}

	set_transient( $cache_key, $payload, PCS_BANNER_CACHE_TTL );
	return $payload;
}

/**
 * Construit la payload d'une bannière depuis son ID.
 *
 * @param int $post_id ID du CPT pcs_banner.
 * @return array<string, mixed>|null
 */
function pcs_banner_build_payload( int $post_id ): ?array {
	$thumb_id = (int) get_post_thumbnail_id( $post_id );
	if ( ! $thumb_id ) {
		return null;
	}

	$image_src = wp_get_attachment_image_src( $thumb_id, 'full' );
	if ( ! is_array( $image_src ) ) {
		return null;
	}

	$type = (string) get_post_meta( $post_id, '_pcs_banner_type', true );
	if ( ! in_array( $type, [ 'display', 'sponsored', 'affilie' ], true ) ) {
		$type = 'display';
	}

	$label = (string) get_post_meta( $post_id, '_pcs_banner_label', true );
	if ( '' === $label ) {
		$label = pcs_banner_default_label( $type );
	}

	$alt = (string) get_post_meta( $thumb_id, '_wp_attachment_image_alt', true );
	if ( '' === $alt ) {
		$alt = get_the_title( $post_id );
	}

	return [
		'id'      => $post_id,
		'title'   => get_the_title( $post_id ),
		'image'   => $image_src[0],
		'image_w' => (int) $image_src[1],
		'image_h' => (int) $image_src[2],
		'alt'     => $alt,
		'url'     => (string) get_post_meta( $post_id, '_pcs_banner_url', true ),
		'type'    => $type,
		'label'   => $label,
	];
}

/**
 * Étiquette par défaut selon le type.
 *
 * @param string $type display | sponsored | affilie.
 * @return string
 */
function pcs_banner_default_label( string $type ): string {
	switch ( $type ) {
		case 'sponsored':
			return __( 'Sponsorisé', 'pluscestsimple' );
		case 'affilie':
			return __( 'En partenariat', 'pluscestsimple' );
		case 'display':
		default:
			return ''; // Pas d'étiquette par défaut pour display.
	}
}

/**
 * Calcule l'attribut rel à appliquer au lien selon le type.
 *
 * @param string $type display | sponsored | affilie.
 * @return string Valeur de rel (vide si display).
 */
function pcs_banner_rel_for_type( string $type ): string {
	switch ( $type ) {
		case 'sponsored':
		case 'affilie':
			return 'sponsored nofollow noopener';
		case 'display':
		default:
			return 'noopener';
	}
}

/**
 * Rend le HTML d'une bannière pour un slot donné.
 * Fonction publique principale utilisée par le bloc et le shortcode.
 *
 * Le rendu dépend du mode d'affichage configuré sur le slot (term meta `_pcs_banner_display_mode`) :
 *   - `auto` (défaut)   → pub si disponible, sinon placeholder SVG
 *   - `banner-only`     → pub si disponible, sinon chaîne vide
 *   - `hidden`          → toujours chaîne vide (collapse layout par le thème)
 *
 * @param string $slot             Slug du slot.
 * @param bool   $with_placeholder Conservé pour rétro-compat. Si true et le mode est `banner-only`, force le placeholder.
 *                                  Sans effet en mode `auto` ou `hidden`.
 * @return string HTML prêt à echo.
 */
function pcs_banner_render( string $slot, bool $with_placeholder = false ): string {
	$mode = pcs_banner_slot_mode( $slot );

	// Mode `hidden` : ne rien afficher, quelle que soit la situation.
	if ( 'hidden' === $mode ) {
		return '';
	}

	$payload = pcs_banner_pick( $slot );
	if ( null === $payload ) {
		// Aucune pub : placeholder selon le mode (et le param legacy `$with_placeholder`).
		if ( 'auto' === $mode || ( 'banner-only' === $mode && $with_placeholder ) ) {
			return pcs_banner_render_placeholder( $slot );
		}
		return '';
	}

	pcs_banner_mark_rendered();

	$type   = (string) $payload['type'];
	$rel    = pcs_banner_rel_for_type( $type );
	$label  = (string) $payload['label'];
	$url    = (string) $payload['url'];

	$wrapper_classes = [ 'pcs-banner', 'pcs-banner--' . $type, 'pcs-banner--slot-' . sanitize_html_class( $slot ) ];

	$img_html = sprintf(
		'<img class="pcs-banner__img" src="%1$s" alt="%2$s" width="%3$d" height="%4$d" loading="lazy" decoding="async" />',
		esc_url( $payload['image'] ),
		esc_attr( $payload['alt'] ),
		(int) $payload['image_w'],
		(int) $payload['image_h']
	);

	// Si URL fournie : lien. Sinon : juste l'image.
	if ( '' !== $url ) {
		$link_html = sprintf(
			'<a class="pcs-banner__link" href="%1$s" rel="%2$s" target="_blank">%3$s</a>',
			esc_url( $url ),
			esc_attr( $rel ),
			$img_html
		);
	} else {
		$link_html = $img_html;
	}

	$label_html = '';
	if ( '' !== $label ) {
		$label_html = sprintf(
			'<span class="pcs-banner__label">%s</span>',
			esc_html( $label )
		);
	}

	return sprintf(
		'<aside class="%1$s" data-banner-id="%2$d" data-banner-type="%3$s">%4$s%5$s</aside>',
		esc_attr( implode( ' ', $wrapper_classes ) ),
		(int) $payload['id'],
		esc_attr( $type ),
		$label_html,
		$link_html
	);
}

/**
 * Retourne le format IAB attendu pour un slot donné (largeur × hauteur).
 *
 * @param string $slot Slug du slot.
 * @return string Format au format "WIDTHxHEIGHT" (ex: "300x600").
 */
function pcs_banner_slot_format( string $slot ): string {
	$map = [
		// Sidebars verticales (Half Page)
		'homepage-sidebar'         => '300x600',
		'article-sidebar'          => '300x600',
		'cat-sidebar-decoration'   => '300x600',
		'cat-sidebar-travaux'      => '300x600',
		'cat-sidebar-jardin'       => '300x600',
		'cat-sidebar-architecture' => '300x600',
		'cat-sidebar-lifestyle'    => '300x600',
		// Billboards full-width (970×250)
		'homepage-top'  => '970x250',
		'homepage-mid'  => '970x250',
		'category-mid'  => '970x250',
		// Leaderboard (728×90)
		'category-intro' => '728x90',
		// Medium Rectangle (300×250)
		'in-article'    => '300x250',
	];
	return $map[ $slot ] ?? '300x600';
}

/**
 * Rend un placeholder visuel pour un slot vide (SVG inline-référencé par <img>).
 *
 * Le SVG affiché correspond au format IAB du slot (cf. `pcs_banner_slot_format`).
 * Les 4 SVG sont stockés dans `assets/placeholders/placeholder-{format}.svg`.
 *
 * @param string $slot Slug du slot (pour debug / classe CSS).
 * @return string HTML du placeholder.
 */
function pcs_banner_render_placeholder( string $slot ): string {
	$format         = pcs_banner_slot_format( $slot );
	[ $w, $h ]      = array_map( 'intval', explode( 'x', $format ) );
	$svg_url        = PCS_BANNER_URL . 'assets/placeholders/placeholder-' . $format . '.svg';
	$classes        = [
		'pcs-banner',
		'pcs-banner--placeholder',
		'pcs-banner--placeholder-' . $format,
		'pcs-banner--slot-' . sanitize_html_class( $slot ),
	];

	return sprintf(
		'<aside class="%1$s" aria-hidden="true" data-banner-slot="%2$s">' .
			'<img class="pcs-banner__placeholder-img" src="%3$s" width="%4$d" height="%5$d" alt="" loading="lazy" decoding="async" />' .
		'</aside>',
		esc_attr( implode( ' ', $classes ) ),
		esc_attr( $slot ),
		esc_url( $svg_url ),
		$w,
		$h
	);
}
