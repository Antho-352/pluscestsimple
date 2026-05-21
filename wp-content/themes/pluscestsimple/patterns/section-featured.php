<?php
/**
 * Title: Section — Article à la une (1 article)
 * Slug: pluscestsimple/section-featured
 * Categories: pcs-section
 * Description: Met en avant un article. Par défaut : le plus récent. Pour épingler un article spécifique : Articles → cocher « Épingler cet article ». Pour changer le nombre, cliquer sur le bloc Query → Inspector → "Articles par page".
 * Inserter: yes
 * Keywords: article, une, mise en avant
 */
?>
<!-- wp:group {"tagName":"section","align":"full","className":"pcs-section pcs-section--featured","layout":{"type":"constrained","contentSize":"1180px"}} -->
<section class="wp-block-group alignfull pcs-section pcs-section--featured">

	<!-- wp:paragraph {"className":"pcs-eyebrow","textColor":"accent"} -->
	<p class="pcs-eyebrow has-accent-color has-text-color">À la une</p>
	<!-- /wp:paragraph -->

	<!-- wp:query {"queryId":1,"query":{"perPage":1,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"first","inherit":false},"namespace":"pcs/featured"} -->
	<div class="wp-block-query">
		<!-- wp:post-template {"lock":{"move":true,"remove":true}} -->

			<!-- wp:group {"className":"pcs-featured","layout":{"type":"default"}} -->
			<div class="wp-block-group pcs-featured">

				<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9"} /-->

				<!-- wp:post-terms {"term":"category","className":"pcs-card__eyebrow"} /-->

				<!-- wp:post-title {"isLink":true,"level":3,"className":"pcs-featured__title"} /-->

				<!-- wp:post-excerpt {"moreText":"Lire la suite"} /-->

				<!-- wp:post-date {"format":"j F Y","className":"pcs-card__meta"} /-->

			</div>
			<!-- /wp:group -->

		<!-- /wp:post-template -->
	</div>
	<!-- /wp:query -->

</section>
<!-- /wp:group -->
