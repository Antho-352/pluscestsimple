<?php
/**
 * Produits / Avis affiliés — CPT générique + template de conversion.
 *
 * Inspiré de quel-canape.fr : fiche produit avis (note globale + sous-notes,
 * dimensions, caractéristiques, points forts/faibles, idéal pour, FAQ, CTA affilié
 * sticky, disclosure affiliation). Générique : specs en clé-valeur flexibles →
 * gère canapés, luminaires, tapis, meubles, etc. avec un seul template.
 *
 * Saisie : formulaire structuré (meta box, textareas rapides). Pas de JS lourd.
 *
 * @package pluscestsimple
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

const PCS_PRODUIT_CPT = 'pcs_produit';

// ─── CPT ───────────────────────────────────────────────────────────────────────

add_action( 'init', function () {
	register_post_type( PCS_PRODUIT_CPT, [
		'labels' => [
			'name'          => __( 'Produits', 'pluscestsimple' ),
			'singular_name' => __( 'Produit', 'pluscestsimple' ),
			'menu_name'     => __( 'Produits (avis)', 'pluscestsimple' ),
			'add_new_item'  => __( 'Ajouter un produit', 'pluscestsimple' ),
			'edit_item'     => __( 'Modifier le produit', 'pluscestsimple' ),
			'all_items'     => __( 'Tous les produits', 'pluscestsimple' ),
		],
		'public'       => true,
		'has_archive'  => true,
		'menu_position'=> 26,
		'menu_icon'    => 'dashicons-cart',
		'rewrite'      => [ 'slug' => 'avis' ],
		'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
		'taxonomies'   => [ 'category' ],
	] );
} );

// Flush des permaliens une fois par version (CPT a un slug /avis/ → sinon 404).
add_action( 'init', function () {
	if ( get_option( 'pcs_produit_rewrite' ) !== PCS_VERSION ) {
		flush_rewrite_rules( false );
		update_option( 'pcs_produit_rewrite', PCS_VERSION );
	}
}, 99 );

// ─── Meta box (formulaire structuré) ───────────────────────────────────────────

add_action( 'add_meta_boxes_' . PCS_PRODUIT_CPT, function () {
	add_meta_box( 'pcs_produit_meta', __( 'Fiche produit (avis affilié)', 'pluscestsimple' ), 'pcs_produit_meta_box', PCS_PRODUIT_CPT, 'normal', 'high' );
} );

/** Champs : [meta_key => [label, type(text|textarea), placeholder, description]] */
function pcs_produit_fields(): array {
	return [
		'marque'          => [ 'Marque', 'text', 'Maisons du Monde', '' ],
		'prix'            => [ 'Prix', 'text', '649 €', '' ],
		'paiement'        => [ 'Facilités de paiement', 'text', '3x sans frais, 12x, 24x', '' ],
		'url'             => [ 'Lien affilié (CTA)', 'text', 'https://…', 'URL marchand (rel sponsored nofollow auto).' ],
		'cta'             => [ 'Libellé du bouton', 'text', 'Voir le produit', '' ],
		'note'            => [ 'Note globale (/10)', 'text', '7.8', 'Nombre, ex. 7.8' ],
		'sousnotes'       => [ 'Sous-notes', 'textarea', "Confort: 7.5\nQualité: 7\nDesign: 8.5\nRapport qualité-prix: 8", 'Une par ligne : « Label: note » (sur 10).' ],
		'avis30'          => [ 'Notre avis en 30 secondes', 'textarea', '', 'Verdict synthétique (2-4 phrases).' ],
		'idealpour'       => [ 'Idéal pour', 'textarea', "Premier logement\nFamilles pratiques\nAmateurs de scandinave", 'Une cible par ligne.' ],
		'dimensions'      => [ 'Dimensions', 'textarea', "Largeur: 220 cm\nHauteur: 90 cm\nProfondeur: 100 cm", 'Une par ligne : « Label: valeur ».' ],
		'caracteristiques'=> [ 'Caractéristiques', 'textarea', "Tissu: Velours\nConvertible: Oui\nStyle: Scandinave\nFabrication: Pologne", 'Une par ligne : « Label: valeur ».' ],
		'forts'           => [ 'Points forts', 'textarea', "Style scandinave authentique\nConversion rapide en lit", 'Un par ligne.' ],
		'faibles'         => [ 'Points faibles', 'textarea', "Couchage occasionnel uniquement", 'Un par ligne.' ],
		'pourqui'         => [ 'Pour qui ce produit est-il fait ?', 'textarea', '', 'Paragraphe.' ],
		'retour'          => [ 'Retour', 'text', '30 jours', '' ],
		'garantie'        => [ 'Garantie', 'text', 'Garantie légale de 2 ans', '' ],
		'galerie'         => [ 'Galerie (URLs)', 'textarea', '', 'Une URL d\'image par ligne (en plus de l\'image mise en avant).' ],
		'faq'             => [ 'FAQ', 'textarea', "Question ? | Réponse.", 'Une par ligne : « Question | Réponse ».' ],
	];
}

