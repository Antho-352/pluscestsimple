<?php
/**
 * Hubs de cluster — 3e niveau du cocon (pages mères de cluster).
 *
 * Généralise le principe des pages styles à n'importe quel sous-pilier.
 * Chaque hub = page money sur une tête à fort volume winnable, sous un sous-pilier.
 *   - catégorie 3e niveau  {parent-cat}-{slug}-cat
 *   - PAGE  /{parent_path}/{slug}/  en BROUILLON (squelette éditorial)
 *   - Query Loop pcs/cat-loop filtrée vers la catégorie du hub
 *   - section « Ressources liées » pointant vers des articles EXISTANTS (sans les éditer)
 *   - maillage remontant (hub → sous-pilier + pilier)
 *
 * Priorisation : seo-cocoon/STRATEGIE-SEO.md + données Haloscan FR.
 *
 * @package pluscestsimple
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Registre des hubs de cluster.
 * 'related' = slugs d'articles EXISTANTS à lier depuis le hub (down-link sûr,
 * sans toucher ces pages ; permaliens plats donc /{slug}/).
 *
 * @return array<string,array>
 */
function pcs_hubs(): array {
	$hubs = [
		'mur-porteur' => [
			'parent_path' => 'travaux/gros-oeuvre',
			'parent_cat'  => 'travaux-gros-oeuvre-cat',
			'cat_slug'    => 'travaux-gros-oeuvre-mur-porteur-cat',
			'title'       => 'Mur porteur : reconnaître, abattre, ouvrir',
			'label'       => 'Mur porteur',
			'kw'          => 'mur porteur',
			'intro'       => "Reconnaître un mur porteur, l'abattre ou y faire une ouverture en sécurité : le guide complet. Méthodes fiables, ce qui est obligatoire, et quand appeler un pro.",
			'related'     => [
				'muraliere-a-quoi-ca-sert-ou-la-fixer-et-quand-elle-devient-obligatoire' => 'Muralière : à quoi ça sert, où la fixer, quand elle est obligatoire',
				'mur-porteur-maison-1970' => 'Reconnaître un mur porteur dans une maison des années 1970',
				'un-parpaing-ca-pese-combien-et-comment-le-porter-sans-se-casser-le-dos' => 'Combien pèse un parpaing',
				'carotter-mur-sans-fissure' => 'Carotter un mur sans le fissurer',
			],
		],
		'chambre' => [
			'parent_path' => 'decoration/par-piece',
			'parent_cat'  => 'decoration-par-piece-cat',
			'cat_slug'    => 'decoration-par-piece-chambre-cat',
			'title'       => 'Déco chambre : idées par style, couleur et budget',
			'label'       => 'Déco chambre',
			'kw'          => 'déco chambre',
			'intro'       => "Toutes nos idées pour aménager et décorer une chambre : adulte, ado, bébé, petite chambre, choix des couleurs et des matières. Du concret, pièce par pièce.",
			'related'     => [
				'comment-choisir-la-taille-ideale-de-sa-tete-de-lit' => 'Choisir la taille idéale de sa tête de lit',
				'hauteur-de-penderie-les-bonnes-dimensions-pour-suspendre-sans-froisser' => 'Hauteur de penderie : les bonnes dimensions',
				'chambre-cocooning-beige-blanc' => 'Chambre cocooning beige et blanc',
			],
		],
	];

	// ── Pièces déco (scaffolding cocon, brouillon) sous /decoration/par-piece/ ──
	$pcs_pieces = [
		'salon'          => 'Salon',
		'chambre-enfant' => "Chambre d'enfant",
		'cuisine'        => 'Cuisine',
		'salle-a-manger' => 'Salle à manger',
		'salle-de-bain'  => 'Salle de bain',
		'bureau'         => 'Bureau',
		'entree'         => 'Entrée',
	];
	foreach ( $pcs_pieces as $pcs_p_slug => $pcs_p_label ) {
		$pcs_p_low = mb_strtolower( $pcs_p_label );
		$hubs[ $pcs_p_slug ] = [
			'parent_path' => 'decoration/par-piece',
			'parent_cat'  => 'decoration-par-piece-cat',
			'cat_slug'    => 'decoration-par-piece-' . $pcs_p_slug . '-cat',
			'title'       => 'Déco ' . $pcs_p_low,
			'label'       => 'Déco ' . $pcs_p_label,
			'kw'          => 'déco ' . $pcs_p_low,
			'intro'       => 'Idées, styles et conseils déco pour ' . $pcs_p_low . '. (À rédiger.)',
			'related'     => [],
		];
	}

	// ── Haussmannien (différenciateur architecture × déco × travaux) ──
	$hubs['haussmannien'] = [
		'parent_path' => 'architecture/styles-epoques',
		'parent_cat'  => 'architecture-styles-epoques-cat',
		'cat_slug'    => 'architecture-styles-epoques-haussmannien-cat',
		'title'       => 'Le style haussmannien : codes, moulures et rénovation',
		'label'       => 'Haussmannien',
		'kw'          => 'style haussmannien',
		'intro'       => 'Comprendre et rénover le style haussmannien : moulures, parquet point de Hongrie, hauteur sous plafond, et comment moderniser sans le trahir. (À rédiger.)',
		'related'     => [],
	];

	return $hubs;
}

