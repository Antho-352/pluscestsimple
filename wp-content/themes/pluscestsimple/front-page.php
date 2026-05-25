<?php
/**
 * Front page — page d'accueil.
 *
 * Layout :
 *   1. Bannière publicitaire top (optionnelle, slot "homepage-top")
 *   2. Hero pleine largeur — dernier article tagué "pcs-hero"
 *   3. Grille 2×2 + sidebar pub — 4 articles tagués "pcs-selection" + slot "homepage-sidebar"
 *   4. Deux articles featured — tagués "pcs-une" (1er = 2/3, 2e = 1/3)
 *   5. Section newsletter
 *   6. Les + lus — jusqu'à 9 articles tagués "pcs-plus-lu"
 *   7. Sections par catégorie × 5 — automatiques (derniers articles par catégorie)
 *
 * Tags WP à utiliser dans l'éditeur d'article :
 *   - pcs-hero     → article héro (1 max, le plus récent tagué)
 *   - pcs-une      → 4 articles pour la grille 2×2 "À la une"
 *   - pcs-tendance → 2 articles featured "Tendance" (1er = grand 2/3, 2e = petit 1/3)
 *   - pcs-plus-lu  → jusqu'à 9 articles pour "Les + lus"
 *
 * @package pluscestsimple
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// ── Requêtes préchargées ─────────────────────────────────────────────────────

$pcs_hero_q = new WP_Query( [
	'tag'            => 'pcs-hero',
	'posts_per_page' => 1,
	'no_found_rows'  => true,
] );

$pcs_sel_q = new WP_Query( [
	'tag'            => 'pcs-une',
	'posts_per_page' => 4,
	'no_found_rows'  => true,
] );

$pcs_une_q = new WP_Query( [
	'tag'            => 'pcs-tendance',
	'posts_per_page' => 2,
	'no_found_rows'  => true,
] );

$pcs_lus_q = new WP_Query( [
	'tag'            => 'pcs-plus-lu',
	'posts_per_page' => 9,
	'no_found_rows'  => true,
] );

// Piliers catégories (sans Immobilier pour la homepage — les 5 sections nav)
$pcs_piliers_home = array_filter(
	pcs_content_structure(),
	fn( $k ) => in_array( $k, [ 'decoration', 'travaux', 'jardin', 'architecture', 'lifestyle' ], true ),
	ARRAY_FILTER_USE_KEY
);

?>
<div class="pcs-home">

<?php /* ═══════════════════════════════════════════════════════════════════════
 * 1. BANNIÈRE TOP (optionnelle)
 * ═══════════════════════════════════════════════════════════════════════════ */ ?>
<?php
if ( function_exists( 'pcs_banner_render' ) ) {
	$pcs_banner_top = pcs_banner_render( 'homepage-top' );
	if ( ! empty( trim( $pcs_banner_top ) ) ) {
		echo '<div class="pcs-home__banner-top">' . $pcs_banner_top . '</div>';
	}
}
?>

<?php /* ═══════════════════════════════════════════════════════════════════════
 * 2. HÉRO PLEINE LARGEUR
 * ═══════════════════════════════════════════════════════════════════════════ */ ?>
<?php if ( $pcs_hero_q->have_posts() ) : $pcs_hero_q->the_post(); ?>
<section class="pcs-home__hero">
	<a href="<?php the_permalink(); ?>" class="pcs-home__hero-link">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'full', [ 'class' => 'pcs-home__hero-img', 'alt' => esc_attr( get_the_title() ) ] ); ?>
		<?php endif; ?>
		<div class="pcs-home__hero-caption pcs-container">
			<?php
			$pcs_hero_cats = get_the_category();
			if ( $pcs_hero_cats ) :
			?>
			<span class="pcs-home__hero-eyebrow pcs-eyebrow"><?php echo esc_html( $pcs_hero_cats[0]->name ); ?></span>
			<?php endif; ?>
			<h1 class="pcs-home__hero-title"><?php the_title(); ?></h1>
		</div>
	</a>
</section>
<?php wp_reset_postdata(); endif; ?>

<div class="pcs-container pcs-home__body">

<?php /* ═══════════════════════════════════════════════════════════════════════
 * 3. GRILLE 2×2 + SIDEBAR PUB
 * ═══════════════════════════════════════════════════════════════════════════ */ ?>
