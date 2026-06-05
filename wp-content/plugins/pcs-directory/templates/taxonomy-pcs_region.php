<?php
/**
 * Template : taxonomy pcs_region — liste des départements de la région.
 *
 * URL : /annuaire/region/centre-val-de-loire/
 *
 * @package PCS_Directory
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();

$term  = get_queried_object();
$depts = pcs_directory_departments_in_region( $term->name );

// Teaser : quelques boutiques de la région (enseignes en priorité).
$teaser = new WP_Query( [
	'post_type'      => PCS_DIR_CPT,
	'post_status'    => 'publish',
	'posts_per_page' => 10,
	'orderby'        => 'meta_value_num',
	'meta_key'       => '_pcs_is_enseigne',
	'order'          => 'DESC',
	'tax_query'      => [ [ 'taxonomy' => 'pcs_region', 'field' => 'term_id', 'terms' => $term->term_id ] ],
] );
?>

<div class="pcs-taxonomy">

	<?php pcs_directory_breadcrumb(); ?>

	<div class="pcs-tax-header">
		<h1>Magasins déco et maison en <?php echo esc_html( $term->name ); ?></h1>
		<?php echo pcs_directory_term_intro( $term ); ?>
	</div>

	<!-- Liste des départements -->
	<section class="pcs-index">
		<h2 class="pcs-index__title">Choisir un département</h2>
		<ul class="pcs-index__list">
			<?php foreach ( $depts as $d ) : ?>
				<li class="pcs-index__item">
					<a href="<?php echo esc_url( get_term_link( $d ) ); ?>">
						<?php echo esc_html( $d->name . ' (' . pcs_directory_dept_code( $d ) . ')' ); ?>
					</a>
					<span class="pcs-index__count"><?php echo number_format_i18n( $d->count ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>

	<!-- Teaser boutiques -->
	<?php if ( $teaser->have_posts() ) : ?>
		<section class="pcs-teaser">
			<h2 class="pcs-index__title">Quelques boutiques de la région</h2>
			<ul class="pcs-list">
				<?php while ( $teaser->have_posts() ) : $teaser->the_post(); ?>
					<?php get_template_part( 'templates/partials/row-boutique', null, [ 'post_id' => get_the_ID() ] ); ?>
				<?php endwhile; wp_reset_postdata(); ?>
			</ul>
		</section>
	<?php endif; ?>

	<!-- Texte SEO -->
	<div class="pcs-seo-text">
		<?php echo pcs_directory_term_outro( $term ); ?>
		<p><a href="<?php echo esc_url( get_post_type_archive_link( PCS_DIR_CPT ) ); ?>">← Toutes les régions</a></p>
	</div>

</div>

<?php get_footer(); ?>
