<?php
/**
 * Pages "styles déco" — 3e niveau du cocon (/decoration/styles/{style}/).
 *
 * Module isolé : ne touche pas au moteur 2-niveaux (pilier + sous-pilier).
 * Pour chaque style :
 *   - crée la catégorie  decoration-styles-{style}-cat  (enfant de decoration-styles-cat)
 *   - crée la PAGE       /decoration/styles/{style}/     (enfant de la page styles), en BROUILLON
 *   - filtre la Query Loop pcs/cat-loop de la page vers sa catégorie
 *   - injecte le maillage (remontée vers le hub styles + styles frères)
 *
 * Données de priorisation : seo-cocoon/keywords/PRIORISATION-styles.md (Haloscan FR).
 * Pages en brouillon → pas de thin content indexé ; publication après rédaction.
 *
 * @package pluscestsimple
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

const PCS_STYLE_PARENT_PATH = 'decoration/styles';
const PCS_STYLE_PARENT_CAT  = 'decoration-styles-cat';

/**
 * Définition des pages style à créer (Tier 1 + quick wins validés SERP).
 *
 * @return array<string,array{label:string,kw:string,intro:string}>
 */
function pcs_style_pages(): array {
	return [
		'scandinave' => [
			'label' => 'Scandinave',
			'kw'    => 'décoration scandinave',
			'intro' => "Le style scandinave, c'est la chaleur du bois clair, les lignes épurées et la lumière. On décrypte les codes, pièce par pièce, avec les bons matériaux et les erreurs à éviter — sans tomber dans le catalogue.",
		],
		'boheme' => [
			'label' => 'Bohème',
			'kw'    => 'déco bohème',
			'intro' => "Le bohème mêle matières naturelles, motifs et objets chinés pour un intérieur chaleureux et personnel. Voici comment l'adopter sans surcharge, du salon à la chambre.",
		],
		'industriel' => [
			'label' => 'Industriel',
			'kw'    => 'déco industrielle',
			'intro' => "Métal, bois brut, briques et esprit atelier : le style industriel donne du caractère. On voit comment l'équilibrer pour qu'il reste chaleureux, pièce par pièce.",
		],
		'mediterraneen' => [
			'label' => 'Méditerranéen',
			'kw'    => 'décoration méditerranéenne',
			'intro' => "Couleurs solaires, matières naturelles, terre cuite et fraîcheur : la déco méditerranéenne fait entrer le Sud à la maison. Les codes et les associations qui marchent.",
		],
		'japandi' => [
			'label' => 'Japandi',
			'kw'    => 'déco japandi',
			'intro' => "Le japandi croise le minimalisme japonais et le confort scandinave : épure, matières naturelles et sérénité. Comment l'adopter sans tomber dans le froid.",
		],
		'campagne-chic' => [
			'label' => 'Campagne chic',
			'kw'    => 'déco campagne chic',
			'intro' => "La campagne chic réchauffe l'authentique rustique d'une élégance maîtrisée : patines douces, lin, bois et touches modernes. Le guide pour l'adopter pièce par pièce.",
		],
		'vintage' => [
			'label' => 'Vintage',
			'kw'    => 'déco vintage',
			'intro' => "Le vintage mêle pièces chinées, mobilier des années 50-70 et patine du temps. Comment l'adopter avec justesse, du salon à la chambre, sans tomber dans le bric-à-brac.",
		],
		'coloree' => [
			'label' => 'Colorée',
			'kw'    => 'déco colorée',
			'intro' => "La déco colorée assume les teintes vives et les associations audacieuses. Les règles pour oser la couleur sans surcharge, pièce par pièce.",
		],
	];
}

// ─── Helpers de détection ───────────────────────────────────────────────────────

