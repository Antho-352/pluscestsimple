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
<!-- wp:group {"tagName":"section","className":"pcs-section pcs-section--weekly","metadata":{"name":"Sélection de la semaine"},"templateLock":"contentOnly","layout":{"type":"constrained","contentSize":"1180px"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70"}}}} -->
<section class="wp-block-group pcs-section pcs-section--weekly" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--70)">

	<!-- wp:paragraph {"className":"pcs-eyebrow","textColor":"accent","style":{"typography":{"textTransform":"uppercase","letterSpacing":"0.18em","fontSize":"0.75rem","fontWeight":"600"}}} -->
	<p class="pcs-eyebrow has-accent-color has-text-color" style="font-size:0.75rem;font-weight:600;letter-spacing:0.18em;text-transform:uppercase">Sélection de la semaine</p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"level":2,"style":{"typography":{"fontSize":"clamp(2rem, 5vw, 3.5rem)","fontWeight":"300","letterSpacing":"-0.02em","lineHeight":"1.05"}}} -->
	<h2 class="wp-block-heading" style="font-size:clamp(2rem, 5vw, 3.5rem);font-style:normal;font-weight:300;letter-spacing:-0.02em;line-height:1.05">À lire cette semaine</h2>
	<!-- /wp:heading -->

	<!-- wp:query {"queryId":2,"query":{"perPage":5,"pages":0,"offset":1,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"exclude","inherit":false},"namespace":"pcs/weekly","lock":{"move":false,"remove":true}} -->
	<div class="wp-block-query">
		<!-- wp:post-template {"className":"pcs-card-grid","lock":{"move":true,"remove":true},"layout":{"type":"grid","columnCount":3,"minimumColumnWidth":null}} -->

			<!-- wp:group {"tagName":"article","className":"pcs-card","layout":{"type":"default"}} -->
			<article class="wp-block-group pcs-card">

				<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"4/3","className":"pcs-card__media","style":{"border":{"radius":"4px"}}} /-->

				<!-- wp:post-terms {"term":"category","className":"pcs-card__eyebrow"} /-->

				<!-- wp:post-title {"isLink":true,"level":3,"className":"pcs-card__title","style":{"typography":{"fontSize":"1.5rem","fontWeight":"400","lineHeight":"1.2"}}} /-->

				<!-- wp:post-date {"format":"j F Y","className":"pcs-card__meta"} /-->

			</article>
			<!-- /wp:group -->

		<!-- /wp:post-template -->
	</div>
	<!-- /wp:query -->

</section>
<!-- /wp:group -->
