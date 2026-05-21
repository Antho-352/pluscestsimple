<?php
if ( ! defined( "ABSPATH" ) ) { exit; }
/**
 * Title: Kit média
 * Slug: arw-pulse/media-kit
 * Categories: arw-press
 * Description: Kit média complet annonceurs : hero KPIs, à propos, audience, engagement, top articles, social, identité, formats pub, partenaires, téléchargements, contact.
 */
$k = function ( $key, $default = '' ) {
	return function_exists( 'arw_pulse_media_kit' ) ? arw_pulse_media_kit( $key, $default ) : $default;
};
$em = '<span style="color:var(--wp--preset--color--muted);font-style:italic">—</span>';
$kpi = function ( $val ) use ( $em ) { return $val !== '' ? esc_html( $val ) : $em; };

// Top articles resolution
$articles_mode = $k( 'top_articles_mode', 'manual' );
$articles      = [];
if ( $articles_mode === 'auto' ) {
	$q = new WP_Query( [ 'post_status' => 'publish', 'posts_per_page' => 5, 'orderby' => 'date', 'order' => 'DESC' ] );
	while ( $q->have_posts() ) {
		$q->the_post();
		$articles[] = [ 'url' => get_permalink(), 'title' => get_the_title() ];
	}
	wp_reset_postdata();
} else {
	$urls = array_filter( array_map( 'trim', explode( "\n", $k( 'top_articles_urls', '' ) ) ) );
	foreach ( $urls as $url ) {
		$pid = url_to_postid( $url );
		$articles[] = [ 'url' => $url, 'title' => $pid ? get_the_title( $pid ) : $url ];
	}
}

$logos = array_filter( array_map( 'trim', explode( "\n", $k( 'partners_logos', '' ) ) ) );