/** Retourne le slug de style si $page est une page style, sinon ''. */
function pcs_style_page_key( WP_Post $page ): string {
	$styles = pcs_style_pages();
	if ( ! isset( $styles[ $page->post_name ] ) || ! $page->post_parent ) {
		return '';
	}
	$parent = get_post( $page->post_parent );
	if ( $parent instanceof WP_Post && 'styles' === $parent->post_name ) {
		return $page->post_name;
	}
	return '';
}

/** Slug de catégorie d'une page style (decoration-styles-{slug}-cat) ou ''. */
function pcs_style_page_cat_slug( WP_Post $page ): string {
	$key = pcs_style_page_key( $page );
	return $key ? 'decoration-styles-' . $key . '-cat' : '';
}

// ─── Seed : catégories + pages (brouillon) ───────────────────────────────────────

add_action( 'init', function () {
	if ( get_option( 'pcs_style_pages_seeded' ) === PCS_VERSION ) { return; }

	$parent_cat  = get_term_by( 'slug', PCS_STYLE_PARENT_CAT, 'category' );
	$parent_page = get_page_by_path( PCS_STYLE_PARENT_PATH );
	if ( ! $parent_cat instanceof WP_Term || ! $parent_page instanceof WP_Post ) {
		return; // structure 2-niveaux pas encore seedée → on retentera au prochain init
	}

	foreach ( pcs_style_pages() as $slug => $data ) {
		// 1. Catégorie 3e niveau.
		$cat_slug = 'decoration-styles-' . $slug . '-cat';
		$term     = get_term_by( 'slug', $cat_slug, 'category' );
		if ( ! $term ) {
			wp_insert_term( 'Déco ' . $data['label'], 'category', [
				'slug'        => $cat_slug,
				'parent'      => $parent_cat->term_id,
				'description' => $data['intro'],
			] );
		} elseif ( (int) $term->parent !== (int) $parent_cat->term_id ) {
			wp_update_term( $term->term_id, 'category', [ 'parent' => $parent_cat->term_id ] );
		}

		// 2. Page style (brouillon) enfant de /decoration/styles/.
		if ( get_page_by_path( PCS_STYLE_PARENT_PATH . '/' . $slug ) instanceof WP_Post ) {
			continue; // déjà créée → on ne réécrit jamais le contenu (édition respectée)
		}
		$page_id = wp_insert_post( [
			'post_title'   => 'Déco ' . $data['label'],
			'post_name'    => $slug,
			'post_parent'  => $parent_page->ID,
			'post_status'  => 'draft', // publication après rédaction
			'post_type'    => 'page',
			'post_excerpt' => $data['intro'],
			'post_content' => pcs_style_page_content( $data ),
		] );
		if ( $page_id && ! is_wp_error( $page_id ) ) {
			update_post_meta( $page_id, '_wp_page_template', 'page-templates/tpl-wide.php' );
			update_post_meta( $page_id, '_pcs_seeded', '1' );
			update_post_meta( $page_id, '_pcs_style_kw', $data['kw'] );
		}
	}

	update_option( 'pcs_style_pages_seeded', PCS_VERSION );
}, 30 ); // init-content seed à la priorité 99 (plus tardive) : style-pages vérifie que la
         // structure parente existe (get_page_by_path/get_term_by) et retente au prochain init sinon.

/**
 * Squelette éditorial d'une page style (blocs Gutenberg).
 * H1 + intro + à la une (loop) + sections rédac + tous les articles (loop).
 */
