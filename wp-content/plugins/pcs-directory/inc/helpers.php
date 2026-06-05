<?php
/**
 * Helpers — génération de contenu SEO, navigation silo, breadcrumb.
 *
 * @package PCS_Directory
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// ─── Listes de boutiques ───────────────────────────────────────────────────────

/**
 * Top enseignes (grandes marques) présentes dans un terme donné.
 *
 * @return string[] Noms d'enseignes uniques.
 */
function pcs_directory_top_enseignes( string $taxonomy, int $term_id, int $limit = 6 ): array {
	$ids = get_posts( [
		'post_type'      => PCS_DIR_CPT,
		'post_status'    => 'publish',
		'posts_per_page' => 60,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'tax_query'      => [ [ 'taxonomy' => $taxonomy, 'field' => 'term_id', 'terms' => $term_id ] ],
		'meta_query'     => [ [ 'key' => '_pcs_is_enseigne', 'value' => '1' ] ],
	] );

	$names = [];
	foreach ( $ids as $id ) {
		$n = get_the_title( $id );
		// Normalise « MAISONS DU MONDE FRANCE » → « Maisons Du Monde France » trop lourd ;
		// on garde le titre tel quel mais on dédoublonne.
		$key = mb_strtolower( $n );
		if ( ! isset( $names[ $key ] ) ) { $names[ $key ] = $n; }
		if ( count( $names ) >= $limit ) { break; }
	}
	return array_values( $names );
}

/**
 * Villes d'un département, triées par nombre de boutiques (desc).
 *
 * @return array<int, array{term_id:int, name:string, slug:string, count:int}>
 */
function pcs_directory_villes_in_dept( int $dept_term_id, int $limit = 0 ): array {
	$cache_key = 'pcs_villes_dept_' . $dept_term_id;
	$cached    = get_transient( $cache_key );
	if ( is_array( $cached ) ) {
		return $limit > 0 ? array_slice( $cached, 0, $limit ) : $cached;
	}

	$object_ids = get_objects_in_term( $dept_term_id, 'pcs_dept' );
	if ( is_wp_error( $object_ids ) || empty( $object_ids ) ) {
		return [];
	}

	global $wpdb;
	$ids_in = implode( ',', array_map( 'intval', $object_ids ) );
	$rows   = $wpdb->get_results(
		"SELECT t.term_id, t.name, t.slug, COUNT(*) AS cnt
		 FROM {$wpdb->term_relationships} tr
		 JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = 'pcs_ville'
		 JOIN {$wpdb->terms} t ON t.term_id = tt.term_id
		 WHERE tr.object_id IN ($ids_in)
		 GROUP BY t.term_id, t.name, t.slug
		 ORDER BY cnt DESC, t.name ASC"
	);

	$out = [];
	foreach ( (array) $rows as $r ) {
		$out[] = [
			'term_id' => (int) $r->term_id,
			'name'    => (string) $r->name,
			'slug'    => (string) $r->slug,
			'count'   => (int) $r->cnt,
		];
	}

	set_transient( $cache_key, $out, 12 * HOUR_IN_SECONDS );
	return $limit > 0 ? array_slice( $out, 0, $limit ) : $out;
}

/**
 * Départements d'une région ayant au moins une boutique.
 *
 * @return WP_Term[]
 */
function pcs_directory_departments_in_region( string $region_name ): array {
	$all = get_terms( [ 'taxonomy' => 'pcs_dept', 'hide_empty' => true, 'orderby' => 'name' ] );
	if ( is_wp_error( $all ) ) { return []; }

	$out = [];
	foreach ( $all as $term ) {
		$code = pcs_directory_dept_code( $term );
		if ( pcs_directory_region_from_dept( $code ) === $region_name ) {
			$out[] = $term;
		}
	}
	return $out;
}

/**
 * Terme région associé à un département (robuste : nom puis slug).
 */
function pcs_directory_region_term_for_dept( WP_Term $dept ): ?WP_Term {
	$region = pcs_directory_region_from_dept( pcs_directory_dept_code( $dept ) );
	if ( ! $region ) { return null; }
	$t = get_term_by( 'name', $region, 'pcs_region' );
	if ( ! $t ) { $t = get_term_by( 'slug', sanitize_title( $region ), 'pcs_region' ); }
	return $t instanceof WP_Term ? $t : null;
}

// ─── Rendu d'une ligne boutique (remplace get_template_part) ───────────────────

