<?php
if ( ! defined( "ABSPATH" ) ) { exit; }
/**
 * Title: Newsletter — bloc inline
 * Slug: arw-pulse/newsletter-inline
 * Categories: arw-marketing
 * Description: Formulaire de capture newsletter, stocké en CPT arw_submission (form_type=newsletter).
 */
?>
<!-- wp:group {"align":"wide","className":"arw-newsletter","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","right":"var:preset|spacing|60","bottom":"var:preset|spacing|70","left":"var:preset|spacing|60"},"margin":{"top":"var:preset|spacing|80"}},"border":{"radius":"var:custom|radius|lg"},"color":{"background":"var:preset|color|surface"}},"layout":{"type":"constrained","contentSize":"640px"}} -->
<div class="wp-block-group alignwide arw-newsletter has-surface-background-color has-background" style="border-radius:var(--wp--custom--radius--lg);background-color:var(--wp--preset--color--surface);margin-top:var(--wp--preset--spacing--80);padding-top:var(--wp--preset--spacing--70);padding-right:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--60)">

	<!-- wp:paragraph {"style":{"typography":{"fontSize":"var:preset|font-size|sm","letterSpacing":"0.2em","textTransform":"uppercase"},"color":{"text":"var:preset|color|accent"}}} -->
	<p style="color:var(--wp--preset--color--accent);font-size:var(--wp--preset--font-size--sm);letter-spacing:0.2em;text-transform:uppercase">Newsletter</p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"level":2,"style":{"typography":{"fontSize":"var:preset|font-size|xl"}}} -->
	<h2 class="wp-block-heading" style="font-size:var(--wp--preset--font-size--xl)">Un résumé clair, chaque semaine.</h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"style":{"color":{"text":"var:preset|color|muted"}}} -->
	<p style="color:var(--wp--preset--color--muted)">Sélection d'articles, bons plans, nouveautés. Pas de spam, désabonnement en 1 clic.</p>
	<!-- /wp:paragraph -->

	<!-- wp:html -->
	<form class="arw-form" data-arw-form>
		<input type="hidden" name="form_type" value="newsletter">
		<label for="arw-nl-email">Email
			<input id="arw-nl-email" type="email" name="email" required autocomplete="email" placeholder="vous@email.fr">
		</label>
		<input class="arw-form__hp" type="text" name="hp" tabindex="-1" autocomplete="off">
		<button type="submit">S'inscrire</button>
		<small style="color:var(--wp--preset--color--muted)">En vous inscrivant, vous acceptez notre <a href="<?php echo esc_url( get_privacy_policy_url() ?: '/politique-de-confidentialite/' ); ?>">politique de confidentialité</a>.</small>
		<p data-arw-form-error hidden></p>
	</form>
	<!-- /wp:html -->

</div>
<!-- /wp:group -->
