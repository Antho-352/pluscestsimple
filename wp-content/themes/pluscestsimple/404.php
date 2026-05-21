<?php
/**
 * Page d'erreur 404.
 *
 * @package pluscestsimple
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="pcs-container pcs-404">

	<header class="pcs-404__header">
		<p class="pcs-404__code">404</p>
		<h1 class="pcs-404__title"><?php esc_html_e( 'Page introuvable', 'pluscestsimple' ); ?></h1>
		<p class="pcs-404__lead">
			<?php esc_html_e( 'La page que vous cherchez n\'existe pas ou a été déplacée.', 'pluscestsimple' ); ?>
		</p>
		<p>
			<a class="pcs-button" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php esc_html_e( 'Retour à l\'accueil', 'pluscestsimple' ); ?>
			</a>
		</p>
	</header>

	<section class="pcs-404__search">
		<h2><?php esc_html_e( 'Chercher sur le site', 'pluscestsimple' ); ?></h2>
		<?php get_search_form(); ?>
	</section>

</div>

<?php
get_footer();
