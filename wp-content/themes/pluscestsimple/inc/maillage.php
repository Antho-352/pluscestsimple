<?php
/**
 * Maillage interne du cocon — rendu serveur (aucune édition de page requise).
 *
 * P4 — Articles : section « À lire dans le même univers » = 3-5 articles du
 *       MÊME silo (sous-pilier de préférence, sinon pilier). Appelée depuis
 *       single.php. Remplace la dépendance à un plugin tiers de related-posts.
 *
 * P3 — Pages piliers / sous-piliers : liens descendants (pilier → sous-piliers)
 *       et remontants (sous-pilier → pilier + frères), injectés via `the_content`.
 *       Dynamique depuis pcs_content_structure() : pas d'ID en dur, pas de page
 *       à éditer, survit aux ré-uploads.
 *
 * @package pluscestsimple
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// ─── Catégorie « silo » principale d'un article ────────────────────────────────
//
// Préfère la sous-catégorie (parent != 0) ; sinon la catégorie pilier (racine).
function pcs_post_primary_cat( int $post_id ): ?WP_Term {
	$cats = get_the_category( $post_id );
	if ( empty( $cats ) ) { return null; }
	$sub = $root = $any = null;
	foreach ( $cats as $c ) {
		if ( ! $any ) { $any = $c; }
		if ( ! str_ends_with( (string) $c->slug, '-cat' ) ) { continue; }
		if ( (int) $c->parent !== 0 && ! $sub )  { $sub  = $c; }
		if ( (int) $c->parent === 0 && ! $root ) { $root = $c; }
	}
	return $sub ?: $root ?: $any;
}

// ─── P4 — Articles liés du même silo ───────────────────────────────────────────

function pcs_related_posts( int $limit = 4 ): string {
	$current = get_the_ID();
	$primary = pcs_post_primary_cat( $current );
	if ( ! $primary instanceof WP_Term ) { return ''; }

	$exclude = [ $current ];
	$posts   = [];

	// 1) Même catégorie (sous-pilier si l'article en a un, sinon pilier + enfants).
	$q1 = new WP_Query( [
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => $limit,
		'post__not_in'        => $exclude,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'orderby'             => 'date',
		'order'               => 'DESC',
		'cat'                 => $primary->term_id, // inclut les enfants pour un pilier
	] );
	foreach ( $q1->posts as $p ) { $posts[] = $p; $exclude[] = $p->ID; }
	wp_reset_postdata();

	// 2) Compléter depuis le pilier parent si l'on part d'un sous-pilier trop maigre.
	if ( count( $posts ) < $limit && (int) $primary->parent !== 0 ) {
		$q2 = new WP_Query( [
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => $limit - count( $posts ),
			'post__not_in'        => $exclude,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'orderby'             => 'date',
			'order'               => 'DESC',
			'cat'                 => (int) $primary->parent,
		] );
		foreach ( $q2->posts as $p ) { $posts[] = $p; }
		wp_reset_postdata();
	}

	if ( count( $posts ) < 2 ) { return ''; } // pas assez pour une section crédible

	ob_start();
	?>
	<section class="pcs-section pcs-section--related">
		<div class="pcs-container">
			<p class="pcs-eyebrow has-accent-color has-text-color"><?php esc_html_e( 'Aller plus loin', 'pluscestsimple' ); ?></p>
			<h2 class="pcs-section__title"><?php esc_html_e( 'À lire dans le même univers', 'pluscestsimple' ); ?></h2>
			<ul class="pcs-card-grid">
				<?php foreach ( $posts as $p ) :
					$pid = $p->ID;
					$pcat = pcs_post_primary_cat( $pid );
					?>
					<li class="pcs-card">
						<?php if ( has_post_thumbnail( $pid ) ) : ?>
							<a class="pcs-card__media" href="<?php echo esc_url( get_permalink( $pid ) ); ?>">
								<?php echo get_the_post_thumbnail( $pid, 'medium', [ 'class' => 'pcs-card__img', 'loading' => 'lazy', 'alt' => esc_attr( get_the_title( $pid ) ) ] ); ?>
							</a>
						<?php endif; ?>
						<div class="pcs-card__body">
							<?php if ( $pcat instanceof WP_Term ) : ?>
								<p class="pcs-card__eyebrow"><?php echo esc_html( $pcat->name ); ?></p>
							<?php endif; ?>
							<h3 class="pcs-card__title"><a href="<?php echo esc_url( get_permalink( $pid ) ); ?>"><?php echo esc_html( get_the_title( $pid ) ); ?></a></h3>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

// ─── P3 — Navigation de silo sur pages piliers / sous-piliers ──────────────────

add_filter( 'the_content', function ( $content ) {
	if ( is_admin() || ! is_main_query() || ! in_the_loop() || ! is_page() ) {
		return $content;
	}
	if ( ! function_exists( 'pcs_content_structure' ) ) { return $content; }

	$page = get_queried_object();
	if ( ! $page instanceof WP_Post ) { return $content; }
	$structure = pcs_content_structure();

	// Page pilier → liens descendants vers ses sous-piliers.
	if ( isset( $structure[ $page->post_name ] ) ) {
		return $content . pcs_silo_nav_pillar( $page->post_name, $structure[ $page->post_name ] );
	}

	// Sous-page → remontée vers le pilier + frères de silo.
	if ( $page->post_parent ) {
		$parent = get_post( $page->post_parent );
		if ( $parent instanceof WP_Post
			&& isset( $structure[ $parent->post_name ]['sub_cats'][ $page->post_name ] ) ) {
			return $content . pcs_silo_nav_subpage( $parent->post_name, $structure[ $parent->post_name ], $page->post_name );
		}
	}

	return $content;
}, 20 );

/**
 * Bloc « Explorer [Pilier] » : liens vers les sous-piliers (descendant).
 */
