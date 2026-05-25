<?php
/**
 * Term meta `_pcs_banner_display_mode` sur la taxonomie pcs_banner_slot.
 *
 * Permet à l'admin de choisir, pour chaque slot, son comportement de rendu
 * quand aucune bannière n'est associée :
 *
 *   - `auto` (défaut)   → pub si dispo, sinon placeholder SVG
 *   - `banner-only`     → pub si dispo, sinon rien (le thème collapse le layout)
 *   - `hidden`          → jamais rien (collapse layout)
 *
 * Helpers publics :
 *   - pcs_banner_slot_mode( $slot )           : retourne 'auto'|'banner-only'|'hidden'
 *   - pcs_banner_slot_will_render( $slot )    : true si le thème doit garder l'espace
 *
 * @package PCS_Banners
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const PCS_BANNER_MODE_META   = '_pcs_banner_display_mode';
const PCS_BANNER_MODE_AUTO   = 'auto';
const PCS_BANNER_MODE_BANNER = 'banner-only';
const PCS_BANNER_MODE_HIDDEN = 'hidden';

/**
 * Liste des modes valides avec leur libellé pour l'admin.
 *
 * @return array<string, string>
 */
function pcs_banner_mode_choices(): array {
	return [
		PCS_BANNER_MODE_AUTO   => __( 'Auto — pub si disponible, sinon placeholder', 'pluscestsimple' ),
		PCS_BANNER_MODE_BANNER => __( 'Pub uniquement — rien si aucune pub (collapse layout)', 'pluscestsimple' ),
		PCS_BANNER_MODE_HIDDEN => __( 'Désactivé — jamais rien (collapse layout)', 'pluscestsimple' ),
	];
}

/**
 * Retourne le mode d'affichage configuré pour un slot.
 * Défaut : `auto` (rétro-compat avec le comportement v1.2.0 + placeholder).
 *
 * @param string $slot Slug du slot.
 * @return string Mode : 'auto', 'banner-only' ou 'hidden'.
 */
function pcs_banner_slot_mode( string $slot ): string {
	$term = get_term_by( 'slug', $slot, PCS_BANNER_SLOT_TAX );
	if ( ! $term instanceof WP_Term ) {
		return PCS_BANNER_MODE_AUTO;
	}
	$mode = (string) get_term_meta( $term->term_id, PCS_BANNER_MODE_META, true );
	$valid = array_keys( pcs_banner_mode_choices() );
	return in_array( $mode, $valid, true ) ? $mode : PCS_BANNER_MODE_AUTO;
}

/**
 * Indique si le slot doit rendre quelque chose de visible (pour que le thème
 * sache s'il doit garder l'espace dans son layout).
 *
 *   - mode `hidden`      → false (jamais rien)
 *   - mode `auto`        → true  (placeholder au minimum)
 *   - mode `banner-only` → true SI une pub existe, sinon false
 *
 * @param string $slot Slug du slot.
 * @return bool
 */
function pcs_banner_slot_will_render( string $slot ): bool {
	$mode = pcs_banner_slot_mode( $slot );
	if ( PCS_BANNER_MODE_HIDDEN === $mode ) {
		return false;
	}
	if ( PCS_BANNER_MODE_AUTO === $mode ) {
		return true;
	}
	// mode = banner-only → seulement si une bannière existe
	return null !== pcs_banner_pick( $slot );
}

// ─── Admin UI : champ select sur édition/création du terme ──────────────────

/**
 * Rend le champ select sur le formulaire d'édition d'un terme existant.
 */
