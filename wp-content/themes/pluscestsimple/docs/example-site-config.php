<?php
/**
 * Plugin Name: ARW Pulse — Site Config [NOM DU SITE]
 * Description: Configuration spécifique au site (handles sociaux, textes, overrides).
 *              Copier ce fichier dans wp-content/mu-plugins/ et adapter.
 * Version:     1.0.0
 * Author:      [TON NOM]
 *
 * ╔══════════════════════════════════════════════════════════════════════════╗
 * ║  Ce fichier est un mu-plugin (must-use plugin). Il est chargé            ║
 * ║  automatiquement par WP sans activation. Aucune dépendance au thème      ║
 * ║  arw-pulse : les filtres ci-dessous sont tous optionnels. Si arw-pulse   ║
 * ║  n'est pas actif, ce fichier est inerte.                                 ║
 * ╚══════════════════════════════════════════════════════════════════════════╝
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// ══════════════════════════════════════════════════════════════════════════════
// 1. IDENTITÉ SOCIALE
// ══════════════════════════════════════════════════════════════════════════════

// Handle Twitter/X pour twitter:site meta tag
add_filter( 'arw_pulse_twitter_site', function () {
	return '@citymoto';
} );

// Handle Twitter/X par défaut pour les articles (twitter:creator)
add_filter( 'arw_pulse_twitter_creator', function () {
	return '@citymoto';
} );

// URLs sociales — émises dans le schema Organization (sameAs)
add_filter( 'arw_pulse_organization_same_as', function () {
	return [
		'https://twitter.com/citymoto',
		'https://www.instagram.com/citymoto.fr',
		'https://www.facebook.com/citymoto.fr',
		'https://www.linkedin.com/company/citymoto',
		'https://www.youtube.com/@citymoto',
	];
} );

// ══════════════════════════════════════════════════════════════════════════════
// 2. IMAGE OG PAR DÉFAUT
// ══════════════════════════════════════════════════════════════════════════════

// Fallback OG image quand un article n'a pas d'image à la une
add_filter( 'arw_pulse_default_og_image', function () {
	return home_url( '/wp-content/uploads/og-default.jpg' );
} );

// ══════════════════════════════════════════════════════════════════════════════
// 3. AFFILIATION
// ══════════════════════════════════════════════════════════════════════════════

// Texte du disclaimer d'affiliation (injecté auto en haut des articles qui contiennent des liens sponsorisés)
add_filter( 'arw_pulse_disclosure_text', function () {
	return 'Cet article contient des liens d\'affiliation. Si vous achetez via ces liens, CityMoto peut percevoir une commission sans surcoût pour vous. Cela nous permet de continuer à produire des tests indépendants.';
} );

// Désactiver le disclaimer auto (si tu préfères l'insérer manuellement)
// add_filter( 'arw_pulse_auto_disclosure', '__return_false' );

// ══════════════════════════════════════════════════════════════════════════════
// 4. PRODUITS
// ══════════════════════════════════════════════════════════════════════════════

// Devise par défaut (si non précisée sur le produit)
add_filter( 'arw_pulse_currency', function () {
	return 'EUR';
} );

// Nombre de produits affichés dans la section "L'Essentiel" en home
add_filter( 'arw_pulse_home_products_count', function () {
	return 6;
} );

// ══════════════════════════════════════════════════════════════════════════════
// 5. PERFORMANCE
// ══════════════════════════════════════════════════════════════════════════════

// Fonts custom en plus des Inter + Space Grotesk du thème (URLs absolues woff2)
// add_filter( 'arw_pulse_preload_fonts', function ( $fonts ) {
//     $fonts[] = content_url( '/uploads/fonts/ma-font.woff2' );
//     return $fonts;
// } );

// DNS prefetch pour domaines externes critiques
add_filter( 'arw_pulse_dns_prefetch', function ( $hints ) {
	$hints[] = 'https://www.google-analytics.com';
	$hints[] = 'https://www.googletagmanager.com';
	return $hints;
} );

// ══════════════════════════════════════════════════════════════════════════════
// 6. FORMULAIRES
// ══════════════════════════════════════════════════════════════════════════════

// Email destinataire par type de formulaire
add_filter( 'arw_pulse_form_to', function ( $email, $form_type ) {
	$map = [
		'press'             => 'presse@citymoto.fr',
		'affiliate-inquiry' => 'partenariats@citymoto.fr',
		'contact'           => 'contact@citymoto.fr',
	];
	return $map[ $form_type ] ?? $email;
}, 10, 2 );

// Types de formulaires acceptés (ajouter ou restreindre)
// add_filter( 'arw_pulse_form_types', function ( $types ) {
//     $types[] = 'devis-assurance';
//     return $types;
// } );

// Cap global submissions/h (défaut 60, réduire si traffic faible)
add_filter( 'arw_pulse_global_rate_cap', function () {
	return 40;
} );

// URL de redirection après submit (défaut: /merci-{type}/)
// add_filter( 'arw_pulse_form_redirect', function ( $url, $form_type ) {
//     return '/merci/'; // redirection unique
// }, 10, 2 );

// ══════════════════════════════════════════════════════════════════════════════
// 7. SÉCURITÉ
// ══════════════════════════════════════════════════════════════════════════════

// Contact dans /.well-known/security.txt (défaut: admin_email)
add_filter( 'arw_pulse_security_contact', function () {
	return 'mailto:security@citymoto.fr';
} );

// Override des headers HTTP (si Cloudflare en définit déjà certains, les retirer ici pour éviter les doublons)
// add_filter( 'arw_pulse_security_headers', function ( $headers ) {
//     unset( $headers['Strict-Transport-Security'] );  // si Cloudflare le gère
//     return $headers;
// } );

// ══════════════════════════════════════════════════════════════════════════════
// 8. SEO — overrides avancés
// ══════════════════════════════════════════════════════════════════════════════

// Modifier le schema Article avant émission (ex : ajouter un publisher spécifique, une note)
// add_filter( 'arw_pulse_article_schema', function ( $schema, $post ) {
//     $schema['isAccessibleForFree'] = true;
//     $schema['copyrightYear']       = get_the_date( 'Y', $post );
//     return $schema;
// }, 10, 2 );

// Modifier le schema Product
// add_filter( 'arw_pulse_product_schema', function ( $schema, $post ) {
//     $schema['category'] = 'Équipement moto';
//     return $schema;
// }, 10, 2 );

// Modifier les breadcrumbs
// add_filter( 'arw_pulse_breadcrumbs', function ( $crumbs ) {
//     // Ex : renommer "Accueil" en "CityMoto"
//     if ( isset( $crumbs[0] ) && $crumbs[0]['name'] === 'Accueil' ) {
//         $crumbs[0]['name'] = 'CityMoto';
//     }
//     return $crumbs;
// } );

// ══════════════════════════════════════════════════════════════════════════════
// 9. FONCTIONNALITÉS — activer/désactiver des modules
// ══════════════════════════════════════════════════════════════════════════════

// Forcer la désactivation des Produits (sites non-affiliés : cuisine, formation, travaux…)
// Le filtre l'emporte sur la case Settings → ARW Pulse.
// add_filter( 'arw_pulse_enable_products', '__return_false' );

// ══════════════════════════════════════════════════════════════════════════════
// 10. CONTENU DES PATTERNS DÉMO — override par site
// ══════════════════════════════════════════════════════════════════════════════

// Hero d'accueil — tout le contenu textuel + visuel est configurable.
add_filter( 'arw_pulse_hero_front_content', function ( $c ) {
	return array_merge( $c, [
		'eyebrow'       => '№01 · Le média',
		'date_text'     => 'Édition 2026',
		'title_html'    => 'Rouler en ville,<br><em>informés.</em>',
		'description'   => 'Tests, comparatifs et guides d\'achat pour les motards urbains. Pas de blabla commercial, que de l\'équipement testé sur route.',
		'cta_primary'   => [ 'label' => 'Lire les articles',    'url' => '/blog/' ],
		'cta_secondary' => [ 'label' => 'Voir l\'équipement ↓', 'url' => '#essentiel' ],
		'image_url'     => content_url( '/uploads/hero-image.jpg' ),
		'image_alt'     => 'Moto en ville',
		'image_width'   => 900,
		'image_height'  => 1100,
		'meta_left'     => '12 produits testés · 48 articles · Mise à jour hebdo',
		'meta_right'    => 'Paris · Depuis 2025',
	] );
} );

// Manifesto — texte éditorial (allows <mark>, <em>, <strong>, <br>).
add_filter( 'arw_pulse_manifesto_content', function ( $c ) {
	return array_merge( $c, [
		'eyebrow'   => '№04 · Manifesto',
		'text_html' => 'Nous écrivons pour ceux qui <mark>roulent</mark> en ville. Ceux qui choisissent leur équipement comme on choisit un <mark>outil de travail</mark>. Sans compromis. Sans bullshit.',
		'sig_name'  => 'La rédaction',
		'sig_loc'   => 'Paris · Édition 2026',
	] );
} );

/*
 * Si tu veux override complètement un pattern (structure HTML différente) :
 *
 * add_action( 'init', function () {
 *     unregister_block_pattern( 'arw-pulse/hero-front' );
 *     register_block_pattern( 'arw-pulse/hero-front', [
 *         'title'      => 'Hero — Accueil (site X)',
 *         'categories' => [ 'arw-home' ],
 *         'content'    => '<!-- wp:group -->…<!-- /wp:group -->',
 *     ] );
 * }, 20 );
 */

// ══════════════════════════════════════════════════════════════════════════════
// 10. DIAGNOSTIC (à laisser activé en staging, désactiver en prod)
// ══════════════════════════════════════════════════════════════════════════════

// Log toutes les soumissions de formulaire dans debug.log (utile en staging)
// add_action( 'arw_pulse_form_submitted', function ( $post_id, $req ) {
//     error_log( '[arw-pulse] submission ' . $post_id . ' from ' . $req['email'] );
// }, 10, 2 );