/** Retourne la clé de hub si $page est une page hub, sinon ''. */
function pcs_hub_key( WP_Post $page ): string {
	foreach ( pcs_hubs() as $slug => $h ) {
		if ( $page->post_name !== $slug || ! $page->post_parent ) { continue; }
		$parent = get_post( $page->post_parent );
		$leaf   = basename( $h['parent_path'] );
		if ( $parent instanceof WP_Post && $parent->post_name === $leaf ) {
			return $slug;
		}
	}
	return '';
}

// ─── Seed : catégories + pages hub (brouillon) ───────────────────────────────────

add_action( 'init', function () {
	if ( get_option( 'pcs_hubs_seeded' ) === PCS_VERSION ) { return; }

	$ok = true;
	foreach ( pcs_hubs() as $slug => $h ) {
		$parent_cat  = get_term_by( 'slug', $h['parent_cat'], 'category' );
		$parent_page = get_page_by_path( $h['parent_path'] );
		if ( ! $parent_cat instanceof WP_Term || ! $parent_page instanceof WP_Post ) {
			$ok = false; // structure parente pas prête → on retentera au prochain init
			continue;
		}

		// 1. Catégorie du hub.
		$term = get_term_by( 'slug', $h['cat_slug'], 'category' );
		if ( ! $term ) {
			wp_insert_term( $h['label'], 'category', [
				'slug'        => $h['cat_slug'],
				'parent'      => $parent_cat->term_id,
				'description' => $h['intro'],
			] );
		} elseif ( (int) $term->parent !== (int) $parent_cat->term_id ) {
			wp_update_term( $term->term_id, 'category', [ 'parent' => $parent_cat->term_id ] );
		}

		// 2. Page hub (brouillon), enfant du sous-pilier.
		if ( get_page_by_path( $h['parent_path'] . '/' . $slug ) instanceof WP_Post ) {
			continue; // déjà créée → on ne réécrit jamais le contenu
		}
		$page_id = wp_insert_post( [
			'post_title'   => $h['title'],
			'post_name'    => $slug,
			'post_parent'  => $parent_page->ID,
			'post_status'  => 'draft',
			'post_type'    => 'page',
			'post_excerpt' => $h['intro'],
			'post_content' => pcs_hub_page_content( $h ),
		] );
		if ( $page_id && ! is_wp_error( $page_id ) ) {
			update_post_meta( $page_id, '_wp_page_template', 'page-templates/tpl-wide.php' );
			update_post_meta( $page_id, '_pcs_seeded', '1' );
			update_post_meta( $page_id, '_pcs_hub_kw', $h['kw'] );
		}
	}

	if ( $ok ) { update_option( 'pcs_hubs_seeded', PCS_VERSION ); }
}, 31 );

