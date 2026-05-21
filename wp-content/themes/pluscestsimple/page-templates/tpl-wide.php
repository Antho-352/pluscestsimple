<?php
/**
 * Template Name: Pleine largeur (sans titre)
 *
 * Page Gutenberg sans header automatique (ni titre H1 ni breadcrumbs).
 * Le H1 doit être placé dans le contenu via un bloc Titre. Utile pour
 * les landing pages, pages d'accueil custom, ou présentations visuelles
 * où le rendu standard de page.php n'est pas souhaité.
 *
 * @package pluscestsimple
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div id="post-<?php the_ID(); ?>" <?php post_class( 'pcs-page pcs-page--wide' ); ?>>
		<div class="pcs-content pcs-page__content">
			<?php the_content(); ?>
		</div>
	</div>
	<?php
endwhile;

get_footer();
