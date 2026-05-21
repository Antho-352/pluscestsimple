<?php
/**
 * Title: Section — Article à la une (1 article)
 * Slug: pluscestsimple/section-featured
 * Categories: pcs-section
 * Description: Met en avant le dernier article publié (Query Loop verrouillé, 1 résultat).
 * Inserter: yes
 * Keywords: article, une, mise en avant
 */
?>
<!-- wp:group {"tagName":"section","className":"pcs-section pcs-section--featured","metadata":{"name":"Article à la une"},"templateLock":"contentOnly","layout":{"type":"constrained","contentSize":"1180px"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70"}}}} -->
<section class="wp-block-group pcs-section pcs-section--featured" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--70)">

	<!-- wp:paragraph {"className":"pcs-eyebrow","textColor":"accent","style":{"typography":{"textTransform":"uppercase","letterSpacing":"0.18em","fontSize":"0.75rem","fontWeight":"600"}}} -->
	<p class="pcs-eyebrow has-accent-color has-text-color" style="font-size:0.75rem;font-weight:600;letter-spacing:0.18em;text-transform:uppercase">À la une</p>
	<!-- /wp:paragraph -->

	<!-- wp:query {"queryId":1,"query":{"perPage":1,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"only","inherit":false},"namespace":"pcs/featured","lock":{"move":false,"remove":true}} -->
	<div class="wp-block-query">
		<!-- wp:post-template {"lock":{"move":true,"remove":true}} -->

			<!-- wp:group {"className":"pcs-hero-featured__inner","layout":{"type":"default"},"style":{"spacing":{"blockGap":"var:preset|spacing|50"}}} -->
			<div class="wp-block-group pcs-hero-featured__inner">

				<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9","style":{"border":{"radius":"4px"}}} /-->

				<!-- wp:post-terms {"term":"category","className":"pcs-card__eyebrow"} /-->

				<!-- wp:post-title {"isLink":true,"level":3,"style":{"typography":{"fontSize":"clamp(1.5rem, 3vw, 2.5rem)","fontWeight":"400","lineHeight":"1.15"}}} /-->

				<!-- wp:post-excerpt {"moreText":"Lire la suite"} /-->

				<!-- wp:post-date {"format":"j F Y","className":"pcs-card__meta"} /-->

			</div>
			<!-- /wp:group -->

		<!-- /wp:post-template -->
	</div>
	<!-- /wp:query -->

</section>
<!-- /wp:group -->
