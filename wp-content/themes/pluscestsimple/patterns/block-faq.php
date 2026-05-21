<?php
/**
 * Title: FAQ — accordéon
 * Slug: pluscestsimple/block-faq
 * Categories: pcs-section
 * Description: 3 à 5 questions/réponses dans un accordéon natif (HTML <details>). Schéma FAQPage compatible si renseigné dans la meta box Données structurées.
 * Inserter: yes
 * Keywords: faq, questions, accordéon
 */
?>
<!-- wp:group {"tagName":"section","align":"wide","className":"pcs-section pcs-section--faq","metadata":{"name":"FAQ"},"templateLock":"contentOnly","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignwide pcs-section pcs-section--faq">

	<!-- wp:paragraph {"className":"pcs-eyebrow","textColor":"accent"} -->
	<p class="pcs-eyebrow has-accent-color has-text-color">Questions fréquentes</p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"level":2,"className":"pcs-section__title"} -->
	<h2 class="wp-block-heading pcs-section__title">Tout ce qu'on nous demande</h2>
	<!-- /wp:heading -->

	<!-- wp:details {"className":"pcs-faq__item"} -->
	<details class="wp-block-details pcs-faq__item"><summary>Question 1 — à remplacer</summary>
		<!-- wp:paragraph -->
		<p>Réponse 1 — à remplacer. Une réponse courte (50-150 mots) qui répond directement à la question.</p>
		<!-- /wp:paragraph -->
	</details>
	<!-- /wp:details -->

	<!-- wp:details {"className":"pcs-faq__item"} -->
	<details class="wp-block-details pcs-faq__item"><summary>Question 2 — à remplacer</summary>
		<!-- wp:paragraph -->
		<p>Réponse 2 — à remplacer.</p>
		<!-- /wp:paragraph -->
	</details>
	<!-- /wp:details -->

	<!-- wp:details {"className":"pcs-faq__item"} -->
	<details class="wp-block-details pcs-faq__item"><summary>Question 3 — à remplacer</summary>
		<!-- wp:paragraph -->
		<p>Réponse 3 — à remplacer.</p>
		<!-- /wp:paragraph -->
	</details>
	<!-- /wp:details -->

</section>
<!-- /wp:group -->
