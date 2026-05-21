<?php
/**
 * Template générique pour les archives (auteur, date, taxonomies hors category).
 * Utilisé en fallback. category.php prend la priorité pour les catégories.
 *
 * @package pluscestsimple
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="pcs-container pcs-archive">

	<header class="pcs-archive__header">
		<?php
		if ( function_exists( 'pcs_breadcrumbs' ) ) {
			pcs_breadcrumbs();
		}
		?>
		<h1 class="pcs-archive__title"><?php the_archive_title(); ?></h1>
		<?php
		$pcs_desc = get_the_archive_description();
		if ( ! empty( $pcs_desc ) ) :
			?>
			<div class="pcs-archive__intro"><?php echo wp_kses_post( $pcs_desc ); ?></div>
		<?php endif; ?>
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
