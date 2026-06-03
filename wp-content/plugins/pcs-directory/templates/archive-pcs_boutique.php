<?php
/**
 * Template : archive pcs_boutique — page mère annuaire.
 *
 * URL : /annuaire/
 *
 * @package PCS_Directory
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();

// Récupère tous les termes pour les selects.
$depts  = get_terms( [ 'taxonomy' => 'pcs_dept',   'orderby' => 'name', 'order' => 'ASC', 'hide_empty' => true ] );
$cats   = get_terms( [ 'taxonomy' => 'pcs_cat',    'orderby' => 'name', 'order' => 'ASC', 'hide_empty' => true ] );
$types  = get_terms( [ 'taxonomy' => 'pcs_type',   'orderby' => 'count', 'order' => 'DESC', 'hide_empty' => true ] );
$modes  = get_terms( [ 'taxonomy' => 'pcs_mode',   'orderby' => 'name', 'order' => 'ASC', 'hide_empty' => true ] );

$total  = wp_count_posts( PCS_DIR_CPT )->publish;
$markers_json = pcs_directory_get_map_markers_json();
?>

<div class="pcs-archive">

	<div class="pcs-archive-header">
		<h1>Annuaire des magasins déco et maison en France</h1>
		<p>Trouvez des boutiques de décoration, meubles et aménagement intérieur près de chez vous. <?php echo number_format_i18n( (int) $total ); ?> magasins référencés.</p>
	</div>

	<!-- ─── Filtres ─────────────────────────────────────────────────────── -->
	<form id="pcs-filters" class="pcs-filters" method="get">

		<select name="dept" aria-label="Département">
			<option value="">Tous les départements</option>
			<?php if ( is_array( $depts ) ) : foreach ( $depts as $term ) : ?>
				<option value="<?php echo esc_attr( str_replace( 'dept-', '', $term->slug ) ); ?>">
					<?php echo esc_html( $term->name . ' (' . $term->count . ')' ); ?>
				</option>
			<?php endforeach; endif; ?>
		</select>

		<select name="cat" aria-label="Catégorie">
			<option value="">Toutes catégories</option>
			<?php if ( is_array( $cats ) ) : foreach ( $cats as $term ) : ?>
				<option value="<?php echo esc_attr( $term->slug ); ?>">
					<?php echo esc_html( $term->name . ' (' . $term->count . ')' ); ?>
				</option>
			<?php endforeach; endif; ?>
		</select>

		<select name="type" aria-label="Type">
			<option value="">Tous les types</option>
			<?php if ( is_array( $types ) ) : foreach ( $types as $term ) : ?>
				<option value="<?php echo esc_attr( $term->slug ); ?>">
					<?php echo esc_html( $term->name . ' (' . $term->count . ')' ); ?>
				</option>
			<?php endforeach; endif; ?>
		</select>

		<select name="mode" aria-label="Mode de vente">
			<option value="">Tous modes</option>
			<?php if ( is_array( $modes ) ) : foreach ( $modes as $term ) : ?>
				<option value="<?php echo esc_attr( $term->slug ); ?>">
					<?php echo esc_html( $term->name ); ?>
				</option>
			<?php endforeach; endif; ?>
		</select>

		<button type="submit" class="pcs-filter-btn">Filtrer</button>
	</form>

	<!-- ─── Carte ────────────────────────────────────────────────────────── -->
	<div id="pcs-map"
		class="pcs-map"
		data-markers="<?php echo esc_attr( $markers_json ); ?>">
		<p class="pcs-map-placeholder">Chargement de la carte…</p>
	</div>

	<!-- ─── Grille ───────────────────────────────────────────────────────── -->
	<div id="pcs-grid" class="pcs-grid">
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
			<?php get_template_part(
				'templates/partials/card-boutique',
				null,
				[ 'post_id' => get_the_ID() ]
			); ?>
		<?php endwhile;
		else : ?>
			<p class="pcs-empty">Aucune boutique référencée pour le moment.</p>
		<?php endif; ?>
	</div>

	<!-- ─── Pagination ───────────────────────────────────────────────────── -->
	<div id="pcs-pagination" class="pcs-pagination">
		<?php the_posts_pagination( [ 'mid_size' => 2 ] ); ?>
	</div>

	<!-- ─── Texte SEO ────────────────────────────────────────────────────── -->
	<div class="pcs-seo-text">
		<h2>L'annuaire complet des magasins déco et maison</h2>
		<p>
			Plus c'est simple recense des milliers de boutiques spécialisées dans la décoration d'intérieur,
			le mobilier, les luminaires et l'aménagement de la maison partout en France.
			Trouvez facilement une boutique près de chez vous : grandes enseignes comme
			Maisons du Monde, IKEA, Conforama, ou indépendants locaux, cuisinistes et spécialistes.
		</p>
		<p>
			Filtrez par département, catégorie de produits ou type d'établissement.
			Chaque fiche inclut l'adresse, le téléphone, les horaires d'ouverture et le lien vers le site web.
		</p>
	</div>

</div><!-- .pcs-archive -->

<?php get_footer(); ?>
