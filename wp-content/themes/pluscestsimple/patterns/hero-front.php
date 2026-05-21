<?php
if ( ! defined( "ABSPATH" ) ) { exit; }
/**
 * Title: Hero — Accueil (éditorial oversize)
 * Slug: arw-pulse/hero-front
 * Categories: arw-home
 * Block Types: core/group
 * Description: Hero d'accueil avec titre oversize + image signature.
 *
 * Site-specific content is injected via the `arw_pulse_hero_front_content`
 * filter. Defaults below are for citymoto. Override in your site mu-plugin.
 */

$content = apply_filters( 'arw_pulse_hero_front_content', [
	'eyebrow'       => '№01 · Le média',
	'date_text'     => 'Édition ' . date_i18n( 'Y' ),
	'title_html'    => 'Rouler en ville,<br><em>informés.</em>',
	'description'   => 'Tests, comparatifs et guides d\'achat pour les motards urbains. Pas de blabla commercial, que de l\'équipement testé sur route.',
	'cta_primary'   => [ 'label' => 'Lire les articles', 'url' => '/blog/' ],
	'cta_secondary' => [ 'label' => 'Voir l\'équipement ↓', 'url' => '#essentiel' ],
	'image_url'     => 'https://placehold.co/900x1100/0a0a0a/ffd600?text=REMPLACE-MOI',
	'image_alt'     => 'Visuel signature',
	'image_width'   => 900,
	'image_height'  => 1100,
	'meta_left'     => '12 produits testés · 48 articles · Mise à jour hebdo',
	'meta_right'    => 'Paris · Depuis 2025',
] );

$title_html = wp_kses( $content['title_html'], [ 'br' => [], 'em' => [], 'span' => [ 'class' => true ] ] );
?>
<!-- wp:group {"align":"full","className":"arw-hero-front","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull arw-hero-front">

	<!-- wp:group {"align":"wide","className":"arw-hero-front__top","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"center"}} -->
	<div class="wp-block-group alignwide arw-hero-front__top">
		<!-- wp:paragraph {"className":"arw-eyebrow"} -->
		<p class="arw-eyebrow"><?php echo esc_html( $content['eyebrow'] ); ?></p>
		<!-- /wp:paragraph -->
		<!-- wp:paragraph {"className":"arw-hero-front__date"} -->
		<p class="arw-hero-front__date"><?php echo esc_html( $content['date_text'] ); ?></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->

	<!-- wp:columns {"align":"wide","verticalAlignment":"center","className":"arw-hero-front__cols"} -->
	<div class="wp-block-columns alignwide arw-hero-front__cols are-vertically-aligned-center">

		<!-- wp:column {"verticalAlignment":"center","width":"58%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:58%">

			<!-- wp:html -->
			<h1 class="arw-hero-front__title"><?php echo $title_html; ?></h1>
			<!-- /wp:html -->

			<!-- wp:paragraph {"className":"arw-hero-front__desc"} -->
			<p class="arw-hero-front__desc"><?php echo esc_html( $content['description'] ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:buttons {"layout":{"type":"flex"},"className":"arw-hero-front__ctas"} -->
			<div class="wp-block-buttons arw-hero-front__ctas">
				<!-- wp:button {"className":"arw-btn-accent"} -->
				<div class="wp-block-button arw-btn-accent"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $content['cta_primary']['url'] ); ?>"><?php echo esc_html( $content['cta_primary']['label'] ); ?></a></div>
				<!-- /wp:button -->
				<!-- wp:button {"className":"is-style-outline"} -->
				<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $content['cta_secondary']['url'] ); ?>"><?php echo esc_html( $content['cta_secondary']['label'] ); ?></a></div>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->

		</div>
		<!-- /wp:column -->

		<!-- wp:column {"verticalAlignment":"center","width":"42%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:42%">

			<!-- wp:html -->
			<figure class="arw-hero-front__img">
				<img src="<?php echo esc_url( $content['image_url'] ); ?>" alt="<?php echo esc_attr( $content['image_alt'] ); ?>" width="<?php echo (int) $content['image_width']; ?>" height="<?php echo (int) $content['image_height']; ?>">
			</figure>
			<!-- /wp:html -->

		</div>
		<!-- /wp:column -->

	</div>
	<!-- /wp:columns -->

	<!-- wp:group {"align":"wide","className":"arw-hero-front__footer","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"center"}} -->
	<div class="wp-block-group alignwide arw-hero-front__footer">
		<!-- wp:paragraph {"className":"arw-meta"} -->
		<p class="arw-meta"><?php echo esc_html( $content['meta_left'] ); ?></p>
		<!-- /wp:paragraph -->
		<!-- wp:paragraph {"className":"arw-meta"} -->
		<p class="arw-meta"><?php echo esc_html( $content['meta_right'] ); ?></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->