function pcs_silo_nav_pillar( string $root, array $data ): string {
	$subs = $data['sub_cats'] ?? [];
	if ( empty( $subs ) ) { return ''; }

	ob_start();
	?>
	<section class="pcs-section pcs-section--maillage">
		<div class="pcs-container">
			<p class="pcs-eyebrow has-accent-color has-text-color"><?php esc_html_e( 'Explorer ce thème', 'pluscestsimple' ); ?></p>
			<h2 class="pcs-section__title"><?php echo esc_html( sprintf( /* translators: %s = pilier */ __( 'Tout %s, par sujet', 'pluscestsimple' ), mb_strtolower( $data['label'] ) ) ); ?></h2>
			<ul class="pcs-link-list">
				<?php foreach ( $subs as $sub_slug => $sub_label ) :
					$url = home_url( '/' . $root . '/' . $sub_slug . '/' );
					?>
					<li><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $sub_label ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

/**
 * Bloc de sous-pilier : remontée vers le pilier + liens vers les frères.
 */
function pcs_silo_nav_subpage( string $root, array $data, string $current_sub ): string {
	$label  = $data['label'] ?? $root;
	$subs   = $data['sub_cats'] ?? [];

	ob_start();
	?>
	<section class="pcs-section pcs-section--maillage">
		<div class="pcs-container">
			<p class="pcs-eyebrow has-accent-color has-text-color"><?php esc_html_e( 'Dans le même thème', 'pluscestsimple' ); ?></p>
			<h2 class="pcs-section__title"><?php echo esc_html( sprintf( /* translators: %s = pilier */ __( 'Revenir à %s', 'pluscestsimple' ), $label ) ); ?></h2>
			<ul class="pcs-link-list">
				<li><a href="<?php echo esc_url( home_url( '/' . $root . '/' ) ); ?>"><strong><?php echo esc_html( sprintf( __( 'Tout %s', 'pluscestsimple' ), mb_strtolower( $label ) ) ); ?></strong></a></li>
				<?php foreach ( $subs as $sub_slug => $sub_label ) :
					if ( $sub_slug === $current_sub ) { continue; }
					?>
					<li><a href="<?php echo esc_url( home_url( '/' . $root . '/' . $sub_slug . '/' ) ); ?>"><?php echo esc_html( $sub_label ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}