<?php if ( $pcs_sel_q->have_posts() ) :
	$pcs_sel_show_sidebar = function_exists( 'pcs_banner_slot_will_render' )
		? pcs_banner_slot_will_render( 'homepage-sidebar' )
		: true;
?>
<section class="pcs-home__selection">

	<header class="pcs-home__section-header">
		<h2 class="pcs-home__section-title"><?php esc_html_e( 'À la une', 'pluscestsimple' ); ?></h2>
	</header>

	<div class="pcs-home__selection-layout <?php echo $pcs_sel_show_sidebar ? 'has-sidebar' : 'no-sidebar'; ?>">

		<div class="pcs-home__selection-grid">
			<?php while ( $pcs_sel_q->have_posts() ) : $pcs_sel_q->the_post(); ?>
			<article class="pcs-home__sel-card">
				<a href="<?php the_permalink(); ?>">
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="pcs-home__sel-img-wrap">
							<?php the_post_thumbnail( 'medium_large', [ 'class' => 'pcs-home__sel-img', 'alt' => esc_attr( get_the_title() ) ] ); ?>
						</div>
					<?php endif; ?>
					<div class="pcs-home__sel-body">
						<?php $pcs_sel_cats = get_the_category(); if ( $pcs_sel_cats ) : ?>
						<span class="pcs-eyebrow pcs-home__sel-cat"><?php echo esc_html( $pcs_sel_cats[0]->name ); ?></span>
						<?php endif; ?>
						<h3 class="pcs-home__sel-title"><?php the_title(); ?></h3>
					</div>
				</a>
			</article>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>

		<?php if ( $pcs_sel_show_sidebar ) : ?>
		<aside class="pcs-home__selection-sidebar">
			<?php if ( function_exists( 'pcs_banner_render' ) ) : ?>
				<?php echo pcs_banner_render( 'homepage-sidebar' ); ?>
			<?php endif; ?>
		</aside>
		<?php endif; ?>

	</div>

</section>
<?php endif; ?>

<?php /* ═══════════════════════════════════════════════════════════════════════
 * 4. DEUX ARTICLES FEATURED (2/3 + 1/3)
 * ═══════════════════════════════════════════════════════════════════════════ */ ?>
<?php
// Collecte les 2 posts avant de looper pour éviter les conflits de global $post
$pcs_une_posts = [];
while ( $pcs_une_q->have_posts() ) {
	$pcs_une_q->the_post();
	$pcs_une_posts[] = get_post();
}
wp_reset_postdata();
?>
<?php if ( ! empty( $pcs_une_posts ) ) : ?>
<section class="pcs-home__une">

	<header class="pcs-home__section-header">
		<h2 class="pcs-home__section-title"><?php esc_html_e( 'Tendance', 'pluscestsimple' ); ?></h2>
	</header>

	<div class="pcs-home__une-grid">
	<?php if ( isset( $pcs_une_posts[0] ) ) :
		$pcs_une_large = $pcs_une_posts[0];
		$GLOBALS['post'] = $pcs_une_large; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
		setup_postdata( $pcs_une_large );
	?>
	<article class="pcs-home__une-large">
		<a href="<?php the_permalink(); ?>">
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="pcs-home__une-img-wrap">
					<?php the_post_thumbnail( 'large', [ 'class' => 'pcs-home__une-img', 'alt' => esc_attr( get_the_title() ) ] ); ?>
				</div>
			<?php endif; ?>
			<?php $pcs_ularge_cats = get_the_category(); if ( $pcs_ularge_cats ) : ?>
			<span class="pcs-eyebrow pcs-home__une-cat"><?php echo esc_html( $pcs_ularge_cats[0]->name ); ?></span>
			<?php endif; ?>
			<h2 class="pcs-home__une-title"><?php the_title(); ?></h2>
			<p class="pcs-home__une-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18, '…' ) ); ?></p>
		</a>
	</article>
	<?php endif; ?>

	<?php if ( isset( $pcs_une_posts[1] ) ) :
		$pcs_une_small = $pcs_une_posts[1];
		$GLOBALS['post'] = $pcs_une_small; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
		setup_postdata( $pcs_une_small );
	?>
	<article class="pcs-home__une-small">
		<a href="<?php the_permalink(); ?>">
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="pcs-home__une-img-wrap">
					<?php the_post_thumbnail( 'medium_large', [ 'class' => 'pcs-home__une-img', 'alt' => esc_attr( get_the_title() ) ] ); ?>
				</div>
			<?php endif; ?>
			<?php $pcs_usmall_cats = get_the_category(); if ( $pcs_usmall_cats ) : ?>
			<span class="pcs-eyebrow pcs-home__une-cat"><?php echo esc_html( $pcs_usmall_cats[0]->name ); ?></span>
			<?php endif; ?>
			<h2 class="pcs-home__une-title"><?php the_title(); ?></h2>
		</a>
	</article>
	<?php endif; wp_reset_postdata(); ?>
	</div><!-- /.pcs-home__une-grid -->

