<?php
/**
 * Footer du site : fermeture <main>, footer (brand + nav + colophon), wp_footer.
 *
 * @package pluscestsimple
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
</main><!-- /#pcs-main -->

<footer class="pcs-footer">

	<div class="pcs-container pcs-footer__inner">

		<div class="pcs-footer__brand">
			<?php
			if ( has_custom_logo() ) {
				the_custom_logo();
			} else {
				printf(
					'<p class="pcs-site-title">%s</p>',
					esc_html( get_bloginfo( 'name' ) )
				);
			}
			$pcs_tagline = get_bloginfo( 'description', 'display' );
			if ( $pcs_tagline ) {
				printf( '<p class="pcs-footer__tagline">%s</p>', esc_html( $pcs_tagline ) );
			}
			?>
		</div>

		<?php pcs_footer_menu(); ?>

		<?php pcs_social_menu(); ?>

	</div>

	<div class="pcs-container pcs-footer__bottom">
		<p class="pcs-footer__copy">© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
		<p><a class="pcs-consent-open" href="#"><?php esc_html_e( 'Préférences cookies', 'pluscestsimple' ); ?></a></p>
	</div>

</footer>

<?php wp_footer(); ?>
</body>
</html>
