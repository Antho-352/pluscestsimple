<?php
/**
 * Partial : ligne de boutique (style liste / lien bleu).
 *
 * Args : $args['post_id'] (int)
 *
 * @package PCS_Directory
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$pid = isset( $args['post_id'] ) ? (int) $args['post_id'] : get_the_ID();
if ( ! $pid ) { return; }

$title       = get_the_title( $pid );
$url         = get_permalink( $pid );
$adresse     = get_post_meta( $pid, '_pcs_adresse',     true );
$cp          = get_post_meta( $pid, '_pcs_code_postal', true );
$website     = get_post_meta( $pid, '_pcs_website',     true );
$phone       = get_post_meta( $pid, '_pcs_phone',       true );
$is_enseigne = get_post_meta( $pid, '_pcs_is_enseigne', true );

$cats  = get_the_terms( $pid, 'pcs_cat' );
$cat   = ( is_array( $cats ) && $cats ) ? $cats[0]->name : '';

$villes = get_the_terms( $pid, 'pcs_ville' );
$ville  = ( is_array( $villes ) && $villes ) ? $villes[0]->name : '';

$meta_parts = array_filter( [ $adresse, trim( $cp . ' ' . $ville ) ] );
?>
<li class="pcs-list__item">
	<a class="pcs-list__link" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $title ); ?></a>
	<?php if ( $meta_parts ) : ?>
		<span class="pcs-list__meta"><?php echo esc_html( implode( ' · ', $meta_parts ) ); ?></span>
	<?php endif; ?>
	<span class="pcs-list__flags">
		<?php if ( $is_enseigne ) : ?><span class="pcs-flag pcs-flag--enseigne">enseigne</span><?php endif; ?>
		<?php if ( $cat ) : ?><span class="pcs-flag pcs-flag--cat"><?php echo esc_html( $cat ); ?></span><?php endif; ?>
		<?php if ( $website ) : ?><span class="pcs-flag pcs-flag--web">site web</span><?php endif; ?>
		<?php if ( $phone ) : ?><span class="pcs-flag pcs-flag--phone">tél.</span><?php endif; ?>
	</span>
</li>