</section>
<?php endif; ?>

</div><!-- /.pcs-container.pcs-home__body -->

<?php /* ═══════════════════════════════════════════════════════════════════════
 * 5. NEWSLETTER (pleine largeur, fond sombre)
 * ═══════════════════════════════════════════════════════════════════════════ */ ?>
<section class="pcs-section pcs-section--newsletter pcs-home__newsletter">
	<div class="pcs-container">
		<p class="pcs-eyebrow"><?php esc_html_e( 'Newsletter', 'pluscestsimple' ); ?></p>
		<h2 class="pcs-section__title"><?php esc_html_e( "Les dernières tendances, astuces travaux et déco dans votre boîte email deux fois par semaine", 'pluscestsimple' ); ?></h2>
		<p class="pcs-section__lead"><?php esc_html_e( "Notre newsletter vous informe des nouveautés et tendances pour la décoration de votre espace de vie. Quelques conseils et astuces pour embellir vos pièces à vivre, dépenser moins et éviter les erreurs les plus courantes.", 'pluscestsimple' ); ?></p>
		<form class="pcs-newsletter-form" data-pcs-form="newsletter" data-form-type="newsletter" method="post" action="">
			<label for="pcs-home-nl-email" class="screen-reader-text"><?php esc_html_e( 'Adresse email', 'pluscestsimple' ); ?></label>
			<input type="email" id="pcs-home-nl-email" name="email" required autocomplete="email" placeholder="<?php esc_attr_e( 'vous@exemple.com', 'pluscestsimple' ); ?>">
			<input type="text" name="hp" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
			<button type="submit"><?php esc_html_e( 'Recevoir la newsletter', 'pluscestsimple' ); ?></button>
			<label class="pcs-newsletter-form__consent">
				<input type="checkbox" name="consent" value="1" required>
				<span><?php esc_html_e( "J'accepte de recevoir la newsletter de Plus c'est simple. Désabonnement en 1 clic.", 'pluscestsimple' ); ?></span>
			</label>
		</form>
	</div>
</section>

<div class="pcs-container pcs-home__body">

<?php /* ═══════════════════════════════════════════════════════════════════════
 * 6. LES + LUS
 * ═══════════════════════════════════════════════════════════════════════════ */ ?>
<?php if ( $pcs_lus_q->have_posts() ) : ?>
<section class="pcs-home__plus-lus">

	<h2 class="pcs-home__plus-lus-title">
		<?php esc_html_e( 'Les ', 'pluscestsimple' ); ?>
		<span class="pcs-home__plus-lus-plus">+</span>
		<?php esc_html_e( 'lus', 'pluscestsimple' ); ?>
	</h2>

	<div class="pcs-home__plus-lus-grid">
		<?php $pcs_lus_i = 1; while ( $pcs_lus_q->have_posts() ) : $pcs_lus_q->the_post(); ?>
		<article class="pcs-home__plus-lus-item">
			<span class="pcs-home__plus-lus-num"><?php echo esc_html( $pcs_lus_i++ ); ?></span>
			<a href="<?php the_permalink(); ?>" class="pcs-home__plus-lus-link"><?php the_title(); ?></a>
		</article>
		<?php endwhile; wp_reset_postdata(); ?>
	</div>

</section>
<?php endif; ?>

<?php /* ═══════════════════════════════════════════════════════════════════════
 * 7. SECTIONS PAR CATÉGORIE
 * ═══════════════════════════════════════════════════════════════════════════ */ ?>
