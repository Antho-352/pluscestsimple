<?php
if ( ! defined( "ABSPATH" ) ) { exit; }
/**
 * Title: L'Essentiel — Équipement sélectionné (affiliation)
 * Slug: arw-pulse/essentials-gear
 * Categories: arw-home, arw-affiliate
 * Description: Grille de 6 produits affiliation. Alimentée dynamiquement par le CPT "Produits" — édite depuis WP admin, jamais ici.
 */
?>
<!-- wp:group {"align":"full","className":"arw-essentials","anchor":"essentiel","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull arw-essentials" id="essentiel">

	<!-- wp:group {"align":"wide","className":"arw-section-head","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"bottom"}} -->
	<div class="wp-block-group alignwide arw-section-head">
		<!-- wp:group {"layout":{"type":"default"}} -->
		<div class="wp-block-group">
			<!-- wp:paragraph {"className":"arw-section-num"} -->
			<p class="arw-section-num">02</p>
			<!-- /wp:paragraph -->
			<!-- wp:heading {"level":2,"className":"arw-section-title"} -->
			<h2 class="wp-block-heading arw-section-title">L'Essentiel</h2>
			<!-- /wp:heading -->
		</div>
		<!-- /wp:group -->
		<!-- wp:paragraph {"className":"arw-section-lead"} -->
		<p class="arw-section-lead">6 pièces d'équipement testées et approuvées. Gérées depuis <em>Produits</em> dans l'admin.</p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"align":"wide","layout":{"type":"default"}} -->
	<div class="wp-block-group alignwide">
		<!-- wp:shortcode -->
		[arw_products limit="6"]
		<!-- /wp:shortcode -->
	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->