$primary    = $k( 'primary_hex', '' );
$secondary  = $k( 'secondary_hex', '' );
$font_d     = $k( 'font_display', '' );
$font_b     = $k( 'font_body', '' );
?>
<!-- wp:html -->
<div class="arw-mediakit">

	<!-- 1. Hero -->
	<section class="arw-mediakit__hero">
		<p class="arw-mediakit__eyebrow"><?php echo esc_html( $k( 'tagline', get_bloginfo( 'description' ) ) ); ?></p>
		<h1>Kit média</h1>
		<div class="arw-mediakit__kpis">
			<?php foreach ( [ 1, 2, 3 ] as $i ) : ?>
				<div class="arw-mediakit__kpi">
					<strong><?php echo $kpi( $k( "hero_kpi{$i}_value" ) ); ?></strong>
					<small><?php echo $kpi( $k( "hero_kpi{$i}_label" ) ); ?></small>
				</div>
			<?php endforeach; ?>
		</div>
	</section>

	<!-- 2. À propos -->
	<section class="arw-mediakit__section">
		<h2>À propos</h2>
		<div class="arw-mediakit__grid-2">
			<div>
				<h3>Qui édite</h3>
				<p><?php echo $k( 'about_who' ) !== '' ? nl2br( esc_html( $k( 'about_who' ) ) ) : $em; ?></p>
			</div>
			<div>
				<h3>Angle éditorial</h3>
				<p><?php echo $k( 'about_angle' ) !== '' ? nl2br( esc_html( $k( 'about_angle' ) ) ) : $em; ?></p>
			</div>
		</div>
		<?php if ( $tf = $k( 'topic_focus' ) ) : ?>
			<p class="arw-mediakit__topics"><strong>Thématiques :</strong> <?php echo esc_html( $tf ); ?></p>
		<?php endif; ?>
	</section>

	<!-- 3. Audience -->
	<section class="arw-mediakit__section">
		<h2>Profil audience</h2>
		<div class="arw-mediakit__grid-4">
			<div><h3>Audience</h3><p class="arw-mediakit__metric"><?php echo $kpi( $k( 'audience_monthly' ) ); ?></p></div>
			<div><h3>Âge</h3><p class="arw-mediakit__metric"><?php echo $kpi( $k( 'audience_age' ) ); ?></p></div>
			<div><h3>Genre</h3><p class="arw-mediakit__metric"><?php echo $kpi( $k( 'audience_gender' ) ); ?></p></div>
			<div><h3>Géographie</h3><p class="arw-mediakit__metric"><?php echo $kpi( $k( 'audience_geo' ) ); ?></p></div>
		</div>
		<?php if ( $ai = $k( 'audience_interests' ) ) : ?>
			<p class="arw-mediakit__topics"><strong>Centres d'intérêt :</strong> <?php echo esc_html( $ai ); ?></p>
		<?php endif; ?>
	</section>

	<!-- 4. Engagement -->
	<section class="arw-mediakit__section">
		<h2>Engagement</h2>
		<div class="arw-mediakit__grid-4">
			<div><h3>Temps lecture</h3><p class="arw-mediakit__metric"><?php echo $kpi( $k( 'engagement_time' ) ); ?></p></div>
			<div><h3>Scroll moyen</h3><p class="arw-mediakit__metric"><?php echo $kpi( $k( 'engagement_scroll' ) ); ?></p></div>
			<div><h3>Taux retour</h3><p class="arw-mediakit__metric"><?php echo $kpi( $k( 'engagement_return' ) ); ?></p></div>
			<div><h3>Pages / session</h3><p class="arw-mediakit__metric"><?php echo $kpi( $k( 'engagement_pages_session' ) ); ?></p></div>
		</div>
	</section>

	<!-- 5. Top articles -->
	<section class="arw-mediakit__section">
		<h2>Top articles</h2>
		<?php if ( $articles ) : ?>
			<ol class="arw-mediakit__articles">
				<?php foreach ( $articles as $a ) : ?>
					<li><a href="<?php echo esc_url( $a['url'] ); ?>"><?php echo esc_html( $a['title'] ); ?></a></li>
				<?php endforeach; ?>
			</ol>
		<?php else : echo $em; endif; ?>
	</section>

	<!-- 6. Réseaux sociaux -->
	<section class="arw-mediakit__section">
		<h2>Réseaux sociaux</h2>
		<?php if ( $st = $k( 'social_total' ) ) : ?>
			<p class="arw-mediakit__topics"><strong>Communauté totale :</strong> <?php echo esc_html( $st ); ?></p>
		<?php endif; ?>
		<div class="arw-mediakit__grid-4">
			<?php foreach ( [ 'instagram' => 'Instagram', 'tiktok' => 'TikTok', 'linkedin' => 'LinkedIn', 'youtube' => 'YouTube' ] as $key => $label ) :
				$url = $k( "social_{$key}_url" );
				$followers = $k( "social_{$key}_followers" );
			?>
				<div class="arw-mediakit__social">
					<h3><?php echo esc_html( $label ); ?></h3>
					<p class="arw-mediakit__metric"><?php echo $kpi( $followers ); ?></p>
					<?php if ( $url ) : ?><a href="<?php echo esc_url( $url ); ?>" rel="noopener" target="_blank">Voir →</a><?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</section>

	<!-- 7. Identité visuelle -->
	<section class="arw-mediakit__section">
		<h2>Identité visuelle</h2>
		<div class="arw-mediakit__grid-2">
			<div>
				<h3>Couleurs</h3>
				<?php if ( $primary || $secondary ) : ?>
					<div class="arw-mediakit__swatches">
						<?php if ( $primary ) : ?><span class="arw-mediakit__swatch" style="--c:<?php echo esc_attr( $primary ); ?>"><?php echo esc_html( $primary ); ?></span><?php endif; ?>
						<?php if ( $secondary ) : ?><span class="arw-mediakit__swatch" style="--c:<?php echo esc_attr( $secondary ); ?>"><?php echo esc_html( $secondary ); ?></span><?php endif; ?>
					</div>
				<?php else : echo $em; endif; ?>
			</div>
			<div>
				<h3>Typographie</h3>
				<?php if ( $font_d ) : ?>
					<p style="margin:0"><span style="font-family:var(--wp--preset--font-family--display);font-size:var(--wp--preset--font-size--lg)"><?php echo esc_html( $font_d ); ?></span><br><small style="color:var(--wp--preset--color--muted)">Display</small></p>
				<?php endif; ?>
				<?php if ( $font_b ) : ?>
					<p style="margin:0.5rem 0 0"><span style="font-family:var(--wp--preset--font-family--body)"><?php echo esc_html( $font_b ); ?></span><br><small style="color:var(--wp--preset--color--muted)">Body</small></p>
				<?php endif; ?>
				<?php if ( ! $font_d && ! $font_b ) echo $em; ?>
			</div>
		</div>
	</section>

	<!-- 8. Formats publicitaires -->
	<section class="arw-mediakit__section">
		<h2>Formats publicitaires</h2>
		<?php
		$any_format = false;
		foreach ( [ 1, 2, 3 ] as $i ) {
			if ( $k( "format{$i}_title" ) || $k( "format{$i}_desc" ) || $k( "format{$i}_price" ) ) { $any_format = true; break; }
		}
		if ( $any_format ) : ?>
			<div class="arw-mediakit__formats">
				<?php foreach ( [ 1, 2, 3 ] as $i ) :
					$t = $k( "format{$i}_title" );
					$d = $k( "format{$i}_desc" );
					$p = $k( "format{$i}_price" );
					if ( ! $t && ! $d && ! $p ) continue;
				?>
					<div class="arw-mediakit__format">
						<h3><?php echo $kpi( $t ); ?></h3>
						<p><?php echo $d !== '' ? nl2br( esc_html( $d ) ) : $em; ?></p>
						<div class="arw-mediakit__price"><?php echo $kpi( $p ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php else : echo $em; endif; ?>
	</section>

	<!-- 9. Partenaires -->
	<?php if ( $logos || $k( 'partners_text' ) ) : ?>
		<section class="arw-mediakit__section">
			<h2>Partenaires</h2>
			<?php if ( $pt = $k( 'partners_text' ) ) : ?><p class="arw-mediakit__topics"><?php echo esc_html( $pt ); ?></p><?php endif; ?>
			<?php if ( $logos ) : ?>
				<div class="arw-mediakit__logos">
					<?php foreach ( $logos as $logo ) : ?>
						<img src="<?php echo esc_url( $logo ); ?>" alt="" loading="lazy">
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</section>
	<?php endif; ?>

	<!-- 10. Téléchargements -->
	<section class="arw-mediakit__section">
		<h2>Téléchargements</h2>
		<div class="arw-mediakit__downloads">
			<?php
			$dls = [
				'logo_pack_url'      => 'Pack logos (ZIP)',
				'media_kit_pdf_url'  => 'Kit média (PDF)',
				'press_photo_url'    => 'Photo presse',
			];
			foreach ( $dls as $key_dl => $label ) :
				$url = $k( $key_dl );
				if ( $url ) : ?>
					<a href="<?php echo esc_url( $url ); ?>" class="arw-mediakit__dl" download><?php echo esc_html( $label ); ?> ↓</a>
				<?php else : ?>
					<span class="arw-mediakit__dl arw-mediakit__dl--disabled"><?php echo esc_html( $label ); ?> —</span>
				<?php endif;
			endforeach; ?>
		</div>
	</section>

	<!-- 11. Contact -->
	<section class="arw-mediakit__section arw-mediakit__contact">
		<h2>Contact presse &amp; partenariats</h2>
		<div class="arw-mediakit__contact-card">
			<?php if ( $photo = $k( 'contact_photo_url' ) ) : ?>
				<img class="arw-mediakit__contact-photo" src="<?php echo esc_url( $photo ); ?>" alt="" loading="lazy">
			<?php endif; ?>
			<div>
				<?php if ( $cn = $k( 'contact_name' ) ) : ?><div class="arw-mediakit__contact-name"><?php echo esc_html( $cn ); ?></div><?php endif; ?>
				<?php if ( $cr = $k( 'contact_role' ) ) : ?><div class="arw-mediakit__contact-role"><?php echo esc_html( $cr ); ?></div><?php endif; ?>
				<?php if ( $pe = $k( 'press_email' ) ) : ?>
					<p><a class="arw-mediakit__contact-email" href="mailto:<?php echo esc_attr( $pe ); ?>"><?php echo esc_html( $pe ); ?></a></p>
				<?php endif; ?>
				<?php if ( $cp = $k( 'contact_planning' ) ) : ?>
					<p class="arw-mediakit__contact-planning"><?php echo nl2br( esc_html( $cp ) ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</section>

</div>
<!-- /wp:html -->
