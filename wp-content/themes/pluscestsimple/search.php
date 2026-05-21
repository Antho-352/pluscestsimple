<?php
/**
 * Template pour les résultats de recherche.
 *
 * @package pluscestsimple
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wp_query;

get_header();
?>

<div class="pcs-container pcs-archive pcs-search">

	<header class="pcs-archive__header">
		<h1 class="pcs-archive__title">
			<?php
			printf(
				/* translators: %s: termes de recherche */
				wp_kses_post( __( 'Résultats pour : <em>%s</em>', 'pluscestsimple' ) ),
				esc_html( get_search_query() )
			);
			?>
		</h1>
		<?php if ( have_posts() ) : ?>
			<p class="pcs-archive__intro">
				<?php
				$pcs_found = (int) $wp_query->found_posts;
				printf(
					esc_html(
						/* translators: %d: nombre d'articles */
						_n( '%d article trouvé.', '%d articles trouvés.', $pcs_found, 'pluscestsimple' )
					),
					$pcs_found
				);
				?>
			</p>
		<?php endif; ?>
		<div style="margin-top:1.5rem">
			<?php get_search_form(); ?>
		</div>
	</header>

	<?php if ( have_posts() ) : ?>

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