/**
 * Affiche une ligne de boutique (liste « lien bleu »).
 * On n'utilise PAS get_template_part : il ne cherche que dans le thème, jamais
 * dans un plugin. Cette fonction garantit le rendu quel que soit le thème actif.
 */
function pcs_directory_render_row( int $post_id ): void {
	if ( ! $post_id ) { return; }

	$title       = get_the_title( $post_id );
	$url         = get_permalink( $post_id );
	$adresse     = (string) get_post_meta( $post_id, '_pcs_adresse', true );
	$cp          = (string) get_post_meta( $post_id, '_pcs_code_postal', true );
	$website     = (string) get_post_meta( $post_id, '_pcs_website', true );
	$phone       = (string) get_post_meta( $post_id, '_pcs_phone', true );
	$is_enseigne = get_post_meta( $post_id, '_pcs_is_enseigne', true );
	$rating      = (string) get_post_meta( $post_id, '_pcs_rating', true );
	$reviews     = (string) get_post_meta( $post_id, '_pcs_reviews', true );

	$cats  = get_the_terms( $post_id, 'pcs_cat' );
	$cat   = ( is_array( $cats ) && $cats ) ? $cats[0]->name : '';
	$vils  = get_the_terms( $post_id, 'pcs_ville' );
	$ville = ( is_array( $vils ) && $vils ) ? $vils[0]->name : '';

	$meta_parts = array_filter( [ $adresse, trim( $cp . ' ' . $ville ) ] );

	echo '<li class="pcs-list__item">';
	echo '<a class="pcs-list__link" href="' . esc_url( $url ) . '">' . esc_html( $title ) . '</a>';
	if ( $rating ) {
		echo '<span class="pcs-rating">★ ' . esc_html( $rating ) . ( $reviews ? ' <small>(' . esc_html( $reviews ) . ')</small>' : '' ) . '</span>';
	}
	if ( $meta_parts ) {
		echo '<span class="pcs-list__meta">' . esc_html( implode( ' · ', $meta_parts ) ) . '</span>';
	}
	echo '<span class="pcs-list__flags">';
	if ( $is_enseigne ) { echo '<span class="pcs-flag pcs-flag--enseigne">enseigne</span>'; }
	if ( $cat )         { echo '<span class="pcs-flag pcs-flag--cat">' . esc_html( $cat ) . '</span>'; }
	if ( $website )     { echo '<span class="pcs-flag pcs-flag--web">site web</span>'; }
	if ( $phone )       { echo '<span class="pcs-flag pcs-flag--phone">tél.</span>'; }
	echo '</span>';
	echo '</li>';
}

/**
 * Affiche le conteneur carte avec ses marqueurs.
 */
function pcs_directory_render_map( string $markers_json, string $extra_class = '' ): void {
	$cls = trim( 'pcs-map ' . $extra_class );
	echo '<div id="pcs-map" class="' . esc_attr( $cls ) . '" data-markers="' . esc_attr( $markers_json ) . '">';
	echo '<p class="pcs-map-placeholder">Chargement de la carte…</p>';
	echo '</div>';
}

// ─── Textes SEO : éditable + fallback auto ─────────────────────────────────────

/**
 * Récupère le texte d'intro d'un terme : meta éditable si présente, sinon auto.
 */
function pcs_directory_term_intro( WP_Term $term ): string {
	$manual = (string) get_term_meta( $term->term_id, '_pcs_intro_html', true );
	if ( trim( $manual ) !== '' ) {
		return wp_kses_post( wpautop( $manual ) );
	}
	return pcs_directory_auto_intro( $term );
}

/**
 * Récupère le texte SEO bas de page : meta éditable si présente, sinon auto.
 */
function pcs_directory_term_outro( WP_Term $term ): string {
	$manual = (string) get_term_meta( $term->term_id, '_pcs_outro_html', true );
	if ( trim( $manual ) !== '' ) {
		return wp_kses_post( wpautop( $manual ) );
	}
	return pcs_directory_auto_outro( $term );
}

/**
 * Génère un texte d'introduction depuis les données.
 */
