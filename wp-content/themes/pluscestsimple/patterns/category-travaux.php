<?php
/**
 * Title: Page catégorie — Travaux
 * Slug: pluscestsimple/category-travaux
 * Categories: pcs-page
 * Description: Page pilier Travaux : intro, bannière, à la une (3), 3 sous-sections (Par pièce, Gros œuvre, Rénovation énergétique), bannière mid, tous les articles (12 offset 3), FAQ, partenaires, maillage, disclosure en bas.
 * Inserter: yes
 * Keywords: travaux, rénovation, pilier, catégorie
 */
?>
<!-- wp:group {"className":"pcs-category-page","metadata":{"name":"Page catégorie — Travaux"},"layout":{"type":"constrained"}} -->
<div class="wp-block-group pcs-category-page">

	<!-- wp:heading {"level":1,"className":"pcs-archive__title"} -->
	<h1 class="wp-block-heading pcs-archive__title">Rénovation maison : comprendre avant d'engager 30 000 €</h1>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"className":"pcs-archive__intro"} -->
	<p class="pcs-archive__intro">La rénovation maison reste le sujet le plus anxiogène du logement français. Un devis qui varie du simple au triple. Un dispositif MaPrimeRénov refondu chaque année qui rend obsolètes la moitié des articles en ligne. Un voisin de copropriété qui bloque vos travaux trois mois. Une maison ancienne qui se dégrade après une isolation moderne mal pensée. Et le contenu qu'on trouve n'aide pas : des fourchettes "250 à 4 000 €/m²" recopiées partout, des guides MaPrimeRénov rédigés en 2023 toujours en première page, des marketplaces qui poussent à demander un devis avant d'avoir compris le projet. Sur Plus c'est simple, on prend le problème à l'envers. On part d'un cas réel — un appartement de 60 m² à rénover à Paris, une maison en pierre de 1900 avec remontées capillaires, un DPE F à sortir avant 2028 — et on explique ce que dit la loi en 2026, ce que coûte vraiment chaque poste, et ce qui peut tuer le chantier. Pas de marketplace déguisée, pas de fourchette inutile. De la décision documentée.</p>
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
		<h2 class="wp-block-heading pcs-section__title">Par pièce</h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"className":"pcs-section__lead"} -->
		<p class="pcs-section__lead">Salle de bains, cuisine, chambre, salon : chaque pièce déclenche un chantier différent — diagnostic réseaux (électricité, plomberie, ventilation) avant la déco, conformité DTU 60.1 plomberie et DTU 25.41 cloisons, copropriété pour les pièces humides. Pour comparer matériaux et outillage en grande surface avant de signer un devis, <a href="https://www.leroymerlin.fr" class="pcs-link-partner" rel="sponsored nofollow noopener">Leroy Merlin</a> et <a href="https://www.castorama.fr" class="pcs-link-partner" rel="sponsored nofollow noopener">Castorama</a> couvrent l'essentiel.</p>
		<!-- /wp:paragraph -->

		<!-- wp:buttons -->
		<div class="wp-block-buttons">
			<!-- wp:button {"className":"is-style-outline"} -->
			<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/travaux-par-piece-cat/">Voir tous les articles Par pièce →</a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->

	</section>
	<!-- /wp:group -->

	<!-- wp:pattern {"slug":"pluscestsimple/banner-slot-category-mid"} /-->

	<!-- wp:group {"tagName":"section","align":"wide","className":"pcs-section pcs-section--subcat","layout":{"type":"constrained"}} -->
	<section class="wp-block-group alignwide pcs-section pcs-section--subcat">

		<!-- wp:heading {"level":2,"className":"pcs-section__title"} -->
		<h2 class="wp-block-heading pcs-section__title">Gros œuvre</h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"className":"pcs-section__lead"} -->
		<p class="pcs-section__lead">Démolir, redresser, isoler : les fondamentaux du chantier qui décident de la suite. Bâti d'avant 1948 : matériaux perspirants obligatoires (chaux, chanvre, fibre de bois), refus du ciment sur pierre et du polystyrène sur murs anciens. Diagnostic ventilation avant chantier. Pour les matériaux pro à prix négociés (15-25 % d'écart vs grande surface sur carrelage, placo, isolant), <a href="https://www.pointp.fr" class="pcs-link-partner" rel="sponsored nofollow noopener">Point.P</a> en négoce. Pour comparer 3 devis sur un même geste structurel, <a href="https://www.ootravaux.fr" class="pcs-link-partner" rel="sponsored nofollow noopener">Ootravaux</a>.</p>
		<!-- /wp:paragraph -->

		<!-- wp:buttons -->
		<div class="wp-block-buttons">
			<!-- wp:button {"className":"is-style-outline"} -->
			<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/travaux-gros-oeuvre-cat/">Voir tous les articles Gros œuvre →</a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->

	</section>
	<!-- /wp:group -->

	<!-- wp:group {"tagName":"section","align":"wide","className":"pcs-section pcs-section--subcat","layout":{"type":"constrained"}} -->
	<section class="wp-block-group alignwide pcs-section pcs-section--subcat">

		<!-- wp:heading {"level":2,"className":"pcs-section__title"} -->
		<h2 class="wp-block-heading pcs-section__title">Rénovation énergétique</h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"className":"pcs-section__lead"} -->
		<p class="pcs-section__lead">MaPrimeRénov 2026 a refondu ses règles : ITE/ITI et chaudières biomasse sorties du parcours par geste, plafonds 30 000 € HT (2 classes DPE gagnées) et 40 000 € HT (3 classes ou plus), Mon Accompagnateur Rénov' obligatoire en parcours accompagné. Au 1er janvier 2026, le coefficient d'énergie primaire de l'électricité passe à 1,9 (contre 2,3) — certains logements gagnent une classe DPE sans rénover. Pour piloter audit + dossier MPR, <a href="https://www.effy.fr" class="pcs-link-partner" rel="sponsored nofollow noopener">Effy</a> et <a href="https://www.hellio.com" class="pcs-link-partner" rel="sponsored nofollow noopener">Hellio</a> gèrent l'accompagnement.</p>
		<!-- /wp:paragraph -->

		<!-- wp:buttons -->
		<div class="wp-block-buttons">
			<!-- wp:button {"className":"is-style-outline"} -->
			<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/travaux-renovation-energetique-cat/">Voir tous les articles Rénovation énergétique →</a></div>
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
		<h2 class="wp-block-heading pcs-section__title">Travaux : tout ce qu'on nous demande</h2>
		<!-- /wp:heading -->

		<!-- wp:details {"className":"pcs-faq__item"} -->
		<details class="wp-block-details pcs-faq__item"><summary>Combien coûte vraiment une rénovation complète de maison en 2026 ?</summary>
			<!-- wp:paragraph -->
			<p>Pour une maison de 100 m², comptez 30 000 € en rénovation légère (peinture, sols, rafraîchissement), 70 000 à 120 000 € en rénovation moyenne (cuisine, salle de bains, électricité, plomberie partielle), et 110 000 à 180 000 € en rénovation complète (gros œuvre repris, réseaux refaits, isolation, menuiseries). Pour une maison ancienne d'avant 1948, ajoutez environ 30 % (contraintes chaux, charpente, désamiantage) — soit 900 à 1 500 €/m². En Île-de-France, ajoutez encore 25 à 40 % sur la main d'œuvre. Méfiez-vous des fourchettes "250 €/m²" : c'est un tarif de rafraîchissement peinture, pas de rénovation.</p>
			<!-- /wp:paragraph -->
		</details>
		<!-- /wp:details -->

		<!-- wp:details {"className":"pcs-faq__item"} -->
		<details class="wp-block-details pcs-faq__item"><summary>Qu'est-ce qui a changé pour MaPrimeRénov en 2026 ?</summary>
			<!-- wp:paragraph -->
			<p>Trois ruptures. (1) L'isolation des murs (ITE et ITI) et les chaudières biomasse ne sont plus financées dans le parcours par geste — uniquement en rénovation d'ampleur. (2) Les plafonds de dépenses éligibles ont été abaissés à 30 000 € HT pour un gain de 2 classes DPE (au lieu de 40 000 €) et 40 000 € HT pour 3 classes ou plus (au lieu de 55 000 €). (3) Le parcours accompagné est désormais réservé aux logements classés E, F ou G et impose un accompagnement par Mon Accompagnateur Rénov'. Règle d'or : déposer la demande avant signature des devis, sinon le droit à l'aide est perdu. Le cumul avec CEE, éco-PTZ et TVA 5,5 % reste possible, plafonné à 80 % du coût total.</p>
			<!-- /wp:paragraph -->
		</details>
		<!-- /wp:details -->

		<!-- wp:details {"className":"pcs-faq__item"} -->
		<details class="wp-block-details pcs-faq__item"><summary>Mon artisan a abandonné le chantier, que faire ?</summary>
			<!-- wp:paragraph -->
			<p>Un arrêt de plus de 15 jours consécutifs sans explication crédible est généralement considéré par la jurisprudence comme un abandon caractérisé. Étape 1 : lettre recommandée avec accusé de réception mettant en demeure de reprendre sous 8 à 15 jours, suspension de tout paiement. Étape 2 : faire constater l'état d'avancement par un commissaire de justice (huissier) — procès-verbal détaillant ce qui est fait et ce qui ne l'est pas, pièce maîtresse de la procédure. Étape 3 : saisir le tribunal judiciaire pour ordonner la reprise sous astreinte, ou être autorisé à faire terminer par une autre entreprise aux frais du défaillant. Avocat obligatoire au-delà de 10 000 €. En cas de liquidation judiciaire, contacter le mandataire et déclarer la créance dans les deux mois.</p>
			<!-- /wp:paragraph -->
		</details>
		<!-- /wp:details -->

		<!-- wp:details {"className":"pcs-faq__item"} -->
		<details class="wp-block-details pcs-faq__item"><summary>Quels travaux nécessitent un vote en assemblée générale de copropriété ?</summary>
			<!-- wp:paragraph -->
			<p>Tout ce qui touche aux parties communes ou modifie l'aspect extérieur passe en AG : fenêtres côté rue, climatiseur visible depuis la voie publique, modification de volet, percement de façade pour VMC, abattage d'une cloison touchant un mur porteur commun, modification de sols si nuisance acoustique. Vote à la majorité absolue (article 25 de la loi du 10 juillet 1965), ou double majorité (article 26) en cas d'appropriation d'une partie commune. Demande à l'ordre du jour via le syndic, accompagnée de plans, devis détaillés, attestation d'assurance décennale et dommage-ouvrage le cas échéant. Les travaux purement privatifs (peinture, déco intérieure) ne nécessitent aucune autorisation.</p>
			<!-- /wp:paragraph -->
		</details>
		<!-- /wp:details -->

		<!-- wp:details {"className":"pcs-faq__item"} -->
		<details class="wp-block-details pcs-faq__item"><summary>Quelle différence entre garantie de parfait achèvement, biennale et décennale ?</summary>
			<!-- wp:paragraph -->
			<p>Trois garanties légales empilées. Parfait achèvement (1 an, article 1792-6 du Code civil) : non-conformités du procès-verbal de réception ou apparues dans l'année, réparation gratuite par l'entreprise. Biennale de bon fonctionnement (2 ans, article 1792-3) : éléments d'équipement dissociables — robinetterie, volets roulants, plaque encastrée, chaudière. Décennale (10 ans, article 1792) : tout désordre compromettant la solidité de l'ouvrage ou le rendant impropre à sa destination (fissure structurelle, infiltration toiture, défaut d'étanchéité). Indispensable : vérifier l'attestation décennale datée de l'année du chantier avant signature, et souscrire une assurance dommage-ouvrage (obligatoire pour le maître d'ouvrage particulier qui fait construire ou rénove lourdement).</p>
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
					"name": "Combien coûte une rénovation complète en 2026 ?",
					"acceptedAnswer": {
						"@type": "Answer",
						"text": "Maison 100 m² : 30 000 € (légère), 70-120 k€ (moyenne), 110-180 k€ (complète). Ancienne <1948 : +30 %. IDF : +25 à 40 % sur main d'œuvre."
					}
				},
				{
					"@type": "Question",
					"name": "Qu'a changé MaPrimeRénov en 2026 ?",
					"acceptedAnswer": {
						"@type": "Answer",
						"text": "ITE/ITI et chaudières biomasse hors parcours geste. Plafonds 30 k€ HT (+2 classes) / 40 k€ HT (+3 classes). Parcours accompagné réservé DPE E/F/G avec Mon Accompagnateur Rénov'."
					}
				},
				{
					"@type": "Question",
					"name": "Mon artisan a abandonné le chantier, que faire ?",
					"acceptedAnswer": {
						"@type": "Answer",
						"text": "Arrêt >15 jours = abandon caractérisé. 1) Lettre AR de mise en demeure 8-15j. 2) Constat huissier. 3) Tribunal judiciaire. Avocat obligatoire >10 000 €."
					}
				},
				{
					"@type": "Question",
					"name": "Quels travaux nécessitent un vote en AG ?",
					"acceptedAnswer": {
						"@type": "Answer",
						"text": "Parties communes ou aspect extérieur : fenêtres côté rue, climatiseur visible, percement façade. Majorité absolue (art. 25 loi du 10 juillet 1965) ou double majorité (art. 26) si appropriation."
					}
				},
				{
					"@type": "Question",
					"name": "Parfait achèvement, biennale, décennale : différences ?",
					"acceptedAnswer": {
						"@type": "Answer",
						"text": "Parfait achèvement (1 an, art. 1792-6 Code civil) : non-conformités. Biennale (2 ans, art. 1792-3) : équipements dissociables. Décennale (10 ans, art. 1792) : solidité de l'ouvrage."
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
			<li><a href="/compatibilimetre/">Vérifier si votre projet est compatible avec budget, logement et réglementation 2026</a></li>
			<li><a href="/decoration/">Quand la rénovation devient déco : finitions, peinture, sols, mobilier sur mesure</a></li>
			<li><a href="/immobilier/">Acheter à rénover : ce que le DPE et l'état du bâti annoncent comme budget</a></li>
			<li><a href="/annuaire/">Trouver un artisan RGE pour la rénovation énergétique</a></li>
			<li><a href="/architecture/">Quand l'ampleur des travaux justifie un architecte : seuil 150 m², permis, mission complète</a></li>
			<li><a href="/jardin/">Terrasse, allée, abri de jardin : quand le jardin demande des travaux</a></li>
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
		<h2 class="wp-block-heading pcs-section__title">Nos partenaires travaux</h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"className":"pcs-section__lead"} -->
		<p class="pcs-section__lead">Sélectionnés pour la qualité, la transparence et le SAV. On choisit nos partenaires comme on choisirait pour notre propre chantier — grande surface, spécialistes énergie, négoce pro et plateformes de mise en relation.</p>
		<!-- /wp:paragraph -->

		<!-- wp:html -->
		<div class="pcs-partners__grid">

			<article class="pcs-partner-card">
				<span class="pcs-partner-card__badge">Partenaire</span>
				<div class="pcs-partner-card__logo">Leroy Merlin</div>
				<h3 class="pcs-partner-card__name">Leroy Merlin</h3>
				<p class="pcs-partner-card__pitch">Grande surface bricolage, large catalogue, retrait magasin, gammes pro accessibles aux particuliers. Repère national pour comparer matériaux et outillage avant chantier.</p>
				<p class="pcs-partner-card__cta"><a href="https://www.leroymerlin.fr" class="pcs-link-partner" rel="sponsored nofollow noopener">Voir Leroy Merlin</a></p>
			</article>

			<article class="pcs-partner-card">
				<span class="pcs-partner-card__badge">Partenaire</span>
				<div class="pcs-partner-card__logo">Effy</div>
				<h3 class="pcs-partner-card__name">Effy</h3>
				<p class="pcs-partner-card__pitch">Spécialiste rénovation énergétique : audit, dossiers MaPrimeRénov, réseau d'artisans RGE certifiés. Utile pour les pompes à chaleur et les rénovations d'ampleur avec Mon Accompagnateur Rénov'.</p>
				<p class="pcs-partner-card__cta"><a href="https://www.effy.fr" class="pcs-link-partner" rel="sponsored nofollow noopener">Voir Effy</a></p>
			</article>

			<article class="pcs-partner-card">
				<span class="pcs-partner-card__badge">Partenaire</span>
				<div class="pcs-partner-card__logo">Hellio</div>
				<h3 class="pcs-partner-card__name">Hellio</h3>
				<p class="pcs-partner-card__pitch">Délégataire CEE pour pompe à chaleur et isolation, accompagnement administratif complet. Mandataire Anah depuis 2020, réseau d'artisans RGE qualifiés.</p>
				<p class="pcs-partner-card__cta"><a href="https://www.hellio.com" class="pcs-link-partner" rel="sponsored nofollow noopener">Voir Hellio</a></p>
			</article>

			<article class="pcs-partner-card">
				<span class="pcs-partner-card__badge">Partenaire</span>
				<div class="pcs-partner-card__logo">Ootravaux</div>
				<h3 class="pcs-partner-card__name">Ootravaux</h3>
				<p class="pcs-partner-card__pitch">Mise en relation avec artisans qualifiés, devis comparés gratuits, suivi qualité du chantier. Utile pour comparer 3 devis sur un même geste (combles, fenêtres, sols).</p>
				<p class="pcs-partner-card__cta"><a href="https://www.ootravaux.fr" class="pcs-link-partner" rel="sponsored nofollow noopener">Voir Ootravaux</a></p>
			</article>

			<article class="pcs-partner-card">
				<span class="pcs-partner-card__badge">Partenaire</span>
				<div class="pcs-partner-card__logo">Point.P</div>
				<h3 class="pcs-partner-card__name">Point.P</h3>
				<p class="pcs-partner-card__pitch">Négoce pro matériaux, prix négociés, plus de 800 agences en France. À comparer systématiquement avec une grande surface pour décrocher 15 à 25 % d'écart sur le carrelage, le placo ou l'isolant.</p>
				<p class="pcs-partner-card__cta"><a href="https://www.pointp.fr" class="pcs-link-partner" rel="sponsored nofollow noopener">Voir Point.P</a></p>
			</article>

			<article class="pcs-partner-card">
				<span class="pcs-partner-card__badge">Partenaire</span>
				<div class="pcs-partner-card__logo">Castorama</div>
				<h3 class="pcs-partner-card__name">Castorama</h3>
				<p class="pcs-partner-card__pitch">Bricolage grand public, click & collect, gamme outillage et matériaux complète. Repère national pour les fournitures techniques et la peinture courante.</p>
				<p class="pcs-partner-card__cta"><a href="https://www.castorama.fr" class="pcs-link-partner" rel="sponsored nofollow noopener">Voir Castorama</a></p>
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
