<?php
/**
 * Template : fiche unique pcs_etablissement.
 *
 * Sections : header (nom + badges) · adresse + carte · contact + horaires
 * · sources + dernière vérif · attribution OSM.
 *
 * @package PCS_Directory
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();

while ( have_posts() ) :
	the_post();

	$pid       = (int) get_the_ID();
	$adresse   = (string) get_post_meta( $pid, '_pcs_etab_adresse', true );
	$cp        = (string) get_post_meta( $pid, '_pcs_etab_code_postal', true );
	$ville     = (string) get_post_meta( $pid, '_pcs_etab_ville', true );
	$lat       = (float)  get_post_meta( $pid, '_pcs_etab_lat', true );
	$lng       = (float)  get_post_meta( $pid, '_pcs_etab_lng', true );
	$tel       = (string) get_post_meta( $pid, '_pcs_etab_telephone', true );
	$site      = (string) get_post_meta( $pid, '_pcs_etab_site_web', true );
	$horaires  = (string) get_post_meta( $pid, '_pcs_etab_horaires_text', true );
	$siret     = (string) get_post_meta( $pid, '_pcs_etab_siret', true );
	$sources   = get_post_meta( $pid, '_pcs_etab_sources', true );
	$verified  = (string) get_post_meta( $pid, '_pcs_etab_last_verified', true );
	$featured  = '1' === (string) get_post_meta( $pid, '_pcs_etab_is_featured', true );

	$type_term = null;
	$terms     = get_the_terms( $pid, PCS_DIR_TAX_TYPE );
	if ( is_array( $terms ) && ! empty( $terms ) ) {
		$type_term = $terms[0];
	}
	?>

	<main id="primary" class="site-main pcs-directory pcs-directory--single">
		<article class="pcs-directory__article">

			<header class="pcs-directory__article-header">
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="pcs-directory__hero"><?php the_post_thumbnail( 'large', [ 'loading' => 'eager' ] ); ?></div>
				<?php endif; ?>

				<div class="pcs-directory__badges">
					<?php if ( $type_term ) : ?>
						<a class="pcs-directory__badge pcs-directory__badge--type" href="<?php echo esc_url( get_term_link( $type_term ) ); ?>">
							<?php echo esc_html( $type_term->name ); ?>
						</a>
					<?php endif; ?>
					<?php if ( $featured ) : ?>
						<span class="pcs-directory__badge pcs-directory__badge--featured">
							<?php esc_html_e( 'Vérifié', 'pluscestsimple' ); ?>
						</span>
					<?php endif; ?>
				</div>

				<h1 class="pcs-directory__h1"><?php the_title(); ?></h1>

				<?php if ( get_the_excerpt() ) : ?>
					<p class="pcs-directory__lede"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
			</header>

			<?php if ( get_the_content() ) : ?>
				<div class="pcs-directory__content">
					<?php the_content(); ?>
				</div>
			<?php endif; ?>

			<section class="pcs-directory__section pcs-directory__section--adresse">
				<h2><?php esc_html_e( 'Adresse', 'pluscestsimple' ); ?></h2>
				<p class="pcs-directory__address">
					<?php
					$addr_parts = array_filter( [ $adresse, trim( $cp . ' ' . $ville ) ] );
					echo esc_html( implode( ' — ', $addr_parts ) );
					?>
				</p>

				<?php if ( 0.0 !== $lat && 0.0 !== $lng ) : ?>
					<div
						class="pcs-directory-map"
						data-lat="<?php echo esc_attr( (string) $lat ); ?>"
						data-lng="<?php echo esc_attr( (string) $lng ); ?>"
						data-zoom="15"
						aria-label="<?php esc_attr_e( 'Carte de l\'établissement', 'pluscestsimple' ); ?>"
					>
						<noscript>
							<a href="https://www.openstreetmap.org/?mlat=<?php echo esc_attr( (string) $lat ); ?>&amp;mlon=<?php echo esc_attr( (string) $lng ); ?>#map=15/<?php echo esc_attr( (string) $lat ); ?>/<?php echo esc_attr( (string) $lng ); ?>" target="_blank" rel="noopener">
								<?php esc_html_e( 'Voir sur OpenStreetMap', 'pluscestsimple' ); ?>
							</a>
						</noscript>
						<p class="pcs-directory-map__placeholder">
							<?php esc_html_e( 'Carte (à activer)', 'pluscestsimple' ); ?>
						</p>
					</div>
				<?php endif; ?>
			</section>

			<section class="pcs-directory__section pcs-directory__section--contact">
				<h2><?php esc_html_e( 'Contact', 'pluscestsimple' ); ?></h2>
				<ul class="pcs-directory__contact-list">
					<?php if ( '' !== $tel ) : ?>
						<li>
							<strong><?php esc_html_e( 'Téléphone :', 'pluscestsimple' ); ?></strong>
							<a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $tel ) ); ?>">
								<?php echo esc_html( $tel ); ?>
							</a>
						</li>
					<?php endif; ?>
					<?php if ( '' !== $site ) : ?>
						<li>
							<strong><?php esc_html_e( 'Site web :', 'pluscestsimple' ); ?></strong>
							<a href="<?php echo esc_url( $site ); ?>" target="_blank" rel="noopener nofollow">
								<?php echo esc_html( $site ); ?>
							</a>
						</li>
					<?php endif; ?>
					<?php if ( '' !== $horaires ) : ?>
						<li>
							<strong><?php esc_html_e( 'Horaires :', 'pluscestsimple' ); ?></strong>
							<span><?php echo nl2br( esc_html( $horaires ) ); ?></span>
						</li>
					<?php endif; ?>
					<?php if ( '' === $tel && '' === $site && '' === $horaires ) : ?>
						<li><em><?php esc_html_e( 'Aucune information de contact disponible pour le moment.', 'pluscestsimple' ); ?></em></li>
					<?php endif; ?>
				</ul>
			</section>

			<footer class="pcs-directory__sources">
				<p>
					<strong><?php esc_html_e( 'Sources :', 'pluscestsimple' ); ?></strong>
					<?php
					if ( is_array( $sources ) && ! empty( $sources ) ) {
						echo esc_html( implode( ', ', $sources ) );
					} else {
						esc_html_e( 'Non renseignées.', 'pluscestsimple' );
					}
					?>
					<?php if ( '' !== $verified ) : ?>
						·
						<?php
						printf(
							/* translators: %s: date Y-m-d. */
							esc_html__( 'Dernière vérification : %s', 'pluscestsimple' ),
							esc_html( $verified )
						);
						?>
					<?php endif; ?>
					<?php if ( '' !== $siret ) : ?>
						· <span class="pcs-directory__siret">SIRET <?php echo esc_html( $siret ); ?></span>
					<?php endif; ?>
				</p>
				<small>
					<?php
					/* translators: HTML link to OSM copyright. */
					echo wp_kses(
						__( 'Données enrichies : © <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap contributors</a>, ODbL.', 'pluscestsimple' ),
						[ 'a' => [ 'href' => [], 'target' => [], 'rel' => [] ] ]
					);
					?>
				</small>
			</footer>

		</article>
	</main>

<?php endwhile;

get_footer();
?>
