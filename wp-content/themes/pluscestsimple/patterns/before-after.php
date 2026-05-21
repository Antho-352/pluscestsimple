<?php
/**
 * Title: Avant / Après
 * Slug: pluscestsimple/before-after
 * Categories: pcs-section
 * Description: Format signature anti-IA. 2 photos côte à côte (avant / après) avec texte structuré.
 * Inserter: yes
 * Keywords: avant, après, retour, expérience
 */
?>
<!-- wp:group {"tagName":"figure","align":"wide","className":"pcs-section pcs-before-after","metadata":{"name":"Avant / Après"},"templateLock":"contentOnly","layout":{"type":"constrained"}} -->
<figure class="wp-block-group alignwide pcs-section pcs-before-after">

	<!-- wp:paragraph {"className":"pcs-eyebrow","textColor":"accent"} -->
	<p class="pcs-eyebrow has-accent-color has-text-color">Retour d'expérience</p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"level":3,"className":"pcs-section__title"} -->
	<h3 class="wp-block-heading pcs-section__title">Titre du projet — à remplacer</h3>
	<!-- /wp:heading -->

	<!-- wp:columns {"className":"pcs-before-after__grid"} -->
	<div class="wp-block-columns pcs-before-after__grid">

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:image {"sizeSlug":"large","className":"pcs-before-after__img pcs-before-after__img--before"} -->
			<figure class="wp-block-image size-large pcs-before-after__img pcs-before-after__img--before"><img alt="Avant — décrire la scène" /><figcaption class="wp-element-caption">Avant — décrire la scène</figcaption></figure>
			<!-- /wp:image -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:image {"sizeSlug":"large","className":"pcs-before-after__img pcs-before-after__img--after"} -->
			<figure class="wp-block-image size-large pcs-before-after__img pcs-before-after__img--after"><img alt="Après — décrire la transformation" /><figcaption class="wp-element-caption">Après — décrire la transformation</figcaption></figure>
			<!-- /wp:image -->
		</div>
		<!-- /wp:column -->

	</div>
	<!-- /wp:columns -->

	<!-- wp:paragraph -->
	<p>Contexte (50-100 mots) : Qui, pour quel besoin, quel budget réel, combien de temps, quelles surprises.</p>
	<!-- /wp:paragraph -->

	<!-- wp:list {"className":"pcs-before-after__facts"} -->
	<ul class="wp-block-list pcs-before-after__facts">
		<li><strong>Budget :</strong> X €</li>
		<li><strong>Temps :</strong> X jours</li>
		<li><strong>Pièges évités :</strong> 1-2 points clés</li>
	</ul>
	<!-- /wp:list -->

</figure>
<!-- /wp:group -->
