<?php
/**
 * Template pour les archives de catégorie.
 *
 * Stratégie D1 : les 4 catégories principales (decoration, travaux, immobilier, divers)
 * ont une page Gutenberg dédiée avec le slug visible (/decoration/, etc.) et un slug
 * interne de catégorie suffixé (decoration-cat, etc.). Le module inc/category-base.php
 * gère la redirection 301 de l'archive vers la page. Ce template sert :
 * - de fallback pour toute catégorie qui n'a pas encore de page dédiée
 * - de rendu si la redirection 301 n'a pas été configurée
 *
 * @package pluscestsimple
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$pcs_term     = get_queried_object();
$pcs_term_url = $pcs_term ? get_term_link( $pcs_term ) : '';
?>

<div class="pcs-container pcs-archive pcs-category">

	<header class="pcs-archive__header">
		<?php
		if ( function_exists( 'pcs_breadcrumbs' ) ) {
			pcs_breadcrumbs();
		}
		?>
		<p class="pcs-archive__eyebrow"><?php esc_html_e( 'Catégorie', 'pluscestsimple' ); ?></p>
		<h1 class="pcs-archive__title"><?php single_cat_title(); ?></h1>
		<?php
		$pcs_desc = category_description();
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
