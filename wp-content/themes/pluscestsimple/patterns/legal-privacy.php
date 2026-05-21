<?php
if ( ! defined( "ABSPATH" ) ) { exit; }
/**
 * Title: Politique de confidentialité (RGPD)
 * Slug: arw-pulse/legal-privacy
 * Categories: arw-legal
 * Description: Politique de confidentialité conforme RGPD. Auto-remplie avec les données du site.
 */
$l = function_exists( 'arw_pulse_legal' ) ? arw_pulse_legal() : [];
$host      = wp_parse_url( home_url(), PHP_URL_HOST ) ?: 'site.fr';
$host_clean = preg_replace( '/^www\./i', '', $host );
$email     = $l['contact_email'] ?? ( 'contact@' . $host_clean );
$site_name = get_bloginfo( 'name' );
?>
<!-- wp:paragraph --><p>La présente politique a pour objet d'informer les utilisateurs de <?php echo esc_html( $site_name ); ?> des données collectées, de leur usage, de leurs droits et de la manière de les exercer, conformément au RGPD et à la loi Informatique et Libertés.</p><!-- /wp:paragraph -->

<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Responsable du traitement</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p><?php echo esc_html( $l['publisher_name'] ?? 'Anthony Russo' ); ?> — SIRET <?php echo esc_html( $l['siret'] ?? '98497752000019' ); ?><br>
Contact : <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></p><!-- /wp:paragraph -->

<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Données collectées</h2><!-- /wp:heading -->
<!-- wp:list --><ul>
<li><strong>Formulaires</strong> : nom, email, téléphone, entreprise, message — pour répondre à votre demande.</li>
<li><strong>Newsletter</strong> : email — pour vous envoyer nos contenus.</li>
<li><strong>Navigation</strong> : adresse IP, user-agent, pages consultées, temps passé — uniquement avec votre consentement (voir Cookies).</li>
<li><strong>Commentaires</strong> : nom, email, IP — quand cette fonctionnalité est activée.</li>
</ul><!-- /wp:list -->

<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Base légale et finalités</h2><!-- /wp:heading -->
<!-- wp:list --><ul>
<li>Exécution d'une demande (formulaire) — intérêt légitime</li>
<li>Inscription newsletter — consentement</li>
<li>Mesure d'audience — consentement (désactivable à tout moment)</li>
<li>Sécurité du site — intérêt légitime</li>
</ul><!-- /wp:list -->

<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Durée de conservation</h2><!-- /wp:heading -->
<!-- wp:list --><ul>
<li>Formulaires : 3 ans après le dernier contact</li>
<li>Newsletter : jusqu'au désabonnement</li>
<li>Logs serveur : 12 mois maximum</li>
<li>Cookies : 13 mois maximum (CNIL)</li>
</ul><!-- /wp:list -->

<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Destinataires</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Vos données ne sont jamais vendues. Elles peuvent être partagées avec nos sous-traitants techniques (hébergeur <?php echo esc_html( $l['host_name'] ?? 'OVH' ); ?>, fournisseur d'envoi d'email) strictement pour l'exploitation du service. Aucun transfert hors UE n'est effectué sans garanties appropriées.</p><!-- /wp:paragraph -->

<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Vos droits</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Vous disposez d'un droit d'accès, de rectification, d'effacement, de limitation, de portabilité, d'opposition et de retrait du consentement. Pour les exercer : <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>. Vous pouvez également introduire une réclamation auprès de la CNIL (<a href="https://www.cnil.fr" rel="noopener">cnil.fr</a>).</p><!-- /wp:paragraph -->

<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Sécurité</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Le site est servi en HTTPS. Les données sont stockées sur des serveurs sécurisés. Les accès sont limités aux personnes autorisées.</p><!-- /wp:paragraph -->
