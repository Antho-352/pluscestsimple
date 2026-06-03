<?php
/**
 * Template : taxonomy pcs_dept — liste par département.
 *
 * URL : /annuaire/departement/dept-45/
 *
 * @package PCS_Directory
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();

$term   = get_queried_object();
$dept   = $term->name ?? '';
$count  = $term->count ?? 0;
$cats   = get_terms( [ 'taxonomy' => 'pcs_cat',  'orderby' => 'name', 'hide_empty' => true ] );
$types  = get_terms( [ 'taxonomy' => 'pcs_type', 'orderby' => 'count', 'order' => 'DESC', 'hide_empty' => true ] );
$modes  = get_terms( [ 'taxonomy' => 'pcs_mode', 'orderby' => 'name', 'hide_empty' => true ] );

// Markers filtrés sur ce département.
$markers_json = pcs_directory_get_map_markers_json( [ [
	'taxonomy' => 'pcs_dept',
	'field'    => 'term_id',
	'terms'    => $term->term_id,
] ] );

// Top enseignes pour le texte SEO.
$top_query = new WP_Query( [
	'post_type'      => PCS_DIR_CPT,
	'post_status'    => 'publish',
	'posts_per_page' => 5,
	'tax_query'      => [
		[ 'taxonomy' => 'pcs_dept',   'field' => 'term_id', 'terms' => $term->term_id ],
		[ 'taxonomy' => 'pcs_type',   'field' => 'slug',    'terms' => 'grande-enseigne' ],
	],
	'fields'         => 'ids',
	'no_found_rows'  => true,
] );
$top_names = array_map( 'get_the_title', $top_query->posts );
?>

<div class="pcs-taxonomy">

	<div class="pcs-tax-header">
		<h1>Magasins déco et maison — Département <?php echo esc_html( $dept ); ?></h1>
		<p><?php echo number_format_i18n( $count ); ?> boutique<?php echo $count > 1 ? 's' : ''; ?> référencée<?php echo $count > 1 ? 's' : ''; ?> dans le département <?php echo esc_html( $dept ); ?>.</p>
	</div>

	<!-- Filtres -->
	<form id="pcs-filters" class="pcs-filters" method="get">
		<input type="hidden" name="dept" value="<?php echo esc_attr( $dept ); ?>" />

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
			<p class="pcs-empty">Aucune boutique trouvée dans ce département.</p>
		<?php endif; ?>
	</div>

	<div id="pcs-pagination" class="pcs-pagination">
		<?php the_posts_pagination( [ 'mid_size' => 2 ] ); ?>
	</div>

	<!-- Texte SEO -->
	<div class="pcs-seo-text">
		<h2>Magasins de décoration et ameublement dans le département <?php echo esc_html( $dept ); ?></h2>
		<p>
			Retrouvez <?php echo number_format_i18n( $count ); ?> boutiques spécialisées en décoration d'intérieur, meubles et aménagement de la maison dans le département <?php echo esc_html( $dept ); ?>.
			<?php if ( $top_names ) : ?>
				Parmi les enseignes présentes : <?php echo esc_html( implode( ', ', $top_names ) ); ?>.
			<?php endif; ?>
		</p>
		<p>
			<a href="<?php echo esc_url( get_post_type_archive_link( PCS_DIR_CPT ) ); ?>">← Retour à l'annuaire national</a>
		</p>
	</div>

</div><!-- .pcs-taxonomy -->

<?php get_footer(); ?>
