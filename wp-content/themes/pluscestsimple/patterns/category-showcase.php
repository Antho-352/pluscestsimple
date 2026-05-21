<?php
if ( ! defined( "ABSPATH" ) ) { exit; }
/**
 * Title: Vitrine catégorie
 * Slug: arw-pulse/category-showcase
 * Categories: arw-home
 * Description: Mise en avant d'une catégorie avec ses 3 articles les plus récents.
 */
?>
<!-- wp:group {"align":"wide","style":{"spacing":{"margin":{"top":"var:preset|spacing|80","bottom":"var:preset|spacing|70"}}}} -->
<div class="wp-block-group alignwide" style="margin-top:var(--wp--preset--spacing--80);margin-bottom:var(--wp--preset--spacing--70)">

	<!-- wp:group {"layout":{"type":"flex","justifyContent":"space-between","verticalAlignment":"bottom"},"style":{"spacing":{"margin":{"bottom":"var:preset|spacing|50"}}}} -->
	<div class="wp-block-group" style="margin-bottom:var(--wp--preset--spacing--50)">
		<!-- wp:heading {"level":2,"style":{"typography":{"fontSize":"var:preset|font-size|xl"}}} -->
		<h2 class="wp-block-heading" style="font-size:var(--wp--preset--font-size--xl)">À lire aussi</h2>
		<!-- /wp:heading -->
		<!-- wp:paragraph -->
		<p><a href="/blog/" style="text-decoration:none">Tout voir →</a></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->

	<!-- wp:query {"queryId":0,"query":{"perPage":3,"postType":"post","order":"desc","orderBy":"date","inherit":false,"taxQuery":{"category":[]}}} -->
	<div class="wp-block-query">
		<!-- wp:post-template {"layout":{"type":"grid","minimumColumnWidth":"280px"}} -->
			<!-- wp:group {"className":"arw-card"} -->
			<div class="wp-block-group arw-card">
				<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9","style":{"border":{"radius":"var:custom|radius|md"}}} /-->
				<!-- wp:post-terms {"term":"category","className":"arw-card__eyebrow"} /-->
				<!-- wp:post-title {"isLink":true,"className":"arw-card__title"} /-->
			</div>
			<!-- /wp:group -->
		<!-- /wp:post-template -->
	</div>
	<!-- /wp:query -->

</div>
<!-- /wp:group -->
