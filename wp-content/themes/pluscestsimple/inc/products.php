<?php
/**
 * Custom Post Type: arw_product
 *
 * Stores affiliate products (shown in the "L'Essentiel" grid).
 * Each product has:
 *   - title (product name)
 *   - featured image
 *   - excerpt (optional short description)
 *   - meta: brand, score (0-10), price, affiliate link, badge/tag
 *   - taxonomy arw_product_cat (Blouson, Casque, Gants, ...)
 *
 * Renders via shortcode [arw_products limit="6"] or helper arw_pulse_render_products().
 * Completely decoupled from templates: theme updates don't touch product data.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Feature toggle. If disabled, register silent shortcodes so [arw_essential]
// doesn't render raw on legacy pages, and skip CPT/admin/schema entirely.
if ( ! apply_filters( 'arw_pulse_enable_products', true ) ) {
	add_shortcode( 'arw_essential', '__return_empty_string' );
	add_shortcode( 'arw_products',  '__return_empty_string' );
	return;
}

const ARW_PULSE_PRODUCT_CPT = 'arw_product';
const ARW_PULSE_PRODUCT_TAX = 'arw_product_cat';

// --- Register CPT + taxonomy + meta ----------------------------------------

add_action( 'init', function () {
	register_post_type( ARW_PULSE_PRODUCT_CPT, [
		'labels' => [
			'name'               => __( 'Produits', 'arw-pulse' ),
			'singular_name'      => __( 'Produit', 'arw-pulse' ),
			'menu_name'          => __( 'Produits', 'arw-pulse' ),
			'add_new'            => __( 'Ajouter', 'arw-pulse' ),
			'add_new_item'       => __( 'Ajouter un produit', 'arw-pulse' ),
			'edit_item'          => __( 'Modifier le produit', 'arw-pulse' ),
			'new_item'           => __( 'Nouveau produit', 'arw-pulse' ),
			'search_items'       => __( 'Rechercher', 'arw-pulse' ),
			'not_found'          => __( 'Aucun produit', 'arw-pulse' ),
			'not_found_in_trash' => __( 'Aucun produit dans la corbeille', 'arw-pulse' ),
			'featured_image'     => __( 'Visuel produit', 'arw-pulse' ),
			'set_featured_image' => __( 'Ajouter un visuel', 'arw-pulse' ),
		],
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => defined( 'ARW_PULSE_ADMIN_SLUG' ) ? ARW_PULSE_ADMIN_SLUG : true,
		'show_in_rest'        => true,
		'show_in_admin_bar'   => false,
		'menu_icon'           => 'dashicons-cart',
		'supports'            => [ 'title', 'thumbnail', 'excerpt', 'page-attributes' ],
		'has_archive'         => false,
		'rewrite'             => false,
		'exclude_from_search' => true,
	] );

	register_taxonomy( ARW_PULSE_PRODUCT_TAX, [ ARW_PULSE_PRODUCT_CPT ], [
		'labels' => [
			'name'          => __( 'Catégories produit', 'arw-pulse' ),
			'singular_name' => __( 'Catégorie', 'arw-pulse' ),
			'all_items'     => __( 'Toutes', 'arw-pulse' ),
			'add_new_item'  => __( 'Ajouter une catégorie', 'arw-pulse' ),
		],
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'hierarchical'      => false,
		'rewrite'           => false,
	] );

	// Meta éditables par tout rôle pouvant éditer un produit.
	$editor_meta = [ 'arw_product_brand', 'arw_product_score', 'arw_product_price', 'arw_product_tag', 'arw_product_sku', 'arw_product_availability', 'arw_product_currency', 'arw_product_price_amount' ];
	foreach ( $editor_meta as $key ) {
		register_post_meta( ARW_PULSE_PRODUCT_CPT, $key, [
			'type'          => 'string',
			'single'        => true,
			'default'       => '',
			'show_in_rest'  => true,
			'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
		] );
	}
	// Lien affilié : sensible (revenue), restreint à manage_options pour éviter détournement.
	register_post_meta( ARW_PULSE_PRODUCT_CPT, 'arw_product_link', [
		'type'          => 'string',
		'single'        => true,
		'default'       => '',
		'show_in_rest'  => true,
		'auth_callback' => function () { return current_user_can( 'manage_options' ); },
	] );
} );

// --- Admin meta box ---------------------------------------------------------

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'arw_product_meta',
		__( 'Infos produit', 'arw-pulse' ),
		'arw_pulse_product_meta_box',
		ARW_PULSE_PRODUCT_CPT,
		'normal',
		'high'
	);
} );

function arw_pulse_product_meta_box( $post ) {
	wp_nonce_field( 'arw_product_meta', 'arw_product_meta_nonce' );
	$brand    = get_post_meta( $post->ID, 'arw_product_brand', true );
	$score    = get_post_meta( $post->ID, 'arw_product_score', true );
	$price    = get_post_meta( $post->ID, 'arw_product_price', true );
	$link     = get_post_meta( $post->ID, 'arw_product_link', true );
	$tag      = get_post_meta( $post->ID, 'arw_product_tag', true );
	$sku      = get_post_meta( $post->ID, 'arw_product_sku', true );
	$avail    = get_post_meta( $post->ID, 'arw_product_availability', true ) ?: 'InStock';
	$currency = get_post_meta( $post->ID, 'arw_product_currency', true ) ?: 'EUR';
	$amount   = get_post_meta( $post->ID, 'arw_product_price_amount', true );
	?>
	<style>
		.arw-prod-meta td input { width: 100%; max-width: 420px; }
		.arw-prod-meta .arw-prod-hint { color: #666; font-size: 12px; margin-top: 4px; display: block; }
	</style>
	<table class="form-table arw-prod-meta" role="presentation">
		<tr>
			<th><label for="arw_product_brand"><?php esc_html_e( 'Marque', 'arw-pulse' ); ?></label></th>
			<td><input type="text" id="arw_product_brand" name="arw_product_brand" value="<?php echo esc_attr( $brand ); ?>" placeholder="Shoei, Alpinestars, Dainese…"></td>
		</tr>
		<tr>
			<th><label for="arw_product_score"><?php esc_html_e( 'Note / 10', 'arw-pulse' ); ?></label></th>
			<td>
				<input type="text" id="arw_product_score" name="arw_product_score" value="<?php echo esc_attr( $score ); ?>" placeholder="8.5">
				<span class="arw-prod-hint"><?php esc_html_e( 'Nombre décimal entre 0 et 10. Vide = pas de note affichée.', 'arw-pulse' ); ?></span>
			</td>
		</tr>
		<tr>
			<th><label for="arw_product_price"><?php esc_html_e( 'Prix (affichage)', 'arw-pulse' ); ?></label></th>
			<td>
				<input type="text" id="arw_product_price" name="arw_product_price" value="<?php echo esc_attr( $price ); ?>" placeholder="dès 299 €">
				<span class="arw-prod-hint"><?php esc_html_e( 'Texte libre affiché sur la carte : « dès 299 € », « 149 € ».', 'arw-pulse' ); ?></span>
			</td>
		</tr>
		<tr>
			<th><label for="arw_product_price_amount"><?php esc_html_e( 'Prix (schema)', 'arw-pulse' ); ?></label></th>
			<td>
				<input type="number" step="0.01" id="arw_product_price_amount" name="arw_product_price_amount" value="<?php echo esc_attr( $amount ); ?>" placeholder="299.00" style="max-width:180px">
				<select name="arw_product_currency" style="max-width:100px">
					<?php foreach ( [ 'EUR', 'USD', 'GBP', 'CHF', 'CAD' ] as $c ) : ?>
						<option value="<?php echo esc_attr( $c ); ?>" <?php selected( $currency, $c ); ?>><?php echo esc_html( $c ); ?></option>
					<?php endforeach; ?>
				</select>
				<span class="arw-prod-hint"><?php esc_html_e( 'Valeur numérique pour le Product schema (rich result prix). Laisse vide si pas pertinent.', 'arw-pulse' ); ?></span>
			</td>
		</tr>
		<tr>
			<th><label for="arw_product_availability"><?php esc_html_e( 'Disponibilité', 'arw-pulse' ); ?></label></th>
			<td>
				<select id="arw_product_availability" name="arw_product_availability">
					<?php foreach ( [
						'InStock'      => __( 'En stock',        'arw-pulse' ),
						'OutOfStock'   => __( 'Rupture',         'arw-pulse' ),
						'PreOrder'     => __( 'Précommande',     'arw-pulse' ),
						'BackOrder'    => __( 'Sur commande',    'arw-pulse' ),
						'Discontinued' => __( 'Plus commercialisé', 'arw-pulse' ),
					] as $k => $label ) : ?>
						<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $avail, $k ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="arw_product_sku"><?php esc_html_e( 'SKU / MPN', 'arw-pulse' ); ?></label></th>
			<td>
				<input type="text" id="arw_product_sku" name="arw_product_sku" value="<?php echo esc_attr( $sku ); ?>" placeholder="NEO-PREMIUM-2024" style="max-width:260px">
				<span class="arw-prod-hint"><?php esc_html_e( 'Référence interne ou fabricant. Renforce le Product schema.', 'arw-pulse' ); ?></span>
			</td>
		</tr>
		<?php if ( current_user_can( 'manage_options' ) ) : ?>
		<tr>
			<th><label for="arw_product_link"><?php esc_html_e( 'Lien affilié', 'arw-pulse' ); ?></label></th>
			<td>
				<input type="url" id="arw_product_link" name="arw_product_link" value="<?php echo esc_attr( $link ); ?>" placeholder="https://…">
				<span class="arw-prod-hint"><?php esc_html_e( 'URL complète. rel="sponsored nofollow noopener" et target="_blank" ajoutés automatiquement. Champ visible et éditable uniquement par les admins.', 'arw-pulse' ); ?></span>
			</td>
		</tr>
		<?php elseif ( $link ) : ?>
		<tr>
			<th><?php esc_html_e( 'Lien affilié', 'arw-pulse' ); ?></th>
			<td><em style="color:#666"><?php esc_html_e( 'Réservé aux administrateurs.', 'arw-pulse' ); ?></em></td>
		</tr>
		<?php endif; ?>
		<tr>
			<th><label for="arw_product_tag"><?php esc_html_e( 'Badge', 'arw-pulse' ); ?></label></th>
			<td>
				<input type="text" id="arw_product_tag" name="arw_product_tag" value="<?php echo esc_attr( $tag ); ?>" placeholder="Pick rédac (optionnel)">
				<span class="arw-prod-hint"><?php esc_html_e( 'Affiché en surimpression sur l\'image. Ex : « Pick rédac », « Nouveau », « Promo ».', 'arw-pulse' ); ?></span>
			</td>
		</tr>
	</table>
	<?php
}

add_action( 'save_post_' . ARW_PULSE_PRODUCT_CPT, function ( $post_id ) {
	if ( ! isset( $_POST['arw_product_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['arw_product_meta_nonce'] ) ), 'arw_product_meta' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

	foreach ( [ 'arw_product_brand', 'arw_product_score', 'arw_product_price', 'arw_product_tag', 'arw_product_sku', 'arw_product_currency', 'arw_product_price_amount' ] as $k ) {
		if ( isset( $_POST[ $k ] ) ) {
			update_post_meta( $post_id, $k, sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) );
		}
	}
	// Availability: strict enum to prevent polluting Product schema with arbitrary strings.
	if ( isset( $_POST['arw_product_availability'] ) ) {
		$raw   = sanitize_text_field( wp_unslash( $_POST['arw_product_availability'] ) );
		$clean = function_exists( 'arw_pulse_normalize_availability' )
			? arw_pulse_normalize_availability( $raw )
			: ( in_array( $raw, [ 'InStock', 'OutOfStock', 'PreOrder', 'BackOrder', 'Discontinued' ], true ) ? $raw : 'InStock' );
		update_post_meta( $post_id, 'arw_product_availability', $clean );
	}
	// Currency: ISO 4217 whitelist.
	if ( isset( $_POST['arw_product_currency'] ) ) {
		$raw     = strtoupper( sanitize_text_field( wp_unslash( $_POST['arw_product_currency'] ) ) );
		$allowed = [ 'EUR', 'USD', 'GBP', 'CHF', 'CAD', 'AUD', 'JPY' ];
		update_post_meta( $post_id, 'arw_product_currency', in_array( $raw, $allowed, true ) ? $raw : 'EUR' );
	}
	// Price amount: numeric only.
	if ( isset( $_POST['arw_product_price_amount'] ) ) {
		$raw = preg_replace( '/[^0-9\.,]/', '', (string) wp_unslash( $_POST['arw_product_price_amount'] ) );
		$raw = str_replace( ',', '.', $raw );
		update_post_meta( $post_id, 'arw_product_price_amount', is_numeric( $raw ) ? (string) (float) $raw : '' );
	}
	// Lien affilié : revenue-sensitive, sauvegarde réservée aux administrators.
	if ( isset( $_POST['arw_product_link'] ) && current_user_can( 'manage_options' ) ) {
		update_post_meta( $post_id, 'arw_product_link', esc_url_raw( wp_unslash( $_POST['arw_product_link'] ) ) );
	}
} );

// --- Admin list columns -----------------------------------------------------

add_filter( 'manage_' . ARW_PULSE_PRODUCT_CPT . '_posts_columns', function ( $cols ) {
	$new = [
		'cb'                             => $cols['cb'] ?? '<input type="checkbox">',
		'title'                          => __( 'Produit', 'arw-pulse' ),
		'thumb'                          => __( 'Visuel', 'arw-pulse' ),
		'brand'                          => __( 'Marque', 'arw-pulse' ),
		'taxonomy-arw_product_cat'       => __( 'Catégorie', 'arw-pulse' ),
		'score'                          => __( 'Note', 'arw-pulse' ),
		'price'                          => __( 'Prix', 'arw-pulse' ),
		'link'                           => __( 'Lien', 'arw-pulse' ),
		'date'                           => __( 'Date', 'arw-pulse' ),
	];
	return $new;
} );

add_action( 'manage_' . ARW_PULSE_PRODUCT_CPT . '_posts_custom_column', function ( $col, $post_id ) {
	switch ( $col ) {
		case 'thumb':
			if ( has_post_thumbnail( $post_id ) ) {
				echo get_the_post_thumbnail( $post_id, [ 60, 60 ], [ 'style' => 'border-radius:6px;object-fit:cover' ] );
			} else {
				echo '<span style="color:#bbb">—</span>';
			}
			break;
		case 'brand':
			echo esc_html( get_post_meta( $post_id, 'arw_product_brand', true ) ?: '—' );
			break;
		case 'score':
			$s = get_post_meta( $post_id, 'arw_product_score', true );
			echo $s ? esc_html( $s ) . '<small style="color:#777"> /10</small>' : '—';
			break;
		case 'price':
			echo esc_html( get_post_meta( $post_id, 'arw_product_price', true ) ?: '—' );
			break;
		case 'link':
			$url = get_post_meta( $post_id, 'arw_product_link', true );
			if ( $url ) {
				printf( '<a href="%s" target="_blank" rel="noopener">Ouvrir ↗</a>', esc_url( $url ) );
			} else {
				echo '—';
			}
			break;
	}
}, 10, 2 );

// Make score/price/brand columns sortable.
add_filter( 'manage_edit-' . ARW_PULSE_PRODUCT_CPT . '_sortable_columns', function ( $cols ) {
	$cols['score'] = 'arw_product_score';
	$cols['brand'] = 'arw_product_brand';
	$cols['price'] = 'arw_product_price';
	return $cols;
} );

// --- Render helpers ---------------------------------------------------------

/**
 * Render the L'Essentiel grid of products.
 *
 * @param int         $limit Max products to show.
 * @param string|null $cat   Optional product category slug to filter.
 */
