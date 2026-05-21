<?php
/**
 * Title: Bannière — emplacement
 * Slug: pluscestsimple/banner-slot
 * Categories: pcs-section
 * Description: Emplacement pour une bannière (requiert le plugin Bannières). Choisir le slot via le shortcode.
 * Inserter: yes
 * Keywords: banner, bannière, sponsor, slot
 */
?>
<!-- wp:group {"align":"wide","className":"pcs-banner-wrap","metadata":{"name":"Bannière"},"templateLock":"contentOnly","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide pcs-banner-wrap">

	<!-- wp:shortcode -->
	[pcs_banner slot="homepage-mid"]
	<!-- /wp:shortcode -->

</div>
<!-- /wp:group -->
