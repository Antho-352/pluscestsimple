<?php
/**
 * Title: Section — Sélection de la semaine (5 articles)
 * Slug: pluscestsimple/section-weekly
 * Categories: pcs-section
 * Description: Grille des 5 derniers articles. Query Loop verrouillé (l'utilisateur ne peut pas casser la structure).
 * Inserter: yes
 * Keywords: articles, semaine, grille, liste
 */
?>
<!-- wp:group {"tagName":"section","align":"full","className":"pcs-section pcs-section--weekly","layout":{"type":"constrained","contentSize":"1180px"}} -->
<section class="wp-block-group alignfull pcs-section pcs-section--weekly">

	<!-- wp:paragraph {"className":"pcs-eyebrow","textColor":"accent"} -->
	<p class="pcs-eyebrow has-accent-color has-text-color">Sélection de la semaine</p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"level":2,"className":"pcs-section__title"} -->
	<h2 class="wp-block-heading pcs-section__title">À lire cette semaine</h2>
	<!-- /wp:heading -->

	<!-- wp:query {"queryId":2,"query":{"perPage":5,"pages":0,"offset":1,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false},"namespace":"pcs/weekly","lock":{"move":false,"remove":true}} -->
	<div class="wp-block-query">
		<!-- wp:post-template {"className":"pcs-card-grid","lock":{"move":true,"remove":true}} -->

			<!-- wp:group {"tagName":"article","className":"pcs-card","layout":{"type":"default"}} -->
			<article class="wp-block-group pcs-card">

				<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"4/3","className":"pcs-card__media"} /-->

				<!-- wp:post-terms {"term":"category","className":"pcs-card__eyebrow"} /-->

				<!-- wp:post-title {"isLink":true,"level":3,"className":"pcs-card__title"} /-->

				<!-- wp:post-date {"format":"j F Y","className":"pcs-card__meta"} /-->

			</article>
			<!-- /wp:group -->

		<!-- /wp:post-template -->
	</div>
	<!-- /wp:query -->

</section>
<!-- /wp:group -->
