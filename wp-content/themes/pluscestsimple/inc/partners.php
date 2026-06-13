<?php
/**
 * Partenaires — CPT éditable + rendu dynamique de la section "Nos partenaires".
 *
 * Remplace les 6 cartes statiques du pattern section-partners par des données
 * gérées en back-office (menu "Partenaires"). La section apparaît en bas des
 * pages catégories / piliers via le shortcode [pcs_partners] (appelé par le
 * pattern). Chaque partenaire peut être global (tous) ou limité à un pilier.
 *
 * @package pluscestsimple
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

const PCS_PARTNER_CPT = 'pcs_partner';

// ─── CPT ───────────────────────────────────────────────────────────────────────

add_action( 'init', function () {
	register_post_type( PCS_PARTNER_CPT, [
		'labels' => [
			'name'               => __( 'Partenaires', 'pluscestsimple' ),
			'singular_name'      => __( 'Partenaire', 'pluscestsimple' ),
			'menu_name'          => __( 'Partenaires', 'pluscestsimple' ),
			'add_new_item'       => __( 'Ajouter un partenaire', 'pluscestsimple' ),
			'edit_item'          => __( 'Modifier le partenaire', 'pluscestsimple' ),
			'all_items'          => __( 'Tous les partenaires', 'pluscestsimple' ),
			'not_found'          => __( 'Aucun partenaire', 'pluscestsimple' ),
		],
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 25,
		'menu_icon'           => 'dashicons-awards',
		'supports'            => [ 'title', 'editor', 'thumbnail', 'page-attributes' ], // title=nom, editor=pitch, thumbnail=logo, menu_order=tri
		'has_archive'         => false,
		'rewrite'             => false,
		'exclude_from_search' => true,
	] );
} );

// ─── Meta box (URL, badge, pilier) ─────────────────────────────────────────────

add_action( 'add_meta_boxes_' . PCS_PARTNER_CPT, function () {
	add_meta_box( 'pcs_partner_meta', __( 'Détails partenaire', 'pluscestsimple' ), 'pcs_partner_meta_box', PCS_PARTNER_CPT, 'side', 'high' );
} );

function pcs_partner_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'pcs_partner_save', 'pcs_partner_nonce' );
	$url    = (string) get_post_meta( $post->ID, '_pcs_partner_url', true );
	$badge  = (string) get_post_meta( $post->ID, '_pcs_partner_badge', true );
	$pilier = (string) get_post_meta( $post->ID, '_pcs_partner_pilier', true );
	$piliers = function_exists( 'pcs_content_structure' ) ? pcs_content_structure() : [];
	?>
	<p>
		<label for="pcs_partner_url"><strong><?php esc_html_e( 'Lien (affilié)', 'pluscestsimple' ); ?></strong></label><br>
		<input type="url" id="pcs_partner_url" name="pcs_partner_url" value="<?php echo esc_attr( $url ); ?>" class="widefat" placeholder="https://…" />
	</p>
	<p>
		<label for="pcs_partner_badge"><strong><?php esc_html_e( 'Badge', 'pluscestsimple' ); ?></strong></label><br>
		<input type="text" id="pcs_partner_badge" name="pcs_partner_badge" value="<?php echo esc_attr( $badge ); ?>" class="widefat" placeholder="Partenaire" />
	</p>
	<p>
		<label for="pcs_partner_pilier"><strong><?php esc_html_e( 'Afficher sur', 'pluscestsimple' ); ?></strong></label><br>
		<select id="pcs_partner_pilier" name="pcs_partner_pilier" class="widefat">
			<option value=""><?php esc_html_e( 'Toutes les catégories', 'pluscestsimple' ); ?></option>
			<?php foreach ( $piliers as $slug => $data ) : ?>
				<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $pilier, $slug ); ?>><?php echo esc_html( $data['label'] ?? $slug ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p class="description"><?php esc_html_e( 'Nom = titre · Pitch = contenu · Logo = image mise en avant · Ordre = champ "Ordre" (Attributs).', 'pluscestsimple' ); ?></p>
	<?php
}

add_action( 'save_post_' . PCS_PARTNER_CPT, function ( int $post_id ): void {
	if ( ! isset( $_POST['pcs_partner_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pcs_partner_nonce'] ) ), 'pcs_partner_save' ) ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	update_post_meta( $post_id, '_pcs_partner_url', esc_url_raw( (string) ( $_POST['pcs_partner_url'] ?? '' ) ) );
	update_post_meta( $post_id, '_pcs_partner_badge', sanitize_text_field( (string) ( $_POST['pcs_partner_badge'] ?? '' ) ) );
	update_post_meta( $post_id, '_pcs_partner_pilier', sanitize_key( (string) ( $_POST['pcs_partner_pilier'] ?? '' ) ) );
} );

// ─── Détection du pilier courant ───────────────────────────────────────────────

function pcs_current_pilier_slug(): string {
	if ( ! function_exists( 'pcs_content_structure' ) ) { return ''; }
	$obj = get_queried_object();
	if ( ! $obj instanceof WP_Post ) { return ''; }
	$structure = pcs_content_structure();
	if ( isset( $structure[ $obj->post_name ] ) ) {
		return $obj->post_name; // page pilier
	}
	if ( $obj->post_parent ) {
		$parent = get_post( $obj->post_parent );
		if ( $parent instanceof WP_Post && isset( $structure[ $parent->post_name ] ) ) {
			return $parent->post_name; // sous-page
		}
	}
	return '';
}

// ─── Rendu de la section ───────────────────────────────────────────────────────

function pcs_render_partners( int $limit = 6 ): string {
	$pilier = pcs_current_pilier_slug();

	$args = [
		'post_type'      => PCS_PARTNER_CPT,
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'orderby'        => [ 'menu_order' => 'ASC', 'title' => 'ASC' ],
		'no_found_rows'  => true,
	];
	// Filtre par pilier : partenaire global ('') OU correspondant au pilier courant.
	if ( $pilier ) {
		$args['meta_query'] = [
			'relation' => 'OR',
			[ 'key' => '_pcs_partner_pilier', 'value' => '', 'compare' => '=' ],
			[ 'key' => '_pcs_partner_pilier', 'compare' => 'NOT EXISTS' ],
			[ 'key' => '_pcs_partner_pilier', 'value' => $pilier, 'compare' => '=' ],
		];
	}

	$q = new WP_Query( $args );
	if ( ! $q->have_posts() ) {
		return ''; // aucun partenaire → section masquée (pas de placeholders)
	}

	ob_start();
	?>
	<section class="wp-block-group alignwide pcs-section pcs-partners">
		<p class="pcs-eyebrow has-accent-color has-text-color">Notre sélection</p>
		<h2 class="wp-block-heading pcs-section__title">Nos partenaires</h2>
		<p class="pcs-section__lead">Sélectionnés pour la qualité, la transparence et le service client.</p>
		<div class="pcs-partners__grid">
			<?php while ( $q->have_posts() ) : $q->the_post();
				$pid   = get_the_ID();
				$url   = (string) get_post_meta( $pid, '_pcs_partner_url', true );
				$badge = (string) get_post_meta( $pid, '_pcs_partner_badge', true ) ?: 'Partenaire';
				$name  = get_the_title();
				$pitch = has_excerpt() ? get_the_excerpt() : wp_trim_words( wp_strip_all_tags( get_the_content() ), 28 );
				$logo  = has_post_thumbnail( $pid ) ? get_the_post_thumbnail( $pid, 'medium', [ 'class' => 'pcs-partner-card__logo-img', 'loading' => 'lazy' ] ) : '';
				?>
				<article class="pcs-partner-card">
					<span class="pcs-partner-card__badge"><?php echo esc_html( $badge ); ?></span>
					<div class="pcs-partner-card__logo"><?php echo $logo ?: esc_html( $name ); ?></div>
					<h3 class="pcs-partner-card__name"><?php echo esc_html( $name ); ?></h3>
					<?php if ( $pitch ) : ?><p class="pcs-partner-card__pitch"><?php echo esc_html( $pitch ); ?></p><?php endif; ?>
					<?php if ( $url ) : ?>
						<p class="pcs-partner-card__cta"><a href="<?php echo esc_url( $url ); ?>" class="pcs-link-partner" rel="sponsored nofollow noopener"><?php echo esc_html( sprintf( 'Voir %s', $name ) ); ?></a></p>
					<?php endif; ?>
				</article>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
		<p class="pcs-partners__charter">
			<a href="<?php echo esc_url( home_url( '/charte-partenaires/' ) ); ?>">Pourquoi ces partenaires ?</a>
			<a href="<?php echo esc_url( home_url( '/travailler-avec-nous/' ) ); ?>">Devenir partenaire</a>
		</p>
	</section>
	<?php
	return (string) ob_get_clean();
}

add_shortcode( 'pcs_partners', function ( $atts ) {
	$atts = shortcode_atts( [ 'limit' => 6 ], $atts, 'pcs_partners' );
	return pcs_render_partners( max( 1, (int) $atts['limit'] ) );
} );

// ─── Migration : remplace les cartes partenaires figées par le shortcode ────────
//
// Les pages piliers ont été seedées en inline-ant le pattern category-<pilier>,
// qui contenait 6 cartes <article class="pcs-partner-card"> en HTML statique.
// On remplace ce bloc figé par <!-- wp:shortcode -->[pcs_partners]<!-- /wp:shortcode -->
// pour que la section devienne pilotée par le CPT Partenaires. Idempotent.

add_action( 'init', function () {
	if ( get_option( 'pcs_partners_migrated' ) === PCS_VERSION ) { return; }

	$pages = get_posts( [
		'post_type'      => [ 'page' ],
		'post_status'    => [ 'publish', 'draft', 'pending', 'private' ],
		'posts_per_page' => -1,
		'fields'         => 'ids',
		's'              => 'pcs-partner-card', // pré-filtre large
	] );

	$shortcode_block = "<!-- wp:shortcode -->\n[pcs_partners]\n<!-- /wp:shortcode -->";
	// Bloc group .pcs-partners (open) → premier <!-- /wp:group --> qui le ferme.
	// Pas de wp:group imbriqué dans la section partenaires → match non-greedy fiable.
	$pattern = '/<!-- wp:group \{[^\n]*pcs-partners[^\n]*\} -->.*?<!-- \/wp:group -->/s';

	foreach ( $pages as $pid ) {
		$content = (string) get_post_field( 'post_content', $pid );
		if ( strpos( $content, 'pcs-partner-card' ) === false ) { continue; }
		$new = preg_replace( $pattern, $shortcode_block, $content, 1 );
		if ( $new !== null && $new !== $content ) {
			wp_update_post( [ 'ID' => $pid, 'post_content' => $new ] );
		}
	}

	update_option( 'pcs_partners_migrated', PCS_VERSION );
}, 99 );

// ─── Seed d'exemples (une seule fois) ──────────────────────────────────────────

add_action( 'init', function () {
	if ( get_option( 'pcs_partners_seeded' ) ) { return; }

	$examples = [
		[ 'Marque exemple 1', 'Ce qu\'elle fait et pourquoi on la recommande. (À éditer dans Partenaires.)' ],
		[ 'Marque exemple 2', 'Pitch en 1-2 phrases. (À éditer.)' ],
		[ 'Marque exemple 3', 'Pitch en 1-2 phrases. (À éditer.)' ],
	];
	foreach ( $examples as $i => $ex ) {
		$id = wp_insert_post( [
			'post_type'    => PCS_PARTNER_CPT,
			'post_status'  => 'publish',
			'post_title'   => $ex[0],
			'post_content' => $ex[1],
			'menu_order'   => $i,
		] );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_pcs_partner_badge', 'Partenaire' );
			update_post_meta( $id, '_pcs_partner_url', '' );
		}
	}
	update_option( 'pcs_partners_seeded', '1' );
}, 100 );
