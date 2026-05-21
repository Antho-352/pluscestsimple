<?php
/**
 * JSON-LD schema.org output. Server-rendered, no JS.
 * Graph:
 *   - Organization (publisher)  — with logo (Google News eligibility)
 *   - WebSite + SearchAction
 *   - BreadcrumbList (non-home)
 *   - Article (single post)
 *   - Product (CPT arw_product)
 *   - CollectionPage + ItemList (archives/category)
 *   - FAQPage (auto-detect <details>/<summary> or core/details block)
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

// ─── Helpers ──────────────────────────────────────────────────────────────────

/**
 * Build Product schema array from an arw_product post.
 * Used both for singular product pages AND the front-page ItemList.
 *
 * Returns NULL si aucun de offers/review/aggregateRating ne peut être renseigné.
 * Google exige au moins l'un des trois — sans ça, Search Console émet une erreur
 * "Données structurées Extraits de produits". Mieux vaut ne pas émettre Product
 * que d'émettre un schema invalide.
 */
function arw_pulse_build_product_schema( WP_Post $post ): ?array {
	$brand    = (string) get_post_meta( $post->ID, 'arw_product_brand', true );
	$score    = (string) get_post_meta( $post->ID, 'arw_product_score', true );
	$amount   = (string) get_post_meta( $post->ID, 'arw_product_price_amount', true );
	$currency = (string) get_post_meta( $post->ID, 'arw_product_currency', true ) ?: apply_filters( 'arw_pulse_currency', 'EUR' );
	$avail    = (string) get_post_meta( $post->ID, 'arw_product_availability', true ) ?: 'InStock';
	$sku      = (string) get_post_meta( $post->ID, 'arw_product_sku', true );
	$link     = (string) get_post_meta( $post->ID, 'arw_product_link', true );
	$image    = has_post_thumbnail( $post ) ? get_the_post_thumbnail_url( $post, 'large' ) : '';
	$url      = $link ?: ( get_permalink( $post ) ?: home_url( '/' ) );

	// Anti-spam Google : AggregateRating ne doit JAMAIS être émise avec
	// ratingCount=1 sur une review interne — c'est du « self-serving review markup »,
	// risque de pénalité manuelle. Cf. developers.google.com/search/docs/appearance/structured-data/review-snippet
	// On exige rating_count ≥ 5 (méta `arw_product_rating_count`) pour émettre.
	$rating_count = (int) get_post_meta( $post->ID, 'arw_product_rating_count', true );
	$has_rating   = is_numeric( $score ) && $score >= 0 && $score <= 10 && $rating_count >= 5;
	$has_offers   = is_numeric( $amount ) && (float) $amount > 0;
	if ( ! $has_rating && ! $has_offers ) { return null; }

	$product = [
		'@type'       => 'Product',
		'@id'         => home_url( '/#product-' . $post->ID ),
		'name'        => get_the_title( $post ),
		'description' => $post->post_excerpt ? wp_strip_all_tags( $post->post_excerpt ) : '',
		'url'         => $url,
	];
	if ( $image ) { $product['image'] = $image; }
	if ( $sku )   { $product['sku']   = $sku; $product['mpn'] = $sku; }
	if ( $brand ) { $product['brand'] = [ '@type' => 'Brand', 'name' => $brand ]; }
	if ( $has_rating ) {
		$product['aggregateRating'] = [
			'@type'       => 'AggregateRating',
			'ratingValue' => (float) $score,
			'bestRating'  => 10,
			'worstRating' => 0,
			'ratingCount' => $rating_count,
		];
	}
	if ( $has_offers ) {
		$product['offers'] = [
			'@type'         => 'Offer',
			'price'         => number_format( (float) $amount, 2, '.', '' ),
			'priceCurrency' => $currency,
			'availability'  => 'https://schema.org/' . $avail,
			'url'           => $url,
		];
	}
	return apply_filters( 'arw_pulse_product_schema', $product, $post );
}

