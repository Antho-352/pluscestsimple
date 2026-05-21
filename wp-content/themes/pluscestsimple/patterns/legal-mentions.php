<?php
if ( ! defined( "ABSPATH" ) ) { exit; }
/**
 * Title: Mentions légales (FR)
 * Slug: arw-pulse/legal-mentions
 * Categories: arw-legal
 * Description: Mentions légales FR — auto-remplies avec les valeurs du site (SIRET, hébergeur, directeur de publication).
 */
$l = function_exists( 'arw_pulse_legal' ) ? arw_pulse_legal() : [];
$host      = wp_parse_url( home_url(), PHP_URL_HOST ) ?: 'site.fr';
$host_clean = preg_replace( '/^www\./i', '', $host );
$site_name = get_bloginfo( 'name' );
$email     = $l['contact_email'] ?? ( 'contact@' . $host_clean );
?>
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Éditeur du site</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p><?php echo esc_html( $l['company_name'] ?? 'Anthony Russo EI' ); ?><br>
<?php echo esc_html( $l['publisher_role'] ?? 'Directeur de la publication' ); ?> : <?php echo esc_html( $l['publisher_name'] ?? 'Anthony Russo' ); ?><br>
SIRET : <?php echo esc_html( $l['siret'] ?? '98497752000019' ); ?><br>
Email : <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></p><!-- /wp:paragraph -->

<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Hébergeur</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p><?php echo esc_html( $l['host_name'] ?? 'OVH' ); ?><br>
<?php echo esc_html( $l['host_address'] ?? '2 rue Kellermann, 59100 Roubaix, France' ); ?><br>
<a href="<?php echo esc_url( $l['host_website'] ?? 'https://www.ovhcloud.com' ); ?>" rel="noopener"><?php echo esc_html( $l['host_website'] ?? 'https://www.ovhcloud.com' ); ?></a></p><!-- /wp:paragraph -->

<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Propriété intellectuelle</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>L'ensemble du contenu publié sur <?php echo esc_html( $site_name ); ?> (textes, images, graphismes, logos, vidéos, icônes) est protégé par le droit d'auteur. Toute reproduction, représentation, modification ou exploitation totale ou partielle sans autorisation écrite préalable est interdite et constitue une contrefaçon sanctionnée par les articles L.335-2 et suivants du Code de la propriété intellectuelle.</p><!-- /wp:paragraph -->

<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Liens hypertextes</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Le site peut contenir des liens vers des sites tiers. <?php echo esc_html( $site_name ); ?> ne peut être tenu responsable du contenu ou de la disponibilité de ces sites externes.</p><!-- /wp:paragraph -->

<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Responsabilité</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Les informations publiées sur <?php echo esc_html( $site_name ); ?> sont données à titre indicatif. Malgré le soin apporté à leur exactitude, l'éditeur ne saurait être tenu responsable des erreurs, omissions ou résultats obtenus par un mauvais usage de ces informations.</p><!-- /wp:paragraph -->

<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Contact</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Pour toute question relative aux présentes mentions légales : <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></p><!-- /wp:paragraph -->
