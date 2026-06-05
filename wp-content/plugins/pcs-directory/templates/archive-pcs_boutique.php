<?php
/**
 * Template : archive pcs_boutique — page mère annuaire.
 *
 * Index des RÉGIONS + recherche rapide + texte SEO.
 *
 * @package PCS_Directory
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();

$regions = get_terms( [ 'taxonomy' => 'pcs_region', 'orderby' => 'name', 'hide_empty' => true ] );
$cats    = get_terms( [ 'taxonomy' => 'pcs_cat',    'orderby' => 'name', 'hide_empty' => true ] );
$types   = get_terms( [ 'taxonomy' => 'pcs_type',   'orderby' => 'count', 'order' => 'DESC', 'hide_empty' => true ] );
$total   = (int) wp_count_posts( PCS_DIR_CPT )->publish;

$intro = get_option( 'pcs_archive_intro', '' );
$outro = get_option( 'pcs_archive_outro', '' );
?>

<div class="pcs-archive">

	<?php pcs_directory_breadcrumb(); ?>

	<div class="pcs-tax-header">
		<h1>Annuaire des magasins déco et maison en France</h1>
		<?php if ( trim( $intro ) !== '' ) : ?>
			<?php echo wp_kses_post( wpautop( $intro ) ); ?>
		<?php else : ?>
			<p>Trouvez une boutique de décoration, de meubles ou d'aménagement intérieur près de chez vous. <strong><?php echo number_format_i18n( $total ); ?></strong> magasins référencés, classés par région, département et ville.</p>
		<?php endif; ?>
	</div>

	<!-- ─── Index par région ─────────────────────────────────────────────── -->
	<section class="pcs-index">
		<h2 class="pcs-index__title">Parcourir par région</h2>
		<ul class="pcs-index__list">
			<?php if ( is_array( $regions ) ) : foreach ( $regions as $r ) : ?>
				<li class="pcs-index__item">
					<a href="<?php echo esc_url( get_term_link( $r ) ); ?>"><?php echo esc_html( $r->name ); ?></a>
					<span class="pcs-index__count"><?php echo number_format_i18n( $r->count ); ?></span>
				</li>
			<?php endforeach; endif; ?>
		</ul>
	</section>

	<!-- ─── Recherche rapide ─────────────────────────────────────────────── -->
	<section class="pcs-quicksearch">
		<h2 class="pcs-index__title">Recherche rapide</h2>
		<form id="pcs-filters" class="pcs-filters" method="get">
			<select name="cat" aria-label="Catégorie">
				<option value="">Toutes catégories</option>
				<?php if ( is_array( $cats ) ) : foreach ( $cats as $t ) : ?>
					<option value="<?php echo esc_attr( $t->slug ); ?>"><?php echo esc_html( $t->name . ' (' . $t->count . ')' ); ?></option>
				<?php endforeach; endif; ?>
			</select>
			<select name="type" aria-label="Type">
				<option value="">Tous les types</option>
				<?php if ( is_array( $types ) ) : foreach ( $types as $t ) : ?>
					<option value="<?php echo esc_attr( $t->slug ); ?>"><?php echo esc_html( $t->name . ' (' . $t->count . ')' ); ?></option>
				<?php endforeach; endif; ?>
			</select>
			<button type="submit" class="pcs-filter-btn">Filtrer</button>
		</form>

		<div id="pcs-grid" class="pcs-grid">
			<ul class="pcs-list">
				<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
					<?php get_template_part( 'templates/partials/row-boutique', null, [ 'post_id' => get_the_ID() ] ); ?>
				<?php endwhile; endif; ?>
			</ul>
		</div>
		<div id="pcs-pagination" class="pcs-pagination">
			<?php the_posts_pagination( [ 'mid_size' => 2 ] ); ?>
		</div>
	</section>

	<!-- ─── Texte SEO ────────────────────────────────────────────────────── -->
	<div class="pcs-seo-text">
		<?php if ( trim( $outro ) !== '' ) : ?>
			<?php echo wp_kses_post( wpautop( $outro ) ); ?>
		<?php else : ?>
			<h2>L'annuaire complet des magasins déco et maison</h2>
			<p>
				Plus c'est simple recense des boutiques spécialisées dans la décoration d'intérieur,
				le mobilier, les luminaires, la literie et l'aménagement de la maison partout en France.
				Grandes enseignes comme Maisons du Monde, IKEA, Conforama ou BUT, indépendants locaux,
				cuisinistes et spécialistes : sélectionnez votre région pour explorer les magasins
				département par département, puis ville par ville.
			</p>
		<?php endif; ?>
	</div>

</div><!-- .pcs-archive -->

<?php get_footer(); ?>
