<?php
/**
 * HTML sitemap (page plan du site). Auto-generated.
 * Usage: create a page, assign the "Plan du site" template (page-sitemap.html),
 * or call arw_pulse_html_sitemap() from any pattern.
 *
 * XML sitemap for search engines is handled by WordPress core (wp-sitemap.xml).
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Return HTML markup of the sitemap (pages, categories, tags, latest posts).
 */
function arw_pulse_html_sitemap() {
	ob_start();
	?>
	<div class="arw-sitemap">

		<section class="arw-sitemap__section">
			<h2><?php esc_html_e( 'Pages', 'arw-pulse' ); ?></h2>
			<ul>
				<?php
				$pages = get_pages( [ 'sort_column' => 'menu_order,post_title', 'post_status' => 'publish' ] );
				foreach ( $pages as $p ) {
					printf( '<li><a href="%s">%s</a></li>', esc_url( get_permalink( $p ) ), esc_html( get_the_title( $p ) ) );
				}
				?>
			</ul>
		</section>

		<section class="arw-sitemap__section">
			<h2><?php esc_html_e( 'Catégories', 'arw-pulse' ); ?></h2>
			<ul>
				<?php
				foreach ( get_categories( [ 'hide_empty' => true ] ) as $cat ) {
					printf( '<li><a href="%s">%s</a> <span class="arw-sitemap__count">(%d)</span></li>', esc_url( get_category_link( $cat ) ), esc_html( $cat->name ), (int) $cat->count );
				}
				?>
			</ul>
		</section>

		<section class="arw-sitemap__section">
			<h2><?php esc_html_e( 'Articles récents', 'arw-pulse' ); ?></h2>
			<ul>
				<?php
				$posts = get_posts( [ 'numberposts' => 50, 'orderby' => 'date', 'order' => 'DESC' ] );
				foreach ( $posts as $post ) {
					printf(
						'<li><time datetime="%s">%s</time> — <a href="%s">%s</a></li>',
						esc_attr( get_the_date( 'c', $post ) ),
						esc_html( get_the_date( '', $post ) ),
						esc_url( get_permalink( $post ) ),
						esc_html( get_the_title( $post ) )
					);
				}
				?>
			</ul>
		</section>

		<?php
		// ─── Custom Post Types : auto-discovery ──────────────────────────────────
		// Tout CPT public (hors page, post, attachment) est listé automatiquement.
		// Permet aux packs de niche (Maison, Délices, etc.) d'apparaître sans toucher au thème.
		$cpts = get_post_types( [ 'public' => true, '_builtin' => false ], 'objects' );
		$cpts = apply_filters( 'arw_pulse_sitemap_cpts', $cpts );

		foreach ( $cpts as $cpt ) :
			$cpt_posts = get_posts( [
				'post_type'      => $cpt->name,
				'numberposts'    => 500,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'post_status'    => 'publish',
				'no_found_rows'  => true,
			] );
			if ( empty( $cpt_posts ) ) { continue; }

			$label = ! empty( $cpt->labels->name ) ? $cpt->labels->name : $cpt->name;
			?>
			<section class="arw-sitemap__section">
				<h2><?php echo esc_html( $label ); ?> <span class="arw-sitemap__count">(<?php echo (int) count( $cpt_posts ); ?>)</span></h2>
				<ul>
					<?php foreach ( $cpt_posts as $cp ) : ?>
						<li><a href="<?php echo esc_url( get_permalink( $cp ) ); ?>"><?php echo esc_html( get_the_title( $cp ) ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</section>
			<?php
		endforeach;

		// ─── Custom taxonomies : auto-discovery ──────────────────────────────────
		$taxes = get_taxonomies( [ 'public' => true, '_builtin' => false ], 'objects' );
		$taxes = apply_filters( 'arw_pulse_sitemap_taxonomies', $taxes );

		foreach ( $taxes as $tax ) :
			$terms = get_terms( [ 'taxonomy' => $tax->name, 'hide_empty' => true, 'number' => 200 ] );
			if ( is_wp_error( $terms ) || empty( $terms ) ) { continue; }
			$label = ! empty( $tax->labels->name ) ? $tax->labels->name : $tax->name;
			?>
			<section class="arw-sitemap__section">
				<h2><?php echo esc_html( $label ); ?></h2>
				<ul>
					<?php foreach ( $terms as $term ) : ?>
						<li><a href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo esc_html( $term->name ); ?></a> <span class="arw-sitemap__count">(<?php echo (int) $term->count; ?>)</span></li>
					<?php endforeach; ?>
				</ul>
			</section>
			<?php
		endforeach;
		?>

		<?php $tags = get_tags( [ 'hide_empty' => true, 'number' => 100 ] ); if ( $tags ) : ?>
		<section class="arw-sitemap__section">
			<h2><?php esc_html_e( 'Tags', 'arw-pulse' ); ?></h2>
			<ul class="arw-sitemap__tags">
				<?php foreach ( $tags as $tag ) : ?>
					<li><a href="<?php echo esc_url( get_term_link( $tag ) ); ?>"><?php echo esc_html( $tag->name ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</section>
		<?php endif; ?>

	</div>
	<?php
	return ob_get_clean();
}

/**
 * Shortcode wrapper for convenience.
 */
add_shortcode( 'arw_sitemap', 'arw_pulse_html_sitemap' );

/**
 * Current year — usable in FSE templates via [arw_year].
 */
add_shortcode( 'arw_year', function () {
	return date_i18n( 'Y' );
} );
