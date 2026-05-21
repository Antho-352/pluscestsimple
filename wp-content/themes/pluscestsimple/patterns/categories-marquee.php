<?php
if ( ! defined( "ABSPATH" ) ) { exit; }
/**
 * Title: Marquee catégories
 * Slug: arw-pulse/categories-marquee
 * Categories: arw-home
 * Description: Défilement infini CSS-only des catégories. Modifiable en éditant le contenu.
 */
?>
<!-- wp:html -->
<div class="is-marquee arw-categories-marquee" aria-hidden="true" style="padding:1.5rem 0;border-top:1px solid var(--wp--preset--color--border);border-bottom:1px solid var(--wp--preset--color--border);font-family:var(--wp--preset--font-family--display);font-weight:700;font-size:var(--wp--preset--font-size--lg);letter-spacing:-0.01em;text-transform:uppercase">
	<div>
		<?php
		$cats = get_categories( [ 'number' => 12, 'hide_empty' => true ] );
		$items = [];
		foreach ( $cats as $cat ) {
			$items[] = sprintf( '<a href="%s" style="color:inherit;text-decoration:none">%s</a>', esc_url( get_category_link( $cat ) ), esc_html( $cat->name ) );
		}
		if ( empty( $items ) ) {
			$items = [ 'Tests', 'Guides d\'achat', 'Comparatifs', 'Actualités', 'Équipement', 'Assurance' ];
		}
		$items   = array_merge( $items, $items );
		$divider = '<span style="color:var(--wp--preset--color--accent)" aria-hidden="true">★</span>';
		echo implode( $divider, $items );
		?>
	</div>
</div>
<!-- /wp:html -->
