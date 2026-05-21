<?php
/**
 * Front page — page d'accueil.
 *
 * Utilisé si une page statique est désignée comme accueil dans Réglages → Lecture.
 * Le contenu est intégralement édité en Gutenberg (avec patterns proposés par le
 * thème pour les sections types : hero, sélection, newsletter, etc.).
 *
 * Si aucune page n'est désignée comme accueil, index.php prend la main (blog).
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
	<div id="post-<?php the_ID(); ?>" <?php post_class( 'pcs-front-page' ); ?>>
		<div class="pcs-content pcs-front-page__content">
			<?php the_content(); ?>
		</div>
	</div>
	<?php
endwhile;

get_footer();
