<?php
/**
 * Legal defaults — injected into legal patterns via placeholders.
 * Editable via wp-admin → Réglages → Mentions légales.
 *
 * @package pluscestsimple
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'admin_menu', function () {
	add_submenu_page(
		'options-general.php',
		__( 'Mentions légales', 'pluscestsimple' ),
		__( 'Mentions légales', 'pluscestsimple' ),
		'manage_options',
		'pcs-legal',
		'pcs_render_legal_settings'
	);
} );

add_action( 'admin_init', function () {
	register_setting( 'pcs_legal', 'pcs_legal', [
		'type'              => 'array',
		'sanitize_callback' => 'pcs_sanitize_legal',
		'default'           => pcs_legal_defaults(),
	] );
} );

/**
 * Site-wide defaults (Anthony's standard).
 * These are applied automatically if the corresponding option is empty.
 */
function pcs_legal_defaults() {
	$host = wp_parse_url( home_url(), PHP_URL_HOST ) ?: 'site.fr';
	$host = preg_replace( '/^www\./i', '', $host );
	return [
		'publisher_name'     => 'Anthony Russo',
		'publisher_role'     => 'Directeur de la publication',
		'siret'              => '98497752000019',
		'host_name'          => 'OVH',
		'host_address'       => '2 rue Kellermann, 59100 Roubaix, France',
		'host_website'       => 'https://www.ovhcloud.com',
		'contact_email'      => 'contact@' . $host,
		'company_name'       => 'Anthony Russo EI',
		'address'            => '',
		'vat_number'         => '',
	];
}

function pcs_sanitize_legal( $input ) {
	$clean = [];
	foreach ( pcs_legal_defaults() as $k => $_default ) {
		$clean[ $k ] = isset( $input[ $k ] ) ? sanitize_text_field( $input[ $k ] ) : '';
	}
	if ( isset( $input['contact_email'] ) ) {
		$clean['contact_email'] = is_email( $input['contact_email'] ) ? sanitize_email( $input['contact_email'] ) : '';
	}
	if ( isset( $input['host_website'] ) ) {
		$clean['host_website'] = esc_url_raw( $input['host_website'] );
	}
	return $clean;
}

function pcs_render_legal_settings() {
	$v = wp_parse_args( get_option( 'pcs_legal', [] ), pcs_legal_defaults() );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Mentions légales — valeurs du site', 'pluscestsimple' ); ?></h1>
		<p><?php esc_html_e( 'Ces champs alimentent les patterns légaux (mentions, confidentialité, cookies).', 'pluscestsimple' ); ?></p>
		<form method="post" action="options.php">
			<?php settings_fields( 'pcs_legal' ); ?>
			<table class="form-table" role="presentation">
				<?php foreach ( $v as $k => $val ) : ?>
					<tr>
						<th><label><?php echo esc_html( ucwords( str_replace( '_', ' ', $k ) ) ); ?></label></th>
						<td><input type="text" class="regular-text" name="pcs_legal[<?php echo esc_attr( $k ); ?>]" value="<?php echo esc_attr( $val ); ?>"></td>
					</tr>
				<?php endforeach; ?>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Accessor.
 */
function pcs_legal( $key = null ) {
	$all = wp_parse_args( get_option( 'pcs_legal', [] ), pcs_legal_defaults() );
	return null === $key ? $all : ( $all[ $key ] ?? '' );
}

/**
 * Replace {{placeholders}} in a string with legal values.
 */
function pcs_fill_legal( $text ) {
	$vals = pcs_legal();
	foreach ( $vals as $k => $v ) {
		$text = str_replace( '{{' . strtoupper( $k ) . '}}', $v, $text );
	}
	return $text;
}
