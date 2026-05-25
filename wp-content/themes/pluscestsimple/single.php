<?php
/**
 * Template pour les articles uniques (post).
 *
 * Layout 2 colonnes : article (main, ~2/3) + sidebar pub (~1/3, sticky).
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
	<div class="pcs-container pcs-article-layout">

		<article id="post-<?php the_ID(); ?>" <?php post_class( 'pcs-article' ); ?>>

			<header class="pcs-article__header">
				<?php
				if ( function_exists( 'pcs_breadcrumbs' ) ) {
					pcs_breadcrumbs();
				}

				$pcs_cats = get_the_category();
				if ( ! empty( $pcs_cats ) ) :
					$pcs_cat = $pcs_cats[0];
					?>
					<p class="pcs-article__eyebrow">
						<a href="<?php echo esc_url( get_category_link( $pcs_cat ) ); ?>"><?php echo esc_html( $pcs_cat->name ); ?></a>
					</p>
				<?php endif; ?>

				<h1 class="pcs-article__title"><?php the_title(); ?></h1>

				<?php if ( has_excerpt() ) : ?>
					<p class="pcs-article__lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>

				<div class="pcs-article__meta">
					<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
						<?php echo esc_html( get_the_date() ); ?>
					</time>
					<?php
					$pcs_modified  = get_the_modified_date( 'U' );
					$pcs_published = get_the_date( 'U' );
					if ( $pcs_modified - $pcs_published > DAY_IN_SECONDS ) :
						?>
						<span class="pcs-article__sep" aria-hidden="true">·</span>
						<span><?php printf( esc_html__( 'mis à jour le %s', 'pluscestsimple' ), esc_html( get_the_modified_date() ) ); ?></span>
					<?php endif; ?>
					<?php if ( function_exists( 'pcs_reading_time' ) ) : ?>
						<span class="pcs-article__sep" aria-hidden="true">·</span>
						<span><?php echo esc_html( pcs_reading_time() ); ?></span>
					<?php endif; ?>
				</div>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="pcs-article__featured">
					<?php
					the_post_thumbnail(
						'pcs-hero',
						[
							'class'         => 'pcs-article__featured-img',
							'loading'       => 'eager',
							'fetchpriority' => 'high',
							'decoding'      => 'async',
						]
					);
					?>
				</figure>
			<?php endif; ?>

			<div class="pcs-content pcs-article__content">
				<?php the_content(); ?>
				<?php
				wp_link_pages(
					[
						'before'      => '<nav class="pcs-page-links" aria-label="' . esc_attr__( "Pages de l'article", 'pluscestsimple' ) . '">' . esc_html__( 'Pages :', 'pluscestsimple' ),
						'after'       => '</nav>',
						'link_before' => '<span class="pcs-page-links__num">',
						'link_after'  => '</span>',
					]
				);
				?>
			</div>

		</article>

		<aside class="pcs-article__sidebar">
			<?php if ( function_exists( 'pcs_banner_render' ) ) : ?>
				<?php echo pcs_banner_render( 'article-sidebar', true ); ?>
			<?php endif; ?>
		</aside>

	</div><!-- /.pcs-article-layout -->

	<?php
	if ( comments_open() || get_comments_number() ) {
		echo '<div class="pcs-container pcs-comments">';
		comments_template();
		echo '</div>';
	}

endwhile;

get_footer();
