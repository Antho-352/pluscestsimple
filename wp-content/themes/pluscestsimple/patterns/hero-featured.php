<?php
if ( ! defined( "ABSPATH" ) ) { exit; }
/**
 * Title: Hero — À la une (magazine)
 * Slug: arw-pulse/hero-featured
 * Categories: arw-magazine
 * Description: Article le plus récent mis en avant, format magazine 2 colonnes.
 */
?>
<!-- wp:query {"queryId":0,"query":{"perPage":1,"offset":0,"postType":"post","order":"desc","orderBy":"date","inherit":false},"align":"wide","className":"arw-hero-featured"} -->
<div class="wp-block-query alignwide arw-hero-featured">
	<!-- wp:post-template -->
		<!-- wp:group {"className":"arw-hero-featured__inner","layout":{"type":"default"}} -->
		<div class="wp-block-group arw-hero-featured__inner">

			<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9","sizeSlug":"large","className":"arw-hero-featured__img"} /-->

			<!-- wp:group {"className":"arw-hero-featured__body","layout":{"type":"default"}} -->
			<div class="wp-block-group arw-hero-featured__body">

				<!-- wp:paragraph {"className":"arw-eyebrow"} -->
				<p class="arw-eyebrow">À la une</p>
				<!-- /wp:paragraph -->

				<!-- wp:post-title {"isLink":true,"level":2,"className":"arw-hero-featured__title"} /-->

				<!-- wp:post-excerpt {"moreText":"Lire l'article","excerptLength":28,"className":"arw-hero-featured__excerpt"} /-->

				<!-- wp:template-part {"slug":"post-meta","tagName":"div"} /-->

			</div>
			<!-- /wp:group -->

		</div>
		<!-- /wp:group -->
	<!-- /wp:post-template -->
</div>
<!-- /wp:query -->
