<?php
/**
 * Title: Page catégorie — structure complète
 * Slug: pluscestsimple/category-rich
 * Categories: pcs-page
 * Description: Assemblage complet d'une page catégorie pilier : intro éditoriale + breadcrumbs + bannière + sections sous-cat + Query Loop + FAQ + maillage.
 * Inserter: yes
 * Keywords: catégorie, pilier, structure
 */
?>
<!-- wp:group {"className":"pcs-category-page","metadata":{"name":"Page catégorie"},"templateLock":"contentOnly","layout":{"type":"constrained"}} -->
<div class="wp-block-group pcs-category-page">

	<!-- wp:heading {"level":1,"className":"pcs-archive__title"} -->
	<h1 class="wp-block-heading pcs-archive__title">Nom de la catégorie — à remplacer</h1>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"className":"pcs-archive__intro"} -->
	<p class="pcs-archive__intro">Intro éditoriale (150-250 mots) qui cible le terme large de la catégorie. Pose l'angle, le périmètre, ce qu'on trouve ici, ce qu'on n'y trouve pas. Doit être lue par un humain et utile au SEO.</p>
	<!-- /wp:paragraph -->

	<!-- wp:pattern {"slug":"pluscestsimple/disclosure-partners"} /-->

	<!-- wp:pattern {"slug":"pluscestsimple/banner-slot-category-intro"} /-->

	<!-- wp:pattern {"slug":"pluscestsimple/section-editorial"} /-->

	<!-- wp:pattern {"slug":"pluscestsimple/section-editorial"} /-->

	<!-- wp:pattern {"slug":"pluscestsimple/banner-slot-category-mid"} /-->

	<!-- wp:pattern {"slug":"pluscestsimple/section-editorial"} /-->

	<!-- wp:group {"tagName":"section","className":"pcs-section pcs-section--cat-loop","layout":{"type":"constrained"}} -->
	<section class="wp-block-group pcs-section pcs-section--cat-loop">

		<!-- wp:heading {"level":2,"className":"pcs-section__title"} -->
		<h2 class="wp-block-heading pcs-section__title">Tous les articles</h2>
		<!-- /wp:heading -->

		<!-- wp:query {"queryId":10,"query":{"perPage":9,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":true},"namespace":"pcs/cat-loop","lock":{"move":false,"remove":true}} -->
		<div class="wp-block-query">
			<!-- wp:post-template {"className":"pcs-card-grid","lock":{"move":true,"remove":true}} -->
				<!-- wp:group {"tagName":"article","className":"pcs-card"} -->
				<article class="wp-block-group pcs-card">
					<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"4/3","className":"pcs-card__media"} /-->
					<!-- wp:post-terms {"term":"category","className":"pcs-card__eyebrow"} /-->
					<!-- wp:post-title {"isLink":true,"level":3,"className":"pcs-card__title"} /-->
					<!-- wp:post-excerpt /-->
					<!-- wp:post-date {"format":"j F Y","className":"pcs-card__meta"} /-->
				</article>
				<!-- /wp:group -->
			<!-- /wp:post-template -->
			<!-- wp:query-pagination -->
				<!-- wp:query-pagination-previous /-->
				<!-- wp:query-pagination-numbers /-->
				<!-- wp:query-pagination-next /-->
			<!-- /wp:query-pagination -->
		</div>
		<!-- /wp:query -->

	</section>
	<!-- /wp:group -->

	<!-- wp:pattern {"slug":"pluscestsimple/block-faq"} /-->

	<!-- wp:pattern {"slug":"pluscestsimple/section-partners"} /-->

	<!-- wp:group {"tagName":"section","className":"pcs-section pcs-section--maillage","layout":{"type":"constrained"}} -->
	<section class="wp-block-group pcs-section pcs-section--maillage">

		<!-- wp:paragraph {"className":"pcs-eyebrow","textColor":"accent"} -->
		<p class="pcs-eyebrow has-accent-color has-text-color">Aller plus loin</p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"level":2,"className":"pcs-section__title"} -->
		<h2 class="wp-block-heading pcs-section__title">Voir aussi</h2>
		<!-- /wp:heading -->

		<!-- wp:list {"className":"pcs-link-list"} -->
		<ul class="wp-block-list pcs-link-list">
			<li><a href="/decoration/">Décoration</a></li>
			<li><a href="/travaux/">Travaux</a></li>
			<li><a href="/jardin/">Jardin</a></li>
			<li><a href="/architecture/">Architecture</a></li>
			<li><a href="/immobilier/">Immobilier</a></li>
			<li><a href="/lifestyle/">Lifestyle</a></li>
			<li><a href="/travailler-avec-nous/">Travailler avec nous</a></li>
		</ul>
		<!-- /wp:list -->

	</section>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->
