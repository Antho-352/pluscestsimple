<?php
if ( ! defined( "ABSPATH" ) ) { exit; }
/**
 * Title: Partenaires — Marquee
 * Slug: arw-pulse/partners-marquee
 * Categories: arw-home, arw-marketing
 * Description: Défilement horizontal des marques testées / partenaires affiliation. CSS-only, 0 JS.
 */
$brands = apply_filters( 'arw_pulse_partner_brands', [ 'Alpinestars', 'Shoei', 'Dainese', 'Cardo', 'Kriega', 'AGV', 'Sidi', 'REV\'IT', 'Spidi', 'Held', 'Furygan', 'Bering' ] );
?>
<!-- wp:group {"align":"full","className":"arw-partners","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull arw-partners" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">

	<!-- wp:paragraph {"align":"center","className":"arw-eyebrow","style":{"typography":{"fontSize":"var:preset|font-size|xs","letterSpacing":"0.24em","textTransform":"uppercase","fontWeight":"600"},"color":{"text":"var:preset|color|muted"},"spacing":{"margin":{"top":"0","bottom":"var:preset|spacing|40"}}}} -->
	<p class="has-text-align-center arw-eyebrow" style="color:var(--wp--preset--color--muted);font-size:var(--wp--preset--font-size--xs);font-weight:600;letter-spacing:0.24em;text-transform:uppercase;text-align:center;margin-top:0;margin-bottom:var(--wp--preset--spacing--40)">Marques testées & partenaires</p>
	<!-- /wp:paragraph -->

	<!-- wp:html -->
	<div class="is-marquee arw-partners__marquee" aria-hidden="true" style="--arw-marquee-duration:50s;--arw-marquee-gap:4rem;font-family:var(--wp--preset--font-family--display);font-weight:700;font-size:clamp(1.5rem, 3vw, 2.5rem);letter-spacing:-0.02em;text-transform:uppercase;color:var(--wp--preset--color--foreground)">
		<div>
			<?php
			$items = array_merge( $brands, $brands );
			$divider = '<span style="color:var(--wp--preset--color--accent)" aria-hidden="true">●</span>';
			$out = [];
			foreach ( $items as $b ) {
				$out[] = '<span>' . esc_html( $b ) . '</span>';
			}
			echo implode( $divider, $out );
			?>
		</div>
	</div>
	<!-- /wp:html -->

</div>
<!-- /wp:group -->
