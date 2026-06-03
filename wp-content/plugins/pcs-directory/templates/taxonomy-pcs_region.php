<?php
/**
 * Template : taxonomy pcs_region.
 *
 * URL : /annuaire/region/centre-val-de-loire/
 *
 * @package PCS_Directory
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();

$term   = get_queried_object();
$region = $term->name ?? '';
$count  = $term->count ?? 0;

$markers_json = pcs_directory_get_map_markers_json( [ [
	'taxonomy' => 'pcs_region',
	'field'    => 'term_id',
	'terms'    => $term->term_id,
] ] );
?>

<div class="pcs-taxonomy">

	<div class="pcs-tax-header">
		<h1>Magasins déco et maison en <?php echo esc_html( $region ); ?></h1>
		<p><?php echo number_format_i18n( $count ); ?> boutique<?php echo $count > 1 ? 's' : ''; ?> référencée<?php echo $count > 1 ? 's' : ''; ?> en <?php echo esc_html( $region ); ?>.</p>
	</div>

	<!-- Carte -->
	<div id="pcs-map" class="pcs-map" data-markers="<?php echo esc_attr( $markers_json ); ?>">
		<p class="pcs-map-placeholder">Chargement de la carte…</p>
	</div>

	<!-- Grille -->
	<div id="pcs-grid" class="pcs-grid">
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
			<?php get_template_part( 'templates/partials/card-boutique', null, [ 'post_id' => get_the_ID() ] ); ?>
		<?php endwhile;
		else : ?>
			<p class="pcs-empty">Aucune boutique trouvée dans cette région.</p>
		<?php endif; ?>
	</div>

	<div id="pcs-pagination" class="pcs-pagination">
		<?php the_posts_pagination( [ 'mid_size' => 2 ] ); ?>
	</div>

	<div class="pcs-seo-text">
		<h2>Boutiques de décoration en <?php echo esc_html( $region ); ?></h2>
		<p>
			Découvrez <?php echo number_format_i18n( $count ); ?> magasins de décoration d'intérieur, meubles et aménagement de la maison en <?php echo esc_html( $region ); ?>.
		</p>
		<p><a href="<?php echo esc_url( get_post_type_archive_link( PCS_DIR_CPT ) ); ?>">← Annuaire national</a></p>
	</div>

</div>

<?php get_footer(); ?>
