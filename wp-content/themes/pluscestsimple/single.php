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
	$pcs_article_show_sidebar = function_exists( 'pcs_banner_slot_will_render' )
		? pcs_banner_slot_will_render( 'article-sidebar' )
		: true;
	?>
	<div class="pcs-container pcs-article-layout <?php echo $pcs_article_show_sidebar ? 'has-sidebar' : 'no-sidebar'; ?>">

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

		<?php if ( $pcs_article_show_sidebar ) : ?>
		<aside class="pcs-article__sidebar">
			<?php if ( function_exists( 'pcs_banner_render' ) ) : ?>
				<?php echo pcs_banner_render( 'article-sidebar' ); ?>
			<?php endif; ?>
		</aside>
		<?php endif; ?>

	</div><!-- /.pcs-article-layout -->

	<?php
	/* P4 — Articles liés du même silo (cocon), pleine largeur sous le layout. */
	if ( function_exists( 'pcs_related_posts' ) ) {
		echo pcs_related_posts( 4 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — markup interne échappé.
	}
	?>

	<?php /* Newsletter en bas de chaque article (avant commentaires / suggestions) */ ?>
	<section class="pcs-section pcs-section--newsletter pcs-article__newsletter">
		<div class="pcs-container">
			<p class="pcs-eyebrow"><?php esc_html_e( 'Newsletter', 'pluscestsimple' ); ?></p>
			<h2 class="pcs-section__title"><?php esc_html_e( "Vous avez aimé cet article ?", 'pluscestsimple' ); ?></h2>
			<p class="pcs-section__lead"><?php esc_html_e( "Recevez nos meilleurs conseils déco, travaux et jardin deux fois par semaine, directement par email. Pas de baratin, pas de revente.", 'pluscestsimple' ); ?></p>
			<form class="pcs-newsletter-form" data-pcs-form="newsletter" data-form-type="newsletter" method="post" action="">
				<label for="pcs-article-nl-email-<?php the_ID(); ?>" class="screen-reader-text"><?php esc_html_e( 'Adresse email', 'pluscestsimple' ); ?></label>
				<input type="email" id="pcs-article-nl-email-<?php the_ID(); ?>" name="email" required autocomplete="email" placeholder="<?php esc_attr_e( 'vous@exemple.com', 'pluscestsimple' ); ?>">
				<input type="text" name="hp" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
				<button type="submit"><?php esc_html_e( 'Recevoir la newsletter', 'pluscestsimple' ); ?></button>
				<label class="pcs-newsletter-form__consent">
					<input type="checkbox" name="consent" value="1" required>
					<span><?php esc_html_e( "J'accepte de recevoir la newsletter de Plus c'est simple. Désabonnement en 1 clic.", 'pluscestsimple' ); ?></span>
				</label>
			</form>
		</div>
	</section>

	<?php
	if ( comments_open() || get_comments_number() ) {
		echo '<div class="pcs-container pcs-comments">';
		comments_template();
		echo '</div>';
	}

endwhile;

get_footer();