function arw_pulse_publisher_logo() {
	$logo_id = get_theme_mod( 'custom_logo' );
	if ( $logo_id ) {
		$src = wp_get_attachment_image_src( $logo_id, 'full' );
		if ( $src ) {
			return [ 'url' => $src[0], 'width' => $src[1], 'height' => $src[2] ];
		}
	}
	// Fallback to site icon for Google News (requires logo).
	$icon_id = (int) get_option( 'site_icon' );
	if ( $icon_id ) {
		$src = wp_get_attachment_image_src( $icon_id, 'full' );
		if ( $src ) {
			return [ 'url' => $src[0], 'width' => $src[1], 'height' => $src[2] ];
		}
	}
	return null;
}

/**
 * Detect FAQ blocks in content: accepts <details><summary>…</summary>…</details>
 * which is what core/details block outputs. Returns array of Q/A pairs.
 */
function arw_pulse_extract_faqs( string $html ): array {
	if ( ! $html || strpos( $html, '<details' ) === false ) { return []; }
	$pairs = [];
	if ( preg_match_all( '#<details[^>]*>\s*<summary[^>]*>(.+?)</summary>(.*?)</details>#is', $html, $m, PREG_SET_ORDER ) ) {
		foreach ( $m as $match ) {
			$q = trim( wp_strip_all_tags( $match[1] ) );
			$a = trim( wp_strip_all_tags( $match[2] ) );
			if ( $q && $a ) { $pairs[] = [ 'q' => $q, 'a' => $a ]; }
		}
	}
	return $pairs;
}

// ─── Main JSON-LD emitter ─────────────────────────────────────────────────────

