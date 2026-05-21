<?php
/**
 * Template fallback générique. Utilisé pour blog index si page d'accueil n'est pas
 * configurée comme statique, et comme dernier recours dans la hiérarchie de templates.
 *
 * @package pluscestsimple
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="pcs-container pcs-archive">

	<?php if ( have_posts() ) : ?>

		<header class="pcs-archive__header">
			<h1 class="pcs-archive__title">
				<?php
				if ( is_home() && ! is_front_page() ) {
					single_post_title();
				} else {
					esc_html_e( 'Articles récents', 'pluscestsimple' );
				}
				?>
			</h1>
		</header>

		<div class="pcs-card-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content/card-article' );
			endwhile;
			?>
		</div>

		<?php
		the_posts_pagination(
			[
				'mid_size'  => 2,
				'prev_text' => __( '« Précédent', 'pluscestsimple' ),
				'next_text' => __( 'Suivant »', 'pluscestsimple' ),
			]
		);
		?>

	<?php else : ?>

		<?php get_template_part( 'template-parts/content/none' ); ?>

	<?php endif; ?>

</div>

<?php
get_footer();