function pcs_produit_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'pcs_produit_save', 'pcs_produit_nonce' );
	$featured = (string) get_post_meta( $post->ID, '_pcs_prod_featured', true );
	echo '<p><label><input type="checkbox" name="pcs_prod_featured" value="1" ' . checked( $featured, '1', false ) . '> <strong>' . esc_html__( 'Mettre en avant sur la page d\'accueil (Sélection déco tendance)', 'pluscestsimple' ) . '</strong></label></p><hr>';
	echo '<style>.pcs-pf{margin:0 0 14px}.pcs-pf label{font-weight:600;display:block;margin-bottom:3px}.pcs-pf textarea{width:100%;min-height:70px}.pcs-pf input[type=text]{width:100%}.pcs-pf .description{color:#666;font-size:12px}</style>';
	foreach ( pcs_produit_fields() as $key => $f ) {
		[ $label, $type, $ph, $desc ] = $f;
		$val = (string) get_post_meta( $post->ID, '_pcs_prod_' . $key, true );
		echo '<div class="pcs-pf"><label for="pcs_prod_' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label>';
		if ( 'textarea' === $type ) {
			echo '<textarea id="pcs_prod_' . esc_attr( $key ) . '" name="pcs_prod_' . esc_attr( $key ) . '" placeholder="' . esc_attr( $ph ) . '">' . esc_textarea( $val ) . '</textarea>';
		} else {
			echo '<input type="text" id="pcs_prod_' . esc_attr( $key ) . '" name="pcs_prod_' . esc_attr( $key ) . '" value="' . esc_attr( $val ) . '" placeholder="' . esc_attr( $ph ) . '">';
		}
		if ( $desc ) { echo '<p class="description">' . esc_html( $desc ) . '</p>'; }
		echo '</div>';
	}
}

add_action( 'save_post_' . PCS_PRODUIT_CPT, function ( int $post_id ): void {
	if ( ! isset( $_POST['pcs_produit_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pcs_produit_nonce'] ) ), 'pcs_produit_save' ) ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	update_post_meta( $post_id, '_pcs_prod_featured', isset( $_POST['pcs_prod_featured'] ) ? '1' : '' );
	foreach ( array_keys( pcs_produit_fields() ) as $key ) {
		$raw = (string) wp_unslash( $_POST[ 'pcs_prod_' . $key ] ?? '' );
		$val = ( 'url' === $key ) ? esc_url_raw( $raw ) : sanitize_textarea_field( $raw );
		update_post_meta( $post_id, '_pcs_prod_' . $key, $val );
	}
} );

// ─── Helpers de parsing ─────────────────────────────────────────────────────────

/** Lignes non vides d'un meta textarea. */
function pcs_prod_lines( int $id, string $key ): array {
	$v = (string) get_post_meta( $id, '_pcs_prod_' . $key, true );
	return array_values( array_filter( array_map( 'trim', explode( "\n", $v ) ) ) );
}
/** Paires [label, valeur] séparées par le 1er séparateur ($sep). */
function pcs_prod_pairs( int $id, string $key, string $sep = ':' ): array {
	$out = [];
	foreach ( pcs_prod_lines( $id, $key ) as $line ) {
		$p = explode( $sep, $line, 2 );
		$out[] = [ trim( $p[0] ), trim( $p[1] ?? '' ) ];
	}
	return $out;
}
function pcs_prod_meta( int $id, string $key ): string {
	return (string) get_post_meta( $id, '_pcs_prod_' . $key, true );
}

// ─── Section home : Sélection déco tendance ─────────────────────────────────────