add_action( 'wp_head', function () {
	$graph    = [];
	$site_url = home_url( '/' );
	$site     = get_bloginfo( 'name' );
	$logo     = arw_pulse_publisher_logo();

	// Organization (publisher).
	$organization = [
		'@type' => 'Organization',
		'@id'   => $site_url . '#organization',
		'name'  => $site,
		'url'   => $site_url,
	];
	if ( $logo ) {
		$organization['logo'] = [
			'@type'  => 'ImageObject',
			'url'    => $logo['url'],
			'width'  => $logo['width'],
			'height' => $logo['height'],
		];
	}
	$same_as = apply_filters( 'arw_pulse_organization_same_as', [] );
	if ( $same_as ) { $organization['sameAs'] = array_values( $same_as ); }
	$graph[] = $organization;

	// WebSite with SearchAction.
	$graph[] = [
		'@type'           => 'WebSite',
		'@id'             => $site_url . '#website',
		'url'             => $site_url,
		'name'            => $site,
		'description'     => get_bloginfo( 'description' ),
		'publisher'       => [ '@id' => $site_url . '#organization' ],
		'inLanguage'      => get_bloginfo( 'language' ),
		'potentialAction' => [
			'@type'       => 'SearchAction',
			'target'      => [ '@type' => 'EntryPoint', 'urlTemplate' => $site_url . '?s={search_term_string}' ],
			'query-input' => 'required name=search_term_string',
		],
	];

	// Article (single post).
	if ( is_singular( 'post' ) ) {
		$post         = get_queried_object();
		$image        = has_post_thumbnail() ? get_the_post_thumbnail_url( $post, 'large' ) : '';
		$author       = get_userdata( $post->post_author );
		$author_url   = get_author_posts_url( $author->ID );
		$author_image = get_avatar_url( $author->ID, [ 'size' => 192 ] );
		$words        = (int) get_post_meta( $post->ID, '_arw_word_count', true );
		if ( ! $words ) {
			$words = str_word_count( wp_strip_all_tags( $post->post_content ) );
		}

		$article = [
			'@type'            => 'Article',
			'@id'              => get_permalink( $post ) . '#article',
			'headline'         => get_the_title( $post ),
			'description'      => arw_pulse_meta_description(),
			'url'              => get_permalink( $post ),
			'datePublished'    => get_the_date( 'c', $post ),
			'dateModified'     => get_the_modified_date( 'c', $post ),
			'inLanguage'       => get_bloginfo( 'language' ),
			'wordCount'        => $words,
			'isAccessibleForFree' => true,
			'author'           => [
				'@type' => 'Person',
				'@id'   => $author_url . '#author',
				'name'  => $author->display_name,
				'url'   => $author_url,
				'image' => $author_image,
			],
			'publisher'        => [ '@id' => $site_url . '#organization' ],
			'mainEntityOfPage' => [ '@id' => get_permalink( $post ) ],
			'articleSection'   => implode( ', ', wp_list_pluck( get_the_category( $post ), 'name' ) ),
		];
		if ( $image ) {
			$article['image'] = [ '@type' => 'ImageObject', 'url' => $image ];
		}
		$article = apply_filters( 'arw_pulse_article_schema', $article, $post );
		$graph[] = $article;

		// FAQPage — manual meta box takes precedence, else auto-detect <details>.
		$faqs = function_exists( 'arw_pulse_get_faq' ) ? arw_pulse_get_faq( $post->ID ) : [];
		if ( ! $faqs ) {
			$rendered = apply_filters( 'the_content', $post->post_content );
			$faqs     = arw_pulse_extract_faqs( $rendered );
		}
		if ( $faqs ) {
			$items = [];
			foreach ( $faqs as $f ) {
				$items[] = [
					'@type'          => 'Question',
					'name'           => $f['q'],
					'acceptedAnswer' => [ '@type' => 'Answer', 'text' => $f['a'] ],
				];
			}
			$graph[] = [
				'@type'      => 'FAQPage',
				'@id'        => get_permalink( $post ) . '#faq',
				'mainEntity' => $items,
			];
		}

		// HowTo schema (from meta box).
		$howto = function_exists( 'arw_pulse_get_howto' ) ? arw_pulse_get_howto( $post->ID ) : [];
		if ( ! empty( $howto['steps'] ) ) {
			$step_items = [];
			$pos = 1;
			foreach ( $howto['steps'] as $s ) {
				$item = [ '@type' => 'HowToStep', 'position' => $pos++ ];
				if ( ! empty( $s['name'] ) ) { $item['name'] = $s['name']; }
				if ( ! empty( $s['text'] ) ) { $item['text'] = $s['text']; }
				$step_items[] = $item;
			}
			$graph[] = [
				'@type' => 'HowTo',
				'@id'   => get_permalink( $post ) . '#howto',
				'name'  => $howto['title'] ?: get_the_title( $post ),
				'step'  => $step_items,
			];
		}
	}

	// Product schema on home / front page — emits a Product ItemList from the
	// top 6 products shown in [arw_essential]. Also fires on any singular
	// arw_product (if made public later).
	$products_to_emit = [];
	if ( is_front_page() || is_home() ) {
		$products_to_emit = get_posts( [
			'post_type'      => 'arw_product',
			'post_status'    => 'publish',
			'posts_per_page' => (int) apply_filters( 'arw_pulse_home_products_count', 6 ),
			'orderby'        => [ 'menu_order' => 'ASC', 'date' => 'DESC' ],
		] );
	} elseif ( is_singular( 'arw_product' ) ) {
		$products_to_emit = [ get_queried_object() ];
	}

	$product_items = [];
	foreach ( $products_to_emit as $pp ) {
		$schema = arw_pulse_build_product_schema( $pp );
		if ( $schema ) { $product_items[] = $schema; }
	}
	if ( count( $product_items ) === 1 ) {
		$graph[] = $product_items[0];
	} elseif ( count( $product_items ) > 1 ) {
		$list_items = [];
		$pos = 1;
		foreach ( $product_items as $prod ) {
			$list_items[] = [
				'@type'    => 'ListItem',
				'position' => $pos++,
				'item'     => $prod,
			];
		}
		$graph[] = [
			'@type'           => 'ItemList',
			'@id'             => home_url( '/#products' ),
			'name'            => __( 'Produits sélectionnés', 'arw-pulse' ),
			'itemListElement' => $list_items,
		];
	}

	// CollectionPage + ItemList (archives / category / tag).
	if ( is_category() || is_tag() || is_tax() || is_home() ) {
		$term       = ( is_category() || is_tag() || is_tax() ) ? get_queried_object() : null;
		$collection_url = $term ? get_term_link( $term ) : ( get_permalink( get_option( 'page_for_posts' ) ) ?: $site_url );
		$name       = $term ? $term->name : __( 'Articles', 'arw-pulse' );
		$desc       = $term ? wp_strip_all_tags( term_description( $term ) ) : get_bloginfo( 'description' );

		global $wp_query;
		$items = [];
		$pos   = 1;
		foreach ( $wp_query->posts ?? [] as $p ) {
			$items[] = [
				'@type'    => 'ListItem',
				'position' => $pos++,
				'url'      => get_permalink( $p ),
				'name'     => get_the_title( $p ),
			];
			if ( $pos > 20 ) { break; }
		}

		$graph[] = [
			'@type'       => 'CollectionPage',
			'@id'         => $collection_url . '#collection',
			'url'         => $collection_url,
			'name'        => $name,
			'description' => $desc,
			'isPartOf'    => [ '@id' => $site_url . '#website' ],
			'inLanguage'  => get_bloginfo( 'language' ),
		];
		if ( $items ) {
			$graph[] = [
				'@type'           => 'ItemList',
				'@id'             => $collection_url . '#itemlist',
				'itemListElement' => $items,
			];
		}
	}

	// BreadcrumbList — toutes pages, home incluse (1 item « Accueil » minimum).
	$crumbs = arw_pulse_get_breadcrumbs();
	if ( is_front_page() && count( $crumbs ) === 0 ) {
		$crumbs = [ [ 'name' => __( 'Accueil', 'arw-pulse' ), 'url' => home_url( '/' ) ] ];
	}
	if ( count( $crumbs ) >= 1 ) {
		$items = [];
		$pos   = 1;
		foreach ( $crumbs as $crumb ) {
			$item = [
				'@type'    => 'ListItem',
				'position' => $pos++,
				'name'     => $crumb['name'],
			];
			// Schema.org : "item" doit être une URL absolue valide. Omettre si vide
			// (autorisé pour le dernier crumb représentant la page courante).
			if ( ! empty( $crumb['url'] ) ) { $item['item'] = $crumb['url']; }
			$items[] = $item;
		}
		$graph[] = [
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $items,
		];
	}

	$output = [ '@context' => 'https://schema.org', '@graph' => $graph ];
	echo '<script type="application/ld+json">' . wp_json_encode( $output, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP ) . '</script>' . "\n";
}, 7 );

