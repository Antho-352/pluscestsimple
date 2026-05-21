<?php
/**
 * Title: Section éditoriale (titre + texte + liens articles)
 * Slug: pluscestsimple/section-editorial
 * Categories: pcs-section
 * Description: Le bloc de base des pages catégories et de l'accueil. Titre + paragraphe de cadrage + 3-5 liens d'articles.
 * Inserter: yes
 * Keywords: section, éditoriale, liens, articles
 */
?>
<!-- wp:group {"tagName":"section","align":"wide","className":"pcs-section pcs-section--editorial","metadata":{"name":"Section éditoriale"},"templateLock":"contentOnly","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignwide pcs-section pcs-section--editorial">

	<!-- wp:heading {"level":2,"className":"pcs-section__title"} -->
	<h2 class="wp-block-heading pcs-section__title">Titre de la section — à remplacer</h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"className":"pcs-section__lead"} -->
	<p class="pcs-section__lead">Paragraphe de cadrage (80-150 mots) — situe le sujet, donne l'angle Plus c'est simple, prépare la lecture des articles ci-dessous.</p>
	<!-- /wp:paragraph -->

	<!-- wp:list {"className":"pcs-link-list"} -->
	<ul class="wp-block-list pcs-link-list">
		<li><a href="#">Article 1 — titre exact</a></li>
		<li><a href="#">Article 2 — titre exact</a></li>
		<li><a href="#">Article 3 — titre exact</a></li>
		<li><a href="#">Article 4 — titre exact</a></li>
	</ul>
	<!-- /wp:list -->

	<!-- wp:paragraph {"className":"pcs-section__more"} -->
	<p class="pcs-section__more"><a href="#">Tout voir →</a></p>
	<!-- /wp:paragraph -->

</section>
<!-- /wp:group -->
