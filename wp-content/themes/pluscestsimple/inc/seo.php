<?php
/**
 * SEO — title, meta description, canonical, robots, OpenGraph, Twitter.
 * Replaces Yoast / Rank Math on editorial / affiliate sites.
 *
 * @package pluscestsimple
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

// ─── Helpers ──────────────────────────────────────────────────────────────────

/**
 * Normalise un texte destiné aux meta sociaux (OG/Twitter) :
 * - strip toutes balises HTML
 * - retire les caractères de contrôle, zero-width, RTL override (anti-phishing/homograph)
 * - réduit les espaces multiples
 *
 * Sortie utilisée avant esc_attr — défense en profondeur sur les preview Twitter/OG.
 */
function pcs_normalize_social_text( string $s ): string {
	$s = wp_strip_all_tags( $s );
	// Retire ranges Unicode dangereux : zero-width (U+200B-U+200F), RTL/LTR override (U+202A-U+202E),
	// invisible separator (U+2063), word joiner (U+2060), tag chars (U+E0000-U+E007F), C0/C1 controls.
	$s = preg_replace( '/[\x{200B}-\x{200F}\x{202A}-\x{202E}\x{2060}-\x{2064}\x{E0000}-\x{E007F}\x{0000}-\x{001F}\x{007F}-\x{009F}]/u', '', $s );
	$s = preg_replace( '/\s+/u', ' ', (string) $s );
	return trim( (string) $s );
}

function pcs_meta_description() {
	$desc = '';
	if ( is_singular() ) {
		$post = get_queried_object();
		$desc = get_post_meta( $post->ID, '_pcs_meta_description', true );
		if ( ! $desc ) { $desc = (string) get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true ); }
		if ( ! $desc ) { $desc = (string) get_post_meta( $post->ID, 'rank_math_description', true ); }
		if ( ! $desc ) {
			// Si le contenu n'est qu'un shortcode, l'expansion via do_shortcode() évite
			// que le snippet exposé soit le shortcode brut.
			$raw  = has_excerpt( $post ) ? get_the_excerpt( $post ) : $post->post_content;
			$raw  = trim( wp_strip_all_tags( strip_shortcodes( $raw ) ) );
			if ( '' === $raw ) {
				$expanded = trim( wp_strip_all_tags( do_shortcode( $post->post_content ) ) );
				$raw      = $expanded;
			}
			$desc = $raw ? wp_trim_words( $raw, 30, '…' ) : '';
		}
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		$desc = term_description( $term )
			? wp_strip_all_tags( term_description( $term ) )
			: sprintf( __( 'Articles dans la catégorie %s.', 'pluscestsimple' ), $term->name );
	} elseif ( is_author() ) {
		$desc = get_the_author_meta( 'description' );
	} elseif ( is_home() || is_front_page() ) {
		$desc = get_bloginfo( 'description' );
		if ( ! $desc ) {
			// Fallback : description générée depuis le nom du site.
			$desc = sprintf( __( 'Découvrez %s : actualités, guides et ressources éditoriales.', 'pluscestsimple' ), get_bloginfo( 'name' ) );
		}
	} elseif ( is_search() ) {
		$desc = sprintf( __( 'Résultats de recherche pour « %s »', 'pluscestsimple' ), get_search_query() );
	}
	$desc = wp_strip_all_tags( $desc );
	// Hard cap at 160 to avoid Google snippet truncation.
	if ( mb_strlen( $desc ) > 160 ) { $desc = rtrim( mb_substr( $desc, 0, 157 ) ) . '…'; }
	return apply_filters( 'pcs_meta_description', $desc );
}

/**
 * Canonical URL, pagination-aware.
 * Paginated archive + single post pages must canonicalise to the paginated URL,
 * NOT the page 1 URL — otherwise deep content gets deduped by Google.
 */
