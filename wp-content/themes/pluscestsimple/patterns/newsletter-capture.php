<?php
/**
 * Title: Capture newsletter (inline, compact)
 * Slug: pluscestsimple/newsletter-capture
 * Categories: pcs-section
 * Description: Version compacte du formulaire d'inscription newsletter, à insérer dans un article ou en fin de page. RGPD-compliant.
 * Inserter: yes
 * Keywords: newsletter, email, capture, inline
 */
?>
<!-- wp:group {"tagName":"aside","align":"wide","className":"pcs-section pcs-newsletter-inline","metadata":{"name":"Capture newsletter compacte"},"templateLock":"contentOnly","layout":{"type":"constrained"}} -->
<aside class="wp-block-group alignwide pcs-section pcs-newsletter-inline">

	<!-- wp:heading {"level":3,"className":"pcs-newsletter-inline__title"} -->
	<h3 class="wp-block-heading pcs-newsletter-inline__title">La lettre mensuelle, sans baratin.</h3>
	<!-- /wp:heading -->

	<!-- wp:paragraph -->
	<p>Un email par mois : ce qu'on a vérifié, ce qui mérite votre attention en travaux et déco, ce qu'on évite.</p>
	<!-- /wp:paragraph -->

	<!-- wp:html -->
	<form class="pcs-newsletter-form pcs-newsletter-form--inline" data-pcs-form="newsletter" data-form-type="newsletter" method="post" action="">
		<label for="pcs-nl-inline-email" class="screen-reader-text">Adresse email</label>
		<input type="email" id="pcs-nl-inline-email" name="email" required autocomplete="email" placeholder="vous@exemple.com">
		<input type="text" name="hp" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
		<button type="submit">S'inscrire</button>
		<label class="pcs-newsletter-form__consent">
			<input type="checkbox" name="consent" value="1" required>
			<span>J'accepte de recevoir la lettre mensuelle. Désabonnement en 1 clic.</span>
		</label>
	</form>
	<!-- /wp:html -->

</aside>
<!-- /wp:group -->
