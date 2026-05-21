<?php
/**
 * Carte d'article utilisée dans les grilles (index, archive, search).
 *
 * @package pluscestsimple
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'pcs-card' ); ?>>

	<?php if ( has_post_thumbnail() ) : ?>
		<a class="pcs-card__media" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
			<?php
			the_post_thumbnail(
				'pcs-card',
				[
					'loading'       => 'lazy',
					'decoding'      => 'async',
					'class'         => 'pcs-card__img',
					'alt'           => the_title_attribute( [ 'echo' => false ] ),
				]
			);
			?>
		</a>
	<?php endif; ?>

	<div class="pcs-card__body">

		<?php
		$pcs_cats = get_the_category();
		if ( ! empty( $pcs_cats ) ) :
			$pcs_cat = $pcs_cats[0];
			?>
			<p class="pcs-card__eyebrow">
				<a href="<?php echo esc_url( get_category_link( $pcs_cat ) ); ?>"><?php echo esc_html( $pcs_cat->name ); ?></a>
			</p>
		<?php endif; ?>

		<h2 class="pcs-card__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h2>

		<?php if ( has_excerpt() ) : ?>
			<p class="pcs-card__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<?php endif; ?>

		<p class="pcs-card__meta">
			<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
			<?php if ( function_exists( 'pcs_reading_time' ) ) : ?>
				<span class="pcs-card__sep" aria-hidden="true">·</span>
				<span><?php echo esc_html( pcs_reading_time() ); ?></span>
			<?php endif; ?>
		</p>

	</div>
</article>
