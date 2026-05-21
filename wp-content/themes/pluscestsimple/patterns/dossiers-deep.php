<?php
if ( ! defined( "ABSPATH" ) ) { exit; }
/**
 * Title: Dossiers — Deep reads
 * Slug: arw-pulse/dossiers-deep
 * Categories: arw-home, arw-magazine
 * Description: Section éditoriale "Dossiers" — 3 articles longs avec numérotation géante accent.
 */
?>
<!-- wp:group {"align":"full","className":"arw-dossiers","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull arw-dossiers">

	<!-- wp:group {"align":"wide","className":"arw-section-head","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"bottom"}} -->
	<div class="wp-block-group alignwide arw-section-head">
		<!-- wp:group {"layout":{"type":"default"}} -->
		<div class="wp-block-group">
			<!-- wp:paragraph {"className":"arw-section-num"} -->
			<p class="arw-section-num">03</p>
			<!-- /wp:paragraph -->
			<!-- wp:heading {"level":2,"className":"arw-section-title"} -->
			<h2 class="wp-block-heading arw-section-title">Dossiers</h2>
			<!-- /wp:heading -->
		</div>
		<!-- /wp:group -->
		<!-- wp:paragraph {"className":"arw-section-lead"} -->
		<p class="arw-section-lead">Deep reads. Pour ceux qui veulent vraiment comprendre avant d'acheter.</p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->

	<!-- wp:query {"queryId":0,"query":{"perPage":3,"postType":"post","order":"desc","orderBy":"date","inherit":false},"align":"wide"} -->
	<div class="wp-block-query alignwide arw-dossiers__list">
		<!-- wp:post-template {"layout":{"type":"grid","minimumColumnWidth":"300px"}} -->
			<!-- wp:group {"className":"arw-dossier","layout":{"type":"default"}} -->
			<div class="wp-block-group arw-dossier">

				<!-- wp:paragraph {"className":"arw-dossier__num"} -->
				<p class="arw-dossier__num">01</p>
				<!-- /wp:paragraph -->

				<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"4/3","sizeSlug":"large","className":"arw-dossier__img"} /-->

				<!-- wp:post-terms {"term":"category","className":"arw-dossier__cat"} /-->

				<!-- wp:post-title {"isLink":true,"level":3,"className":"arw-dossier__title"} /-->

				<!-- wp:post-excerpt {"moreText":"","excerptLength":22,"className":"arw-dossier__excerpt"} /-->

				<!-- wp:template-part {"slug":"post-meta","tagName":"div"} /-->

			</div>
			<!-- /wp:group -->
		<!-- /wp:post-template -->
	</div>
	<!-- /wp:query -->

</div>
<!-- /wp:group -->