function pcs_banner_render_mode_field_edit( WP_Term $term ): void {
	$current = (string) get_term_meta( $term->term_id, PCS_BANNER_MODE_META, true );
	if ( '' === $current ) {
		$current = PCS_BANNER_MODE_AUTO;
	}
	?>
	<tr class="form-field">
		<th scope="row"><label for="pcs-banner-display-mode"><?php esc_html_e( "Mode d'affichage", 'pluscestsimple' ); ?></label></th>
		<td>
			<select name="pcs_banner_display_mode" id="pcs-banner-display-mode">
				<?php foreach ( pcs_banner_mode_choices() as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current, $value ); ?>>
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<p class="description"><?php esc_html_e( 'Définit comment ce slot se comporte quand aucune bannière publiée ne lui est associée. Le placeholder SVG est généré automatiquement selon le format IAB du slot (300×600, 728×90, etc.).', 'pluscestsimple' ); ?></p>
		</td>
	</tr>
	<?php
}
add_action( 'pcs_banner_slot_edit_form_fields', 'pcs_banner_render_mode_field_edit' );

/**
 * Rend le champ select sur le formulaire de création d'un nouveau terme.
 */
function pcs_banner_render_mode_field_add(): void {
	?>
	<div class="form-field">
		<label for="pcs-banner-display-mode-add"><?php esc_html_e( "Mode d'affichage", 'pluscestsimple' ); ?></label>
		<select name="pcs_banner_display_mode" id="pcs-banner-display-mode-add">
			<?php foreach ( pcs_banner_mode_choices() as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( PCS_BANNER_MODE_AUTO, $value ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<p><?php esc_html_e( 'Définit comment ce slot se comporte quand aucune bannière publiée ne lui est associée.', 'pluscestsimple' ); ?></p>
	</div>
	<?php
}
add_action( 'pcs_banner_slot_add_form_fields', 'pcs_banner_render_mode_field_add' );

/**
 * Sauvegarde la valeur du champ select à la création/édition d'un terme.
 *
 * @param int $term_id ID du terme.
 */
function pcs_banner_save_mode_field( int $term_id ): void {
	if ( ! isset( $_POST['pcs_banner_display_mode'] ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_categories' ) ) {
		return;
	}
	$mode  = sanitize_key( wp_unslash( $_POST['pcs_banner_display_mode'] ) );
	$valid = array_keys( pcs_banner_mode_choices() );
	if ( ! in_array( $mode, $valid, true ) ) {
		$mode = PCS_BANNER_MODE_AUTO;
	}
	update_term_meta( $term_id, PCS_BANNER_MODE_META, $mode );
}
add_action( 'created_pcs_banner_slot', 'pcs_banner_save_mode_field' );
add_action( 'edited_pcs_banner_slot', 'pcs_banner_save_mode_field' );

// ─── Colonne "Mode" sur la liste des termes ─────────────────────────────────

/**
 * Ajoute la colonne "Mode" sur la liste des slots.
 *
 * @param array<string, string> $columns Colonnes existantes.
 * @return array<string, string>
 */
function pcs_banner_add_mode_column( array $columns ): array {
	$columns['pcs_banner_mode'] = __( "Mode d'affichage", 'pluscestsimple' );
	return $columns;
}
add_filter( 'manage_edit-pcs_banner_slot_columns', 'pcs_banner_add_mode_column' );

/**
 * Remplit la colonne "Mode" pour chaque slot.
 *
 * @param string $output      HTML existant.
 * @param string $column_name Nom de colonne.
 * @param int    $term_id     ID du terme.
 * @return string HTML.
 */
function pcs_banner_render_mode_column( string $output, string $column_name, int $term_id ): string {
	if ( 'pcs_banner_mode' !== $column_name ) {
		return $output;
	}
	$mode    = (string) get_term_meta( $term_id, PCS_BANNER_MODE_META, true );
	if ( '' === $mode ) {
		$mode = PCS_BANNER_MODE_AUTO;
	}
	$choices = pcs_banner_mode_choices();
	$label   = $choices[ $mode ] ?? $choices[ PCS_BANNER_MODE_AUTO ];
	$short   = explode( ' — ', $label )[0]; // garde juste "Auto" / "Pub uniquement" / "Désactivé"
	return esc_html( $short );
}
add_filter( 'manage_pcs_banner_slot_custom_column', 'pcs_banner_render_mode_column', 10, 3 );
