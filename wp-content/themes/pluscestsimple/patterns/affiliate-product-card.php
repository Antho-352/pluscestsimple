<?php
if ( ! defined( "ABSPATH" ) ) { exit; }
/**
 * Title: Fiche produit — Affiliation
 * Slug: arw-pulse/affiliate-product-card
 * Categories: arw-affiliate
 * Description: Carte produit avec score, pros/cons rapides, bouton affilié. Idéale en début de review.
 */
?>
<!-- wp:html -->
<div class="arw-product" itemscope itemtype="https://schema.org/Product" style="margin-block:1.5rem 2rem">
	<div>
		<img src="https://placehold.co/600x600/f6f6f4/0a0a0a?text=PRODUIT" alt="Produit" width="600" height="600" style="border-radius:var(--wp--custom--radius--md);aspect-ratio:1/1;object-fit:cover" loading="lazy" decoding="async" itemprop="image">
	</div>
	<div>
		<p class="arw-card__eyebrow" style="margin-top:0">Notre verdict</p>
		<h3 itemprop="name" style="margin:0 0 0.25rem">Nom du produit</h3>
		<p itemprop="brand" style="color:var(--wp--preset--color--muted);margin:0 0 0.75rem;font-size:var(--wp--preset--font-size--sm)">Marque</p>
		<div class="arw-product__score" itemprop="aggregateRating" itemscope itemtype="https://schema.org/AggregateRating">
			<span itemprop="ratingValue">8.5</span><small>/<span itemprop="bestRating">10</span></small>
			<meta itemprop="reviewCount" content="1">
		</div>
		<div class="arw-proscons" style="margin-top:1rem">
			<div>
				<strong style="font-size:var(--wp--preset--font-size--sm)">Points forts</strong>
				<ul class="pros">
					<li>Qualité de finition</li>
					<li>Rapport qualité / prix</li>
					<li>Performances en ville</li>
				</ul>
			</div>
			<div>
				<strong style="font-size:var(--wp--preset--font-size--sm)">Points faibles</strong>
				<ul class="cons">
					<li>Autonomie limitée</li>
					<li>Bruit à haute vitesse</li>
				</ul>
			</div>
		</div>
		<p style="margin-top:1.25rem">
			<a class="is-affiliate wp-element-button" href="https://example.com/produit" style="display:inline-block;background:var(--wp--preset--color--foreground);color:var(--wp--preset--color--background);padding:0.75rem 1.25rem;border-radius:var(--wp--custom--radius--sm);text-decoration:none;font-weight:600">Voir l'offre</a>
		</p>
	</div>
</div>
<!-- /wp:html -->