function pcs_canonical_url() {
	$paged = max( (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ), 1 );

	if ( is_singular() ) {
		$url = get_permalink();
		if ( $paged > 1 ) { $url = user_trailingslashit( trailingslashit( $url ) . $paged ); }
		return $url;
	}
	if ( is_category() || is_tag() || is_tax() ) {
		$url = get_term_link( get_queried_object() );
		if ( $paged > 1 ) { $url = user_trailingslashit( trailingslashit( $url ) . 'page/' . $paged ); }
		return $url;
	}
	if ( is_author() ) {
		$url = get_author_posts_url( get_queried_object_id() );
		if ( $paged > 1 ) { $url = user_trailingslashit( trailingslashit( $url ) . 'page/' . $paged ); }
		return $url;
	}
	if ( is_home() ) {
		$url = get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/' );
		if ( $paged > 1 ) { $url = user_trailingslashit( trailingslashit( $url ) . 'page/' . $paged ); }
		return $url;
	}
	if ( is_front_page() ) { return home_url( '/' ); }
	if ( is_search() ) {
		$url = home_url( '/?s=' . urlencode( get_search_query() ) );
		// Pagination-aware so /page/2/ etc. don't share canonical with page 1.
		if ( $paged > 1 ) { $url = add_query_arg( 'paged', $paged, $url ); }
		return $url;
	}
	return home_url( add_query_arg( null, null ) );
}

/**
 * OG image URL, with per-post override, featured image, identity filter,
 * puis fallback ultime sur le custom_logo du site (plus jamais aucune page sans og:image).
 */
function pcs_og_image() {
	if ( is_singular() ) {
		$post = get_queried_object();
		$override = (int) get_post_meta( $post->ID, '_pcs_og_image_id', true );
		if ( $override ) {
			$src = wp_get_attachment_image_src( $override, 'large' );
			if ( $src ) { return $src[0]; }
		}
		if ( has_post_thumbnail( $post ) ) {
			return get_the_post_thumbnail_url( $post, 'large' );
		}
	}
	$default = apply_filters( 'pcs_default_og_image', '' );
	if ( $default ) { return $default; }
	// Fallback ultime : custom_logo du site (toujours configuré sur un site WP).
	$logo_id = get_theme_mod( 'custom_logo' );
	if ( $logo_id ) {
		$src = wp_get_attachment_image_src( $logo_id, 'large' );
		if ( $src ) { return $src[0]; }
	}
	// Dernier recours : Site Icon (favicon HD).
	$icon_id = get_option( 'site_icon' );
	if ( $icon_id ) {
		$src = wp_get_attachment_image_src( $icon_id, 'full' );
		if ( $src ) { return $src[0]; }
	}
	return '';
}

/**
 * Récupère les dimensions réelles d'une URL d'image attachée à la médiathèque.
 * Utilisé pour og:image:width/height (au lieu de hardcoder 1200×630).
 */
function pcs_og_image_dimensions( string $url ): array {
	$id = attachment_url_to_postid( $url );
	if ( $id ) {
		$src = wp_get_attachment_image_src( $id, 'large' );
		if ( $src && ! empty( $src[1] ) && ! empty( $src[2] ) ) {
			return [ (int) $src[1], (int) $src[2] ];
		}
	}
	return [ 1200, 630 ];
}

/**
 * Twitter handles — set via filter in child / config.
 */
function pcs_twitter_site()    { return apply_filters( 'pcs_twitter_site',    '' ); }
function pcs_twitter_creator() { return apply_filters( 'pcs_twitter_creator', '' ); }

// ─── Disable WP core canonical (we emit our own with pagination-awareness) ───

remove_action( 'wp_head', 'rel_canonical' );

// ─── Charset + viewport (forced thème-side, ne dépend pas de la config WP) ───

add_action( 'wp_head', function () {
	echo '<meta charset="' . esc_attr( get_bloginfo( 'charset' ) ) . '">' . "\n";
	echo '<meta name="viewport" content="width=device-width, initial-scale=1">' . "\n";
}, 1 );

// ─── Head output ──────────────────────────────────────────────────────────────

