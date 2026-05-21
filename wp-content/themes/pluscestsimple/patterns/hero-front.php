<?php
/**
 * Title: Hero accueil
 * Slug: pluscestsimple/hero-front
 * Categories: pcs-hero
 * Description: Hero éditorial signature pour la page d'accueil. Titre oversize + intro + CTA.
 * Inserter: yes
 * Keywords: hero, accueil, titre
 */
?>
<!-- wp:group {"tagName":"section","align":"full","className":"pcs-section pcs-hero-front","metadata":{"name":"Hero accueil"},"templateLock":"contentOnly","layout":{"type":"constrained","contentSize":"1180px"}} -->
<section class="wp-block-group alignfull pcs-section pcs-hero-front">

	<!-- wp:paragraph {"className":"pcs-eyebrow","textColor":"accent"} -->
	<p class="pcs-eyebrow has-accent-color has-text-color">Le média maison · déco · travaux · immo</p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"level":1,"className":"pcs-hero-front__title"} -->
	<h1 class="wp-block-heading pcs-hero-front__title">Refaire sa maison <em>sans se tromper.</em></h1>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"className":"pcs-hero-front__desc"} -->
	<p class="pcs-hero-front__desc">Conseils déco, guides travaux, repères immobiliers. On vous épargne le scroll : ce qui marche, ce qui rate, et pourquoi.</p>
	<!-- /wp:paragraph -->

	<!-- wp:buttons {"layout":{"type":"flex","flexWrap":"wrap"}} -->
	<div class="wp-block-buttons">
		<!-- wp:button -->
		<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/compatibilimetre/">Tester ma rénovation</a></div>
		<!-- /wp:button -->

		<!-- wp:button {"className":"is-style-outline"} -->
		<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/le-carnet/">Lire le carnet</a></div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->

</section>
<!-- /wp:group -->
