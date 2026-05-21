<?php
/**
 * Template Name: Plan du site
 *
 * Affiche un plan du site auto-généré (pages + catégories + articles récents)
 * en complément du contenu Gutenberg éditorial éventuel.
 *
 * @package pluscestsimple
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div id="post-<?php the_ID(); ?>" <?php post_class( 'pcs-page pcs-sitemap-page' ); ?>>

		<header class="pcs-page__header pcs-container">
			<?php
			if ( function_exists( 'pcs_breadcrumbs' ) ) {
				pcs_breadcrumbs();
			}
			?>
			<h1 class="pcs-page__title"><?php the_title(); ?></h1>
		</header>

		<?php if ( has_blocks( get_the_content() ) || trim( get_the_content() ) !== '' ) : ?>
			<div class="pcs-content pcs-page__content">
				<?php the_content(); ?>
			</div>
		<?php endif; ?>

		<div class="pcs-sitemap pcs-container">

			<section class="pcs-sitemap__section">
				<h2 class="pcs-sitemap__title"><?php esc_html_e( 'Pages', 'pluscestsimple' ); ?></h2>
				<ul class="pcs-sitemap__list">
					<?php
					wp_list_pages(
						[
							'title_li'    => '',
							'exclude'     => get_the_ID(),
							'sort_column' => 'menu_order, post_title',
						]
					);
					?>
				</ul>
			</section>

			<section class="pcs-sitemap__section">
				<h2 class="pcs-sitemap__title"><?php esc_html_e( 'Catégories', 'pluscestsimple' ); ?></h2>
				<ul class="pcs-sitemap__list">
					<?php
					$pcs_cats = get_categories(
						[
							'hide_empty' => true,
							'orderby'    => 'name',
						]
					);
					foreach ( $pcs_cats as $pcs_cat ) :
						?>
						<li>
							<a href="<?php echo esc_url( get_category_link( $pcs_cat ) ); ?>"><?php echo esc_html( $pcs_cat->name ); ?></a>
							<span class="pcs-sitemap__count">(<?php echo (int) $pcs_cat->count; ?>)</span>
						</li>
						<?php
					endforeach;
					?>
				</ul>
			</section>

			<section class="pcs-sitemap__section">
				<h2 class="pcs-sitemap__title"><?php esc_html_e( 'Articles récents', 'pluscestsimple' ); ?></h2>
				<ul class="pcs-sitemap__list pcs-sitemap__list--posts">
					<?php
					$pcs_recent = get_posts(
						[
							'numberposts' => 60,
							'orderby'     => 'date',
							'order'       => 'DESC',
						]
					);
					foreach ( $pcs_recent as $pcs_post ) :
						?>
						<li>
							<a href="<?php echo esc_url( get_permalink( $pcs_post ) ); ?>"><?php echo esc_html( get_the_title( $pcs_post ) ); ?></a>
							<time datetime="<?php echo esc_attr( get_the_date( 'c', $pcs_post ) ); ?>" class="pcs-sitemap__date">
								<?php echo esc_html( get_the_date( '', $pcs_post ) ); ?>
							</time>
						</li>
						<?php
					endforeach;
					?>
				</ul>
			</section>

		</div>

	</div>
	<?php
endwhile;

get_footer();
