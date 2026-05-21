<?php
/**
 * Title: Section — Newsletter + lead magnet
 * Slug: pluscestsimple/section-newsletter
 * Categories: pcs-section
 * Description: Capture email avec promesse de guide PDF gratuit. Formulaire HTML qui POST vers /pcs/v1/submit.
 * Inserter: yes
 * Keywords: newsletter, email, lead, magnet, pdf
 */
?>
<!-- wp:group {"tagName":"section","align":"full","className":"pcs-section pcs-section--newsletter","metadata":{"name":"Section Newsletter"},"templateLock":"contentOnly","backgroundColor":"foreground","textColor":"background","layout":{"type":"constrained","contentSize":"760px"}} -->
<section class="wp-block-group alignfull pcs-section pcs-section--newsletter has-background-color has-foreground-background-color has-text-color has-background">

	<!-- wp:paragraph {"className":"pcs-eyebrow","textColor":"accent-secondary"} -->
	<p class="pcs-eyebrow has-accent-secondary-color has-text-color">Guide gratuit</p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"level":2,"className":"pcs-section__title"} -->
	<h2 class="wp-block-heading pcs-section__title">Les 12 erreurs qui ruinent une rénovation</h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"className":"pcs-section__lead"} -->
	<p class="pcs-section__lead">Un PDF de 30 pages, gratuit, par email. Ce que les pros ne disent pas, ce que les artisans rentables font, et ce qui fait exploser un budget.</p>
	<!-- /wp:paragraph -->

	<!-- wp:html -->
	<form class="pcs-newsletter-form" data-pcs-form="newsletter" data-form-type="newsletter" method="post" action="">
		<label for="pcs-newsletter-email" class="screen-reader-text">Adresse email</label>
		<input type="email" id="pcs-newsletter-email" name="email" required autocomplete="email" placeholder="vous@exemple.com">
		<input type="text" name="hp" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
		<button type="submit">Recevoir le guide</button>
		<label class="pcs-newsletter-form__consent">
			<input type="checkbox" name="consent" value="1" required>
			<span>J'accepte de recevoir le guide PDF et la lettre mensuelle de Plus c'est simple. Désabonnement en 1 clic. Aucune revente — <a href="/mentions-legales/">en savoir plus</a>.</span>
		</label>
	</form>
	<!-- /wp:html -->

</section>
<!-- /wp:group -->
