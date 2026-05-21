<?php
/**
 * Title: Page — Travailler avec nous
 * Slug: pluscestsimple/page-travailler
 * Categories: pcs-page
 * Description: Page de présentation des offres (sans tarifs) + formulaire de contact. À insérer une fois sur la page "Travailler avec nous".
 * Inserter: yes
 * Keywords: travailler, contact, offres, brief
 */
?>
<!-- wp:group {"className":"pcs-page-travailler","metadata":{"name":"Page Travailler"},"templateLock":"contentOnly","layout":{"type":"constrained"}} -->
<div class="wp-block-group pcs-page-travailler">

	<!-- wp:heading {"level":1} -->
	<h1 class="wp-block-heading">Travailler avec nous</h1>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"className":"pcs-archive__intro"} -->
	<p class="pcs-archive__intro">Marques, agences, distributeurs : Plus c'est simple s'associe à quelques partenaires sélectionnés pour des contenus utiles, vérifiés, à valeur réelle pour notre audience.</p>
	<!-- /wp:paragraph -->

	<!-- wp:group {"tagName":"section","className":"pcs-section pcs-offers","layout":{"type":"grid","columnCount":3,"minimumColumnWidth":null}} -->
	<section class="wp-block-group pcs-section pcs-offers">

		<!-- wp:group {"tagName":"article","className":"pcs-offer-card"} -->
		<article class="wp-block-group pcs-offer-card">
			<!-- wp:heading {"level":3} -->
			<h3 class="wp-block-heading">Contenu sponsorisé</h3>
			<!-- /wp:heading -->
			<!-- wp:paragraph -->
			<p>Article éditorial sur votre marque, votre produit ou votre méthode. Toujours mentionné « En partenariat ». Toujours utile.</p>
			<!-- /wp:paragraph -->
		</article>
		<!-- /wp:group -->

		<!-- wp:group {"tagName":"article","className":"pcs-offer-card"} -->
		<article class="wp-block-group pcs-offer-card">
			<!-- wp:heading {"level":3} -->
			<h3 class="wp-block-heading">Bannières</h3>
			<!-- /wp:heading -->
			<!-- wp:paragraph -->
			<p>Emplacements display sur l'accueil, les catégories ou les articles. Formats standards (970×250, 728×90, 300×250).</p>
			<!-- /wp:paragraph -->
		</article>
		<!-- /wp:group -->

		<!-- wp:group {"tagName":"article","className":"pcs-offer-card"} -->
		<article class="wp-block-group pcs-offer-card">
			<!-- wp:heading {"level":3} -->
			<h3 class="wp-block-heading">Présence dans l'annuaire</h3>
			<!-- /wp:heading -->
			<!-- wp:paragraph -->
			<p>Mise en avant dans notre annuaire national des magasins déco / maison. Visibilité ciblée par région.</p>
			<!-- /wp:paragraph -->
		</article>
		<!-- /wp:group -->

	</section>
	<!-- /wp:group -->

	<!-- wp:heading {"level":2} -->
	<h2 class="wp-block-heading">Nous contacter</h2>
	<!-- /wp:heading -->

	<!-- wp:html -->
	<form class="pcs-contact-form" data-pcs-form="press" data-form-type="press" method="post" action="">
		<label for="pcs-press-name">Votre nom *</label>
		<input type="text" id="pcs-press-name" name="name" required>

		<label for="pcs-press-email">Email *</label>
		<input type="email" id="pcs-press-email" name="email" required autocomplete="email">

		<label for="pcs-press-company">Entreprise / Marque</label>
		<input type="text" id="pcs-press-company" name="company">

		<label for="pcs-press-message">Votre projet (brief court) *</label>
		<textarea id="pcs-press-message" name="message" rows="5" required></textarea>

		<input type="text" name="hp" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">

		<button type="submit">Envoyer le brief</button>
		<p class="pcs-form__legal">Vos données sont utilisées uniquement pour vous répondre. Aucune revente.</p>
	</form>
	<!-- /wp:html -->

	<!-- wp:paragraph {"className":"pcs-page-travailler__email"} -->
	<p class="pcs-page-travailler__email">Ou directement par email : <a href="mailto:contact@pluscestsimple.com">contact@pluscestsimple.com</a></p>
	<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->
