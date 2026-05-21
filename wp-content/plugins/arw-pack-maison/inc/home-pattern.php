<?php
/**
 * Override the ARW Pulse default front-page layout for the Maison niche.
 *
 * The default arw-pulse layout (citymoto) uses numbered categories, featured
 * comparisons, and partner marquees that don't fit a home/renovation site.
 * This filter replaces it with an editorial home structure:
 *   Hero (texts from seeded options) → Compatibilimètre showcase → Manifesto →
 *   Grid magazine (recent articles) → Full lead form
 *
 * The hero and manifesto read from arw_hp_hero / arw_hp_manifesto WP options
 * which are seeded by inc/home-content.php on first activation.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

// ─── Render-blocking : load compat.css async on front page only ──────────────
// La home a besoin de compat.css uniquement pour la section tease + lead form
// (tous deux assez bas dans la page). On charge en non-blocking via le trick
// media="print" → onload swap to "all". Trade-off : ~50ms de FOUC sur la
// section verte au premier render, contre ~540ms de TBT économisés.

add_filter( 'style_loader_tag', function ( $tag, $handle ) {
	if ( $handle !== 'arw-maison-compat' ) { return $tag; }
	if ( ! is_front_page() ) { return $tag; }

	// Swap media='all' → media='print' onload="this.media='all'"
	$tag = preg_replace(
		'/(media=[\'"])all([\'"])/',
		'$1print$2 onload="this.media=\'all\';this.onload=null"',
		$tag,
		1
	);
	// Fallback for no-JS users.
	$tag .= '<noscript><link rel="stylesheet" href="' . esc_url( ARW_MAISON_URL . 'assets/css/compat.css?ver=' . ARW_MAISON_VERSION ) . '"></noscript>';
	return $tag;
}, 10, 2 );

// Dispatcher home retiré : depuis le thème pluscestsimple v2.0, la home est éditée
// directement en Gutenberg via les patterns du thème (hero-front, section-compatibilimetre,
// section-newsletter, etc.). Le shortcode [arw_maison_compat_tease] reste disponible
// pour usage ponctuel dans le contenu Gutenberg ou un widget.

// ─── [arw_maison_compat_tease] : Compatibilimètre showcase section ────────────

add_shortcode( 'arw_maison_compat_tease', function () {
	// Load the CSS (registered in main plugin file).
	wp_enqueue_style( 'arw-maison-compat' );

	$compat_url = home_url( '/compatibilimetre/' );

	// Count published rules for the "N règles techniques" stat.
	$rule_count = wp_count_posts( ARW_MAISON_RULE_CPT );
	$n_rules    = $rule_count ? max( 0, (int) $rule_count->publish ) : 0;
	$stats_left = $n_rules > 0
		? sprintf( _n( '%d règle technique', '%d règles techniques', $n_rules, 'arw-maison' ), $n_rules )
		: '200+ règles techniques';

	ob_start();
	?>
	<section class="arw-compat-tease" aria-label="<?php esc_attr_e( 'Le Compatibilimètre', 'arw-maison' ); ?>">
		<div class="arw-compat-tease__inner">

			<p class="arw-compat-tease__eyebrow"><?php esc_html_e( 'Outil exclusif', 'arw-maison' ); ?></p>

			<h2 class="arw-compat-tease__title"><?php esc_html_e( 'Le Compatibilimètre', 'arw-maison' ); ?></h2>

			<p class="arw-compat-tease__text">
				<?php esc_html_e( 'Parquet sur carrelage. Verrière sans permis. Douche italienne sans receveur.', 'arw-maison' ); ?><br>
				<?php esc_html_e( 'En une question, vérifiez si votre idée est réalisable — et sous quelles conditions.', 'arw-maison' ); ?>
			</p>

			<a href="<?php echo esc_url( $compat_url ); ?>" class="arw-compat-tease__cta">
				<?php esc_html_e( 'Tester mon projet', 'arw-maison' ); ?> →
			</a>

			<p class="arw-compat-tease__stats">
				<?php echo esc_html( $stats_left ); ?> · <?php esc_html_e( '10 catégories', 'arw-maison' ); ?> · <?php esc_html_e( 'mis à jour régulièrement', 'arw-maison' ); ?>
			</p>

		</div>
	</section>
	<?php
	return (string) ob_get_clean();
} );