<?php foreach ( $pcs_piliers_home as $pcs_cat_slug => $pcs_cat_data ) :

	$pcs_term = get_term_by( 'slug', $pcs_cat_slug . '-cat', 'category' );
	if ( ! $pcs_term instanceof WP_Term ) {
		continue;
	}
	$pcs_term_id   = (int) $pcs_term->term_id;
	$pcs_term_link = get_term_link( $pcs_term );

	// Article principal (dernier publié)
	$pcs_cat_main_q = new WP_Query( [
		'cat'            => $pcs_term_id,
		'posts_per_page' => 1,
		'no_found_rows'  => true,
	] );

	// 4 articles suivants (offset 1)
	$pcs_cat_sub_q = new WP_Query( [
		'cat'            => $pcs_term_id,
		'posts_per_page' => 4,
		'offset'         => 1,
		'no_found_rows'  => true,
	] );

	$pcs_cat_slot         = 'cat-sidebar-' . $pcs_cat_slug;
	$pcs_cat_show_sidebar = function_exists( 'pcs_banner_slot_will_render' )
		? pcs_banner_slot_will_render( $pcs_cat_slot )
		: true;
?>
<section class="pcs-home__cat-section" data-cat="<?php echo esc_attr( $pcs_cat_slug ); ?>">

	<header class="pcs-home__cat-header">
		<h2 class="pcs-home__cat-name"><?php echo esc_html( $pcs_cat_data['label'] ); ?></h2>
		<?php if ( ! is_wp_error( $pcs_term_link ) ) : ?>
		<a href="<?php echo esc_url( get_permalink( get_page_by_path( $pcs_cat_slug ) ) ); ?>" class="pcs-home__cat-more">
			<?php esc_html_e( 'Voir tous les articles →', 'pluscestsimple' ); ?>
		</a>
		<?php endif; ?>
	</header>

	<div class="pcs-home__cat-layout <?php echo $pcs_cat_show_sidebar ? 'has-sidebar' : 'no-sidebar'; ?>">

		<div class="pcs-home__cat-main-col">

			<?php /* Article principal */ ?>
			<?php if ( $pcs_cat_main_q->have_posts() ) : $pcs_cat_main_q->the_post(); ?>
			<article class="pcs-home__cat-hero">
				<a href="<?php the_permalink(); ?>">
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="pcs-home__cat-hero-img-wrap">
							<?php the_post_thumbnail( 'large', [ 'class' => 'pcs-home__cat-hero-img', 'alt' => esc_attr( get_the_title() ) ] ); ?>
						</div>
					<?php endif; ?>
					<?php $pcs_ch_cats = get_the_category(); if ( $pcs_ch_cats ) : ?>
					<span class="pcs-eyebrow pcs-home__cat-hero-cat"><?php echo esc_html( $pcs_ch_cats[0]->name ); ?></span>
					<?php endif; ?>
					<h3 class="pcs-home__cat-hero-title"><?php the_title(); ?></h3>
				</a>
			</article>
			<?php wp_reset_postdata(); endif; ?>

			<?php /* Grille 2×2 */ ?>
			<?php if ( $pcs_cat_sub_q->have_posts() ) : ?>
			<div class="pcs-home__cat-grid">
				<?php while ( $pcs_cat_sub_q->have_posts() ) : $pcs_cat_sub_q->the_post(); ?>
				<article class="pcs-home__cat-card">
					<a href="<?php the_permalink(); ?>">
						<?php if ( has_post_thumbnail() ) : ?>
							<div class="pcs-home__cat-card-img-wrap">
								<?php the_post_thumbnail( 'medium', [ 'class' => 'pcs-home__cat-card-img', 'alt' => esc_attr( get_the_title() ) ] ); ?>
							</div>
						<?php endif; ?>
						<?php $pcs_cc_cats = get_the_category(); if ( $pcs_cc_cats ) : ?>
						<span class="pcs-eyebrow pcs-home__cat-card-cat"><?php echo esc_html( $pcs_cc_cats[0]->name ); ?></span>
						<?php endif; ?>
						<h4 class="pcs-home__cat-card-title"><?php the_title(); ?></h4>
					</a>
				</article>
				<?php endwhile; wp_reset_postdata(); ?>
			</div>
			<?php endif; ?>

		</div><!-- /.pcs-home__cat-main-col -->

		<?php if ( $pcs_cat_show_sidebar ) : ?>
		<aside class="pcs-home__cat-sidebar">
			<?php if ( function_exists( 'pcs_banner_render' ) ) : ?>
				<?php echo pcs_banner_render( $pcs_cat_slot ); ?>
			<?php endif; ?>
		</aside>
		<?php endif; ?>

	</div><!-- /.pcs-home__cat-layout -->

</section>
<?php endforeach; ?>

</div><!-- /.pcs-container.pcs-home__body -->

</div><!-- /.pcs-home -->

<?php get_footer();
