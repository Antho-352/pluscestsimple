<?php
/**
 * Home — page de listing des articles du blog.
 *
 * Utilisé quand une page "Articles" est désignée dans Réglages → Lecture
 * (mode "Une page statique" → "Page des articles"). Si l'accueil est dynamique,
 * c'est index.php qui prend la main.
 *
 * Reproduit la structure de index.php avec un titre et un fil d'Ariane.
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
		$pcs_posts_page_id = (int) get_option( 'page_for_posts' );
		?>
		<h1 class="pcs-archive__title">
			<?php
			if ( $pcs_posts_page_id ) {
				echo esc_html( get_the_title( $pcs_posts_page_id ) );
			} else {
				esc_html_e( 'Le carnet', 'pluscestsimple' );
			}
			?>
		</h1>
		<?php
		if ( $pcs_posts_page_id ) {
			$pcs_intro = get_post_field( 'post_excerpt', $pcs_posts_page_id );
			if ( ! empty( $pcs_intro ) ) {
				printf( '<p class="pcs-archive__intro">%s</p>', esc_html( $pcs_intro ) );
			}
		}
		?>
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