/** Squelette éditorial d'un hub de cluster. */
function pcs_hub_page_content( array $h ): string {
	$label = esc_html( $h['label'] );
	$intro = esc_html( $h['intro'] );

	$loop_feat = '<!-- wp:query {"queryId":40,"query":{"perPage":3,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","inherit":false},"namespace":"pcs/cat-loop"} -->'
		. '<div class="wp-block-query"><!-- wp:post-template -->'
		. '<!-- wp:post-featured-image {"isLink":true} /--><!-- wp:post-title {"isLink":true,"level":3} /--><!-- wp:post-date /-->'
		. '<!-- /wp:post-template --></div><!-- /wp:query -->';

	$loop_all = '<!-- wp:query {"queryId":41,"query":{"perPage":12,"pages":0,"offset":3,"postType":"post","order":"desc","orderBy":"date","inherit":false},"namespace":"pcs/cat-loop"} -->'
		. '<div class="wp-block-query"><!-- wp:post-template -->'
		. '<!-- wp:post-featured-image {"isLink":true} /--><!-- wp:post-title {"isLink":true,"level":3} /--><!-- wp:post-date /-->'
		. '<!-- /wp:post-template -->'
		. '<!-- wp:query-pagination --><!-- wp:query-pagination-previous /--><!-- wp:query-pagination-numbers /--><!-- wp:query-pagination-next /--><!-- /wp:query-pagination -->'
		. '</div><!-- /wp:query -->';

	// Ressources liées (articles existants, liens sûrs sans édition).
	$related = '';
	if ( ! empty( $h['related'] ) ) {
		$items = '';
		foreach ( $h['related'] as $rslug => $rtitle ) {
			$items .= '<li><a href="' . esc_url( home_url( '/' . $rslug . '/' ) ) . '">' . esc_html( $rtitle ) . '</a></li>';
		}
		$related = '<!-- wp:heading {"level":2,"className":"pcs-section__title"} --><h2 class="wp-block-heading pcs-section__title">Ressources liées</h2><!-- /wp:heading -->'
			. '<!-- wp:list {"className":"pcs-link-list"} --><ul class="wp-block-list pcs-link-list">' . $items . '</ul><!-- /wp:list -->';
	}

	$sections = [
		[ 'À retenir en bref', 'Résumé en 3-5 points clés (définition, enjeux, quand agir). (À rédiger.)' ],
		[ 'Le détail, étape par étape', 'Développement structuré du sujet : méthode, repères techniques, précautions. (À rédiger.)' ],
		[ 'Les erreurs à éviter', 'Les pièges fréquents et comment les contourner. (À rédiger.)' ],
		[ 'Questions fréquentes', 'FAQ (3-5 questions) reprenant les "Autres questions posées" de la SERP. (À rédiger.)' ],
	];
	$sec = '';
	foreach ( $sections as $s ) {
		$sec .= '<!-- wp:heading {"level":2,"className":"pcs-section__title"} --><h2 class="wp-block-heading pcs-section__title">' . esc_html( $s[0] ) . '</h2><!-- /wp:heading -->'
			. '<!-- wp:paragraph --><p>' . esc_html( $s[1] ) . '</p><!-- /wp:paragraph -->';
	}

	$out  = '<!-- wp:group {"className":"pcs-category-page","layout":{"type":"constrained"}} --><div class="wp-block-group pcs-category-page">';
	$out .= '<!-- wp:heading {"level":1,"className":"pcs-archive__title"} --><h1 class="wp-block-heading pcs-archive__title">' . esc_html( $h['title'] ) . '</h1><!-- /wp:heading -->';
	$out .= '<!-- wp:paragraph {"className":"pcs-archive__intro"} --><p class="pcs-archive__intro">' . $intro . '</p><!-- /wp:paragraph -->';
	$out .= $sec;
	$out .= '<!-- wp:heading {"level":2,"className":"pcs-section__title"} --><h2 class="wp-block-heading pcs-section__title">À la une</h2><!-- /wp:heading -->' . $loop_feat;
	$out .= $related;
	$out .= '<!-- wp:heading {"level":2,"className":"pcs-section__title"} --><h2 class="wp-block-heading pcs-section__title">Tous les articles ' . $label . '</h2><!-- /wp:heading -->' . $loop_all;
	$out .= '</div><!-- /wp:group -->';
	return $out;
}

