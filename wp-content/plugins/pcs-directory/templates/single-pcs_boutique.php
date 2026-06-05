<?php
/**
 * Template : single pcs_boutique — fiche boutique.
 *
 * @package PCS_Directory
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();

if ( ! have_posts() ) {
	get_footer();
	return;
}

the_post();

$pid       = get_the_ID();
$title     = get_the_title();
$adresse   = get_post_meta( $pid, '_pcs_adresse',     true );
$cp        = get_post_meta( $pid, '_pcs_code_postal', true );
$website   = get_post_meta( $pid, '_pcs_website',     true );
$phone     = get_post_meta( $pid, '_pcs_phone',       true );
$hours     = get_post_meta( $pid, '_pcs_hours',       true );
$lat       = (float) get_post_meta( $pid, '_pcs_lat', true );
$lng       = (float) get_post_meta( $pid, '_pcs_lng', true );
$siret     = get_post_meta( $pid, '_pcs_siret',       true );
$sources   = json_decode( (string) get_post_meta( $pid, '_pcs_sources', true ), true );
$is_enseigne = get_post_meta( $pid, '_pcs_is_enseigne', true );

$cats   = get_the_terms( $pid, 'pcs_cat' );
$types  = get_the_terms( $pid, 'pcs_type' );
$villes = get_the_terms( $pid, 'pcs_ville' );
$depts  = get_the_terms( $pid, 'pcs_dept' );

$cat   = ( is_array( $cats )   && $cats )   ? $cats[0]   : null;
$type  = ( is_array( $types )  && $types )  ? $types[0]  : null;
$ville = ( is_array( $villes ) && $villes ) ? $villes[0] : null;
$dept  = ( is_array( $depts )  && $depts )  ? $depts[0]  : null;

// Boutiques voisines (même ville, sauf current).
$nearby_query = null;
if ( $ville ) {
	$nearby_query = new WP_Query( [
		'post_type'      => PCS_DIR_CPT,
		'post_status'    => 'publish',
		'posts_per_page' => 4,
		'post__not_in'   => [ $pid ],
		'tax_query'      => [ [
			'taxonomy' => 'pcs_ville',
			'field'    => 'term_id',
			'terms'    => $ville->term_id,
		] ],
		'meta_query'     => [ [ 'key' => '_pcs_public', 'value' => '1' ] ],
		'orderby'        => 'rand',
	] );
}
?>

<div class="pcs-single">
<article class="pcs-single__inner">

	<?php pcs_directory_breadcrumb(); ?>

	<!-- Hero -->
	<?php if ( has_post_thumbnail() ) : ?>
		<div class="pcs-single__hero">
			<?php the_post_thumbnail( 'large', [ 'loading' => 'eager' ] ); ?>
		</div>
	<?php endif; ?>

	<!-- Badges -->
	<div class="pcs-single__tags">
		<?php if ( $is_enseigne ) : ?>
			<span class="pcs-badge pcs-badge--enseigne">Grande enseigne</span>
		<?php endif; ?>
		<?php if ( $cat ) : ?>
			<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>" class="pcs-badge pcs-badge--cat">
				<?php echo esc_html( $cat->name ); ?>
			</a>
		<?php endif; ?>
		<?php if ( $type ) : ?>
			<a href="<?php echo esc_url( get_term_link( $type ) ); ?>" class="pcs-badge pcs-badge--type">
				<?php echo esc_html( $type->name ); ?>
			</a>
		<?php endif; ?>
	</div>

	<!-- Titre -->
	<h1 class="pcs-single__h1">
		<?php echo esc_html( $title ); ?>
		<?php if ( $ville ) : ?>
			<span style="font-weight:400;font-size:.7em;color:#666"> — <?php echo esc_html( $ville->name ); ?></span>
		<?php endif; ?>
	</h1>

	<?php
	$rating  = get_post_meta( $pid, '_pcs_rating', true );
	$reviews = get_post_meta( $pid, '_pcs_reviews', true );
	?>
	<?php if ( $rating ) : ?>
		<p class="pcs-single__rating">★ <strong><?php echo esc_html( $rating ); ?></strong><?php echo $reviews ? ' <span>· ' . esc_html( $reviews ) . ' avis Google</span>' : ''; ?></p>
	<?php endif; ?>

	<!-- Contact -->
	<div class="pcs-single__contact">
		<ul class="pcs-contact-list">
			<?php if ( $adresse || $cp ) : ?>
				<li>
					<strong>Adresse</strong>
					<span><?php echo esc_html( trim( $adresse . ( $cp ? ', ' . $cp : '' ) . ( $ville ? ' ' . strtoupper( $ville->name ) : '' ) ) ); ?></span>
				</li>
			<?php endif; ?>

			<?php if ( $phone ) : ?>
				<li>
					<strong>Téléphone</strong>
					<a href="tel:<?php echo esc_attr( preg_replace( '/\s/', '', $phone ) ); ?>">
						<?php echo esc_html( $phone ); ?>
					</a>
				</li>
			<?php endif; ?>

			<?php if ( $website ) : ?>
				<li>
					<strong>Site web</strong>
					<a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer">
						<?php echo esc_html( preg_replace( '#^https?://#', '', rtrim( $website, '/' ) ) ); ?>
					</a>
				</li>
			<?php endif; ?>

			<?php if ( $hours ) : ?>
				<li>
					<strong>Horaires</strong>
					<span><?php echo nl2br( esc_html( $hours ) ); ?></span>
				</li>
			<?php endif; ?>

			<?php if ( $dept ) : ?>
				<li>
					<strong>Département</strong>
					<a href="<?php echo esc_url( get_term_link( $dept ) ); ?>">
						<?php echo esc_html( $dept->name ); ?>
					</a>
				</li>
			<?php endif; ?>
		</ul>
	</div>

	<!-- Carte single -->
	<?php if ( $lat && $lng ) : ?>
		<div id="pcs-single-map"
			class="pcs-map pcs-map--single"
			data-lat="<?php echo esc_attr( $lat ); ?>"
			data-lng="<?php echo esc_attr( $lng ); ?>"
			data-nom="<?php echo esc_attr( $title ); ?>">
		</div>
	<?php endif; ?>

	<!-- Contenu éditorial si présent -->
	<?php if ( get_the_content() ) : ?>
		<div class="pcs-single__content">
			<?php the_content(); ?>
		</div>
	<?php endif; ?>

	<!-- Boutiques voisines -->
	<?php if ( $nearby_query && $nearby_query->have_posts() ) : ?>
		<div class="pcs-single__nearby">
			<h2>Autres boutiques<?php echo $ville ? ' à ' . esc_html( $ville->name ) : ''; ?></h2>
			<ul class="pcs-list">
				<?php while ( $nearby_query->have_posts() ) : $nearby_query->the_post(); ?>
					<?php pcs_directory_render_row( get_the_ID() ); ?>
				<?php endwhile; wp_reset_postdata(); ?>
			</ul>
		</div>
	<?php endif; ?>

	<!-- Sources -->
	<?php if ( is_array( $sources ) && $sources ) : ?>
		<div class="pcs-single__sources">
			Sources : <?php echo esc_html( implode( ', ', $sources ) ); ?>
			<?php if ( $siret ) : ?>
				· SIRET <?php echo esc_html( $siret ); ?>
			<?php endif; ?>
		</div>
	<?php endif; ?>

</article>
</div><!-- .pcs-single -->

<?php get_footer(); ?>