add_action( 'wp_head', function () {
	$desc  = pcs_meta_description();
	$url   = pcs_canonical_url();
	$site  = get_bloginfo( 'name' );
	$title = wp_get_document_title();

	if ( ! $desc ) { $desc = get_bloginfo( 'description' ) ?: get_bloginfo( 'name' ); }
	printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
	printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $url ) );

	// rel=prev/next on paginated archives.
	if ( is_archive() || is_home() || is_search() ) {
		global $paged, $wp_query;
		$paged     = $paged ?: 1;
		$max_pages = isset( $wp_query->max_num_pages ) ? (int) $wp_query->max_num_pages : 1;
		if ( $paged > 1 )          { printf( '<link rel="prev" href="%s">' . "\n", esc_url( get_pagenum_link( $paged - 1 ) ) ); }
		if ( $paged < $max_pages ) { printf( '<link rel="next" href="%s">' . "\n", esc_url( get_pagenum_link( $paged + 1 ) ) ); }
	}

	// Robots — per-post noindex override takes precedence.
	$noindex = false;
	if ( is_singular() ) {
		$noindex = (bool) get_post_meta( get_queried_object_id(), '_pcs_noindex', true );
	}
	if ( is_search() || is_404() ) { $noindex = true; }
	// Author archives noindex global — pas de valeur SEO sur sites éditoriaux mono-auteur.
	if ( is_author() && apply_filters( 'pcs_author_archives_noindex', true ) ) {
		$noindex = true;
	}
	$default_index = apply_filters( 'pcs_robots_meta_index', 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' );
	$robots = $noindex ? 'noindex, follow' : $default_index;
	printf( '<meta name="robots" content="%s">' . "\n", esc_attr( $robots ) );

	// OpenGraph.
	printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( $site ) );
	$locale = str_replace( '-', '_', get_locale() );
	// Facebook OG attend BCP 47 underscore (fr_FR), pas un code 2 lettres (fr).
	if ( strlen( $locale ) === 2 ) { $locale = strtolower( $locale ) . '_' . strtoupper( $locale ); }
	printf( '<meta property="og:locale" content="%s">'    . "\n", esc_attr( $locale ) );
	$og_title       = apply_filters( 'pcs_og_title', $title );
	$og_description = apply_filters( 'pcs_og_description', $desc );
	printf( '<meta property="og:title" content="%s">'     . "\n", esc_attr( $og_title ) );
	printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $og_description ) );
	printf( '<meta property="og:url" content="%s">'       . "\n", esc_url( $url ) );
	$og_type = 'website';
	if ( is_singular() && ! is_page() ) {
		// Posts standards + tous les CPT (article, recette, etc.).
		$og_type = 'article';
	}
	$og_type = apply_filters( 'pcs_og_type', $og_type );
	printf( '<meta property="og:type" content="%s">' . "\n", esc_attr( $og_type ) );

	$og_image = pcs_og_image();
	if ( $og_image ) {
		[ $og_w, $og_h ] = pcs_og_image_dimensions( $og_image );
		printf( '<meta property="og:image" content="%s">'        . "\n", esc_url( $og_image ) );
		printf( '<meta property="og:image:width" content="%d">'  . "\n", (int) $og_w );
		printf( '<meta property="og:image:height" content="%d">' . "\n", (int) $og_h );
	}

	// Article-specific.
	if ( is_singular( 'post' ) ) {
		$post = get_queried_object();
		printf( '<meta property="article:published_time" content="%s">' . "\n", esc_attr( get_the_date( 'c', $post ) ) );
		printf( '<meta property="article:modified_time" content="%s">'  . "\n", esc_attr( get_the_modified_date( 'c', $post ) ) );
		printf( '<meta property="article:author" content="%s">'         . "\n", esc_attr( get_the_author_meta( 'display_name', $post->post_author ) ) );
		foreach ( wp_get_post_categories( $post->ID ) as $cid ) {
			$cat = get_category( $cid );
			printf( '<meta property="article:section" content="%s">' . "\n", esc_attr( $cat->name ) );
		}
		foreach ( wp_get_post_tags( $post->ID ) as $tag ) {
			printf( '<meta property="article:tag" content="%s">' . "\n", esc_attr( $tag->name ) );
		}
	}

	// Twitter — summary_large_image par défaut (plus de place/visibilité, fallback OK sans image).
	// Normalisation unicode pour éviter homograph/RTL-override dans preview.
	printf( '<meta name="twitter:card" content="%s">'  . "\n", 'summary_large_image' );
	printf( '<meta name="twitter:title" content="%s">' . "\n", esc_attr( pcs_normalize_social_text( $title ) ) );
	printf( '<meta name="twitter:description" content="%s">' . "\n", esc_attr( pcs_normalize_social_text( $desc ) ) );
	if ( $og_image ) { printf( '<meta name="twitter:image" content="%s">'       . "\n", esc_url( $og_image ) ); }

	// Sanitiser handles Twitter : forcer "@" en préfixe (sinon meta invalide).
	$tw_site    = trim( (string) pcs_twitter_site(), '@ ' );
	$tw_creator = trim( (string) pcs_twitter_creator(), '@ ' );
	if ( $tw_site )    { printf( '<meta name="twitter:site" content="@%s">'    . "\n", esc_attr( $tw_site ) ); }
	if ( $tw_creator ) { printf( '<meta name="twitter:creator" content="@%s">' . "\n", esc_attr( $tw_creator ) ); }
}, 6 );

