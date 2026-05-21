<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Title: Article — header (catégorie + H1 + meta)
 * Slug: arw-pulse/article-header
 * Categories: arw-content
 * Description: Bloc en-tête réutilisable pour single.html et single-review.html.
 *              Évite la duplication breadcrumbs + terms + title + post-meta.
 *              L'image à la une est gérée par chaque template (placement diffère).
 */
?>
<!-- wp:group {"layout":{"type":"constrained","contentSize":"960px"}} -->
<div class="wp-block-group">

	<!-- wp:post-terms {"term":"category","className":"arw-card__eyebrow","style":{"spacing":{"margin":{"bottom":"0.75rem"}}}} /-->

	<!-- wp:post-title {"level":1,"style":{"typography":{"fontSize":"var:preset|font-size|3xl","lineHeight":"1.1"}}} /-->

	<!-- wp:template-part {"slug":"post-meta","tagName":"div"} /-->

</div>
<!-- /wp:group -->
