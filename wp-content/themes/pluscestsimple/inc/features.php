<?php
/**
 * Feature toggles.
 * Activable/désactivable par site via un écran admin ou par filtre PHP.
 * Filter-priority 5 ici → un mu-plugin peut override à priority 10+.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

const ARW_PULSE_FEATURES_DEFAULTS = [
	'products' => true,
];

function arw_pulse_features(): array {
	return wp_parse_args( get_option( 'arw_pulse_features', [] ), ARW_PULSE_FEATURES_DEFAULTS );
}

// Bind admin option → filter.
add_filter( 'arw_pulse_enable_products', function () {
	return (bool) ( arw_pulse_features()['products'] ?? true );
}, 5 );

// ─── Admin page ──────────────────────────────────────────────────────────────

add_action( 'admin_menu', function () {
	add_submenu_page(
		ARW_PULSE_ADMIN_SLUG,
		__( 'Fonctionnalités', 'arw-pulse' ),
		__( 'Fonctionnalités', 'arw-pulse' ),
		'manage_options',
		'arw-features',
		'arw_pulse_render_features_page'
	);
} );

add_action( 'admin_init', function () {
	register_setting( 'arw_features', 'arw_pulse_features', [
		'type'              => 'array',
		'sanitize_callback' => function ( $input ) {
			$clean = [];
			foreach ( ARW_PULSE_FEATURES_DEFAULTS as $key => $default ) {
				$clean[ $key ] = ! empty( $input[ $key ] );
			}
			return $clean;
		},
		'default'           => ARW_PULSE_FEATURES_DEFAULTS,
	] );
} );

function arw_pulse_render_features_page(): void {
	$v = arw_pulse_features();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'ARW Pulse — Fonctionnalités', 'arw-pulse' ); ?></h1>
		<p style="max-width:720px;color:#50575e">
			<?php esc_html_e( 'Active ou désactive les modules du thème selon le type de site. Chaque module désactivé économise du poids PHP, retire ses écrans admin et ne s\'affiche nulle part en frontend.', 'arw-pulse' ); ?>
		</p>

		<form method="post" action="options.php">
			<?php settings_fields( 'arw_features' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Produits affiliés', 'arw-pulse' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="arw_pulse_features[products]" value="1" <?php checked( $v['products'] ); ?>>
							<?php esc_html_e( 'Activer le CPT Produits + shortcodes [arw_essential] / [arw_products]', 'arw-pulse' ); ?>
						</label>
						<p class="description">
							<?php esc_html_e( 'Recommandé pour les sites affiliation/review. Désactive pour les sites non-affiliés (cuisine, formation, agence, travaux…).', 'arw-pulse' ); ?>
						</p>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