// ─── Last-Modified HTTP header for conditional GETs (304) ─────────────────────

add_action( 'template_redirect', function () {
	if ( is_admin() || is_user_logged_in() ) { return; }
	$time = 0;
	if ( is_singular() ) {
		$time = (int) get_post_modified_time( 'U', true, get_queried_object_id() );
	} elseif ( is_archive() || is_home() ) {
		$latest = get_posts( [ 'posts_per_page' => 1, 'orderby' => 'modified', 'order' => 'DESC', 'fields' => 'ids' ] );
		if ( $latest ) { $time = (int) get_post_modified_time( 'U', true, $latest[0] ); }
	}
	if ( ! $time ) { return; }
	$lm = gmdate( 'D, d M Y H:i:s', $time ) . ' GMT';
	header( 'Last-Modified: ' . $lm );

	$since = isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ? strtotime( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) : 0;
	if ( $since && $since >= $time ) {
		status_header( 304 );
		exit;
	}
} );

// ─── External links : auto rel="noopener noreferrer" sur le_content ─────────
// Sécurité (window.opener leak) + bonne pratique SEO (signal sortants tracés).
// N'altère pas les ancres pointant vers le même host ou les liens sans href.

add_filter( 'the_content', function ( $content ) {
	if ( empty( $content ) ) { return $content; }
	$host = wp_parse_url( home_url(), PHP_URL_HOST );
	return preg_replace_callback(
		'#<a\b([^>]*?)href="(https?://[^"]+)"([^>]*)>#i',
		function ( $m ) use ( $host ) {
			$href      = $m[2];
			$href_host = wp_parse_url( $href, PHP_URL_HOST );
			if ( ! $href_host || strcasecmp( $href_host, (string) $host ) === 0 ) { return $m[0]; }
			$full = $m[1] . $m[3];
			if ( preg_match( '/\brel\s*=\s*"([^"]*)"/i', $full, $r ) ) {
				$rel = $r[1];
				$add = [];
				if ( strpos( $rel, 'noopener' )   === false ) { $add[] = 'noopener'; }
				if ( strpos( $rel, 'noreferrer' ) === false ) { $add[] = 'noreferrer'; }
				if ( ! $add ) { return $m[0]; }
				$new_rel = trim( $rel . ' ' . implode( ' ', $add ) );
				$replaced = preg_replace( '/\brel\s*=\s*"[^"]*"/i', 'rel="' . $new_rel . '"', $m[0], 1 );
				return $replaced;
			}
			return '<a' . $m[1] . 'href="' . esc_url( $href ) . '"' . $m[3] . ' rel="noopener noreferrer">';
		},
		$content
	);
}, 30 );

// ─── Trailing slash 301 — uniformiser singular + archive ────────────────────
// WP gère déjà la plupart des cas, mais selon config serveur certaines URLs
// CPT/archives échappent au redirect. On force la cohérence avec la canonical.

add_action( 'template_redirect', function () {
	if ( is_admin() || wp_doing_ajax() || is_robots() ) { return; }
	if ( ! ( is_singular() || is_archive() || is_home() ) ) { return; }

	$req = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	if ( ! $req ) { return; }

	$canonical = pcs_canonical_url();
	if ( ! $canonical ) { return; }

	$req_path  = (string) wp_parse_url( $req, PHP_URL_PATH );
	$can_path  = (string) wp_parse_url( $canonical, PHP_URL_PATH );
	$req_query = (string) wp_parse_url( $req, PHP_URL_QUERY );

	if ( $req_path && $can_path && rtrim( $req_path, '/' ) === rtrim( $can_path, '/' ) && $req_path !== $can_path ) {
		$target = $canonical;
		if ( $req_query ) {
			$parsed = [];
			wp_parse_str( $req_query, $parsed );
			// Whitelist : on ne propage que les params connus (search/pagination/preview/utm/ref).
			$allowed = apply_filters( 'pcs_redirect_allowed_args', [
				's', 'paged', 'page', 'p', 'preview', 'preview_id', 'preview_nonce',
				'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'ref', 'gclid', 'fbclid',
			] );
			$filtered = array_intersect_key( $parsed, array_flip( $allowed ) );
			if ( $filtered ) { $target = add_query_arg( $filtered, $target ); }
		}
		wp_safe_redirect( $target, 301 );
		exit;
	}
}, 9 );