function pcs_directory_auto_intro( WP_Term $term ): string {
	$count = (int) $term->count;
	$tax   = $term->taxonomy;

	if ( 'pcs_dept' === $tax ) {
		$code      = pcs_directory_dept_code( $term );
		$region    = pcs_directory_region_from_dept( $code );
		$enseignes = pcs_directory_top_enseignes( 'pcs_dept', $term->term_id, 6 );
		$villes    = pcs_directory_villes_in_dept( $term->term_id, 5 );

		$p  = sprintf(
			'Le département du <strong>%s</strong> (%s)%s compte <strong>%s</strong> %s de décoration, ameublement et aménagement de la maison référencé%s sur Plus c\'est simple.',
			esc_html( $term->name ),
			esc_html( strtoupper( $code ) ),
			$region ? ' en ' . esc_html( $region ) : '',
			number_format_i18n( $count ),
			$count > 1 ? 'magasins' : 'magasin',
			$count > 1 ? 's' : ''
		);

		if ( $enseignes ) {
			$p .= ' Parmi les enseignes présentes : ' . esc_html( implode( ', ', array_slice( $enseignes, 0, 6 ) ) ) . '.';
		}
		if ( $villes ) {
			$noms = array_map( fn( $v ) => $v['name'], array_slice( $villes, 0, 5 ) );
			$p .= ' Les principales villes : ' . esc_html( implode( ', ', $noms ) ) . '.';
		}
		return '<p>' . $p . '</p>';
	}

	if ( 'pcs_region' === $tax ) {
		$depts = pcs_directory_departments_in_region( $term->name );
		$p = sprintf(
			'La région <strong>%s</strong> rassemble <strong>%s</strong> %s de décoration et d\'ameublement répartis sur %d département%s.',
			esc_html( $term->name ),
			number_format_i18n( $count ),
			$count > 1 ? 'magasins' : 'magasin',
			count( $depts ),
			count( $depts ) > 1 ? 's' : ''
		);
		return '<p>' . $p . '</p>';
	}

	if ( 'pcs_ville' === $tax ) {
		$enseignes = pcs_directory_top_enseignes( 'pcs_ville', $term->term_id, 5 );
		$p = sprintf(
			'Retrouvez <strong>%s</strong> %s de décoration, meubles et aménagement intérieur à <strong>%s</strong>.',
			number_format_i18n( $count ),
			$count > 1 ? 'magasins' : 'magasin',
			esc_html( $term->name )
		);
		if ( $enseignes ) {
			$p .= ' Notamment : ' . esc_html( implode( ', ', $enseignes ) ) . '.';
		}
		return '<p>' . $p . '</p>';
	}

	// cat / type / mode
	$p = sprintf(
		'<strong>%s</strong> %s référencé%s dans la catégorie « %s ».',
		number_format_i18n( $count ),
		$count > 1 ? 'magasins' : 'magasin',
		$count > 1 ? 's' : '',
		esc_html( $term->name )
	);
	return '<p>' . $p . '</p>';
}

/**
 * Génère un texte SEO de bas de page depuis les données.
 */
function pcs_directory_auto_outro( WP_Term $term ): string {
	$count = (int) $term->count;
	$name  = esc_html( $term->name );
	$tax   = $term->taxonomy;

	if ( 'pcs_dept' === $tax ) {
		$villes = pcs_directory_villes_in_dept( $term->term_id, 8 );
		$liens  = [];
		foreach ( $villes as $v ) {
			$link = get_term_link( (int) $v['term_id'], 'pcs_ville' );
			if ( ! is_wp_error( $link ) ) {
				$liens[] = '<a href="' . esc_url( $link ) . '">' . esc_html( $v['name'] ) . '</a>';
			}
		}
		$html  = '<h2>Trouver un magasin de déco dans le ' . $name . '</h2>';
		$html .= '<p>Que vous cherchiez du mobilier, des objets de décoration, des luminaires ou une cuisine équipée, '
			. 'l\'annuaire référence ' . number_format_i18n( $count ) . ' boutiques dans le ' . $name . '. '
			. 'Chaque fiche précise l\'adresse, le téléphone, les horaires et le site web quand ils sont disponibles.</p>';
		if ( $liens ) {
			$html .= '<p><strong>Villes du département :</strong> ' . implode( ' · ', $liens ) . '</p>';
		}
		return $html;
	}

	if ( 'pcs_region' === $tax ) {
		return '<h2>Magasins de décoration en ' . $name . '</h2>'
			. '<p>Sélectionnez un département ci-dessus pour parcourir les ' . number_format_i18n( $count )
			. ' boutiques de décoration et d\'ameublement de la région ' . $name . '.</p>';
	}

	if ( 'pcs_ville' === $tax ) {
		return '<h2>Où acheter de la déco à ' . $name . ' ?</h2>'
			. '<p>' . number_format_i18n( $count ) . ' magasins de décoration et d\'ameublement sont référencés à ' . $name
			. '. Comparez les adresses, horaires et sites web pour trouver la boutique idéale.</p>';
	}

	return '<h2>Les magasins « ' . $name .' » en France</h2>'
		. '<p>Plus c\'est simple référence ' . number_format_i18n( $count ) . ' boutiques dans cette catégorie partout en France.</p>';
}

