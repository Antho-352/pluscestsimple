<?php
if ( ! defined( "ABSPATH" ) ) { exit; }
/**
 * Title: Formulaire de contact
 * Slug: arw-pulse/contact-form
 * Categories: arw-marketing
 * Description: Formulaire natif (name, email, company, phone, message). Stocké en CPT + email wp_mail.
 */
?>
<!-- wp:group {"align":"wide","className":"arw-contact","style":{"spacing":{"margin":{"top":"var:preset|spacing|70"}}},"layout":{"type":"constrained","contentSize":"720px"}} -->
<div class="wp-block-group alignwide arw-contact" style="margin-top:var(--wp--preset--spacing--70)">

	<!-- wp:heading {"level":2,"style":{"typography":{"fontSize":"var:preset|font-size|xl"},"spacing":{"margin":{"bottom":"0.5rem"}}}} -->
	<h2 class="wp-block-heading" style="font-size:var(--wp--preset--font-size--xl);margin-bottom:0.5rem">Contact</h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"style":{"color":{"text":"var:preset|color|muted"},"spacing":{"margin":{"bottom":"var:preset|spacing|50"}}}} -->
	<p style="color:var(--wp--preset--color--muted);margin-bottom:var(--wp--preset--spacing--50)">Réponse sous 48h ouvrées. Pour la presse, utilisez le <a href="/kit-media/">kit média</a>.</p>
	<!-- /wp:paragraph -->

	<!-- wp:html -->
	<form class="arw-form" data-arw-form>
		<input type="hidden" name="form_type" value="contact">
		<label>Nom
			<input type="text" name="name" required autocomplete="name">
		</label>
		<label>Email
			<input type="email" name="email" required autocomplete="email">
		</label>
		<label>Entreprise (optionnel)
			<input type="text" name="company" autocomplete="organization">
		</label>
		<label>Téléphone (optionnel)
			<input type="tel" name="phone" autocomplete="tel">
		</label>
		<label>Sujet
			<input type="text" name="subject" required>
		</label>
		<label>Message
			<textarea name="message" rows="5" required></textarea>
		</label>
		<input class="arw-form__hp" type="text" name="hp" tabindex="-1" autocomplete="off">
		<button type="submit">Envoyer</button>
		<small style="color:var(--wp--preset--color--muted)">Vos données ne sont utilisées que pour répondre à votre demande. <a href="<?php echo esc_url( get_privacy_policy_url() ?: '/politique-de-confidentialite/' ); ?>">En savoir plus</a>.</small>
		<p data-arw-form-error hidden></p>
	</form>
	<!-- /wp:html -->

</div>
<!-- /wp:group -->
