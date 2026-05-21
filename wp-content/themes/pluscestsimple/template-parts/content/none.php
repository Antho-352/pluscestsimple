<?php
/**
 * Affichage pour les états sans résultat (404 partiel, search vide, archive vide).
 *
 * @package pluscestsimple
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="pcs-no-results">
	<h2 class="pcs-no-results__title"><?php esc_html_e( 'Aucun résultat', 'pluscestsimple' ); ?></h2>

	<?php if ( is_search() ) : ?>
		<p><?php esc_html_e( 'Aucun article ne correspond à votre recherche. Essayez d\'autres mots-clés.', 'pluscestsimple' ); ?></p>
		<?php get_search_form(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Aucun article publié pour le moment.', 'pluscestsimple' ); ?></p>
		<p>
			<a class="pcs-button" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php esc_html_e( 'Retour à l\'accueil', 'pluscestsimple' ); ?>
			</a>
		</p>
	<?php endif; ?>
</section>
