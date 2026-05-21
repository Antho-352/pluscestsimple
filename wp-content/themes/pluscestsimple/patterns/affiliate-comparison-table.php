<?php
if ( ! defined( "ABSPATH" ) ) { exit; }
/**
 * Title: Table comparative — Affiliation
 * Slug: arw-pulse/affiliate-comparison-table
 * Categories: arw-affiliate
 * Description: Tableau comparatif responsive avec CTA affilié par ligne.
 */
?>
<!-- wp:html -->
<div class="arw-compare" style="margin-block:1.5rem 2rem">
	<table>
		<thead>
			<tr>
				<th>Produit</th>
				<th>Note</th>
				<th>Prix</th>
				<th>Idéal pour</th>
				<th aria-label="CTA"></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<th scope="row">Produit A</th>
				<td>9.0/10</td>
				<td>à partir de 199 €</td>
				<td>Trajets quotidiens</td>
				<td><a class="is-affiliate" href="https://example.com/a" style="font-weight:600">Voir →</a></td>
			</tr>
			<tr>
				<th scope="row">Produit B</th>
				<td>8.5/10</td>
				<td>à partir de 149 €</td>
				<td>Budget serré</td>
				<td><a class="is-affiliate" href="https://example.com/b" style="font-weight:600">Voir →</a></td>
			</tr>
			<tr>
				<th scope="row">Produit C</th>
				<td>8.2/10</td>
				<td>à partir de 299 €</td>
				<td>Usage intensif</td>
				<td><a class="is-affiliate" href="https://example.com/c" style="font-weight:600">Voir →</a></td>
			</tr>
		</tbody>
	</table>
</div>
<!-- /wp:html -->
