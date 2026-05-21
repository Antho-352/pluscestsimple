<?php
if ( ! defined( "ABSPATH" ) ) { exit; }
/**
 * Title: Breadcrumbs
 * Slug: arw-pulse/breadcrumbs
 * Categories: arw-magazine
 * Block Types: core/group
 * Inserter: no
 */
$crumbs = function_exists( 'arw_pulse_get_breadcrumbs' ) ? arw_pulse_get_breadcrumbs() : [];
if ( count( $crumbs ) < 2 ) { return; }
?>
<!-- wp:html -->
<nav class="arw-breadcrumbs" aria-label="<?php esc_attr_e( 'Fil d\'Ariane', 'arw-pulse' ); ?>">
	<?php
	$last = count( $crumbs ) - 1;
	foreach ( $crumbs as $i => $c ) :
		if ( $i === $last ) :
			printf( '<span aria-current="page">%s</span>', esc_html( $c['name'] ) );
		else :
			printf( '<a href="%s">%s</a><span aria-hidden="true">/</span>', esc_url( $c['url'] ), esc_html( $c['name'] ) );
		endif;
	endforeach;
	?>
</nav>
<!-- /wp:html -->
