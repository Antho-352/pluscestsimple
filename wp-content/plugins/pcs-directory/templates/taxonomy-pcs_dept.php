<?php
/**
 * Template : taxonomy pcs_dept — page département.
 *
 * URL : /annuaire/departement/loiret-45/
 *
 * @package PCS_Directory
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();

$term   = get_queried_object();
$cats   = get_terms( [ 'taxonomy' => 'pcs_cat',  'orderby' => 'name', 'hide_empty' => true ] );
$types  = get_terms( [ 'taxonomy' => 'pcs_type', 'orderby' => 'count', 'order' => 'DESC', 'hide_empty' => true ] );
$modes  = get_terms( [ 'taxonomy' => 'pcs_mode', 'orderby' => 'name', 'hide_empty' => true ] );

$villes     = pcs_directory_villes_in_dept( $term->term_id );
$villes_top = array_slice( $villes, 0, 16 );

$markers_json = pcs_directory_get_map_markers_json( [ [
	'taxonomy' => 'pcs_dept',
	'field'    => 'term_id',
	'terms'    => $term->term_id,
] ] );
?>

<div class="pcs-taxonomy">

	<?php pcs_directory_breadcrumb(); ?>

	<div class="pcs-tax-header">
		<h1>Magasins déco et maison <?php echo esc_html( pcs_directory_dept_prep( $term->name ) ); ?></h1>
		<?php echo pcs_directory_term_intro( $term ); ?>
	</div>

	<!-- Villes principales -->
	<?php if ( $villes_top ) : ?>
		<section class="pcs-index">
			<h2 class="pcs-index__title">Villes du département</h2>
			<ul class="pcs-index__list">
				<?php foreach ( $villes_top as $v ) :
					$vlink = get_term_link( (int) $v['term_id'], 'pcs_ville' );
					if ( is_wp_error( $vlink ) ) { continue; }
					?>
					<li class="pcs-index__item">
						<a href="<?php echo esc_url( $vlink ); ?>"><?php echo esc_html( $v['name'] ); ?></a>
						<span class="pcs-index__count"><?php echo number_format_i18n( $v['count'] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php if ( count( $villes ) > count( $villes_top ) ) : ?>
				<p class="pcs-index__more"><?php echo esc_html( sprintf( '+ %d autres communes', count( $villes ) - count( $villes_top ) ) ); ?></p>
			<?php endif; ?>
		</section>
	<?php endif; ?>

	<!-- Carte -->
	<div id="pcs-map" class="pcs-map" data-markers="<?php echo esc_attr( $markers_json ); ?>">
		<p class="pcs-map-placeholder">Chargement de la carte…</p>
	</div>

	<!-- Filtres + liste -->
	<section class="pcs-quicksearch">
		<h2 class="pcs-index__title">Toutes les boutiques du <?php echo esc_html( $term->name ); ?></h2>
		<form id="pcs-filters" class="pcs-filters" method="get">
			<input type="hidden" name="dept" value="<?php echo esc_attr( $term->slug ); ?>" />
			<select name="cat" aria-label="Catégorie">
				<option value="">Toutes catégories</option>
				<?php if ( is_array( $cats ) ) : foreach ( $cats as $t ) : ?>
					<option value="<?php echo esc_attr( $t->slug ); ?>"><?php echo esc_html( $t->name ); ?></option>
				<?php endforeach; endif; ?>
			</select>
			<select name="type" aria-label="Type">
				<option value="">Tous les types</option>
				<?php if ( is_array( $types ) ) : foreach ( $types as $t ) : ?>
					<option value="<?php echo esc_attr( $t->slug ); ?>"><?php echo esc_html( $t->name ); ?></option>
				<?php endforeach; endif; ?>
			</select>
			<select name="mode" aria-label="Mode">
				<option value="">Tous modes</option>
				<?php if ( is_array( $modes ) ) : foreach ( $modes as $t ) : ?>
					<option value="<?php echo esc_attr( $t->slug ); ?>"><?php echo esc_html( $t->name ); ?></option>
				<?php endforeach; endif; ?>
			</select>
			<button type="submit" class="pcs-filter-btn">Filtrer</button>
		</form>

		<div id="pcs-grid" class="pcs-grid">
			<ul class="pcs-list">
				<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
					<?php pcs_directory_render_row( get_the_ID() ); ?>
				<?php endwhile;
				else : ?>
					<p class="pcs-empty">Aucune boutique trouvée.</p>
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
		<?php $rterm = pcs_directory_region_term_for_dept( $term ); ?>
		<?php if ( $rterm ) : ?>
			<p><a href="<?php echo esc_url( get_term_link( $rterm ) ); ?>">← Tous les départements de <?php echo esc_html( $rterm->name ); ?></a></p>
		<?php endif; ?>
	</div>

	<?php pcs_directory_render_faq( $term ); ?>

</div>

<?php get_footer(); ?>
