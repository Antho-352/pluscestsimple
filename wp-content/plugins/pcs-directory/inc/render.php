<?php
/**
 * Helpers de rendu réutilisés par les templates et le shortcode.
 *
 * @package PCS_Directory
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Rendu d'une carte d'établissement (utilisée dans l'archive et le shortcode).
 *
 * @param int $post_id ID de l'établissement.
 * @return string HTML.
 */
function pcs_directory_render_card( int $post_id ): string {
	$ville     = (string) get_post_meta( $post_id, '_pcs_etab_ville', true );
	$cp        = (string) get_post_meta( $post_id, '_pcs_etab_code_postal', true );
	$featured  = '1' === (string) get_post_meta( $post_id, '_pcs_etab_is_featured', true );

	$type_term = null;
	$terms     = get_the_terms( $post_id, PCS_DIR_TAX_TYPE );
	if ( is_array( $terms ) && ! empty( $terms ) ) {
		$type_term = $terms[0];
	}

	$thumb = get_the_post_thumbnail( $post_id, 'medium', [ 'class' => 'pcs-directory-card__img', 'loading' => 'lazy' ] );

	ob_start();
	?>
	<article class="pcs-directory-card<?php echo $featured ? ' is-featured' : ''; ?>">
		<a class="pcs-directory-card__link" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
			<?php if ( $thumb ) : ?>
				<div class="pcs-directory-card__media"><?php echo $thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			<?php else : ?>
				<div class="pcs-directory-card__media pcs-directory-card__media--placeholder" aria-hidden="true"></div>
			<?php endif; ?>
			<div class="pcs-directory-card__body">
				<?php if ( $featured ) : ?>
					<span class="pcs-directory-card__badge pcs-directory-card__badge--featured"><?php esc_html_e( 'Vérifié', 'pluscestsimple' ); ?></span>
				<?php endif; ?>
				<?php if ( $type_term ) : ?>
					<span class="pcs-directory-card__badge pcs-directory-card__badge--type"><?php echo esc_html( $type_term->name ); ?></span>
				<?php endif; ?>
				<h3 class="pcs-directory-card__title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
				<?php if ( '' !== $ville ) : ?>
					<p class="pcs-directory-card__meta">
						<?php echo esc_html( trim( $cp . ' ' . $ville ) ); ?>
					</p>
				<?php endif; ?>
			</div>
		</a>
	</article>
	<?php
	return (string) ob_get_clean();
}

/**
 * Rendu de la barre de filtres (type / région / ville + tri).
 *
 * @return string HTML.
 */