function arw_pulse_render_products( $limit = 6, $cat = null ) {
	$args = [
		'post_type'      => ARW_PULSE_PRODUCT_CPT,
		'posts_per_page' => max( 1, (int) $limit ),
		'orderby'        => [ 'menu_order' => 'ASC', 'date' => 'DESC' ],
		'post_status'    => 'publish',
	];
	if ( $cat ) {
		$args['tax_query'] = [ [
			'taxonomy' => ARW_PULSE_PRODUCT_TAX,
			'field'    => 'slug',
			'terms'    => sanitize_title( $cat ),
		] ];
	}
	$products = get_posts( $args );

	if ( empty( $products ) ) {
		// Visiteurs : rien. Admin uniquement : lien de création.
		if ( ! current_user_can( 'edit_posts' ) ) { return ''; }
		return '<p class="arw-gear-empty" style="color:var(--wp--preset--color--muted);padding:2rem 0;text-align:center">' .
			sprintf(
				/* translators: %s admin URL */
				wp_kses( __( 'Aucun produit publié pour l\'instant. <a href="%s">Créer ton premier produit →</a>', 'arw-pulse' ), [ 'a' => [ 'href' => true ] ] ),
				esc_url( admin_url( 'post-new.php?post_type=' . ARW_PULSE_PRODUCT_CPT ) )
			) .
			'</p>';
	}

	ob_start();
	echo '<div class="arw-gear">';
	foreach ( $products as $p ) {
		$terms = wp_get_post_terms( $p->ID, ARW_PULSE_PRODUCT_TAX, [ 'fields' => 'names' ] );
		$cat   = $terms && ! is_wp_error( $terms ) ? $terms[0] : '';
		$brand = (string) get_post_meta( $p->ID, 'arw_product_brand', true );
		$score = (string) get_post_meta( $p->ID, 'arw_product_score', true );
		$price = (string) get_post_meta( $p->ID, 'arw_product_price', true );
		$link  = (string) get_post_meta( $p->ID, 'arw_product_link', true );
		$tag   = (string) get_post_meta( $p->ID, 'arw_product_tag', true );

		$img_id = get_post_thumbnail_id( $p->ID );
		if ( $img_id ) {
			$src    = wp_get_attachment_image_src( $img_id, 'arw-vertical' );
			$img    = $src ? $src[0] : '';
			$img_w  = $src ? $src[1] : 900;
			$img_h  = $src ? $src[2] : 1100;
			$img_alt = (string) get_post_meta( $img_id, '_wp_attachment_image_alt', true );
		} else {
			$img    = 'https://placehold.co/900x1100/161616/ffd600?text=' . rawurlencode( $p->post_title );
			$img_w  = 900;
			$img_h  = 1100;
			$img_alt = $p->post_title;
		}

		$link_attrs  = $link ? sprintf( ' href="%s" class="is-affiliate" target="_blank" rel="sponsored nofollow noopener"', esc_url( $link ) ) : ' href="#" aria-disabled="true"';

		// Schema validity gate : Google rejette tout Product sans offers/review/aggregateRating.
		// Anti-spam : aggregateRating microdata exige rating_count ≥ 5 (sinon
		// self-serving review markup, risque pénalité Google).
		$rating_count    = (int) get_post_meta( $p->ID, 'arw_product_rating_count', true );
		$has_score       = is_numeric( $score ) && (float) $score > 0 && $rating_count >= 5;
		$has_amount      = (float) get_post_meta( $p->ID, 'arw_product_price_amount', true ) > 0;
		$has_schema      = $has_score || $has_amount;
		$article_attrs   = $has_schema ? ' itemscope itemtype="https://schema.org/Product"' : '';
		$prop_image      = $has_schema ? ' itemprop="image"' : '';
		$prop_name       = $has_schema ? ' itemprop="name"' : '';
		$prop_brand      = $has_schema ? ' itemprop="brand" itemscope itemtype="https://schema.org/Brand"' : '';
		$prop_brand_name = $has_schema ? ' itemprop="name"' : '';
		?>
		<article class="arw-gear__card"<?php echo $article_attrs; ?>>
			<div class="arw-gear__media">
				<a<?php echo $link_attrs; ?> aria-label="<?php echo esc_attr( sprintf( __( 'Voir l\'offre : %s', 'arw-pulse' ), $p->post_title ) ); ?>">
					<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $img_alt ); ?>" width="<?php echo (int) $img_w; ?>" height="<?php echo (int) $img_h; ?>" loading="lazy" decoding="async"<?php echo $prop_image; ?>>
				</a>
				<?php if ( $tag ) : ?>
					<span class="arw-gear__tag"><?php echo esc_html( $tag ); ?></span>
				<?php endif; ?>
			</div>

			<?php if ( $cat ) : ?>
				<p class="arw-gear__cat"><?php echo esc_html( $cat ); ?></p>
			<?php endif; ?>

			<h3 class="arw-gear__name"<?php echo $prop_name; ?>><a<?php echo $link_attrs; ?>><?php echo esc_html( $p->post_title ); ?></a></h3>

			<?php if ( $brand ) : ?>
				<p class="arw-gear__brand"<?php echo $prop_brand; ?>><span<?php echo $prop_brand_name; ?>><?php echo esc_html( $brand ); ?></span></p>
			<?php endif; ?>

			<?php if ( $score || $price ) : ?>
			<div class="arw-gear__meta">
				<?php if ( $has_score ) : ?>
					<p class="arw-gear__score" itemprop="aggregateRating" itemscope itemtype="https://schema.org/AggregateRating"><span itemprop="ratingValue"><?php echo esc_html( $score ); ?></span><small>/<span itemprop="bestRating">10</span></small><meta itemprop="ratingCount" content="<?php echo (int) $rating_count; ?>"></p>
				<?php elseif ( $score ) : ?>
					<p class="arw-gear__score"><?php echo esc_html( $score ); ?><small>/10</small></p>
				<?php endif; ?>
				<?php if ( $price ) : ?>
					<p class="arw-gear__price"><?php echo esc_html( $price ); ?></p>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<?php if ( $link ) : ?>
				<p class="arw-gear__cta-wrap">
					<a class="is-affiliate wp-element-button arw-gear__cta-btn" href="<?php echo esc_url( $link ); ?>" target="_blank" rel="sponsored nofollow noopener"><?php esc_html_e( 'Voir l\'offre →', 'arw-pulse' ); ?></a>
				</p>
			<?php endif; ?>
		</article>
		<?php
	}
	echo '</div>';
	return ob_get_clean();
}