// ─── Hn auto-fix : prévient les hiérarchies plates (article 64×H2 sans H3) ──
// Détecte 4+ H2 consécutifs et transforme les suivants en H3 pour donner une
// vraie hiérarchie. N'altère pas les pages dont l'auteur a structuré
// correctement (alternance H2/H3 dès le départ).

add_filter( 'the_content', function ( $content ) {
	if ( ! is_singular() ) { return $content; }
	if ( substr_count( strtolower( $content ), '<h2' ) < 5 ) { return $content; }
	if ( substr_count( strtolower( $content ), '<h3' ) > 2 ) { return $content; }

	$count = 0;
	return preg_replace_callback( '#<(h2)\b([^>]*)>(.*?)</h2>#is', function ( $m ) use ( &$count ) {
		$count++;
		// Garder les 2 premiers H2 comme jalons, transformer les suivants en H3.
		if ( $count <= 2 ) { return $m[0]; }
		return '<h3' . $m[2] . '>' . $m[3] . '</h3>';
	}, $content );
}, 25 );

// ─── Author archive nofollow ──────────────────────────────────────────────────

add_filter( 'the_author_posts_link', function ( $link ) {
	if ( strpos( $link, 'rel=' ) !== false ) {
		return preg_replace( '/rel="([^"]*)"/i', 'rel="$1 nofollow"', $link );
	}
	return str_replace( '<a ', '<a rel="nofollow" ', $link );
} );

add_filter( 'the_content', function ( $content ) {
	if ( ! is_singular() || empty( $content ) ) { return $content; }
	$host = wp_parse_url( home_url(), PHP_URL_HOST );
	$pattern = '#<a\s+([^>]*?)href="(https?://[^"]*' . preg_quote( $host, '#' ) . '/author/[^"]+)"([^>]*)>#i';
	return preg_replace_callback( $pattern, function ( $m ) {
		$attrs_before = $m[1];
		$href         = $m[2];
		$attrs_after  = $m[3];
		$full         = $attrs_before . $attrs_after;
		if ( preg_match( '/rel="([^"]*)"/i', $full, $r ) ) {
			if ( strpos( $r[1], 'nofollow' ) !== false ) { return $m[0]; }
			$new_rel      = trim( $r[1] . ' nofollow' );
			$attrs_after  = preg_replace( '/rel="[^"]*"/i', 'rel="' . $new_rel . '"', $attrs_after );
			$attrs_before = preg_replace( '/rel="[^"]*"/i', 'rel="' . $new_rel . '"', $attrs_before );
			return '<a ' . trim( $attrs_before ) . ' href="' . esc_url( $href ) . '" ' . trim( $attrs_after ) . '>';
		}
		return '<a ' . trim( $attrs_before ) . ' rel="nofollow" href="' . esc_url( $href ) . '"' . $attrs_after . '>';
	}, $content );
}, 20 );

// ─── Register post meta for REST access ───────────────────────────────────────

add_action( 'init', function () {
	$fields = [
		'_pcs_meta_title'       => 'string',
		'_pcs_meta_description' => 'string',
		'_pcs_og_image_id'      => 'integer',
		'_pcs_noindex'          => 'boolean',
		'_pcs_focus_keyword'    => 'string',
	];
	foreach ( $fields as $key => $type ) {
		register_post_meta( '', $key, [
			'type'          => $type,
			'single'        => true,
			'show_in_rest'  => true,
			'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
		] );
	}
} );

// ─── Admin meta box ───────────────────────────────────────────────────────────

add_action( 'add_meta_boxes', function () {
	$types = array_values( array_filter(
		get_post_types( [ 'public' => true ] ),
		fn( $t ) => $t !== 'attachment'
	) );
	add_meta_box( 'pcs_seo', 'SEO', 'pcs_seo_meta_box', $types, 'normal', 'high' );
} );