function pcs_style_page_content( array $data ): string {
	$label = esc_html( $data['label'] );
	$intro = esc_html( $data['intro'] );

	$loop_featured = '<!-- wp:query {"queryId":30,"query":{"perPage":3,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","inherit":false},"namespace":"pcs/cat-loop"} -->'
		. '<div class="wp-block-query"><!-- wp:post-template -->'
		. '<!-- wp:post-featured-image {"isLink":true} /--><!-- wp:post-title {"isLink":true,"level":3} /--><!-- wp:post-date /-->'
		. '<!-- /wp:post-template --></div><!-- /wp:query -->';

	$loop_all = '<!-- wp:query {"queryId":31,"query":{"perPage":12,"pages":0,"offset":3,"postType":"post","order":"desc","orderBy":"date","inherit":false},"namespace":"pcs/cat-loop"} -->'
		. '<div class="wp-block-query"><!-- wp:post-template -->'
		. '<!-- wp:post-featured-image {"isLink":true} /--><!-- wp:post-title {"isLink":true,"level":3} /--><!-- wp:post-date /-->'
		. '<!-- /wp:post-template -->'
		. '<!-- wp:query-pagination --><!-- wp:query-pagination-previous /--><!-- wp:query-pagination-numbers /--><!-- wp:query-pagination-next /--><!-- /wp:query-pagination -->'
		. '</div><!-- /wp:query -->';

	$sections = [
		[ "Les codes du style {$label}", 'Présente en 150-250 mots l\'essence du style : origines, principes, ce qui le distingue. (À rédiger.)' ],
		[ "Le style {$label} pièce par pièce", 'Salon, chambre, cuisine, entrée : comment décliner le style selon la pièce. Liens vers les articles dédiés. (À rédiger.)' ],
		[ 'Couleurs, matières et mobilier', 'La palette, les matériaux signature et les pièces de mobilier clés. (À rédiger.)' ],
		[ 'Les erreurs à éviter', 'Les faux pas classiques et comment les contourner pour un rendu crédible. (À rédiger.)' ],
	];
	$sections_html = '';
	foreach ( $sections as $s ) {
		$sections_html .= '<!-- wp:heading {"level":2,"className":"pcs-section__title"} --><h2 class="wp-block-heading pcs-section__title">' . esc_html( $s[0] ) . '</h2><!-- /wp:heading -->';
		$sections_html .= '<!-- wp:paragraph --><p>' . esc_html( $s[1] ) . '</p><!-- /wp:paragraph -->';
	}

	$out  = '<!-- wp:group {"className":"pcs-category-page","layout":{"type":"constrained"}} --><div class="wp-block-group pcs-category-page">';
	$out .= '<!-- wp:heading {"level":1,"className":"pcs-archive__title"} --><h1 class="wp-block-heading pcs-archive__title">Déco ' . $label . '</h1><!-- /wp:heading -->';
	$out .= '<!-- wp:paragraph {"className":"pcs-archive__intro"} --><p class="pcs-archive__intro">' . $intro . '</p><!-- /wp:paragraph -->';
	$out .= '<!-- wp:heading {"level":2,"className":"pcs-section__title"} --><h2 class="wp-block-heading pcs-section__title">À la une</h2><!-- /wp:heading -->';
	$out .= $loop_featured;
	$out .= $sections_html;
	$out .= '<!-- wp:heading {"level":2,"className":"pcs-section__title"} --><h2 class="wp-block-heading pcs-section__title">Tous les articles ' . $label . '</h2><!-- /wp:heading -->';
	$out .= $loop_all;
	$out .= '</div><!-- /wp:group -->';
	return $out;
}

// ─── Filtre Query Loop pour pages style (3e niveau) ──────────────────────────────
// Le filtre coeur (category-query-filter.php) no-op sur les pages style ; on prend
// le relais ici à une priorité supérieure.

add_filter( 'query_loop_block_query_vars', function ( array $query, $block ) {
	if ( ! is_singular( 'page' ) ) { return $query; }
	$attrs = $block->parsed_block['attrs'] ?? [];
	$ns    = $attrs['namespace'] ?? '';
	if ( '' !== $ns && 'pcs/cat-loop' !== $ns ) { return $query; }
	if ( ! empty( $attrs['query']['inherit'] ) ) { return $query; }
	if ( ! empty( $query['tax_query'] ) || ! empty( $query['category__in'] ) || ! empty( $query['cat'] ) ) { return $query; }

	$page = get_queried_object();
	if ( ! $page instanceof WP_Post ) { return $query; }
	$cat_slug = pcs_style_page_cat_slug( $page );
	if ( '' === $cat_slug ) { return $query; }
	$term = get_term_by( 'slug', $cat_slug, 'category' );
	if ( ! $term instanceof WP_Term ) { return $query; }

	$query['tax_query'] = [ [
		'taxonomy'         => 'category',
		'field'            => 'term_id',
		'terms'            => [ (int) $term->term_id ],
		'include_children' => false,
	] ];
	return $query;
}, 11, 2 );

