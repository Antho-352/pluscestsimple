<?php
/**
 * Title: Page catégorie — Immobilier
 * Slug: pluscestsimple/category-immobilier
 * Categories: pcs-page
 * Description: Page pilier Immobilier : intro, bannière, à la une (3), 3 sous-sections (Acheter, Louer & investir, Vendre), bannière mid, tous les articles (12 offset 3), FAQ, partenaires, maillage, disclosure en bas.
 * Inserter: yes
 * Keywords: immobilier, achat, vente, location, DPE, pilier, catégorie
 */
?>
<!-- wp:group {"className":"pcs-category-page","metadata":{"name":"Page catégorie — Immobilier"},"layout":{"type":"constrained"}} -->
<div class="wp-block-group pcs-category-page">

	<!-- wp:heading {"level":1,"className":"pcs-archive__title"} -->
	<h1 class="wp-block-heading pcs-archive__title">Immobilier : acheter, vendre, louer en France en 2026</h1>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"className":"pcs-archive__intro"} -->
	<p class="pcs-archive__intro">L'immobilier français de 2026 n'a plus rien à voir avec celui de 2020. Les taux moyens autour de 3 à 3,5 %, l'apport requis qui monte à 15-20 %, le DPE devenu variable centrale d'achat et de vente, le calendrier d'interdiction de location qui avance — G interdits depuis le 1er janvier 2025, F au 1er janvier 2028, E au 1er janvier 2034 —, une réforme du mode de calcul du DPE entrée en vigueur le 1er janvier 2026 qui fait sortir environ 850 000 logements chauffés à l'électricité du statut de passoire sans un coup de marteau. Côté frais, la hausse des DMTO votée par 82 départements depuis avril 2025 augmente la facture d'achat dans l'ancien (taux porté à 5 % jusqu'au 31 mars 2028). Sur Plus c'est simple, on traite l'immobilier comme une décision de vie, pas un placement. Acheter sa résidence principale, vendre un bien hérité, louer son ancien appartement, comprendre ce qu'on récupère vraiment quand le bailleur garde la caution. Pas de défiscalisation au forceps, pas de placement à la chaîne, pas de "comment doubler son patrimoine". Vous trouverez ici des guides chronologiques par parcours, des coûts réels chiffrés en euros 2026, et la lecture du droit qui s'applique vraiment à vous.</p>
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
		<h2 class="wp-block-heading pcs-section__title">Acheter</h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"className":"pcs-section__lead"} -->
		<p class="pcs-section__lead">Capacité d'emprunt réelle (taux 3-3,5 %, apport 15-20 %, HCSF 35 % maintenu en 2026), parcours chronologique du compromis à l'acte, coût réel notaire 7-8 % dans l'ancien (DMTO total 6,32 % dans 82 départements depuis avril 2025, mesure temporaire jusqu'au 31 mars 2028). PTZ 2026 élargi aux zones B2 et C pour le neuf, plafonds revalorisés. Pour valider votre HCSF avant de chercher un bien, <a href="https://www.pretto.fr" class="pcs-link-partner" rel="sponsored nofollow noopener">Pretto</a> et <a href="https://www.empruntis.com" class="pcs-link-partner" rel="sponsored nofollow noopener">Empruntis</a> proposent du courtage gratuit.</p>
		<!-- /wp:paragraph -->

		<!-- wp:buttons -->
		<div class="wp-block-buttons">
			<!-- wp:button {"className":"is-style-outline"} -->
			<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/immobilier-acheter-cat/">Voir tous les articles Acheter →</a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->

	</section>
	<!-- /wp:group -->

	<!-- wp:pattern {"slug":"pluscestsimple/banner-slot-category-mid"} /-->

	<!-- wp:group {"tagName":"section","align":"wide","className":"pcs-section pcs-section--subcat","layout":{"type":"constrained"}} -->
	<section class="wp-block-group alignwide pcs-section pcs-section--subcat">

		<!-- wp:heading {"level":2,"className":"pcs-section__title"} -->
		<h2 class="wp-block-heading pcs-section__title">Louer & investir</h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"className":"pcs-section__lead"} -->
		<p class="pcs-section__lead">Bailleur dans un marché tendu en 2026 : G interdits à la location depuis le 1er janvier 2025 (ni nouveau bail, ni renouvellement, ni reconduction tacite), F au 1er janvier 2028, E au 1er janvier 2034. Audit énergétique obligatoire à la vente pour les maisons individuelles classées E, F ou G en métropole (E depuis le 1er janvier 2025). Pour publier une annonce, <a href="https://www.seloger.com" class="pcs-link-partner" rel="sponsored nofollow noopener">SeLoger</a> et <a href="https://www.pap.fr" class="pcs-link-partner" rel="sponsored nofollow noopener">PAP</a> couvrent l'essentiel.</p>
		<!-- /wp:paragraph -->

		<!-- wp:buttons -->
		<div class="wp-block-buttons">
			<!-- wp:button {"className":"is-style-outline"} -->
			<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/immobilier-louer-investir-cat/">Voir tous les articles Louer & investir →</a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->

	</section>
	<!-- /wp:group -->

	<!-- wp:group {"tagName":"section","align":"wide","className":"pcs-section pcs-section--subcat","layout":{"type":"constrained"}} -->
	<section class="wp-block-group alignwide pcs-section pcs-section--subcat">

		<!-- wp:heading {"level":2,"className":"pcs-section__title"} -->
		<h2 class="wp-block-heading pcs-section__title">Vendre</h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"className":"pcs-section__lead"} -->
		<p class="pcs-section__lead">Méthode DVF (base <a href="https://www.data.gouv.fr/fr/datasets/demandes-de-valeurs-foncieres/">Demandes de Valeurs Foncières</a>, gratuite) pour estimer un prix de marché réel. Travaux qui rapportent au m², négociation possible sur un bien classé F ou G (l'audit obligatoire chiffre les travaux que l'acheteur fera). Pour la cartographie 3D et la vue satellite avant déplacement, <a href="https://www.bienici.com" class="pcs-link-partner" rel="sponsored nofollow noopener">Bien'ici</a>. Ressources gratuites : <a href="https://www.immobilier.notaires.fr/fr/frais-de-notaire">Notaires.fr</a>, <a href="https://www.service-public.fr/">Service-Public.fr</a>.</p>
		<!-- /wp:paragraph -->

		<!-- wp:buttons -->
		<div class="wp-block-buttons">
			<!-- wp:button {"className":"is-style-outline"} -->
			<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/immobilier-vendre-cat/">Voir tous les articles Vendre →</a></div>
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
		<h2 class="wp-block-heading pcs-section__title">Immobilier : tout ce qu'on nous demande</h2>
		<!-- /wp:heading -->

		<!-- wp:details {"className":"pcs-faq__item"} -->
		<details class="wp-block-details pcs-faq__item"><summary>Quel est le plafond du PTZ neuf en zone B2 en 2026 ?</summary>
			<!-- wp:paragraph -->
			<p>En zone B2 (villes moyennes), le PTZ neuf 2026 finance jusqu'à 30 % du coût total de l'opération. Les plafonds de revenus en zone B2 démarrent autour de 26 000 € pour une personne seule et augmentent selon la composition du ménage (barèmes en vigueur depuis le décret du 29 mars 2025, application 1er avril 2025, plafonds revalorisés +8-13 % selon zones). Le plafond d'opération dépend également de la composition du ménage. Vérifiez votre tranche exacte sur <a href="https://www.service-public.fr/particuliers/vosdroits/F10871">Service-Public.fr — PTZ</a> ou auprès de votre <a href="https://www.anil.org/lanil-et-les-adil/">ADIL départementale</a>, service public et gratuit.</p>
			<!-- /wp:paragraph -->
		</details>
		<!-- /wp:details -->

		<!-- wp:details {"className":"pcs-faq__item"} -->
		<details class="wp-block-details pcs-faq__item"><summary>Combien coûte vraiment un audit énergétique en 2026 ?</summary>
			<!-- wp:paragraph -->
			<p>Le prix moyen national constaté est d'environ 750 € pour une maison de 90 à 120 m², avec une fourchette typique de 500 à 1 500 € selon la surface, la complexité du logement et la région. L'audit est obligatoire à la vente pour les maisons individuelles (et immeubles entiers) classés E, F ou G en métropole — F et G depuis avril 2023, E depuis le 1er janvier 2025. Il propose deux scénarios de travaux chiffrés permettant d'atteindre au moins la classe B et reste valable 5 ans. Il ne s'applique pas aux appartements en copropriété, ni à l'Outre-mer (calendrier décalé).</p>
			<!-- /wp:paragraph -->
		</details>
		<!-- /wp:details -->

		<!-- wp:details {"className":"pcs-faq__item"} -->
		<details class="wp-block-details pcs-faq__item"><summary>Puis-je encore louer un logement classé G en 2026 ?</summary>
			<!-- wp:paragraph -->
			<p>Non pour un nouveau bail. Les logements G sont interdits à la location depuis le 1er janvier 2025 — ni nouveau bail, ni renouvellement, ni reconduction tacite. Les baux en cours signés avant 2025 peuvent se poursuivre jusqu'à leur fin contractuelle, mais ne peuvent être renouvelés. Les F basculeront au 1er janvier 2028, les E au 1er janvier 2034. Cas particulier 2026 : si votre logement est chauffé à l'électricité, faites mettre à jour gratuitement votre DPE après le 1er janvier 2026 — la réforme du coefficient électrique (passage de 2,3 à 1,9) fait sortir environ 850 000 logements du statut passoire sans aucun travaux. Mise à jour possible via l'<a href="https://observatoire-dpe-audit.ademe.fr/">Observatoire DPE-Audit de l'Ademe</a>.</p>
			<!-- /wp:paragraph -->
		</details>
		<!-- /wp:details -->

		<!-- wp:details {"className":"pcs-faq__item"} -->
		<details class="wp-block-details pcs-faq__item"><summary>Combien coûtent vraiment les frais de notaire en 2026 ?</summary>
			<!-- wp:paragraph -->
			<p>Dans l'ancien : 7 à 8 % du prix de vente. Dans le neuf : 2 à 3 % seulement. L'écart vient principalement des droits de mutation à titre onéreux (DMTO) perçus par les départements. Depuis avril 2025, 82 départements ont voté la hausse du taux départemental de 4,5 % à 5 % (mesure temporaire jusqu'au 31 mars 2028), portant le DMTO total à 6,32 % dans ces départements (parfois affiché 6,31 % selon l'arrondi). Une dizaine de départements ont maintenu le taux à 4,5 %. Exonération possible pour les primo-accédants si votre département l'a votée. Sur ce qu'on appelle "frais de notaire", environ 80 % sont en réalité des taxes reversées à l'État et aux collectivités. Simulateur officiel : <a href="https://www.immobilier.notaires.fr/fr/frais-de-notaire">Immobilier.notaires.fr</a>.</p>
			<!-- /wp:paragraph -->
		</details>
		<!-- /wp:details -->

		<!-- wp:details {"className":"pcs-faq__item"} -->
		<details class="wp-block-details pcs-faq__item"><summary>Quel apport personnel faut-il pour acheter en 2026 ?</summary>
			<!-- wp:paragraph -->
			<p>15 à 20 % du prix d'achat hors frais en 2026, contre environ 10 % avant 2022. Le taux d'endettement reste plafonné à 35 % du revenu net, assurance emprunteur comprise (norme HCSF, confirmée sans assouplissement pour 2026). À cela s'ajoutent les frais de notaire (7-8 % ancien, 2-3 % neuf) que la banque ne finance quasiment jamais. Pour un bien à 250 000 € dans l'ancien, prévoir donc 37 500 à 50 000 € d'apport + environ 17 500 à 20 000 € de frais de notaire = environ 55 000 à 70 000 € de cash mobilisable. Possibilité de débloquer son épargne salariale (intéressement, participation, PEE) pour acquisition de résidence principale (cas de déblocage anticipé prévu par la loi).</p>
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
					"name": "Quel est le plafond du PTZ neuf en zone B2 en 2026 ?",
					"acceptedAnswer": {
						"@type": "Answer",
						"text": "En zone B2 (villes moyennes), le PTZ neuf 2026 finance jusqu'à 30 % du coût total de l'opération. Les plafonds de revenus en zone B2 démarrent autour de 26 000 € pour une personne seule et augmentent selon la composition du ménage (barèmes en vigueur depuis le décret du 29 mars 2025, application 1er avril 2025). Vérifiez votre tranche exacte sur Service-Public.fr ou auprès de votre ADIL départementale."
					}
				},
				{
					"@type": "Question",
					"name": "Combien coûte vraiment un audit énergétique en 2026 ?",
					"acceptedAnswer": {
						"@type": "Answer",
						"text": "Le prix moyen national constaté est d'environ 750 € pour une maison de 90 à 120 m², avec une fourchette typique de 500 à 1 500 € selon la surface, la complexité et la région. L'audit est obligatoire à la vente pour les maisons individuelles classées E, F ou G en métropole : F et G depuis avril 2023, E depuis le 1er janvier 2025. Validité 5 ans. Ne s'applique pas aux appartements en copropriété."
					}
				},
				{
					"@type": "Question",
					"name": "Puis-je encore louer un logement classé G en 2026 ?",
					"acceptedAnswer": {
						"@type": "Answer",
						"text": "Non pour un nouveau bail. Les logements G sont interdits à la location depuis le 1er janvier 2025 (ni nouveau bail, ni renouvellement, ni reconduction tacite). Les baux en cours signés avant 2025 peuvent se poursuivre jusqu'à leur fin. F au 1er janvier 2028, E au 1er janvier 2034. Cas particulier 2026 : la réforme du coefficient électrique du DPE (2,3 vers 1,9) fait sortir environ 850 000 logements chauffés à l'électricité du statut passoire sans travaux."
					}
				},
				{
					"@type": "Question",
					"name": "Combien coûtent vraiment les frais de notaire en 2026 ?",
					"acceptedAnswer": {
						"@type": "Answer",
						"text": "Dans l'ancien : 7 à 8 % du prix de vente. Dans le neuf : 2 à 3 % seulement. Depuis avril 2025, 82 départements ont voté la hausse du taux départemental de 4,5 % à 5 % (mesure temporaire jusqu'au 31 mars 2028), portant le DMTO total à 6,32 % dans ces départements. Exonération possible pour les primo-accédants si le département l'a votée. Environ 80 % des frais sont des taxes reversées à l'État et aux collectivités. Simulateur officiel sur Immobilier.notaires.fr."
					}
				},
				{
					"@type": "Question",
					"name": "Quel apport personnel faut-il pour acheter en 2026 ?",
					"acceptedAnswer": {
						"@type": "Answer",
						"text": "15 à 20 % du prix d'achat hors frais en 2026. Le taux d'endettement reste plafonné à 35 % du revenu net, assurance emprunteur comprise (norme HCSF, confirmée sans assouplissement pour 2026). Pour un bien à 250 000 € dans l'ancien, prévoir 37 500 à 50 000 € d'apport + 17 500 à 20 000 € de frais de notaire = environ 55 000 à 70 000 € de cash mobilisable. Possibilité de débloquer son épargne salariale pour acquisition de résidence principale."
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
			<li><a href="/travaux/">Quand l'achat devient chantier : rénovation lourde, DPE, gros œuvre, isolation</a></li>
			<li><a href="/decoration/">Une fois acheté ou loué : décorer sans casser votre logement</a></li>
			<li><a href="/compatibilimetre/">Évaluer si un bien est compatible avec votre budget, vos besoins et vos contraintes</a></li>
			<li><a href="/architecture/">Acheter une maison ancienne : ce que la façade et la structure révèlent</a></li>
			<li><a href="/jardin/">Extérieur d'une maison nouvellement acquise : potager, terrasse, haie</a></li>
			<li><a href="/lifestyle/">Vivre dans son logement : voisinage, copropriété, charges, vie de quartier</a></li>
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
		<h2 class="wp-block-heading pcs-section__title">Nos partenaires immobilier</h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"className":"pcs-section__lead"} -->
		<p class="pcs-section__lead">Sélectionnés pour la qualité du service, l'indépendance et la transparence. Portails d'annonces, courtiers gratuits, spécialiste rénovation énergétique : on choisit nos partenaires immobilier comme on choisirait pour notre propre achat — sans pousser à la décision.</p>
		<!-- /wp:paragraph -->

		<!-- wp:html -->
		<div class="pcs-partners__grid">

			<article class="pcs-partner-card">
				<span class="pcs-partner-card__badge">Partenaire</span>
				<div class="pcs-partner-card__logo">SeLoger</div>
				<h3 class="pcs-partner-card__name">SeLoger</h3>
				<p class="pcs-partner-card__pitch">Portail d'annonces achat, vente, location, baromètre par ville, recherche avancée. Repère national pour comparer les prix de marché par quartier.</p>
				<p class="pcs-partner-card__cta"><a href="https://www.seloger.com" class="pcs-link-partner" rel="sponsored nofollow noopener">Voir SeLoger</a></p>
			</article>

			<article class="pcs-partner-card">
				<span class="pcs-partner-card__badge">Partenaire</span>
				<div class="pcs-partner-card__logo">PAP</div>
				<h3 class="pcs-partner-card__name">PAP (Particulier à Particulier)</h3>
				<p class="pcs-partner-card__pitch">Annonces de particulier à particulier, modèles de baux téléchargeables, ton anti-agence assumé. Utile pour louer ou vendre sans agence et économiser les frais.</p>
				<p class="pcs-partner-card__cta"><a href="https://www.pap.fr" class="pcs-link-partner" rel="sponsored nofollow noopener">Voir PAP</a></p>
			</article>

			<article class="pcs-partner-card">
				<span class="pcs-partner-card__badge">Partenaire</span>
				<div class="pcs-partner-card__logo">Pretto</div>
				<h3 class="pcs-partner-card__name">Pretto</h3>
				<p class="pcs-partner-card__pitch">Courtier en ligne gratuit, simulation de capacité d'emprunt instantanée, négociation banque. Utile pour valider votre HCSF 35 % avant de chercher un bien.</p>
				<p class="pcs-partner-card__cta"><a href="https://www.pretto.fr" class="pcs-link-partner" rel="sponsored nofollow noopener">Voir Pretto</a></p>
			</article>

			<article class="pcs-partner-card">
				<span class="pcs-partner-card__badge">Partenaire</span>
				<div class="pcs-partner-card__logo">Empruntis</div>
				<h3 class="pcs-partner-card__name">Empruntis</h3>
				<p class="pcs-partner-card__pitch">Comparateur de crédit immobilier et assurance emprunteur, courtage gratuit. Bon complément à Pretto pour cross-checker la meilleure offre bancaire.</p>
				<p class="pcs-partner-card__cta"><a href="https://www.empruntis.com" class="pcs-link-partner" rel="sponsored nofollow noopener">Voir Empruntis</a></p>
			</article>

			<article class="pcs-partner-card">
				<span class="pcs-partner-card__badge">Partenaire</span>
				<div class="pcs-partner-card__logo">Effy</div>
				<h3 class="pcs-partner-card__name">Effy</h3>
				<p class="pcs-partner-card__pitch">Réseau d'artisans RGE certifiés, devis travaux rénovation énergétique, audit énergétique réglementaire à la vente. Utile pour sortir d'un DPE F ou G avant de mettre en vente.</p>
				<p class="pcs-partner-card__cta"><a href="https://www.effy.fr" class="pcs-link-partner" rel="sponsored nofollow noopener">Voir Effy</a></p>
			</article>

			<article class="pcs-partner-card">
				<span class="pcs-partner-card__badge">Partenaire</span>
				<div class="pcs-partner-card__logo">Bien'ici</div>
				<h3 class="pcs-partner-card__name">Bien'ici</h3>
				<p class="pcs-partner-card__pitch">Cartographie immobilière 3D, annonces géolocalisées, vue satellite et plan de masse. Pratique pour visualiser un quartier avant déplacement physique.</p>
				<p class="pcs-partner-card__cta"><a href="https://www.bienici.com" class="pcs-link-partner" rel="sponsored nofollow noopener">Voir Bien'ici</a></p>
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