add_shortcode( 'arw_products', function ( $atts ) {
	$a = shortcode_atts( [ 'limit' => 6, 'cat' => '' ], $atts, 'arw_products' );
	return arw_pulse_render_products( (int) $a['limit'], $a['cat'] ?: null );
} );

/**
 * Full "L'Essentiel" section rendered as a single shortcode.
 * Bypass-s the pattern registry entirely — used directly by front-page.html.
 * Bulletproof : no multi-layer block rendering to worry about.
 */
add_shortcode( 'arw_essential', function ( $atts ) {
	$a = shortcode_atts( [
		'num'   => '02',
		'title' => 'L\'Essentiel',
		'lead'  => '6 pièces d\'équipement testées et approuvées. Gérées depuis <em>Produits</em> dans l\'admin.',
		'limit' => 6,
		'cat'   => '',
	], $atts, 'arw_essential' );

	ob_start();
	?>
	<section class="arw-essentials alignfull" id="essentiel">
		<div class="arw-essentials__wrap">
			<header class="arw-section-head">
				<div>
					<p class="arw-section-num"><?php echo esc_html( $a['num'] ); ?></p>
					<h2 class="arw-section-title"><?php echo esc_html( $a['title'] ); ?></h2>
				</div>
				<p class="arw-section-lead"><?php echo wp_kses_post( $a['lead'] ); ?></p>
			</header>
			<?php echo arw_pulse_render_products( (int) $a['limit'], $a['cat'] ?: null ); ?>
		</div>
	</section>
	<?php
	return ob_get_clean();
} );