// ─── Maillage : remontée hub + styles frères ─────────────────────────────────────

add_filter( 'the_content', function ( $content ) {
	if ( is_admin() || ! is_main_query() || ! in_the_loop() || ! is_page() ) { return $content; }
	$page = get_queried_object();
	if ( ! $page instanceof WP_Post ) { return $content; }

	// Hub /decoration/styles/ → liste descendante vers les pages style PUBLIÉES.
	$parent = $page->post_parent ? get_post( $page->post_parent ) : null;
	if ( 'styles' === $page->post_name && $parent instanceof WP_Post && 'decoration' === $parent->post_name ) {
		$items = '';
		foreach ( pcs_style_pages() as $slug => $d ) {
			$sp = get_page_by_path( PCS_STYLE_PARENT_PATH . '/' . $slug );
			if ( $sp instanceof WP_Post && 'publish' === $sp->post_status ) {
				$items .= '<li><a href="' . esc_url( get_permalink( $sp ) ) . '">' . esc_html( $d['label'] ) . '</a></li>';
			}
		}
		if ( '' === $items ) { return $content; }
		return $content
			. '<section class="pcs-section pcs-section--maillage"><div class="pcs-container">'
			. '<p class="pcs-eyebrow has-accent-color has-text-color">' . esc_html__( 'Par style', 'pluscestsimple' ) . '</p>'
			. '<h2 class="wp-block-heading pcs-section__title">' . esc_html__( 'Découvrir par style', 'pluscestsimple' ) . '</h2>'
			. '<ul class="pcs-link-list">' . $items . '</ul></div></section>';
	}

	$key = pcs_style_page_key( $page );
	if ( '' === $key ) { return $content; }

	$styles = pcs_style_pages();
	ob_start();
	?>
	<section class="pcs-section pcs-section--maillage">
		<div class="pcs-container">
			<p class="pcs-eyebrow has-accent-color has-text-color"><?php esc_html_e( 'Autres styles', 'pluscestsimple' ); ?></p>
			<h2 class="wp-block-heading pcs-section__title"><?php esc_html_e( 'Explorer les styles déco', 'pluscestsimple' ); ?></h2>
			<ul class="pcs-link-list">
				<li><a href="<?php echo esc_url( home_url( '/' . PCS_STYLE_PARENT_PATH . '/' ) ); ?>"><strong><?php esc_html_e( 'Tous les styles', 'pluscestsimple' ); ?></strong></a></li>
				<?php foreach ( $styles as $slug => $d ) :
					if ( $slug === $key ) { continue; }
					$url = home_url( '/' . PCS_STYLE_PARENT_PATH . '/' . $slug . '/' );
					?>
					<li><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $d['label'] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
	<?php
	return $content . (string) ob_get_clean();
}, 21 );

// ─── Redirect SEO : archive catégorie style → page style ─────────────────────────

add_action( 'template_redirect', function () {
	if ( is_admin() || wp_doing_ajax() || ! is_category() ) { return; }
	$cat = get_queried_object();
	if ( ! $cat instanceof WP_Term ) { return; }
	if ( ! preg_match( '/^decoration-styles-(.+)-cat$/', $cat->slug, $m ) ) { return; }
	$page = get_page_by_path( PCS_STYLE_PARENT_PATH . '/' . $m[1] );
	if ( $page instanceof WP_Post && 'publish' === $page->post_status ) {
		wp_safe_redirect( get_permalink( $page ), 301 );
		exit;
	}
}, 0 ); // avant le redirect générique de category-redirects.php
