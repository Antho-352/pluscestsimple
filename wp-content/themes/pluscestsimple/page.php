<?php
/**
 * Template par défaut pour les pages.
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
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'pcs-page' ); ?>>

		<?php if ( ! is_front_page() ) : ?>
			<header class="pcs-page__header pcs-container">
				<?php
				if ( function_exists( 'pcs_breadcrumbs' ) ) {
					pcs_breadcrumbs();
				}
				?>
				<h1 class="pcs-page__title"><?php the_title(); ?></h1>
			</header>
		<?php endif; ?>

		<div class="pcs-content pcs-page__content">
			<?php the_content(); ?>
			<?php
			wp_link_pages(
				[
					'before'      => '<nav class="pcs-page-links" aria-label="' . esc_attr__( 'Pages', 'pluscestsimple' ) . '">' . esc_html__( 'Pages :', 'pluscestsimple' ),
					'after'       => '</nav>',
					'link_before' => '<span class="pcs-page-links__num">',
					'link_after'  => '</span>',
				]
			);
			?>
		</div>

	</article>

	<?php
	if ( comments_open() || get_comments_number() ) {
		echo '<div class="pcs-container pcs-comments">';
		comments_template();
		echo '</div>';
	}

endwhile;

get_footer();