// ─── Filtre Query Loop pour pages hub ────────────────────────────────────────────

add_filter( 'query_loop_block_query_vars', function ( array $query, $block ) {
	if ( ! is_singular( 'page' ) ) { return $query; }
	$attrs = $block->parsed_block['attrs'] ?? [];
	$ns    = $attrs['namespace'] ?? '';
	if ( '' !== $ns && 'pcs/cat-loop' !== $ns ) { return $query; }
	if ( ! empty( $attrs['query']['inherit'] ) ) { return $query; }
	if ( ! empty( $query['tax_query'] ) || ! empty( $query['category__in'] ) || ! empty( $query['cat'] ) ) { return $query; }

	$page = get_queried_object();
	if ( ! $page instanceof WP_Post ) { return $query; }
	$key = pcs_hub_key( $page );
	if ( '' === $key ) { return $query; }
	$h    = pcs_hubs()[ $key ];
	$term = get_term_by( 'slug', $h['cat_slug'], 'category' );
	if ( ! $term instanceof WP_Term ) { return $query; }

	$query['tax_query'] = [ [
		'taxonomy'         => 'category',
		'field'            => 'term_id',
		'terms'            => [ (int) $term->term_id ],
		'include_children' => false,
	] ];
	return $query;
}, 12, 2 );

// ─── Maillage : remontée hub → sous-pilier + pilier ──────────────────────────────

add_filter( 'the_content', function ( $content ) {
	if ( is_admin() || ! is_main_query() || ! in_the_loop() || ! is_page() ) { return $content; }
	$page = get_queried_object();
	if ( ! $page instanceof WP_Post ) { return $content; }
	$key = pcs_hub_key( $page );
	if ( '' === $key ) { return $content; }
	$h = pcs_hubs()[ $key ];

	$parts    = explode( '/', $h['parent_path'] );
	$pilier   = $parts[0] ?? '';
	$souspil  = $parts[1] ?? '';
	$pilier_o = get_page_by_path( $pilier );
	$sous_o   = get_page_by_path( $h['parent_path'] );

	$items = '';
	if ( $sous_o instanceof WP_Post ) {
		$items .= '<li><a href="' . esc_url( get_permalink( $sous_o ) ) . '"><strong>' . esc_html( $sous_o->post_title ) . '</strong></a></li>';
	}
	if ( $pilier_o instanceof WP_Post ) {
		$items .= '<li><a href="' . esc_url( get_permalink( $pilier_o ) ) . '">' . esc_html( $pilier_o->post_title ) . '</a></li>';
	}
	if ( '' === $items ) { return $content; }

	return $content
		. '<section class="pcs-section pcs-section--maillage"><div class="pcs-container">'
		. '<p class="pcs-eyebrow has-accent-color has-text-color">' . esc_html__( 'Dans le même thème', 'pluscestsimple' ) . '</p>'
		. '<h2 class="wp-block-heading pcs-section__title">' . esc_html__( 'Continuer à explorer', 'pluscestsimple' ) . '</h2>'
		. '<ul class="pcs-link-list">' . $items . '</ul></div></section>';
}, 22 );

// ─── Redirect SEO : archive catégorie hub → page hub ─────────────────────────────

add_action( 'template_redirect', function () {
	if ( is_admin() || wp_doing_ajax() || ! is_category() ) { return; }
	$cat = get_queried_object();
	if ( ! $cat instanceof WP_Term ) { return; }
	foreach ( pcs_hubs() as $slug => $h ) {
		if ( $cat->slug !== $h['cat_slug'] ) { continue; }
		$page = get_page_by_path( $h['parent_path'] . '/' . $slug );
		if ( $page instanceof WP_Post && 'publish' === $page->post_status ) {
			wp_safe_redirect( get_permalink( $page ), 301 );
			exit;
		}
	}
}, 0 );