function pcs_seo_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'pcs_seo_save', 'pcs_seo_nonce' );
	$title    = (string) get_post_meta( $post->ID, '_pcs_meta_title', true );
	$desc     = (string) get_post_meta( $post->ID, '_pcs_meta_description', true );
	$og_id    = (int)    get_post_meta( $post->ID, '_pcs_og_image_id', true );
	$noindex  = (bool)   get_post_meta( $post->ID, '_pcs_noindex', true );
	$keyword  = (string) get_post_meta( $post->ID, '_pcs_focus_keyword', true );
	$og_url   = $og_id ? wp_get_attachment_image_url( $og_id, 'medium' ) : '';
	$site     = get_bloginfo( 'name' );
	$def_title = $post->post_title ? $post->post_title . ' — ' . $site : $site;
	$def_desc  = $post->post_excerpt
		? wp_strip_all_tags( $post->post_excerpt )
		: mb_substr( wp_strip_all_tags( $post->post_content ), 0, 160 );
	$permalink = get_permalink( $post ) ?: home_url( '/' . $post->post_name );
	wp_enqueue_media();
	?>
	<style>
		#pcs_seo{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;font-size:13px}
		#pcs_seo .f{margin-bottom:14px}
		#pcs_seo label{display:block;font-weight:600;margin-bottom:4px}
		#pcs_seo label span{font-weight:400;color:#787c82}
		#pcs_seo input[type=text],#pcs_seo textarea{width:100%;box-sizing:border-box;border:1px solid #8c8f94;border-radius:3px;padding:6px 8px;font-size:13px;line-height:1.5}
		#pcs_seo textarea{resize:vertical;min-height:68px}
		#pcs_seo .cnt{font-size:11px;color:#8c8f94;float:right;margin-top:3px}
		#pcs_seo .cnt.warn{color:#d63638;font-weight:600}
		#pcs_seo .prev{background:#f6f7f7;border:1px solid #dcdcde;border-radius:4px;padding:12px 14px;margin-top:6px;overflow:hidden}
		#pcs_seo .prev-url{font-size:12px;color:#0d652d;margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
		#pcs_seo .prev-title{font-size:18px;color:#1a0dab;line-height:1.3;margin:0 0 3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
		#pcs_seo .prev-desc{font-size:13px;color:#4d5156;line-height:1.55;margin:0;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
		#pcs_seo .og-preview{width:160px;height:84px;object-fit:cover;border-radius:4px;border:1px solid #dcdcde;margin-right:10px}
		#pcs_seo .row{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
		#pcs_seo .btn{background:#f0f0f1;border:1px solid #8c8f94;border-radius:3px;padding:5px 10px;font-size:12px;cursor:pointer}
		#pcs_seo .adv{border-top:1px solid #dcdcde;margin-top:16px;padding-top:12px}
		#pcs_seo .adv summary{font-weight:600;cursor:pointer;margin-bottom:10px}
	</style>

	<div class="f">
		<label for="pcs_meta_title">Titre SEO <span>(défaut : «&nbsp;<?php echo esc_html( $def_title ); ?>&nbsp;»)</span></label>
		<input type="text" id="pcs_meta_title" name="pcs_meta_title"
			value="<?php echo esc_attr( $title ); ?>"
			placeholder="<?php echo esc_attr( $def_title ); ?>" maxlength="120">
		<span class="cnt" id="pcs_tc"><?php echo mb_strlen( $title ); ?>/60</span>
	</div>

	<div class="f">
		<label for="pcs_meta_description">Meta description <span>(160 chars max)</span></label>
		<textarea id="pcs_meta_description" name="pcs_meta_description"
			maxlength="160" rows="3"
			placeholder="<?php echo esc_attr( mb_substr( $def_desc, 0, 160 ) ); ?>"><?php echo esc_textarea( $desc ); ?></textarea>
		<span class="cnt" id="pcs_dc"><?php echo mb_strlen( $desc ); ?>/160</span>
	</div>

	<div class="prev">
		<div class="prev-url"><?php echo esc_html( $permalink ); ?></div>
		<div class="prev-title" id="pcs_pt"><?php echo esc_html( $title ?: $def_title ); ?></div>
		<p  class="prev-desc"  id="pcs_pd"><?php echo esc_html( $desc  ?: mb_substr( $def_desc, 0, 160 ) ); ?></p>
	</div>

	<details class="adv">
		<summary>Avancé</summary>

		<div class="f">
			<label>Image sociale (OG) <span>(sinon : image à la une)</span></label>
			<div class="row">
				<img id="pcs_og_prev" class="og-preview" src="<?php echo esc_url( $og_url ); ?>" alt="" style="<?php echo $og_url ? '' : 'display:none'; ?>">
				<button type="button" class="btn" id="pcs_og_pick">Choisir</button>
				<button type="button" class="btn" id="pcs_og_clear" style="<?php echo $og_id ? '' : 'display:none'; ?>">Retirer</button>
				<input type="hidden" id="pcs_og_id" name="pcs_og_image_id" value="<?php echo (int) $og_id; ?>">
			</div>
		</div>

		<div class="f">
			<label><input type="checkbox" name="pcs_noindex" value="1" <?php checked( $noindex ); ?>> Noindex — retirer cette page des moteurs de recherche</label>
		</div>

		<div class="f">
			<label for="pcs_focus_keyword">Mot-clé principal <span>(optionnel, pour ton suivi)</span></label>
			<input type="text" id="pcs_focus_keyword" name="pcs_focus_keyword" value="<?php echo esc_attr( $keyword ); ?>" maxlength="80">
		</div>
	</details>

	<script>
	(function(){
		var ti=document.getElementById('pcs_meta_title'),
		    di=document.getElementById('pcs_meta_description'),
		    tc=document.getElementById('pcs_tc'),
		    dc=document.getElementById('pcs_dc'),
		    pt=document.getElementById('pcs_pt'),
		    pd=document.getElementById('pcs_pd'),
		    dft=<?php echo wp_json_encode( $def_title ); ?>,
		    dfd=<?php echo wp_json_encode( mb_substr( $def_desc, 0, 160 ) ); ?>;
		function upd(){
			var tl=ti.value.length;
			tc.textContent=tl+'/60'; tc.className='cnt'+(tl>60?' warn':'');
			pt.textContent=ti.value||dft;
			var dl=di.value.length;
			dc.textContent=dl+'/160'; dc.className='cnt'+(dl>160?' warn':'');
			pd.textContent=di.value||dfd;
		}
		ti.addEventListener('input',upd); di.addEventListener('input',upd); upd();

		var pickBtn=document.getElementById('pcs_og_pick'),
		    clearBtn=document.getElementById('pcs_og_clear'),
		    prev=document.getElementById('pcs_og_prev'),
		    hid=document.getElementById('pcs_og_id'),
		    frame;
		pickBtn.addEventListener('click',function(e){
			e.preventDefault();
			if(frame){frame.open();return;}
			frame=wp.media({title:'Image sociale',library:{type:'image'},multiple:false});
			frame.on('select',function(){
				var a=frame.state().get('selection').first().toJSON();
				hid.value=a.id;
				prev.src=a.sizes&&a.sizes.medium?a.sizes.medium.url:a.url;
				prev.style.display='';
				clearBtn.style.display='';
			});
			frame.open();
		});
		clearBtn.addEventListener('click',function(e){
			e.preventDefault();
			hid.value='';
			prev.style.display='none';
			clearBtn.style.display='none';
		});
	})();
	</script>
	<?php
}

