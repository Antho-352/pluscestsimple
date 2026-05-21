<?php
/**
 * Template : archive du CPT pcs_etablissement.
 *
 * Grille filtrable (type/région/ville) + pagination + tri. Mode mobile = liste compacte.
 *
 * @package PCS_Directory
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();
?>

<main id="primary" class="site-main pcs-directory pcs-directory--archive">
	<div class="pcs-directory__container">

		<header class="pcs-directory__header">
			<?php
			the_archive_title( '<h1 class="pcs-directory__title">', '</h1>' );
			the_archive_description( '<div class="pcs-directory__intro">', '</div>' );
			?>
			<p class="pcs-directory__count">
				<?php
				global $wp_query;
				printf(
					/* translators: %s: nombre d'établissements. */
					esc_html( _n( '%s établissement', '%s établissements', (int) $wp_query->found_posts, 'pluscestsimple' ) ),
					esc_html( number_format_i18n( (int) $wp_query->found_posts ) )
				);
				?>
			</p>
		</header>

		<?php echo pcs_directory_render_filters(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

		<?php if ( have_posts() ) : ?>

			<div class="pcs-directory__grid">
				<?php while ( have_posts() ) :
					the_post();
					echo pcs_directory_render_card( (int) get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				endwhile; ?>
			</div>

			<nav class="pcs-directory__pagination" aria-label="<?php esc_attr_e( 'Pagination', 'pluscestsimple' ); ?>">
				<?php
				the_posts_pagination( [
					'mid_size'  => 2,
					'prev_text' => __( '« Précédent', 'pluscestsimple' ),
					'next_text' => __( 'Suivant »', 'pluscestsimple' ),
				] );
				?>
			</nav>

		<?php else : ?>

			<p class="pcs-directory__empty">
				<?php esc_html_e( 'Aucun établissement ne correspond à votre recherche.', 'pluscestsimple' ); ?>
			</p>

		<?php endif; ?>

	</div>
</main>

<?php get_footer(); ?>
