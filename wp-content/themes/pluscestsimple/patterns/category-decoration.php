<?php
/**
 * Title: Page catégorie — Décoration
 * Slug: pluscestsimple/category-decoration
 * Categories: pcs-page
 * Description: Page pilier Décoration : intro, bannière, à la une (3), 3 sous-sections (Par pièce, Styles, Petits budgets), bannière mid, tous les articles (12 offset 3), FAQ, partenaires, maillage, disclosure en bas.
 * Inserter: yes
 * Keywords: décoration, intérieur, pilier, catégorie
 */
?>
<!-- wp:group {"className":"pcs-category-page","metadata":{"name":"Page catégorie — Décoration"},"layout":{"type":"constrained"}} -->
<div class="wp-block-group pcs-category-page">

	<!-- wp:heading {"level":1,"className":"pcs-archive__title"} -->
	<h1 class="wp-block-heading pcs-archive__title">Décoration intérieure : ce qui marche vraiment chez vous, et combien ça coûte</h1>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"className":"pcs-archive__intro"} -->
	<p class="pcs-archive__intro">La décoration intérieure est l'un des sujets les plus traités du web français, et l'un des plus mal traités. Tendances 2026 recopiées d'un site à l'autre, listes de "20 idées" interchangeables, sélections shopping déguisées en conseils, canapés à 800 € présentés comme du "petit budget". On a fait le choix inverse. Sur Plus c'est simple, on part de ce qui se passe vraiment chez vous : une pièce orientée nord qui reste sombre douze mois sur douze, un bailleur qui refuse la moindre perceuse, un budget de 300 € pour relooker un salon, une copropriété qui interdit les stores extérieurs. Vous trouverez ici des guides par pièce, des budgets chiffrés sur des marques françaises accessibles, et une lecture critique des styles qui dominent en 2026 — y compris ceux qui vont mal vieillir. Ce qu'on publie a été testé, vérifié et confronté à la réalité de votre logement. Le reste, vous le trouverez ailleurs.</p>
	<!-- /wp:paragraph -->

	<!-- wp:pattern {"slug":"pluscestsimple/banner-slot-category-intro"} /-->

	<!-- wp:group {"tagName":"section","align":"wide","className":"pcs-section pcs-section--cat-top","layout":{"type":"constrained"}} -->
	<section class="wp-block-group alignwide pcs-section pcs-section--cat-top">

		<!-- wp:heading {"level":2,"className":"pcs-section__title"} -->
		<h2 class="wp-block-heading pcs-section__title">À la une</h2>
		<!-- /wp:heading -->

		<!-- wp:query {"queryId":11,"query":{"perPage":3,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false},"namespace":"pcs/cat-loop"} -->
		<div class="wp-block-query">
			<!-- wp:post-template {"lock":{"move":true,"remove":true}} -->
				<!-- wp:group {"tagName":"article","className":"pcs-card","layout":{"type":"default"}} -->
				<article class="wp-block-group pcs-card">
					<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"4/3","className":"pcs-card__media"} /-->
					<!-- wp:post-terms {"term":"category","className":"pcs-card__eyebrow"} /-->
					<!-- wp:post-title {"isLink":true,"level":3,"className":"pcs-card__title"} /-->
					<!-- wp:post-date {"format":"j F Y","className":"pcs-card__meta"} /-->
				</article>
				<!-- /wp:group -->
			<!-- /wp:post-template -->
		</div>
		<!-- /wp:query -->

		<!-- wp:buttons -->
		<div class="wp-block-buttons">
			<!-- wp:button {"className":"is-style-outline"} -->
			<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#all-articles">Voir tous les articles →</a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->

	</section>
	<!-- /wp:group -->

	<!-- wp:group {"tagName":"section","align":"wide","className":"pcs-section pcs-section--subcat","layout":{"type":"constrained"}} -->
	<section class="wp-block-group alignwide pcs-section pcs-section--subcat">

		<!-- wp:heading {"level":2,"className":"pcs-section__title"} -->
		<h2 class="wp-block-heading pcs-section__title">Par pièce</h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"className":"pcs-section__lead"} -->
		<p class="pcs-section__lead">Une pièce n'est pas une autre. Salon de famille avec enfants et chien, chambre orientée nord, salle de bain sans fenêtre, couloir d'1,20 m de large, entrée à optimiser : chacune impose ses arbitrages avant ses inspirations. Pour le mobilier de structure qui doit tenir dix ans, <a href="https://www.laredoute.fr/pplp/500230.aspx" class="pcs-link-partner" rel="sponsored nofollow noopener">La Redoute Intérieurs</a> offre un rapport qualité-prix honnête avec un SAV qui existe. Pour les bois massifs durables, <a href="https://www.tikamoon.com" class="pcs-link-partner" rel="sponsored nofollow noopener">Tikamoon</a> reste la référence française accessible avec garantie cinq ans.</p>
		<!-- /wp:paragraph -->

		<!-- wp:buttons -->
		<div class="wp-block-buttons">
			<!-- wp:button {"className":"is-style-outline"} -->
			<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/decoration-par-piece-cat/">Voir tous les articles Par pièce →</a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->

	</section>
	<!-- /wp:group -->

	<!-- wp:pattern {"slug":"pluscestsimple/banner-slot-category-mid"} /-->

	<!-- wp:group {"tagName":"section","align":"wide","className":"pcs-section pcs-section--subcat","layout":{"type":"constrained"}} -->
	<section class="wp-block-group alignwide pcs-section pcs-section--subcat">

		<!-- wp:heading {"level":2,"className":"pcs-section__title"} -->
		<h2 class="wp-block-heading pcs-section__title">Styles</h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"className":"pcs-section__lead"} -->
		<p class="pcs-section__lead">Japandi mature, wabi-sabi, brutaliste modernisé, scandinave 2.0 : on regarde ce qui dure 10 ans et ce qui se démode en 3. Notre horizon temps : 3 ans (hype), 10 ans (intemporel), 30 ans (regret possible). Pour les pièces fortes qui traversent les styles, le vintage chiné chez <a href="https://www.selency.fr" class="pcs-link-partner" rel="sponsored nofollow noopener">Selency</a> tient mieux qu'un clone neuf à 200 €. Pour du massif français qui passe les modes, <a href="https://www.tikamoon.com" class="pcs-link-partner" rel="sponsored nofollow noopener">Tikamoon</a>.</p>
		<!-- /wp:paragraph -->

		<!-- wp:buttons -->
		<div class="wp-block-buttons">
			<!-- wp:button {"className":"is-style-outline"} -->
			<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/decoration-styles-cat/">Voir tous les articles Styles →</a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->

	</section>
	<!-- /wp:group -->

	<!-- wp:group {"tagName":"section","align":"wide","className":"pcs-section pcs-section--subcat","layout":{"type":"constrained"}} -->
	<section class="wp-block-group alignwide pcs-section pcs-section--subcat">

		<!-- wp:heading {"level":2,"className":"pcs-section__title"} -->
		<h2 class="wp-block-heading pcs-section__title">Petits budgets</h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"className":"pcs-section__lead"} -->
		<p class="pcs-section__lead">Sous 200 €, on ne change pas un canapé : on transforme une ambiance avec textiles, éclairage repensé et un mur peint — un pot <a href="https://www.ressource-peintures.com" class="pcs-link-partner" rel="sponsored nofollow noopener">Ressource Peintures</a> tient dix ans sans jaunir et rentabilise ses 40-60 €/L. Sous 500 €, on attaque la seconde main sérieuse : <a href="https://www.selency.fr" class="pcs-link-partner" rel="sponsored nofollow noopener">Selency</a> pour le vintage authentifié, <a href="https://www.label-emmaus.co" class="pcs-link-partner" rel="sponsored nofollow noopener">Label Emmaüs</a> pour le mobilier réemployé à prix solidaire. Sous 1 500 €, on peut entièrement repenser une pièce.</p>
		<!-- /wp:paragraph -->

		<!-- wp:buttons -->
		<div class="wp-block-buttons">
			<!-- wp:button {"className":"is-style-outline"} -->
			<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/decoration-petits-budgets-cat/">Voir tous les articles Petits budgets →</a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->

	</section>
	<!-- /wp:group -->

	<!-- wp:group {"tagName":"section","align":"wide","className":"pcs-section pcs-section--cat-loop","layout":{"type":"constrained"},"anchor":"all-articles"} -->
	<section id="all-articles" class="wp-block-group alignwide pcs-section pcs-section--cat-loop">

		<!-- wp:heading {"level":2,"className":"pcs-section__title"} -->
		<h2 class="wp-block-heading pcs-section__title">Tous les articles</h2>
		<!-- /wp:heading -->

		<!-- wp:query {"queryId":10,"query":{"perPage":12,"pages":0,"offset":3,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false},"namespace":"pcs/cat-loop"} -->
		<div class="wp-block-query">
			<!-- wp:post-template {"lock":{"move":true,"remove":true}} -->
				<!-- wp:group {"tagName":"article","className":"pcs-card","layout":{"type":"default"}} -->
				<article class="wp-block-group pcs-card">
					<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"4/3","className":"pcs-card__media"} /-->
					<!-- wp:post-terms {"term":"category","className":"pcs-card__eyebrow"} /-->
					<!-- wp:post-title {"isLink":true,"level":3,"className":"pcs-card__title"} /-->
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

	<!-- wp:group {"tagName":"section","className":"pcs-section pcs-section--faq","layout":{"type":"constrained"}} -->
	<section class="wp-block-group pcs-section pcs-section--faq">

		<!-- wp:paragraph {"className":"pcs-eyebrow","textColor":"accent"} -->
		<p class="pcs-eyebrow has-accent-color has-text-color">Questions fréquentes</p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"level":2,"className":"pcs-section__title"} -->
		<h2 class="wp-block-heading pcs-section__title">Décoration : tout ce qu'on nous demande</h2>
		<!-- /wp:heading -->

		<!-- wp:details {"className":"pcs-faq__item"} -->
		<details class="wp-block-details pcs-faq__item"><summary>Combien coûte vraiment de redécorer une pièce en France en 2026 ?</summary>
			<!-- wp:paragraph -->
			<p>Tout dépend du périmètre. Rafraîchir une ambiance (textiles, éclairage, un mur peint, quelques accessoires) : 150 à 400 €. Changer le mobilier principal d'une pièce : 1 000 à 3 000 € selon que vous remplacez le canapé, le lit ou la table. Refonte complète avec peinture pro, sols et mobilier neuf : 100 à 500 € par mètre carré. En dessous de 150 €, seuls les changements cosmétiques mineurs sont possibles — méfiez-vous des articles qui prétendent l'inverse.</p>
			<!-- /wp:paragraph -->
		</details>
		<!-- /wp:details -->

		<!-- wp:details {"className":"pcs-faq__item"} -->
		<details class="wp-block-details pcs-faq__item"><summary>En tant que locataire, qu'ai-je le droit de modifier sans perdre ma caution ?</summary>
			<!-- wp:paragraph -->
			<p>Tout ce qui n'altère pas le logement de manière irréversible. Le décret n° 87-712 du 26 août 1987 vous oblige seulement aux « menus raccords » de peinture et au rebouchage des trous. Vous pouvez repeindre les murs (votre bail peut exiger une couleur neutre au départ, vérifiez les clauses), poser des étagères en rebouchant les trous, changer les poignées en conservant les originales, ajouter des luminaires. Vous ne pouvez pas abattre une cloison, changer les sols collés, modifier la cuisine équipée, percer la façade. En cas de doute, demandez un accord écrit au bailleur — c'est votre meilleure protection juridique.</p>
			<!-- /wp:paragraph -->
		</details>
		<!-- /wp:details -->

		<!-- wp:details {"className":"pcs-faq__item"} -->
		<details class="wp-block-details pcs-faq__item"><summary>Comment décorer une pièce sombre ou sans fenêtre ?</summary>
			<!-- wp:paragraph -->
			<p>Trois leviers à actionner en parallèle. Couleurs : blancs cassés, beiges chauds, finitions satinées plutôt que mates qui absorbent la lumière. Éclairage : minimum trois sources par pièce (plafonnier, applique, lampadaire), ampoules 2 700 à 3 000 K pour une lumière chaude. Miroirs : placés face à une source lumineuse (fenêtre ou luminaire), jamais à côté. À éviter : couleurs sombres sur grandes surfaces, mobilier massif foncé, rideaux opaques même si la pièce est exposée.</p>
			<!-- /wp:paragraph -->
		</details>
		<!-- /wp:details -->

		<!-- wp:details {"className":"pcs-faq__item"} -->
		<details class="wp-block-details pcs-faq__item"><summary>Quels styles déco vont mal vieillir d'ici 2030 ?</summary>
			<!-- wp:paragraph -->
			<p>L'industriel brut (tuyaux apparents partout, métal noir massif) a saturé et perd du terrain face à des versions plus tempérées. Le tout-bohème avec macramés et plantes envahissantes commence à dater. Les couleurs trop marquées sur grandes surfaces — terracotta intégral, vert sauge sur quatre murs — vous obligeront à repeindre dans cinq ans. À l'inverse, le minimalisme neutre, le contemporain en matériaux nobles et le scandinave tempéré résistent depuis quinze ans et continueront.</p>
			<!-- /wp:paragraph -->
		</details>
		<!-- /wp:details -->

		<!-- wp:details {"className":"pcs-faq__item"} -->
		<details class="wp-block-details pcs-faq__item"><summary>Quelles marques déco accessibles sont vraiment fiables en France ?</summary>
			<!-- wp:paragraph -->
			<p>IKEA pour le mobilier de structure : rapport qualité-prix imbattable, garantie 10 ans sur les canapés, durée de vie en usage normal entre 7 et 15 ans. Maisons du Monde pour les accents (luminaires, accessoires, textiles), pas pour le canapé. La Redoute Intérieurs pour les pièces moyennes à fortes. Castorama et Leroy Merlin pour le bricolage et la peinture grande surface. Selency et Label Emmaüs pour la seconde main qualitative. Tikamoon pour le bois massif (garantie 5 ans). Action, surtout pour le consommable décoratif éphémère.</p>
			<!-- /wp:paragraph -->
		</details>
		<!-- /wp:details -->

		<!-- wp:html -->
		<script type="application/ld+json">
		{
			"@context": "https://schema.org",
			"@type": "FAQPage",
			"mainEntity": [
				{
					"@type": "Question",
					"name": "Combien coûte vraiment de redécorer une pièce en France en 2026 ?",
					"acceptedAnswer": {
						"@type": "Answer",
						"text": "Tout dépend du périmètre. Rafraîchir une ambiance (textiles, éclairage, un mur peint, quelques accessoires) : 150 à 400 €. Changer le mobilier principal d'une pièce : 1 000 à 3 000 € selon que vous remplacez le canapé, le lit ou la table. Refonte complète avec peinture pro, sols et mobilier neuf : 100 à 500 € par mètre carré. En dessous de 150 €, seuls les changements cosmétiques mineurs sont possibles — méfiez-vous des articles qui prétendent l'inverse."
					}
				},
				{
					"@type": "Question",
					"name": "En tant que locataire, qu'ai-je le droit de modifier sans perdre ma caution ?",
					"acceptedAnswer": {
						"@type": "Answer",
						"text": "Tout ce qui n'altère pas le logement de manière irréversible. Le décret n° 87-712 du 26 août 1987 vous oblige seulement aux « menus raccords » de peinture et au rebouchage des trous. Vous pouvez repeindre les murs (votre bail peut exiger une couleur neutre au départ, vérifiez les clauses), poser des étagères en rebouchant les trous, changer les poignées en conservant les originales, ajouter des luminaires. Vous ne pouvez pas abattre une cloison, changer les sols collés, modifier la cuisine équipée, percer la façade. En cas de doute, demandez un accord écrit au bailleur."
					}
				},
				{
					"@type": "Question",
					"name": "Comment décorer une pièce sombre ou sans fenêtre ?",
					"acceptedAnswer": {
						"@type": "Answer",
						"text": "Trois leviers à actionner en parallèle. Couleurs : blancs cassés, beiges chauds, finitions satinées plutôt que mates qui absorbent la lumière. Éclairage : minimum trois sources par pièce (plafonnier, applique, lampadaire), ampoules 2 700 à 3 000 K pour une lumière chaude. Miroirs : placés face à une source lumineuse (fenêtre ou luminaire), jamais à côté. À éviter : couleurs sombres sur grandes surfaces, mobilier massif foncé, rideaux opaques même si la pièce est exposée."
					}
				},
				{
					"@type": "Question",
					"name": "Quels styles déco vont mal vieillir d'ici 2030 ?",
					"acceptedAnswer": {
						"@type": "Answer",
						"text": "L'industriel brut (tuyaux apparents partout, métal noir massif) a saturé et perd du terrain face à des versions plus tempérées. Le tout-bohème avec macramés et plantes envahissantes commence à dater. Les couleurs trop marquées sur grandes surfaces — terracotta intégral, vert sauge sur quatre murs — vous obligeront à repeindre dans cinq ans. À l'inverse, le minimalisme neutre, le contemporain en matériaux nobles et le scandinave tempéré résistent depuis quinze ans et continueront."
					}
				},
				{
					"@type": "Question",
					"name": "Quelles marques déco accessibles sont vraiment fiables en France ?",
					"acceptedAnswer": {
						"@type": "Answer",
						"text": "IKEA pour le mobilier de structure : rapport qualité-prix imbattable, garantie 10 ans sur les canapés, durée de vie en usage normal entre 7 et 15 ans. Maisons du Monde pour les accents (luminaires, accessoires, textiles), pas pour le canapé. La Redoute Intérieurs pour les pièces moyennes à fortes. Castorama et Leroy Merlin pour le bricolage et la peinture grande surface. Selency et Label Emmaüs pour la seconde main qualitative. Tikamoon pour le bois massif (garantie 5 ans). Action, surtout pour le consommable décoratif éphémère."
					}
				}
			]
		}
		</script>
		<!-- /wp:html -->

	</section>
	<!-- /wp:group -->

	<!-- wp:group {"tagName":"section","align":"wide","className":"pcs-section pcs-section--maillage","layout":{"type":"constrained"}} -->
	<section class="wp-block-group alignwide pcs-section pcs-section--maillage">

		<!-- wp:paragraph {"className":"pcs-eyebrow","textColor":"accent"} -->
		<p class="pcs-eyebrow has-accent-color has-text-color">Aller plus loin</p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"level":2,"className":"pcs-section__title"} -->
		<h2 class="wp-block-heading pcs-section__title">Voir aussi</h2>
		<!-- /wp:heading -->

		<!-- wp:list {"className":"pcs-link-list"} -->
		<ul class="wp-block-list pcs-link-list">
			<li><a href="/travaux/">Quand la déco devient travaux — peinture pro, électricité, plomberie</a></li>
			<li><a href="/jardin/">Prolonger la déco intérieure jusqu'à la terrasse et au balcon</a></li>
			<li><a href="/architecture/">Avant de redécorer, faut-il revoir les volumes ?</a></li>
			<li><a href="/immobilier/">Ce que la déco actuelle d'un bien révèle avant d'acheter</a></li>
			<li><a href="/lifestyle/">Vivre dans son intérieur — quotidien, rangement, routines</a></li>
			<li><a href="/compatibilimetre/">Évaluer si votre projet déco est compatible avec votre logement, budget et contraintes</a></li>
		</ul>
		<!-- /wp:list -->

	</section>
	<!-- /wp:group -->

	<!-- wp:group {"tagName":"section","align":"wide","className":"pcs-section pcs-partners","layout":{"type":"constrained"}} -->
	<section class="wp-block-group alignwide pcs-section pcs-partners">

		<!-- wp:paragraph {"className":"pcs-eyebrow","textColor":"accent"} -->
		<p class="pcs-eyebrow has-accent-color has-text-color">Notre sélection</p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"level":2,"className":"pcs-section__title"} -->
		<h2 class="wp-block-heading pcs-section__title">Nos partenaires décoration</h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"className":"pcs-section__lead"} -->
		<p class="pcs-section__lead">Sélectionnés pour la qualité, la transparence et le service client. On choisit nos partenaires déco comme on choisirait pour notre propre salon — du vintage authentifié au bois massif garanti cinq ans, en passant par les peintures qui tiennent dix ans.</p>
		<!-- /wp:paragraph -->

		<!-- wp:html -->
		<div class="pcs-partners__grid">

			<article class="pcs-partner-card">
				<span class="pcs-partner-card__badge">Partenaire</span>
				<div class="pcs-partner-card__logo">Selency</div>
				<h3 class="pcs-partner-card__name">Selency</h3>
				<p class="pcs-partner-card__pitch">Brocante en ligne premium, sourcée par chineurs pros français, avec authentification des pièces signées de créateurs. Pour acheter du vintage sans le risque "dropshipping" qu'on trouve sur les marketplaces génériques.</p>
				<p class="pcs-partner-card__cta"><a href="https://www.selency.fr" class="pcs-link-partner" rel="sponsored nofollow noopener">Voir Selency</a></p>
			</article>

			<article class="pcs-partner-card">
				<span class="pcs-partner-card__badge">Partenaire</span>
				<div class="pcs-partner-card__logo">La Redoute Int.</div>
				<h3 class="pcs-partner-card__name">La Redoute Intérieurs</h3>
				<p class="pcs-partner-card__pitch">Mobilier moyenne gamme et linge de maison fabriqués ou édités par La Redoute. SAV existant, retours simples, rapport qualité-prix honnête sur le mobilier de structure.</p>
				<p class="pcs-partner-card__cta"><a href="https://www.laredoute.fr/pplp/500230.aspx" class="pcs-link-partner" rel="sponsored nofollow noopener">Voir La Redoute Intérieurs</a></p>
			</article>

			<article class="pcs-partner-card">
				<span class="pcs-partner-card__badge">Partenaire</span>
				<div class="pcs-partner-card__logo">Tikamoon</div>
				<h3 class="pcs-partner-card__name">Tikamoon</h3>
				<p class="pcs-partner-card__pitch">Mobilier en bois massif (teck, manguier, chêne) à prix accessible pour le segment, garantie constructeur cinq ans. Idéal quand on veut un meuble qui passe les dix ans sans plier.</p>
				<p class="pcs-partner-card__cta"><a href="https://www.tikamoon.com" class="pcs-link-partner" rel="sponsored nofollow noopener">Voir Tikamoon</a></p>
			</article>

			<article class="pcs-partner-card">
				<span class="pcs-partner-card__badge">Partenaire</span>
				<div class="pcs-partner-card__logo">Ressource</div>
				<h3 class="pcs-partner-card__name">Ressource Peintures</h3>
				<p class="pcs-partner-card__pitch">Peintures haut de gamme françaises, pigments stables, tenue dans le temps. Plus cher à l'achat (40 à 60 € le litre selon finition et format) mais rentabilisé sur la durée de vie sans jaunissement.</p>
				<p class="pcs-partner-card__cta"><a href="https://www.ressource-peintures.com" class="pcs-link-partner" rel="sponsored nofollow noopener">Voir Ressource Peintures</a></p>
			</article>

			<article class="pcs-partner-card">
				<span class="pcs-partner-card__badge">Partenaire</span>
				<div class="pcs-partner-card__logo">Castorama</div>
				<h3 class="pcs-partner-card__name">Castorama</h3>
				<p class="pcs-partner-card__pitch">Grande surface bricolage pour la peinture courante, l'outillage, les fournitures techniques. Pas pour le mobilier de structure, oui pour tout ce qui supporte la maison.</p>
				<p class="pcs-partner-card__cta"><a href="https://www.castorama.fr" class="pcs-link-partner" rel="sponsored nofollow noopener">Voir Castorama</a></p>
			</article>

			<article class="pcs-partner-card">
				<span class="pcs-partner-card__badge">Partenaire</span>
				<div class="pcs-partner-card__logo">Label Emmaüs</div>
				<h3 class="pcs-partner-card__name">Label Emmaüs</h3>
				<p class="pcs-partner-card__pitch">E-commerce solidaire du réseau Emmaüs (coopérative SCIC créée en 2016). Mobilier et déco réemployés, prix maîtrisés, traçabilité française. Souvent du mobilier mieux fait que son équivalent neuf à prix égal.</p>
				<p class="pcs-partner-card__cta"><a href="https://www.label-emmaus.co" class="pcs-link-partner" rel="sponsored nofollow noopener">Voir Label Emmaüs</a></p>
			</article>

		</div>
		<p class="pcs-partners__charter">
			<a href="/charte-partenaires/">Pourquoi ces partenaires ?</a>
			<a href="/travailler-avec-nous/">Devenir partenaire</a>
		</p>
		<!-- /wp:html -->

	</section>
	<!-- /wp:group -->

	<!-- wp:pattern {"slug":"pluscestsimple/disclosure-partners"} /-->

</div>
<!-- /wp:group -->