function pcs_render_product_selection( int $limit = 4 ): string {
	$q = new WP_Query( [
		'post_type'      => PCS_PRODUIT_CPT,
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'meta_key'       => '_pcs_prod_featured',
		'meta_value'     => '1',
		'no_found_rows'  => true,
	] );
	if ( ! $q->have_posts() ) {
		// fallback : derniers produits publiés
		$q = new WP_Query( [ 'post_type' => PCS_PRODUIT_CPT, 'post_status' => 'publish', 'posts_per_page' => $limit, 'no_found_rows' => true ] );
	}
	if ( ! $q->have_posts() ) { return ''; }

	ob_start(); ?>
	<section class="pcs-home__produits">
		<header class="pcs-home__section-header">
			<h2 class="pcs-home__section-title"><?php esc_html_e( 'Sélection déco tendance', 'pluscestsimple' ); ?></h2>
		</header>
		<div class="pcs-prod-selection">
			<?php while ( $q->have_posts() ) : $q->the_post(); $pid = get_the_ID(); ?>
			<article class="pcs-prod-card">
				<a href="<?php the_permalink(); ?>" class="pcs-prod-card__link">
					<div class="pcs-prod-card__media">
						<?php echo has_post_thumbnail( $pid ) ? get_the_post_thumbnail( $pid, 'pcs-card', [ 'class' => 'pcs-prod-card__img', 'loading' => 'lazy' ] ) : ''; ?>
						<?php $note = pcs_prod_meta( $pid, 'note' ); if ( $note ) : ?>
						<span class="pcs-prod-card__note"><?php echo esc_html( $note ); ?><small>/10</small></span>
						<?php endif; ?>
					</div>
					<div class="pcs-prod-card__body">
						<?php $m = pcs_prod_meta( $pid, 'marque' ); if ( $m ) : ?><span class="pcs-eyebrow"><?php echo esc_html( $m ); ?></span><?php endif; ?>
						<h3 class="pcs-prod-card__title"><?php the_title(); ?></h3>
						<?php $px = pcs_prod_meta( $pid, 'prix' ); if ( $px ) : ?><span class="pcs-prod-card__price"><?php echo esc_html( $px ); ?></span><?php endif; ?>
					</div>
				</a>
			</article>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

// ─── Phase 3 : shortcodes de sélection éditoriale (style AD Magazine) ───────────

/** Carte produit compacte (insérable dans un article). */
function pcs_render_product_inline( int $id ): string {
	if ( get_post_type( $id ) !== PCS_PRODUIT_CPT ) { return ''; }
	$url    = pcs_prod_meta( $id, 'url' );
	$marque = pcs_prod_meta( $id, 'marque' );
	$prix   = pcs_prod_meta( $id, 'prix' );
	$perm   = get_permalink( $id );
	ob_start(); ?>
	<div class="pcs-prod-inline">
		<a class="pcs-prod-inline__media" href="<?php echo esc_url( $perm ); ?>">
			<?php echo has_post_thumbnail( $id ) ? get_the_post_thumbnail( $id, 'pcs-card', [ 'class' => 'pcs-prod-inline__img', 'loading' => 'lazy' ] ) : ''; ?>
		</a>
		<div class="pcs-prod-inline__body">
			<?php if ( $marque ) : ?><span class="pcs-eyebrow"><?php echo esc_html( $marque ); ?></span><?php endif; ?>
			<h3 class="pcs-prod-inline__name"><a href="<?php echo esc_url( $perm ); ?>"><?php echo esc_html( get_the_title( $id ) ); ?></a></h3>
			<?php if ( $url ) : ?>
			<a class="pcs-prod-inline__cta" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="sponsored nofollow noopener noreferrer">
				<?php if ( $prix ) : ?><strong><?php echo esc_html( $prix ); ?></strong><?php endif; ?>
				<?php if ( $marque ) : ?><span><?php echo esc_html( $marque ); ?></span><?php endif; ?>
			</a>
			<?php endif; ?>
		</div>
	</div>
	<?php
	return (string) ob_get_clean();
}

/** [pcs_produit id="12"] ou [pcs_produit slug="canape-nils"] → 1 carte. */
add_shortcode( 'pcs_produit', function ( $atts ) {
	$atts = shortcode_atts( [ 'id' => 0, 'slug' => '' ], $atts, 'pcs_produit' );
	$id   = (int) $atts['id'];
	if ( ! $id && $atts['slug'] ) {
		$p  = get_posts( [ 'name' => sanitize_title( $atts['slug'] ), 'post_type' => PCS_PRODUIT_CPT, 'numberposts' => 1, 'fields' => 'ids', 'post_status' => 'publish' ] );
		$id = $p ? (int) $p[0] : 0;
	}
	return $id ? pcs_render_product_inline( $id ) : '';
} );

/** [pcs_produits ids="12,15,21"] → grille de cartes. */
add_shortcode( 'pcs_produits', function ( $atts ) {
	$atts = shortcode_atts( [ 'ids' => '' ], $atts, 'pcs_produits' );
	$ids  = array_filter( array_map( 'intval', explode( ',', (string) $atts['ids'] ) ) );
	if ( ! $ids ) { return ''; }
	$out = '<div class="pcs-prod-inline-grid">';
	foreach ( $ids as $id ) { $out .= pcs_render_product_inline( $id ); }
	return $out . '</div>';
} );

/** [pcs_disclosure] → encadré transparence affiliation réutilisable. */
add_shortcode( 'pcs_disclosure', function () {
	return '<p class="pcs-disclosure">' . esc_html__( "Tous les produits de cet article sont sélectionnés indépendamment par notre rédaction. Lorsque vous achetez via nos liens, nous pouvons percevoir une commission d'affiliation, sans surcoût pour vous.", 'pluscestsimple' ) . '</p>';
} );
