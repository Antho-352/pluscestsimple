<?php
if ( ! defined( "ABSPATH" ) ) { exit; }
/**
 * Title: CTA inline (lead-gen)
 * Slug: arw-pulse/cta-inline
 * Categories: arw-marketing
 * Description: Bloc CTA plein-width, à placer au milieu ou fin d'article.
 */
?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","right":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|60"},"margin":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70"}},"border":{"radius":"var:custom|radius|md"},"color":{"background":"var:preset|color|foreground","text":"var:preset|color|background"}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"center"}} -->
<div class="wp-block-group alignwide has-background-color has-foreground-background-color has-text-color has-background" style="color:var(--wp--preset--color--background);background-color:var(--wp--preset--color--foreground);border-radius:var(--wp--custom--radius--md);margin-top:var(--wp--preset--spacing--70);margin-bottom:var(--wp--preset--spacing--70);padding:var(--wp--preset--spacing--60)">

	<!-- wp:group {"layout":{"type":"default"}} -->
	<div class="wp-block-group">
		<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"var:preset|font-size|lg"}}} -->
		<h3 class="wp-block-heading" style="font-size:var(--wp--preset--font-size--lg)">Besoin d'un devis ?</h3>
		<!-- /wp:heading -->
		<!-- wp:paragraph {"style":{"typography":{"fontSize":"var:preset|font-size|sm"},"color":{"text":"var:preset|color|muted"}}} -->
		<p style="color:var(--wp--preset--color--muted);font-size:var(--wp--preset--font-size--sm);margin:0">Réponse sous 48h ouvrées.</p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->

	<!-- wp:buttons -->
	<div class="wp-block-buttons">
		<!-- wp:button {"style":{"color":{"background":"var:preset|color|accent","text":"var:preset|color|foreground"}}} -->
		<div class="wp-block-button"><a class="wp-block-button__link has-text-color has-background wp-element-button" href="/contact/" style="color:var(--wp--preset--color--foreground);background-color:var(--wp--preset--color--accent)">Demander un devis</a></div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->

</div>
<!-- /wp:group -->
