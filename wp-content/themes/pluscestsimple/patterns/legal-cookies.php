<?php
if ( ! defined( "ABSPATH" ) ) { exit; }
/**
 * Title: Politique cookies
 * Slug: arw-pulse/legal-cookies
 * Categories: arw-legal
 * Description: Explication des cookies utilisés et modalités de gestion du consentement (Consent Mode v2).
 */
$l = function_exists( 'arw_pulse_legal' ) ? arw_pulse_legal() : [];
$email = $l['contact_email'] ?? ( 'contact@' . preg_replace( '/^www\./i', '', wp_parse_url( home_url(), PHP_URL_HOST ) ?: 'site.fr' ) );
?>
<!-- wp:paragraph --><p>Un cookie est un petit fichier déposé sur votre appareil lors de votre visite. Nous les utilisons pour le bon fonctionnement du site et, avec votre consentement, pour la mesure d'audience et la publicité.</p><!-- /wp:paragraph -->

<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Cookies techniques (toujours actifs)</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Indispensables au fonctionnement du site (session, sécurité, préférences). Exemption CNIL, pas de consentement requis.</p><!-- /wp:paragraph -->

<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Cookies de mesure d'audience</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Avec votre consentement, nous utilisons un outil de mesure d'audience pour comprendre comment le site est utilisé et l'améliorer. Les données sont agrégées et ne permettent pas de vous identifier individuellement.</p><!-- /wp:paragraph -->

<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Cookies publicitaires</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Avec votre consentement, certains partenaires peuvent déposer des cookies de mesure et de personnalisation publicitaire. Vous pouvez les refuser ou retirer votre consentement à tout moment.</p><!-- /wp:paragraph -->

<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Gérer mes préférences</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Vous pouvez à tout moment modifier vos choix en cliquant sur le lien ci-dessous ou sur "Préférences cookies" en bas de page.</p><!-- /wp:paragraph -->

<!-- wp:html -->
<p><a href="#" class="arw-consent-open wp-element-button" style="display:inline-block;background:var(--wp--preset--color--foreground);color:var(--wp--preset--color--background);padding:0.75rem 1.25rem;border-radius:var(--wp--custom--radius--sm);text-decoration:none;font-weight:600">Rouvrir les préférences cookies</a></p>
<!-- /wp:html -->

<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Durée de conservation</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Conformément à la recommandation CNIL, le consentement est conservé 13 mois maximum, puis redemandé.</p><!-- /wp:paragraph -->

<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Contact</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Pour toute question : <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></p><!-- /wp:paragraph -->
