<?php
if ( ! defined( "ABSPATH" ) ) { exit; }
/**
 * Title: Manifesto
 * Slug: arw-pulse/manifesto
 * Categories: arw-home
 * Description: Bloc éditorial "Manifesto" avec typographie oversize et mots-clés soulignés accent.
 *
 * Site-specific content via `arw_pulse_manifesto_content` filter.
 * `text_html` allows a small whitelist: <mark>, <em>, <strong>, <br>.
 */

$content = apply_filters( 'arw_pulse_manifesto_content', [
	'eyebrow'    => '№04 · Manifesto',
	'text_html'  => 'Nous écrivons pour ceux qui <mark>roulent</mark> en ville. Ceux qui choisissent leur équipement comme on choisit un <mark>outil de travail</mark>. Sans compromis. Sans bullshit. Sans promesse commerciale que personne ne tient.',
	'sig_name'   => 'La rédaction',
	'sig_loc'    => 'Paris · Édition ' . date_i18n( 'Y' ),
] );

$text_html = wp_kses( $content['text_html'], [
	'mark'   => [],
	'em'     => [],
	'strong' => [],
	'br'     => [],
] );
?>
<!-- wp:group {"align":"full","className":"arw-manifesto","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull arw-manifesto">

	<!-- wp:group {"align":"wide","layout":{"type":"default"}} -->
	<div class="wp-block-group alignwide">

		<!-- wp:paragraph {"className":"arw-eyebrow arw-eyebrow--spaced"} -->
		<p class="arw-eyebrow arw-eyebrow--spaced"><?php echo esc_html( $content['eyebrow'] ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:html -->
		<p class="arw-manifesto__text"><?php echo $text_html; ?></p>
		<!-- /wp:html -->

		<!-- wp:group {"className":"arw-manifesto__sig","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"center"}} -->
		<div class="wp-block-group arw-manifesto__sig">
			<!-- wp:paragraph {"className":"arw-manifesto__sig-name"} -->
			<p class="arw-manifesto__sig-name"><?php echo esc_html( $content['sig_name'] ); ?></p>
			<!-- /wp:paragraph -->
			<!-- wp:paragraph {"className":"arw-manifesto__sig-loc"} -->
			<p class="arw-manifesto__sig-loc"><?php echo esc_html( $content['sig_loc'] ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->

	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->
