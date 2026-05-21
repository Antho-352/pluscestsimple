<?php
/**
 * Title: Section — Newsletter + lead magnet
 * Slug: pluscestsimple/section-newsletter
 * Categories: pcs-section
 * Description: Capture email avec promesse de guide PDF gratuit (12 erreurs à éviter). Formulaire HTML qui POST vers /pcs/v1/submit.
 * Inserter: yes
 * Keywords: newsletter, email, lead, magnet, pdf
 */
?>
<!-- wp:group {"tagName":"section","className":"pcs-section pcs-section--newsletter","metadata":{"name":"Section Newsletter"},"templateLock":"contentOnly","backgroundColor":"foreground","textColor":"background","layout":{"type":"constrained","contentSize":"760px"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|80","bottom":"var:preset|spacing|80"}}}} -->
<section class="wp-block-group pcs-section pcs-section--newsletter has-background-color has-foreground-background-color has-text-color has-background" style="padding-top:var(--wp--preset--spacing--80);padding-bottom:var(--wp--preset--spacing--80)">

	<!-- wp:paragraph {"className":"pcs-eyebrow","textColor":"accent-secondary","style":{"typography":{"textTransform":"uppercase","letterSpacing":"0.18em","fontSize":"0.75rem","fontWeight":"600"}}} -->
	<p class="pcs-eyebrow has-accent-secondary-color has-text-color" style="font-size:0.75rem;font-weight:600;letter-spacing:0.18em;text-transform:uppercase">Guide gratuit</p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"level":2,"style":{"typography":{"fontSize":"clamp(2rem, 5vw, 3rem)","fontWeight":"300","letterSpacing":"-0.02em","lineHeight":"1.1"}}} -->
	<h2 class="wp-block-heading" style="font-size:clamp(2rem, 5vw, 3rem);font-style:normal;font-weight:300;letter-spacing:-0.02em;line-height:1.1">Les 12 erreurs qui ruinent une rénovation</h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"style":{"typography":{"fontSize":"1.125rem","lineHeight":"1.6"}}} -->
	<p style="font-size:1.125rem;line-height:1.6">Un PDF de 30 pages, gratuit, par email. Ce que les pros ne disent pas, ce que les artisans rentables font, et ce qui fait exploser un budget.</p>
	<!-- /wp:paragraph -->

	<!-- wp:html -->
	<form class="pcs-newsletter-form" data-pcs-form="newsletter" data-form-type="newsletter" method="post" action="">
		<label for="pcs-newsletter-email" class="screen-reader-text">Adresse email</label>
		<input type="email" id="pcs-newsletter-email" name="email" required autocomplete="email" placeholder="vous@exemple.com">
		<input type="text" name="hp" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
		<button type="submit">Recevoir le guide</button>
		<p class="pcs-newsletter-form__legal">En vous inscrivant, vous acceptez de recevoir nos emails. Désabonnement en un clic. Aucune revente.</p>
	</form>
	<!-- /wp:html -->

</section>
<!-- /wp:group -->