function pcs_directory_render_filters(): string {
	$current_type   = isset( $_GET['etab_type'] )   ? sanitize_text_field( wp_unslash( $_GET['etab_type'] ) )   : '';
	$current_region = isset( $_GET['etab_region'] ) ? sanitize_text_field( wp_unslash( $_GET['etab_region'] ) ) : '';
	$current_ville  = isset( $_GET['etab_ville'] )  ? sanitize_text_field( wp_unslash( $_GET['etab_ville'] ) )  : '';
	$current_sort   = isset( $_GET['orderby'] )     ? sanitize_key( wp_unslash( $_GET['orderby'] ) )            : 'date';

	$types   = get_terms( [ 'taxonomy' => PCS_DIR_TAX_TYPE,   'hide_empty' => false ] );
	$regions = get_terms( [ 'taxonomy' => PCS_DIR_TAX_REGION, 'hide_empty' => false ] );
	$villes  = get_terms( [ 'taxonomy' => PCS_DIR_TAX_VILLE,  'hide_empty' => true, 'number' => 200 ] );

	ob_start();
	?>
	<form class="pcs-directory-filters" method="get" action="<?php echo esc_url( get_post_type_archive_link( PCS_DIR_CPT ) ); ?>">
		<button type="button" class="pcs-directory-filters__toggle" aria-expanded="false">
			<?php esc_html_e( 'Filtres', 'pluscestsimple' ); ?>
		</button>

		<div class="pcs-directory-filters__group">
			<label for="etab_type"><?php esc_html_e( 'Type', 'pluscestsimple' ); ?></label>
			<select id="etab_type" name="etab_type">
				<option value=""><?php esc_html_e( 'Tous', 'pluscestsimple' ); ?></option>
				<?php if ( is_array( $types ) ) : foreach ( $types as $t ) : ?>
					<option value="<?php echo esc_attr( $t->slug ); ?>" <?php selected( $current_type, $t->slug ); ?>>
						<?php echo esc_html( $t->name ); ?>
					</option>
				<?php endforeach; endif; ?>
			</select>
		</div>

		<div class="pcs-directory-filters__group">
			<label for="etab_region"><?php esc_html_e( 'Région', 'pluscestsimple' ); ?></label>
			<select id="etab_region" name="etab_region">
				<option value=""><?php esc_html_e( 'Toutes', 'pluscestsimple' ); ?></option>
				<?php if ( is_array( $regions ) ) : foreach ( $regions as $r ) : ?>
					<option value="<?php echo esc_attr( $r->slug ); ?>" <?php selected( $current_region, $r->slug ); ?>>
						<?php echo esc_html( $r->name ); ?>
					</option>
				<?php endforeach; endif; ?>
			</select>
		</div>

		<div class="pcs-directory-filters__group">
			<label for="etab_ville"><?php esc_html_e( 'Ville', 'pluscestsimple' ); ?></label>
			<select id="etab_ville" name="etab_ville">
				<option value=""><?php esc_html_e( 'Toutes', 'pluscestsimple' ); ?></option>
				<?php if ( is_array( $villes ) ) : foreach ( $villes as $v ) : ?>
					<option value="<?php echo esc_attr( $v->slug ); ?>" <?php selected( $current_ville, $v->slug ); ?>>
						<?php echo esc_html( $v->name ); ?>
					</option>
				<?php endforeach; endif; ?>
			</select>
		</div>

		<div class="pcs-directory-filters__group">
			<label for="orderby"><?php esc_html_e( 'Tri', 'pluscestsimple' ); ?></label>
			<select id="orderby" name="orderby">
				<option value="date"  <?php selected( $current_sort, 'date' );  ?>><?php esc_html_e( 'Récents', 'pluscestsimple' ); ?></option>
				<option value="title" <?php selected( $current_sort, 'title' ); ?>><?php esc_html_e( 'Alphabétique', 'pluscestsimple' ); ?></option>
			</select>
		</div>

		<button type="submit" class="pcs-directory-filters__submit"><?php esc_html_e( 'Filtrer', 'pluscestsimple' ); ?></button>
	</form>
	<?php
	return (string) ob_get_clean();
}

/**
 * Applique les filtres GET (etab_type/region/ville) à la WP_Query principale
 * sur l'archive du CPT et les tri (date/title).
 *
 * @param WP_Query $q Query principale.
 * @return void
 */
function pcs_directory_filter_main_query( WP_Query $q ): void {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}
	if ( ! $q->is_post_type_archive( PCS_DIR_CPT ) && ! $q->is_tax( [ PCS_DIR_TAX_TYPE, PCS_DIR_TAX_REGION, PCS_DIR_TAX_VILLE ] ) ) {
		return;
	}

	$tax_query = [];
	foreach ( [
		'etab_type'   => PCS_DIR_TAX_TYPE,
		'etab_region' => PCS_DIR_TAX_REGION,
		'etab_ville'  => PCS_DIR_TAX_VILLE,
	] as $param => $tax ) {
		if ( ! empty( $_GET[ $param ] ) ) {
			$tax_query[] = [
				'taxonomy' => $tax,
				'field'    => 'slug',
				'terms'    => [ sanitize_text_field( wp_unslash( $_GET[ $param ] ) ) ],
			];
		}
	}
	if ( ! empty( $tax_query ) ) {
		if ( count( $tax_query ) > 1 ) {
			$tax_query['relation'] = 'AND';
		}
		$q->set( 'tax_query', $tax_query );
	}

	// Tri : featured en tête (DESC sur meta_value_num) + tri secondaire
	// récents/alphabétique. WP_Query gère le orderby array pour combiner les deux.
	$orderby      = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : 'date';
	$secondary    = 'title' === $orderby ? 'title' : 'date';
	$secondary_dir = 'title' === $orderby ? 'ASC' : 'DESC';

	$q->set( 'meta_key', '_pcs_etab_is_featured' ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
	$q->set( 'orderby', [
		'meta_value_num' => 'DESC',
		$secondary       => $secondary_dir,
	] );

	$q->set( 'posts_per_page', 24 );
}
add_action( 'pre_get_posts', 'pcs_directory_filter_main_query' );
