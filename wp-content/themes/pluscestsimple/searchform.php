<?php
/**
 * Formulaire de recherche. Surchargé par get_search_form().
 *
 * @package pluscestsimple
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pcs_uid = 'pcs-searchform-' . wp_unique_id();
?>
<form role="search" method="get" class="pcs-searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $pcs_uid ); ?>">
		<?php esc_html_e( 'Rechercher :', 'pluscestsimple' ); ?>
	</label>
	<input
		type="search"
		id="<?php echo esc_attr( $pcs_uid ); ?>"
		class="pcs-searchform__input"
		name="s"
		value="<?php echo esc_attr( get_search_query() ); ?>"
		placeholder="<?php esc_attr_e( 'Que cherchez-vous ?', 'pluscestsimple' ); ?>"
	>
	<button type="submit" class="pcs-searchform__submit">
		<?php esc_html_e( 'Rechercher', 'pluscestsimple' ); ?>
	</button>
</form>
