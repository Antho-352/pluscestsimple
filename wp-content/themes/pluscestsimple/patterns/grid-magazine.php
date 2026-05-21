<?php
if ( ! defined( "ABSPATH" ) ) { exit; }
/**
 * Title: Grille magazine (1 gros + 4 petits)
 * Slug: arw-pulse/grid-magazine
 * Categories: arw-home, arw-magazine
 * Description: Grille asymétrique magazine — 1 article dominant à gauche + 4 en grille à droite.
 */
?>
<!-- wp:group {"align":"wide","className":"arw-grid-magazine","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide arw-grid-magazine">

	<!-- wp:heading {"level":2,"className":"arw-grid-magazine__title"} -->
	<h2 class="wp-block-heading arw-grid-magazine__title">La sélection de la semaine</h2>
	<!-- /wp:heading -->

	<!-- wp:columns -->
	<div class="wp-block-columns">

		<!-- wp:column {"width":"58%"} -->
		<div class="wp-block-column" style="flex-basis:58%">
			<!-- wp:query {"queryId":0,"query":{"perPage":1,"offset":1,"postType":"post","order":"desc","orderBy":"date","inherit":false}} -->
			<div class="wp-block-query">
				<!-- wp:post-template -->
					<!-- wp:group {"className":"arw-card arw-card--hero"} -->
					<div class="wp-block-group arw-card arw-card--hero">
						<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"4/3"} /-->
						<!-- wp:post-terms {"term":"category","className":"arw-card__eyebrow"} /-->
						<!-- wp:post-title {"isLink":true,"className":"arw-card__title arw-card__title--xl"} /-->
						<!-- wp:post-excerpt {"moreText":"","excerptLength":22,"className":"arw-card__excerpt"} /-->
					</div>
					<!-- /wp:group -->
				<!-- /wp:post-template -->
			</div>
			<!-- /wp:query -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column {"width":"42%"} -->
		<div class="wp-block-column" style="flex-basis:42%">
			<!-- wp:query {"queryId":1,"query":{"perPage":4,"offset":2,"postType":"post","order":"desc","orderBy":"date","inherit":false}} -->
			<div class="wp-block-query">
				<!-- wp:post-template -->
					<!-- wp:group {"className":"arw-card arw-card--small","layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"top"}} -->
					<div class="wp-block-group arw-card arw-card--small">
						<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"4/3","className":"arw-card--small__img"} /-->
						<!-- wp:group {"layout":{"type":"default"}} -->
						<div class="wp-block-group">
							<!-- wp:post-terms {"term":"category","className":"arw-card__eyebrow"} /-->
							<!-- wp:post-title {"isLink":true,"level":3,"className":"arw-card__title arw-card__title--sm"} /-->
						</div>
						<!-- /wp:group -->
					</div>
					<!-- /wp:group -->
				<!-- /wp:post-template -->
			</div>
			<!-- /wp:query -->
		</div>
		<!-- /wp:column -->

	</div>
	<!-- /wp:columns -->

</div>
<!-- /wp:group -->
