<?php
/**
 * Title: Article pilier — Tendances (PDF lead-magnet)
 * Slug: pluscestsimple/article-pilier-tendances
 * Categories: pcs-page
 * Description: Structure d'un article pilier "Tendances 20XX" avec CTA de téléchargement du PDF (lead-magnet).
 * Inserter: yes
 * Keywords: tendances, pilier, pdf, lead-magnet
 */
?>
<!-- wp:group {"className":"pcs-article-pilier","metadata":{"name":"Article pilier Tendances"},"templateLock":"contentOnly","layout":{"type":"constrained"}} -->
<div class="wp-block-group pcs-article-pilier">

	<!-- wp:paragraph {"className":"pcs-eyebrow","textColor":"accent"} -->
	<p class="pcs-eyebrow has-accent-color has-text-color">Tendances 2026</p>
	<!-- /wp:paragraph -->

	<!-- wp:paragraph -->
	<p>Synthèse éditoriale (300-500 mots) : ce qui ressort de notre veille sur les salons et études du secteur cette année. À remplacer par le contenu rédigé.</p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"level":2} -->
	<h2 class="wp-block-heading">Les 5 tendances qui comptent</h2>
	<!-- /wp:heading -->

	<!-- wp:list -->
	<ul class="wp-block-list">
		<li><strong>Tendance 1</strong> — pitch en 1 phrase.</li>
		<li><strong>Tendance 2</strong> — pitch en 1 phrase.</li>
		<li><strong>Tendance 3</strong> — pitch en 1 phrase.</li>
		<li><strong>Tendance 4</strong> — pitch en 1 phrase.</li>
		<li><strong>Tendance 5</strong> — pitch en 1 phrase.</li>
	</ul>
	<!-- /wp:list -->

	<!-- wp:heading {"level":2} -->
	<h2 class="wp-block-heading">Sources</h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph -->
	<p>Maison&amp;Objet, fabricants cités, observatoires sectoriels — citer les sources avec liens. Vérifiable.</p>
	<!-- /wp:paragraph -->

	<!-- wp:group {"tagName":"aside","align":"wide","className":"pcs-section pcs-section--newsletter","metadata":{"name":"CTA téléchargement PDF"},"backgroundColor":"foreground","textColor":"background","layout":{"type":"constrained","contentSize":"760px"}} -->
	<aside class="wp-block-group alignwide pcs-section pcs-section--newsletter has-background-color has-foreground-background-color has-text-color has-background">

		<!-- wp:paragraph {"className":"pcs-eyebrow","textColor":"accent-secondary"} -->
		<p class="pcs-eyebrow has-accent-secondary-color has-text-color">Le rapport complet</p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"level":3,"className":"pcs-section__title"} -->
		<h3 class="wp-block-heading pcs-section__title">Recevoir le PDF Tendances 2026 (8 pages)</h3>
		<!-- /wp:heading -->

		<!-- wp:paragraph -->
		<p>Sources détaillées, analyses de marché, et 5 tendances qu'on a vu monter cette année. Gratuit, par email.</p>
		<!-- /wp:paragraph -->

		<!-- wp:html -->
		<form class="pcs-newsletter-form" data-pcs-form="newsletter" data-form-type="lead-tendances" method="post" action="">
			<label for="pcs-tendances-email" class="screen-reader-text">Adresse email</label>
			<input type="email" id="pcs-tendances-email" name="email" required autocomplete="email" placeholder="vous@exemple.com">
			<input type="text" name="hp" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
			<button type="submit">Recevoir le PDF</button>
			<label class="pcs-newsletter-form__consent">
				<input type="checkbox" name="consent" value="1" required>
				<span>J'accepte de recevoir le PDF et la lettre mensuelle de Plus c'est simple.</span>
			</label>
		</form>
		<!-- /wp:html -->

	</aside>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->
