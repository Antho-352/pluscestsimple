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

$term  = get_queried_object();
$name  = $term->name ?? '';
$count = $term->count ?? 0;
$tax   = $term->taxonomy ?? 'pcs_cat';

// Libellé contextuel selon la taxonomy.
$intro_map = [
	'pcs_cat'  => "Trouvez {$count} magasin" . ( $count > 1 ? 's' : '' ) . " spécialisé" . ( $count > 1 ? 's' : '' ) . " en <strong>" . esc_html( $name ) . "</strong> partout en France.",
	'pcs_type' => "{$count} boutique" . ( $count > 1 ? 's' : '' ) . " du type <strong>" . esc_html( $name ) . "</strong> référencée" . ( $count > 1 ? 's' : '' ) . " dans l'annuaire.",
	'pcs_mode' => "{$count} boutique" . ( $count > 1 ? 's' : '' ) . " en mode <strong>" . esc_html( $name ) . "</strong>.",
];
$intro = $intro_map[ $tax ] ?? "{$count} boutiques trouvées.";

$markers_json = pcs_directory_get_map_markers_json( [ [
	'taxonomy' => $tax,
	'field'    => 'term_id',
	'terms'    => $term->term_id,
] ] );
?>

<div class="pcs-taxonomy">

	<div class="pcs-tax-header">
		<h1><?php echo esc_html( $name ); ?> — Annuaire magasins France</h1>
		<p><?php echo wp_kses_post( $intro ); ?></p>
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
			<p class="pcs-empty">Aucune boutique dans cette catégorie.</p>
		<?php endif; ?>
	</div>

	<div id="pcs-pagination" class="pcs-pagination">
		<?php the_posts_pagination( [ 'mid_size' => 2 ] ); ?>
	</div>

	<div class="pcs-seo-text">
		<h2>Les meilleurs magasins <?php echo esc_html( strtolower( $name ) ); ?> en France</h2>
		<p>
			Plus c'est simple recense <?php echo number_format_i18n( $count ); ?> boutiques spécialisées
			en <?php echo esc_html( strtolower( $name ) ); ?> à travers la France.
			Trouvez celle la plus proche de chez vous grâce à la carte interactive.
		</p>
		<p><a href="<?php echo esc_url( get_post_type_archive_link( PCS_DIR_CPT ) ); ?>">← Annuaire national</a></p>
	</div>

</div>

<?php get_footer(); ?>