// ─── Breadcrumb (fil d'Ariane) + schema BreadcrumbList ─────────────────────────

/**
 * Construit le fil d'Ariane contextuel et l'affiche (HTML + JSON-LD).
 */
function pcs_directory_breadcrumb(): void {
	$trail   = [];
	$trail[] = [ 'label' => 'Accueil',  'url' => home_url( '/' ) ];
	$trail[] = [ 'label' => 'Annuaire', 'url' => get_post_type_archive_link( PCS_DIR_CPT ) ];

	if ( is_singular( PCS_DIR_CPT ) ) {
		$pid = get_the_ID();
		$reg = get_the_terms( $pid, 'pcs_region' );
		$dep = get_the_terms( $pid, 'pcs_dept' );
		$vil = get_the_terms( $pid, 'pcs_ville' );
		if ( is_array( $reg ) && $reg ) { $trail[] = [ 'label' => $reg[0]->name, 'url' => get_term_link( $reg[0] ) ]; }
		if ( is_array( $dep ) && $dep ) { $trail[] = [ 'label' => $dep[0]->name, 'url' => get_term_link( $dep[0] ) ]; }
		if ( is_array( $vil ) && $vil ) { $trail[] = [ 'label' => $vil[0]->name, 'url' => get_term_link( $vil[0] ) ]; }
		$trail[] = [ 'label' => get_the_title( $pid ), 'url' => '' ];

	} elseif ( is_tax( 'pcs_dept' ) ) {
		$term  = get_queried_object();
		$rterm = pcs_directory_region_term_for_dept( $term );
		if ( $rterm ) {
			$trail[] = [ 'label' => $rterm->name, 'url' => get_term_link( $rterm ) ];
		}
		$trail[] = [ 'label' => $term->name, 'url' => '' ];

	} elseif ( is_tax( 'pcs_region' ) ) {
		$term = get_queried_object();
		$trail[] = [ 'label' => $term->name, 'url' => '' ];

	} elseif ( is_tax( 'pcs_ville' ) ) {
		$term = get_queried_object();
		// Déduit le département depuis une boutique de la ville.
		$sample = get_posts( [
			'post_type' => PCS_DIR_CPT, 'posts_per_page' => 1, 'fields' => 'ids', 'no_found_rows' => true,
			'tax_query' => [ [ 'taxonomy' => 'pcs_ville', 'field' => 'term_id', 'terms' => $term->term_id ] ],
		] );
		if ( $sample ) {
			$dep = get_the_terms( $sample[0], 'pcs_dept' );
			if ( is_array( $dep ) && $dep ) { $trail[] = [ 'label' => $dep[0]->name, 'url' => get_term_link( $dep[0] ) ]; }
		}
		$trail[] = [ 'label' => $term->name, 'url' => '' ];

	} elseif ( is_tax( [ 'pcs_cat', 'pcs_type', 'pcs_mode' ] ) ) {
		$term = get_queried_object();
		$trail[] = [ 'label' => $term->name, 'url' => '' ];

	} else {
		// Archive : Annuaire est la page courante.
		$trail[1]['url'] = '';
	}

	// — HTML —
	echo '<nav class="pcs-breadcrumb" aria-label="Fil d\'Ariane"><ol>';
	$last = count( $trail ) - 1;
	foreach ( $trail as $i => $crumb ) {
		echo '<li>';
		if ( $crumb['url'] && ! is_wp_error( $crumb['url'] ) && $i !== $last ) {
			echo '<a href="' . esc_url( $crumb['url'] ) . '">' . esc_html( $crumb['label'] ) . '</a>';
		} else {
			echo '<span aria-current="page">' . esc_html( $crumb['label'] ) . '</span>';
		}
		echo '</li>';
	}
	echo '</ol></nav>';

	// — JSON-LD —
	$items = [];
	foreach ( $trail as $i => $crumb ) {
		$entry = [
			'@type'    => 'ListItem',
			'position' => $i + 1,
			'name'     => $crumb['label'],
		];
		if ( $crumb['url'] && ! is_wp_error( $crumb['url'] ) ) { $entry['item'] = $crumb['url']; }
		$items[] = $entry;
	}
	$schema = [ '@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $items ];
	echo "\n" . '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
}
