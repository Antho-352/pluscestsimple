<?php
/**
 * Partial : card boutique.
 *
 * Args : $args['post_id'] (int)
 *
 * @package PCS_Directory
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$pid = isset( $args['post_id'] ) ? (int) $args['post_id'] : get_the_ID();
if ( ! $pid ) { return; }

$title      = get_the_title( $pid );
$url        = get_permalink( $pid );
$adresse    = get_post_meta( $pid, '_pcs_adresse', true );
$cp         = get_post_meta( $pid, '_pcs_code_postal', true );
$website    = get_post_meta( $pid, '_pcs_website', true );
$phone      = get_post_meta( $pid, '_pcs_phone', true );
$is_enseigne = get_post_meta( $pid, '_pcs_is_enseigne', true );

$cats  = get_the_terms( $pid, 'pcs_cat' );
$types = get_the_terms( $pid, 'pcs_type' );
$cat   = ( is_array( $cats )  && $cats )  ? $cats[0]->name  : '';
$type  = ( is_array( $types ) && $types ) ? $types[0]->name : '';

$card_class = 'pcs-card';
if ( $is_enseigne ) { $card_class .= ' is-enseigne'; }
?>
<article class="<?php echo esc_attr( $card_class ); ?>">
	<a class="pcs-card__link" href="<?php echo esc_url( $url ); ?>">

		<div class="pcs-card__thumbnail">
			<?php if ( has_post_thumbnail( $pid ) ) : ?>
				<?php echo get_the_post_thumbnail( $pid, 'medium', [ 'loading' => 'lazy' ] ); ?>
			<?php else : ?>
				<div class="pcs-card__thumbnail-placeholder" aria-hidden="true"></div>
			<?php endif; ?>
		</div>

		<div class="pcs-card__body">
			<div class="pcs-card__tags">
				<?php if ( $is_enseigne ) : ?>
					<span class="pcs-badge pcs-badge--enseigne">Enseigne</span>
				<?php endif; ?>
				<?php if ( $cat ) : ?>
					<span class="pcs-badge pcs-badge--cat"><?php echo esc_html( $cat ); ?></span>
				<?php endif; ?>
				<?php if ( $type && $type !== 'Grande enseigne' ) : ?>
					<span class="pcs-badge pcs-badge--type"><?php echo esc_html( $type ); ?></span>
				<?php endif; ?>
				<?php if ( $website ) : ?>
					<span class="pcs-badge pcs-badge--web">Site web</span>
				<?php endif; ?>
				<?php if ( $phone ) : ?>
					<span class="pcs-badge pcs-badge--phone">Tél.</span>
				<?php endif; ?>
			</div>

			<h3 class="pcs-card__title"><?php echo esc_html( $title ); ?></h3>

			<?php if ( $adresse || $cp ) : ?>
				<p class="pcs-card__meta">
					<?php echo esc_html( trim( $adresse . ( $cp ? ' · ' . $cp : '' ) ) ); ?>
				</p>
			<?php endif; ?>
		</div>

	</a>
</article>