add_action( 'save_post', function ( int $post_id ): void {
	if ( empty( $_POST['pcs_seo_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pcs_seo_nonce'] ) ), 'pcs_seo_save' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

	// Text fields.
	foreach ( [
		'pcs_meta_title'       => [ '_pcs_meta_title',       120 ],
		'pcs_meta_description' => [ '_pcs_meta_description', 160 ],
		'pcs_focus_keyword'    => [ '_pcs_focus_keyword',     80 ],
	] as $field => [ $meta_key, $max ] ) {
		if ( ! isset( $_POST[ $field ] ) ) { continue; }
		$val = mb_substr( sanitize_textarea_field( wp_unslash( $_POST[ $field ] ) ), 0, $max );
		$val ? update_post_meta( $post_id, $meta_key, $val ) : delete_post_meta( $post_id, $meta_key );
	}

	// Image ID.
	$og_id = isset( $_POST['pcs_og_image_id'] ) ? (int) $_POST['pcs_og_image_id'] : 0;
	$og_id ? update_post_meta( $post_id, '_pcs_og_image_id', $og_id ) : delete_post_meta( $post_id, '_pcs_og_image_id' );

	// Checkbox.
	$noindex = ! empty( $_POST['pcs_noindex'] );
	$noindex ? update_post_meta( $post_id, '_pcs_noindex', 1 ) : delete_post_meta( $post_id, '_pcs_noindex' );
} );
