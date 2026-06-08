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
			<?php pcs_social_menu(); ?>
		</div>

		<?php
		/* Colonnes de silo (un pilier = une colonne, sous-piliers en liens) + colonne Infos.
		   Dynamique depuis pcs_content_structure() → s'adapte à la réorg des piliers. */
		?>
		<nav class="pcs-footer__cols" aria-label="<?php esc_attr_e( 'Plan du site', 'pluscestsimple' ); ?>">
			<?php
			if ( function_exists( 'pcs_content_structure' ) ) :
				foreach ( pcs_content_structure() as $pcs_slug => $pcs_data ) :
					$pcs_pillar = get_page_by_path( $pcs_slug );
					if ( ! $pcs_pillar instanceof WP_Post ) {
						continue;
					}
					?>
					<div class="pcs-footer__col">
						<h2 class="pcs-footer__col-title"><a href="<?php echo esc_url( get_permalink( $pcs_pillar ) ); ?>"><?php echo esc_html( $pcs_data['label'] ); ?></a></h2>
						<ul>
							<?php foreach ( ( $pcs_data['sub_cats'] ?? [] ) as $pcs_sub_slug => $pcs_sub_label ) :
								$pcs_sub = get_page_by_path( $pcs_slug . '/' . $pcs_sub_slug );
								if ( ! $pcs_sub instanceof WP_Post ) { continue; }
								?>
								<li><a href="<?php echo esc_url( get_permalink( $pcs_sub ) ); ?>"><?php echo esc_html( $pcs_sub_label ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endforeach; endif; ?>

			<div class="pcs-footer__col">
				<h2 class="pcs-footer__col-title"><?php esc_html_e( 'Infos', 'pluscestsimple' ); ?></h2>
				<ul>
					<?php
					$pcs_infos = [
						'annuaire'             => __( 'Annuaire', 'pluscestsimple' ),
						'travailler-avec-nous' => __( 'Partenariats', 'pluscestsimple' ),
						'contact'              => __( 'Contact', 'pluscestsimple' ),
						'plan-du-site'         => __( 'Plan du site', 'pluscestsimple' ),
						'mentions-legales'     => __( 'Mentions légales', 'pluscestsimple' ),
					];
					foreach ( $pcs_infos as $pcs_p => $pcs_lbl ) {
						$pcs_pg = get_page_by_path( $pcs_p );
						if ( $pcs_pg instanceof WP_Post ) {
							printf( '<li><a href="%s">%s</a></li>', esc_url( get_permalink( $pcs_pg ) ), esc_html( $pcs_lbl ) );
						}
					}
					?>
				</ul>
			</div>
		</nav>

	</div>

	<div class="pcs-container pcs-footer__bottom">
		<p class="pcs-footer__copy">© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
		<p><a class="pcs-consent-open" href="#"><?php esc_html_e( 'Préférences cookies', 'pluscestsimple' ); ?></a></p>
	</div>

</footer>

<?php wp_footer(); ?>
</body>
</html>
