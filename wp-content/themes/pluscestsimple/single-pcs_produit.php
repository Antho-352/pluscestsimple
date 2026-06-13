<?php
/**
 * Template fiche produit / avis affilié (CPT pcs_produit).
 * Layout conversion inspiré quel-canape.fr : 2 colonnes + sticky CTA + disclosure.
 *
 * @package pluscestsimple
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();

while ( have_posts() ) :
	the_post();
	$pid    = get_the_ID();
	$url    = pcs_prod_meta( $pid, 'url' );
	$cta    = pcs_prod_meta( $pid, 'cta' ) ?: __( 'Voir le produit', 'pluscestsimple' );
	$prix   = pcs_prod_meta( $pid, 'prix' );
	$note   = pcs_prod_meta( $pid, 'note' );
	$marque = pcs_prod_meta( $pid, 'marque' );

	$cta_link = $url
		? '<a class="pcs-prod__cta" href="' . esc_url( $url ) . '" target="_blank" rel="sponsored nofollow noopener">' . esc_html( $cta ) . ' <span aria-hidden="true">→</span></a>'
		: '';
	?>
	<div class="pcs-container pcs-prod" id="post-<?php echo (int) $pid; ?>">

		<?php if ( function_exists( 'pcs_breadcrumbs' ) ) { pcs_breadcrumbs(); } ?>

		<div class="pcs-prod__grid">

			<?php /* ── Colonne gauche : galerie + avis 30s ── */ ?>
			<div class="pcs-prod__left">
				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="pcs-prod__gallery-main"><?php the_post_thumbnail( 'pcs-hero', [ 'class' => 'pcs-prod__img', 'alt' => esc_attr( get_the_title() ) ] ); ?></figure>
				<?php endif; ?>
				<?php $gal = pcs_prod_lines( $pid, 'galerie' ); if ( $gal ) : ?>
				<div class="pcs-prod__thumbs">
					<?php foreach ( $gal as $g ) : ?><img src="<?php echo esc_url( $g ); ?>" alt="" loading="lazy" class="pcs-prod__thumb"><?php endforeach; ?>
				</div>
				<?php endif; ?>

				<?php $avis = pcs_prod_meta( $pid, 'avis30' ); if ( $avis ) : ?>
				<div class="pcs-prod__verdict">
					<p class="pcs-eyebrow"><?php esc_html_e( 'Notre avis en 30 secondes', 'pluscestsimple' ); ?></p>
					<p><?php echo wp_kses_post( wpautop( esc_html( $avis ) ) ); ?></p>
				</div>
				<?php endif; ?>
			</div>

			<?php /* ── Colonne droite : carte d'achat ── */ ?>
			<div class="pcs-prod__right">
				<span class="pcs-prod__badge">★ <?php esc_html_e( 'TEST & AVIS', 'pluscestsimple' ); ?></span>
				<h1 class="pcs-prod__title"><?php the_title(); ?></h1>
				<?php if ( $marque ) : ?><p class="pcs-prod__brand"><?php esc_html_e( 'Par', 'pluscestsimple' ); ?> <strong><?php echo esc_html( $marque ); ?></strong></p><?php endif; ?>

				<div class="pcs-prod__buybox">
					<div class="pcs-prod__pricewrap">
						<?php if ( $prix ) : ?><span class="pcs-prod__price"><?php echo esc_html( $prix ); ?></span><?php endif; ?>
						<?php $pay = pcs_prod_meta( $pid, 'paiement' ); if ( $pay ) : ?><span class="pcs-prod__pay"><?php echo esc_html( $pay ); ?></span><?php endif; ?>
					</div>
					<?php if ( $note ) : ?>
					<div class="pcs-prod__note"><span class="pcs-prod__note-label"><?php esc_html_e( 'Note globale', 'pluscestsimple' ); ?></span><span class="pcs-prod__note-val"><?php echo esc_html( $note ); ?></span></div>
					<?php endif; ?>
					<?php echo $cta_link; // phpcs:ignore ?>
				</div>

				<?php $sn = pcs_prod_pairs( $pid, 'sousnotes' ); if ( $sn ) : ?>
				<div class="pcs-prod__scores">
					<?php foreach ( $sn as [$lab, $sc] ) : $pct = max( 0, min( 100, (float) str_replace( ',', '.', $sc ) * 10 ) ); ?>
					<div class="pcs-prod__score">
						<div class="pcs-prod__score-head"><span><?php echo esc_html( $lab ); ?></span><span><?php echo esc_html( $sc ); ?>/10</span></div>
						<div class="pcs-prod__score-bar"><span style="width:<?php echo esc_attr( $pct ); ?>%"></span></div>
					</div>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>

				<p class="pcs-prod__disclosure"><?php esc_html_e( 'Cette page contient des liens d\'affiliation. Si vous achetez via ces liens, nous percevons une commission sans surcoût pour vous. Nos analyses restent indépendantes.', 'pluscestsimple' ); ?></p>

				<?php $ideal = pcs_prod_lines( $pid, 'idealpour' ); if ( $ideal ) : ?>
				<div class="pcs-prod__card pcs-prod__ideal">
					<p class="pcs-prod__card-title"><?php esc_html_e( 'Idéal pour', 'pluscestsimple' ); ?></p>
					<ul><?php foreach ( $ideal as $i ) : ?><li><?php echo esc_html( $i ); ?></li><?php endforeach; ?></ul>
				</div>
				<?php endif; ?>

				<?php
				$dims = pcs_prod_pairs( $pid, 'dimensions' );
				$cars = pcs_prod_pairs( $pid, 'caracteristiques' );
				foreach ( [ [ __( 'Dimensions', 'pluscestsimple' ), $dims ], [ __( 'Caractéristiques', 'pluscestsimple' ), $cars ] ] as [$t, $rows] ) :
					if ( ! $rows ) { continue; } ?>
				<div class="pcs-prod__card">
					<p class="pcs-prod__card-title"><?php echo esc_html( $t ); ?></p>
					<table class="pcs-prod__spec"><?php foreach ( $rows as [$k, $v] ) : ?><tr><th><?php echo esc_html( $k ); ?></th><td><?php echo esc_html( $v ); ?></td></tr><?php endforeach; ?></table>
				</div>
				<?php endforeach; ?>

				<?php $ret = pcs_prod_meta( $pid, 'retour' ); $gar = pcs_prod_meta( $pid, 'garantie' ); if ( $ret || $gar ) : ?>
				<p class="pcs-prod__warranty"><?php if ( $ret ) : ?>↩ <?php esc_html_e( 'Retour', 'pluscestsimple' ); ?> : <?php echo esc_html( $ret ); ?><?php endif; ?><?php if ( $ret && $gar ) : ?> · <?php endif; ?><?php if ( $gar ) : ?>🛡 <?php echo esc_html( $gar ); ?><?php endif; ?></p>
				<?php endif; ?>
			</div>
		</div>

		<?php /* ── Points forts / faibles ── */ ?>
		<?php $forts = pcs_prod_lines( $pid, 'forts' ); $faibles = pcs_prod_lines( $pid, 'faibles' ); if ( $forts || $faibles ) : ?>
		<div class="pcs-prod__pros-cons">
			<?php if ( $forts ) : ?><div class="pcs-prod__pros"><p class="pcs-prod__card-title">✓ <?php esc_html_e( 'Points forts', 'pluscestsimple' ); ?></p><ul><?php foreach ( $forts as $f ) : ?><li><?php echo esc_html( $f ); ?></li><?php endforeach; ?></ul></div><?php endif; ?>
			<?php if ( $faibles ) : ?><div class="pcs-prod__cons"><p class="pcs-prod__card-title">✕ <?php esc_html_e( 'Points faibles', 'pluscestsimple' ); ?></p><ul><?php foreach ( $faibles as $f ) : ?><li><?php echo esc_html( $f ); ?></li><?php endforeach; ?></ul></div><?php endif; ?>
		</div>
		<?php endif; ?>

		<?php $pourqui = pcs_prod_meta( $pid, 'pourqui' ); if ( $pourqui ) : ?>
		<section class="pcs-prod__section">
			<h2 class="pcs-section__title"><?php esc_html_e( 'Pour qui ce produit est-il fait ?', 'pluscestsimple' ); ?></h2>
			<?php echo wp_kses_post( wpautop( esc_html( $pourqui ) ) ); ?>
		</section>
		<?php endif; ?>

		<?php /* Corps de l'avis (éditeur) */ if ( trim( get_the_content() ) ) : ?>
		<section class="pcs-prod__section pcs-content"><?php the_content(); ?></section>
		<?php endif; ?>

		<?php /* FAQ (accordéon natif <details>, 0 JS) */ $faq = pcs_prod_pairs( $pid, 'faq', '|' ); if ( $faq ) : ?>
		<section class="pcs-prod__section pcs-prod__faq">
			<h2 class="pcs-section__title"><?php esc_html_e( 'Questions fréquentes', 'pluscestsimple' ); ?></h2>
			<?php foreach ( $faq as [$q, $a] ) : if ( '' === $q ) { continue; } ?>
			<details class="pcs-prod__faq-item"><summary><?php echo esc_html( $q ); ?></summary><p><?php echo esc_html( $a ); ?></p></details>
			<?php endforeach; ?>
		</section>
		<?php endif; ?>

	</div><!-- /.pcs-prod -->

	<?php /* ── Barre sticky de conversion ── */ ?>
	<?php if ( $cta_link ) : ?>
	<div class="pcs-prod__sticky">
		<span class="pcs-prod__sticky-name"><?php the_title(); ?></span>
		<?php if ( $prix ) : ?><span class="pcs-prod__sticky-price"><?php echo esc_html( $prix ); ?></span><?php endif; ?>
		<?php echo $cta_link; // phpcs:ignore ?>
	</div>
	<?php endif; ?>

<?php
endwhile;

get_footer();
