<?php
/**
 * Title: Page catégorie — Jardin
 * Slug: pluscestsimple/category-jardin
 * Categories: pcs-page
 * Description: Page pilier Jardin : intro, bannière, à la une (3), 2 sous-sections (Aménagement extérieur, Entretien), bannière mid, tous les articles (12 offset 3), FAQ, partenaires, maillage, disclosure en bas.
 * Inserter: yes
 * Keywords: jardin, potager, balcon, pilier, catégorie
 */
?>
<!-- wp:group {"className":"pcs-category-page","metadata":{"name":"Page catégorie — Jardin"},"layout":{"type":"constrained"}} -->
<div class="wp-block-group pcs-category-page">

	<!-- wp:heading {"level":1,"className":"pcs-archive__title"} -->
	<h1 class="wp-block-heading pcs-archive__title">Jardin : ce qui marche vraiment en 2026 sous climat français</h1>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"className":"pcs-archive__intro"} -->
	<p class="pcs-archive__intro">Jardiner en France en 2026, ce n'est plus jardiner comme en 2000. Les canicules s'enchaînent, les arrêtés sécheresse coupent l'arrosage trois à quatre mois par an dans la moitié sud, et la quasi-totalité des conseils trouvés en ligne ignore tout ça. Sur Plus c'est simple, on a fait le choix de partir du jardin d'aujourd'hui, pas du potager de Mémé. Concret : votre exposition, votre sol, votre zone climatique (méditerranéenne, océanique, continentale ou montagne), votre budget réel et le cadre légal qui s'applique chez vous. Pas de "10 légumes magiques". Pas de carré potager affiché à 80 € quand il en coûte 220 € monté et rempli. Pas de calendrier national qui ignore que Lille gèle encore mi-avril quand Marseille sème en février. Vous trouverez ici un hub potager (jardin et balcon), un volet aménagement chiffré, un calendrier d'entretien par région, et la section que peu de sites traitent : jardiner en locataire, en copropriété, sous restriction d'eau. Avec les prix, les volumes, les surfaces et les textes.</p>
	<!-- /wp:paragraph -->

	<!-- wp:pattern {"slug":"pluscestsimple/banner-slot-category-intro"} /-->

	<!-- wp:group {"tagName":"section","align":"wide","className":"pcs-section pcs-section--cat-top","layout":{"type":"constrained"}} -->
	<section class="wp-block-group alignwide pcs-section pcs-section--cat-top">

		<!-- wp:heading {"level":2,"className":"pcs-section__title"} -->
		<h2 class="wp-block-heading pcs-section__title">À la une</h2>
		<!-- /wp:heading -->

		<!-- wp:query {"queryId":11,"query":{"perPage":3,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false},"namespace":"pcs/cat-loop"} -->
		<div class="wp-block-query">
			<!-- wp:post-template {"layout":{"type":"grid","columnCount":3},"lock":{"move":true,"remove":true}} -->
				<!-- wp:group {"tagName":"article","className":"pcs-card","layout":{"type":"default"}} -->
				<article class="wp-block-group pcs-card">
					<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9","sizeSlug":"pcs-card-wide","className":"pcs-card__media"} /-->
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
		<h2 class="wp-block-heading pcs-section__title">Aménagement extérieur</h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"className":"pcs-section__lead"} -->
		<p class="pcs-section__lead">Terrasse, allée, clôture, plantations structurelles, balcon et toit-terrasse : ce qui compose un extérieur durable et chiffré. Honoraires paysagistes 25-65 € HT/h (+15-25 % en Île-de-France), terrasse bois posée 40-90 €/m² (européen) jusqu'à 150 €/m² (exotique), dallage pierre 30-105 €/m². Charge admissible balcon : 350 kg/m² (Eurocode/DTU 43.1) — un pot de 40 L rempli pèse 55-70 kg. Pour les fournitures (carrés potagers, terreau, mobilier extérieur), <a href="https://www.truffaut.com" class="pcs-link-partner" rel="sponsored nofollow noopener">Truffaut</a> et <a href="https://www.jardiland.com" class="pcs-link-partner" rel="sponsored nofollow noopener">Jardiland</a> couvrent l'essentiel.</p>
		<!-- /wp:paragraph -->

		<!-- wp:buttons -->
		<div class="wp-block-buttons">
			<!-- wp:button {"className":"is-style-outline"} -->
			<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/jardin-amenagement-exterieur-cat/">Voir tous les articles Aménagement extérieur →</a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->

	</section>
	<!-- /wp:group -->

	<!-- wp:pattern {"slug":"pluscestsimple/banner-slot-category-mid"} /-->

	<!-- wp:group {"tagName":"section","align":"wide","className":"pcs-section pcs-section--subcat","layout":{"type":"constrained"}} -->
	<section class="wp-block-group alignwide pcs-section pcs-section--subcat">

		<!-- wp:heading {"level":2,"className":"pcs-section__title"} -->
		<h2 class="wp-block-heading pcs-section__title">Entretien</h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"className":"pcs-section__lead"} -->
		<p class="pcs-section__lead">Calendrier potager et entretien par zone climatique (méditerranéenne, océanique, continentale, montagne) : Saints de Glace (11-13 mai), périodes de taille (16 mars-15 août interdit pour les agriculteurs PAC, l'<a href="https://www.ofb.gouv.fr/">OFB</a> recommande la même fenêtre pour les particuliers), arrêtés sécheresse (sanction 1 500 € — contravention de 5ᵉ classe). Récupérateur 300 L : 60-90 € en grande surface ; cuve aérienne 500-1 000 L à 200-500 €. Pour les semences fiables, <a href="https://www.graines-baumaux.fr" class="pcs-link-partner" rel="sponsored nofollow noopener">Baumaux</a> (Vosges, fondé 1943). Récupérateurs d'eau et matériel éco-conçu chez <a href="https://www.greenweez.com" class="pcs-link-partner" rel="sponsored nofollow noopener">Greenweez</a>.</p>
		<!-- /wp:paragraph -->

		<!-- wp:buttons -->
		<div class="wp-block-buttons">
			<!-- wp:button {"className":"is-style-outline"} -->
			<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/jardin-entretien-cat/">Voir tous les articles Entretien →</a></div>
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
			<!-- wp:post-template {"layout":{"type":"grid","columnCount":3},"lock":{"move":true,"remove":true}} -->
				<!-- wp:group {"tagName":"article","className":"pcs-card","layout":{"type":"default"}} -->
				<article class="wp-block-group pcs-card">
					<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9","sizeSlug":"pcs-card-wide","className":"pcs-card__media"} /-->
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
		<h2 class="wp-block-heading pcs-section__title">Jardin : tout ce qu'on nous demande</h2>
		<!-- /wp:heading -->

		<!-- wp:details {"className":"pcs-faq__item"} -->
		<details class="wp-block-details pcs-faq__item"><summary>Quand semer les tomates en France en 2026 ?</summary>
			<!-- wp:paragraph -->
			<p>Semis intérieur en godet : fin février à fin mars selon la région (plus tôt au sud). Repiquage au jardin ou en pot extérieur : après les Saints de Glace (11, 12 et 13 mai — Mamert, Pancrace, Servais) en zone tempérée et continentale, à partir du 20-25 avril en zone méditerranéenne avec voile P17 de secours, début juin en montagne (>600 m). Une plantation prématurée bloque la croissance dix jours et fait perdre l'avance espérée. À noter : Météo-France constate que les gelées tombent rarement précisément ces trois jours-là, mais les statistiques sur les gelées tardives justifient toujours la prudence jusqu'à mi-mai.</p>
			<!-- /wp:paragraph -->
		</details>
		<!-- /wp:details -->

		<!-- wp:details {"className":"pcs-faq__item"} -->
		<details class="wp-block-details pcs-faq__item"><summary>Quel volume de pot minimum pour réussir un potager balcon ?</summary>
			<!-- wp:paragraph -->
			<p>Radis et aromatiques : 5-15 L. Salades et fraisiers : 15-20 L. Tomates, poivrons, aubergines : 40 L par pied, profondeur 40 cm, non négociable — en dessous, le rendement chute de 50-70 %. Courgette compacte : 40-50 L, profondeur 40 cm. Pomme de terre primeur en sac : 40-60 L. Melon, courge coureuse, pastèque, maïs : à oublier sur balcon (surface racinaire et besoin en eau incompatibles). Le terreau potager pèse 70-90 kg/100 L : vérifier la charge admissible du balcon (350 kg/m² en règle générale, norme Eurocode/DTU 43.1) avant d'aligner les pots.</p>
			<!-- /wp:paragraph -->
		</details>
		<!-- /wp:details -->

		<!-- wp:details {"className":"pcs-faq__item"} -->
		<details class="wp-block-details pcs-faq__item"><summary>Faut-il l'autorisation du syndic pour des jardinières de balcon ?</summary>
			<!-- wp:paragraph -->
			<p>Pour des pots posés au sol, aucun texte ne l'interdit et le syndic ne peut s'y opposer. Le règlement de copropriété interdit en général trois choses : jardinières fixées en rambarde (risque de chute), écoulement d'eau sur le balcon inférieur, et fixations modifiant l'aspect extérieur. Pour une installation lourde (bac maçonné, treillage fixé en façade, sol modifié) : passage en assemblée générale obligatoire, majorité prévue à l'article 25 de la loi du 10 juillet 1965.</p>
			<!-- /wp:paragraph -->
		</details>
		<!-- /wp:details -->

		<!-- wp:details {"className":"pcs-faq__item"} -->
		<details class="wp-block-details pcs-faq__item"><summary>Qu'est-ce qui reste autorisé pour arroser le potager en alerte sécheresse ?</summary>
			<!-- wp:paragraph -->
			<p>Cela dépend du niveau de l'arrêté préfectoral. En alerte simple, arrosage du potager autorisé hors créneau 11 h-18 h. En alerte renforcée, généralement potager autorisé en soirée uniquement (après 20 h), pelouse et massifs interdits. En crise (niveau rouge), tout arrosage non vital peut être interdit, y compris potager, à l'exception fréquente de l'arrosoir à la main au pied. Sanction : 1 500 € pour un particulier (contravention de 5ᵉ classe, article 131-13-5° du Code pénal), jusqu'à 3 000 € en récidive. Vérifier l'arrêté en cours sur le site de votre préfecture, mis à jour chaque semaine en saison.</p>
			<!-- /wp:paragraph -->
		</details>
		<!-- /wp:details -->

		<!-- wp:details {"className":"pcs-faq__item"} -->
		<details class="wp-block-details pcs-faq__item"><summary>Combien coûte un aménagement de jardin par un paysagiste ?</summary>
			<!-- wp:paragraph -->
			<p>Honoraires : jardinier-paysagiste auto-entrepreneur 25-45 €/h, entreprise paysagiste 35-65 € HT/h (42-78 € TTC), architecte paysagiste 40-65 €/h, +15-25 % en Île-de-France. Conception seule sur 200-500 m² : 800-2 500 €. Conception + travaux clé en main : 5 000-15 000 € pour un jardin moyen (terrassement léger, allée, plantations structurelles, arrosage). Postes lourds : terrasse bois 40-90 €/m² posée (européen) ou jusqu'à 150 €/m² (exotique), dallage pierre 30-105 €/m² posé, allée gravillonnée 25-50 €/m², clôture grillage rigide 15-25 €/m linéaire posé. Faire faire 3 devis, mai à octobre = créneau plein, prix +10-15 %.</p>
			<!-- /wp:paragraph -->
		</details>
		<!-- /wp:details -->

		<!-- wp:details {"className":"pcs-faq__item"} -->
		<details class="wp-block-details pcs-faq__item"><summary>Locataire, qu'est-ce qu'on a le droit de modifier dans le jardin ?</summary>
			<!-- wp:paragraph -->
			<p>Entretien courant à votre charge (tonte, taille, désherbage des allées). Plantations annuelles (potager, fleurs en pot, jardinières non fixées) : libres. Plantations pérennes (arbres, arbustes, haie) : accord écrit du bailleur recommandé, sinon le propriétaire peut exiger remise en état à la sortie. Modifications lourdes (terrasse, abri de jardin >5 m², dalle béton, piscine) : accord écrit obligatoire, et déclaration ou permis selon surface. Compost en bac fermé : autorisé sauf clause contraire au bail.</p>
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
					"name": "Quand semer les tomates en France en 2026 ?",
					"acceptedAnswer": {
						"@type": "Answer",
						"text": "Semis intérieur en godet : fin février à fin mars selon la région (plus tôt au sud). Repiquage au jardin ou en pot extérieur : après les Saints de Glace (11, 12 et 13 mai — Mamert, Pancrace, Servais) en zone tempérée et continentale, à partir du 20-25 avril en zone méditerranéenne avec voile P17 de secours, début juin en montagne (>600 m). Une plantation prématurée bloque la croissance dix jours."
					}
				},
				{
					"@type": "Question",
					"name": "Quel volume de pot minimum pour réussir un potager balcon ?",
					"acceptedAnswer": {
						"@type": "Answer",
						"text": "Radis et aromatiques : 5-15 L. Salades et fraisiers : 15-20 L. Tomates, poivrons, aubergines : 40 L par pied minimum, profondeur 40 cm — en dessous, le rendement chute de 50-70 %. Courgette compacte : 40-50 L. Pomme de terre primeur en sac : 40-60 L. Melon, courge coureuse, pastèque, maïs : à oublier sur balcon. Vérifier la charge admissible du balcon (350 kg/m² en règle générale, Eurocode/DTU 43.1) avant d'aligner les pots."
					}
				},
				{
					"@type": "Question",
					"name": "Faut-il l'autorisation du syndic pour des jardinières de balcon ?",
					"acceptedAnswer": {
						"@type": "Answer",
						"text": "Pour des pots posés au sol, aucun texte ne l'interdit et le syndic ne peut s'y opposer. Le règlement de copropriété interdit en général : jardinières fixées en rambarde (chute), écoulement d'eau sur le balcon inférieur, fixations modifiant l'aspect extérieur. Pour une installation lourde (bac maçonné, treillage fixé en façade, sol modifié), passage en AG obligatoire, majorité prévue à l'article 25 de la loi du 10 juillet 1965."
					}
				},
				{
					"@type": "Question",
					"name": "Qu'est-ce qui reste autorisé pour arroser le potager en alerte sécheresse ?",
					"acceptedAnswer": {
						"@type": "Answer",
						"text": "Cela dépend du niveau de l'arrêté préfectoral. En alerte simple, arrosage du potager autorisé hors créneau 11 h-18 h. En alerte renforcée, généralement potager en soirée uniquement (après 20 h), pelouse et massifs interdits. En crise, tout arrosage non vital peut être interdit, y compris potager, à l'exception de l'arrosoir à la main au pied. Sanction : 1 500 € pour un particulier (contravention de 5ᵉ classe, article 131-13-5° du Code pénal), jusqu'à 3 000 € en récidive."
					}
				},
				{
					"@type": "Question",
					"name": "Combien coûte un aménagement de jardin par un paysagiste ?",
					"acceptedAnswer": {
						"@type": "Answer",
						"text": "Honoraires : jardinier-paysagiste auto-entrepreneur 25-45 €/h, entreprise paysagiste 35-65 € HT/h (42-78 € TTC), architecte paysagiste 40-65 €/h, +15-25 % en Île-de-France. Conception seule sur 200-500 m² : 800-2 500 €. Conception + travaux clé en main : 5 000-15 000 €. Postes lourds : terrasse bois 40-90 €/m² posée, dallage pierre 30-105 €/m², allée gravillonnée 25-50 €/m², clôture grillage rigide 15-25 €/ml posé."
					}
				},
				{
					"@type": "Question",
					"name": "Locataire, qu'est-ce qu'on a le droit de modifier dans le jardin ?",
					"acceptedAnswer": {
						"@type": "Answer",
						"text": "Entretien courant à votre charge (tonte, taille, désherbage). Plantations annuelles (potager, fleurs en pot, jardinières non fixées) : libres. Plantations pérennes (arbres, arbustes, haie) : accord écrit du bailleur recommandé, sinon remise en état possible à la sortie. Modifications lourdes (terrasse, abri >5 m², dalle béton, piscine) : accord écrit obligatoire, déclaration ou permis selon surface. Compost en bac fermé : autorisé sauf clause contraire au bail."
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
			<li><a href="/travaux/">Terrasse, allée, abri de jardin : quand le jardin demande des travaux</a></li>
			<li><a href="/architecture/">Extension véranda ou jardin d'hiver : ce que l'orientation change</a></li>
			<li><a href="/decoration/">Décoration extérieure et mobilier de jardin : choisir sans s'épuiser</a></li>
			<li><a href="/immobilier/">Acheter une maison : ce que l'état du jardin révèle du bien</a></li>
			<li><a href="/lifestyle/">Rythme saisonnier au jardin : entretien sans y passer ses week-ends</a></li>
			<li><a href="/compatibilimetre/">Tester la compatibilité de votre projet jardin avec logement, climat et budget</a></li>
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
		<h2 class="wp-block-heading pcs-section__title">Nos partenaires jardin</h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"className":"pcs-section__lead"} -->
		<p class="pcs-section__lead">Sélectionnés pour la qualité des plants, la fiabilité des semences et le service. On choisit nos partenaires jardin comme on choisirait pour notre propre potager — du grainetier historique vosgien à la pépinière familiale du Nord, en passant par les grandes surfaces de bricolage pour l'outillage.</p>
		<!-- /wp:paragraph -->

		<!-- wp:html -->
		<div class="pcs-partners__grid">

			<article class="pcs-partner-card">
				<span class="pcs-partner-card__badge">Partenaire</span>
				<div class="pcs-partner-card__logo">Truffaut</div>
				<h3 class="pcs-partner-card__name">Truffaut</h3>
				<p class="pcs-partner-card__pitch">Jardinerie référence multi-régions, gamme potager et plantes adaptées climat. Repère national pour le test de sol en kit, les amendements et les plants de saison.</p>
				<p class="pcs-partner-card__cta"><a href="https://www.truffaut.com" class="pcs-link-partner" rel="sponsored nofollow noopener">Voir Truffaut</a></p>
			</article>

			<article class="pcs-partner-card">
				<span class="pcs-partner-card__badge">Partenaire</span>
				<div class="pcs-partner-card__logo">Jardiland</div>
				<h3 class="pcs-partner-card__name">Jardiland</h3>
				<p class="pcs-partner-card__pitch">Réseau large, conseils débutant, gamme aromatiques et carrés potager. Bon point d'entrée pour démarrer un balcon ou un premier potager 4-20 m².</p>
				<p class="pcs-partner-card__cta"><a href="https://www.jardiland.com" class="pcs-link-partner" rel="sponsored nofollow noopener">Voir Jardiland</a></p>
			</article>

			<article class="pcs-partner-card">
				<span class="pcs-partner-card__badge">Partenaire</span>
				<div class="pcs-partner-card__logo">Promesse de Fleurs</div>
				<h3 class="pcs-partner-card__name">Promesse de Fleurs</h3>
				<p class="pcs-partner-card__pitch">Pépinière familiale (Houplines, Nord, fondée 1950), vivaces, arbustes, plantes méditerranéennes adaptées climat 2026, expédition partout en France.</p>
				<p class="pcs-partner-card__cta"><a href="https://www.promessedefleurs.com" class="pcs-link-partner" rel="sponsored nofollow noopener">Voir Promesse de Fleurs</a></p>
			</article>

			<article class="pcs-partner-card">
				<span class="pcs-partner-card__badge">Partenaire</span>
				<div class="pcs-partner-card__logo">Graines Baumaux</div>
				<h3 class="pcs-partner-card__name">Graines Baumaux</h3>
				<p class="pcs-partner-card__pitch">Grainetier vosgien (Mazirot, fondé 1943), gamme potagère la plus étoffée d'Europe, mainteneur officiel de plus de 390 variétés anciennes. Référence pour les semences fiables.</p>
				<p class="pcs-partner-card__cta"><a href="https://www.graines-baumaux.fr" class="pcs-link-partner" rel="sponsored nofollow noopener">Voir Baumaux</a></p>
			</article>

			<article class="pcs-partner-card">
				<span class="pcs-partner-card__badge">Partenaire</span>
				<div class="pcs-partner-card__logo">Leroy Merlin</div>
				<h3 class="pcs-partner-card__name">Leroy Merlin extérieur</h3>
				<p class="pcs-partner-card__pitch">Carrés potager, terreau, récupérateurs d'eau, outillage motorisé. Repère national pour l'aménagement extérieur et les fournitures techniques.</p>
				<p class="pcs-partner-card__cta"><a href="https://www.leroymerlin.fr/c/jardin-1300101144" class="pcs-link-partner" rel="sponsored nofollow noopener">Voir Leroy Merlin</a></p>
			</article>

			<article class="pcs-partner-card">
				<span class="pcs-partner-card__badge">Partenaire</span>
				<div class="pcs-partner-card__logo">Greenweez</div>
				<h3 class="pcs-partner-card__name">Greenweez</h3>
				<p class="pcs-partner-card__pitch">Bio et éco-conçu : terreaux sans tourbe, récupérateurs d'eau, paillage. Pour jardiner cohérent avec les enjeux climat 2026 (eau, biodiversité, sols vivants).</p>
				<p class="pcs-partner-card__cta"><a href="https://www.greenweez.com" class="pcs-link-partner" rel="sponsored nofollow noopener">Voir Greenweez</a></p>
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