/**
 * Build breadcrumb trail (reusable by schema + visual breadcrumb block).
 *
 * @return array<int, array{name:string,url:string}>
 */
function arw_pulse_get_breadcrumbs() {
	$crumbs = [ [ 'name' => __( 'Accueil', 'arw-pulse' ), 'url' => home_url( '/' ) ] ];

	if ( is_singular( 'post' ) ) {
		$cats = get_the_category();
		if ( ! empty( $cats ) ) {
			$primary  = $cats[0];
			$crumbs[] = [ 'name' => $primary->name, 'url' => get_category_link( $primary ) ];
		}
		$crumbs[] = [ 'name' => get_the_title(), 'url' => get_permalink() ];
	} elseif ( is_page() ) {
		$post = get_queried_object();
		if ( $post->post_parent ) {
			$ancestors = array_reverse( get_post_ancestors( $post ) );
			foreach ( $ancestors as $aid ) {
				$crumbs[] = [ 'name' => get_the_title( $aid ), 'url' => get_permalink( $aid ) ];
			}
		}
		$crumbs[] = [ 'name' => get_the_title( $post ), 'url' => get_permalink( $post ) ];
	} elseif ( is_category() ) {
		$term     = get_queried_object();
		$crumbs[] = [ 'name' => $term->name, 'url' => get_term_link( $term ) ];
	} elseif ( is_tag() ) {
		$term     = get_queried_object();
		$crumbs[] = [ 'name' => sprintf( __( 'Tag : %s', 'arw-pulse' ), $term->name ), 'url' => get_term_link( $term ) ];
	} elseif ( is_author() ) {
		$user     = get_queried_object();
		$crumbs[] = [ 'name' => $user->display_name, 'url' => get_author_posts_url( $user->ID ) ];
	} elseif ( is_search() ) {
		$crumbs[] = [ 'name' => sprintf( __( 'Recherche : %s', 'arw-pulse' ), get_search_query() ), 'url' => arw_pulse_canonical_url() ];
	} elseif ( is_404() ) {
		$crumbs[] = [ 'name' => __( '404', 'arw-pulse' ), 'url' => home_url( '/' ) ];
	}

	return apply_filters( 'arw_pulse_breadcrumbs', $crumbs );
}
