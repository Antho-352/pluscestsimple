<?php
/**
 * Template : taxonomy pcs_cat — par catégorie.
 * Sert aussi de fallback pour pcs_type et pcs_mode.
 *
 * URL : /annuaire/categorie/meubles-amenagement/
 *
 * @package PCS_Directory
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();

$term = get_queried_object();
$tax  = $term->taxonomy ?? 'pcs_cat';

$markers_json = pcs_directory_get_map_markers_json( [ [
	'taxonomy' => $tax,
	'field'    => 'term_id',
	'terms'    => $term->term_id,
] ] );
?>

<div class="pcs-taxonomy">

	<?php pcs_directory_breadcrumb(); ?>

	<div class="pcs-tax-header">
		<h1><?php echo esc_html( $term->name ); ?> — Annuaire des magasins en France</h1>
		<?php echo pcs_directory_term_intro( $term ); ?>
	</div>

	<!-- Carte -->
	<div id="pcs-map" class="pcs-map" data-markers="<?php echo esc_attr( $markers_json ); ?>">
		<p class="pcs-map-placeholder">Chargement de la carte…</p>
	</div>

	<!-- Liste boutiques -->
	<section class="pcs-quicksearch">
		<div id="pcs-grid" class="pcs-grid">
			<ul class="pcs-list">
				<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
					<?php get_template_part( 'templates/partials/row-boutique', null, [ 'post_id' => get_the_ID() ] ); ?>
				<?php endwhile;
				else : ?>
					<p class="pcs-empty">Aucune boutique dans cette catégorie.</p>
				<?php endif; ?>
			</ul>
		</div>
		<div id="pcs-pagination" class="pcs-pagination">
			<?php the_posts_pagination( [ 'mid_size' => 2 ] ); ?>
		</div>
	</section>

	<!-- Texte SEO -->
	<div class="pcs-seo-text">
		<?php echo pcs_directory_term_outro( $term ); ?>
		<p><a href="<?php echo esc_url( get_post_type_archive_link( PCS_DIR_CPT ) ); ?>">← Annuaire national</a></p>
	</div>

</div>

<?php get_footer(); ?>
