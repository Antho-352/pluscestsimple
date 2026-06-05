<?php
/**
 * Champs SEO éditables par terme + réglages de la page mère + migration slugs.
 *
 * Ajoute aux écrans d'édition de termes (département, région, ville, catégorie) :
 *   - Texte d'introduction (HTML)  → meta _pcs_intro_html
 *   - Texte SEO bas de page (HTML) → meta _pcs_outro_html
 *
 * @package PCS_Directory
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

const PCS_DIR_EDITABLE_TAX = [ 'pcs_dept', 'pcs_region', 'pcs_ville', 'pcs_cat' ];

// ─── Champs sur l'écran « Ajouter un terme » ──────────────────────────────────

foreach ( PCS_DIR_EDITABLE_TAX as $tax ) {
	add_action( "{$tax}_add_form_fields", 'pcs_directory_term_add_fields' );
	add_action( "{$tax}_edit_form_fields", 'pcs_directory_term_edit_fields', 10, 2 );
	add_action( "created_{$tax}", 'pcs_directory_term_save_fields' );
	add_action( "edited_{$tax}",  'pcs_directory_term_save_fields' );
}

function pcs_directory_term_add_fields(): void {
	?>
	<div class="form-field">
		<label for="pcs_intro_html">Texte d'introduction (SEO)</label>
		<textarea name="pcs_intro_html" id="pcs_intro_html" rows="4"></textarea>
		<p>Affiché en haut de la page. Laisser vide pour un texte généré automatiquement.</p>
	</div>
	<div class="form-field">
		<label for="pcs_outro_html">Texte SEO bas de page</label>
		<textarea name="pcs_outro_html" id="pcs_outro_html" rows="4"></textarea>
		<p>Affiché en bas de page. Laisser vide pour un texte généré automatiquement.</p>
	</div>
	<?php wp_nonce_field( 'pcs_term_meta', 'pcs_term_meta_nonce' ); ?>
	<?php
}

function pcs_directory_term_edit_fields( WP_Term $term ): void {
	$intro = get_term_meta( $term->term_id, '_pcs_intro_html', true );
	$outro = get_term_meta( $term->term_id, '_pcs_outro_html', true );
	?>
	<tr class="form-field">
		<th scope="row"><label for="pcs_intro_html">Texte d'introduction (SEO)</label></th>
		<td>
			<textarea name="pcs_intro_html" id="pcs_intro_html" rows="5" class="large-text"><?php echo esc_textarea( (string) $intro ); ?></textarea>
			<p class="description">Affiché en haut de la page. Vide = texte auto-généré depuis les données.</p>
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row"><label for="pcs_outro_html">Texte SEO bas de page</label></th>
		<td>
			<textarea name="pcs_outro_html" id="pcs_outro_html" rows="6" class="large-text"><?php echo esc_textarea( (string) $outro ); ?></textarea>
			<p class="description">Affiché en bas de page. Vide = texte auto-généré. HTML simple autorisé (&lt;p&gt;, &lt;h2&gt;, &lt;a&gt;…).</p>
		</td>
	</tr>
	<?php wp_nonce_field( 'pcs_term_meta', 'pcs_term_meta_nonce' ); ?>
	<?php
}

function pcs_directory_term_save_fields( int $term_id ): void {
	if ( ! isset( $_POST['pcs_term_meta_nonce'] ) ) { return; }
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pcs_term_meta_nonce'] ) ), 'pcs_term_meta' ) ) { return; }
	if ( ! current_user_can( 'manage_categories' ) ) { return; }

	if ( isset( $_POST['pcs_intro_html'] ) ) {
		update_term_meta( $term_id, '_pcs_intro_html', wp_kses_post( wp_unslash( $_POST['pcs_intro_html'] ) ) );
	}
	if ( isset( $_POST['pcs_outro_html'] ) ) {
		update_term_meta( $term_id, '_pcs_outro_html', wp_kses_post( wp_unslash( $_POST['pcs_outro_html'] ) ) );
	}
}

// ─── Réglages page mère (Annuaire → Réglages) ─────────────────────────────────

add_action( 'admin_menu', function (): void {
	add_submenu_page(
		'edit.php?post_type=' . PCS_DIR_CPT,
		'Réglages annuaire',
		'Réglages',
		'manage_options',
		'pcs-directory-settings',
		'pcs_directory_settings_render'
	);
} );

function pcs_directory_settings_render(): void {
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Accès refusé.' ); }

	if (
		isset( $_POST['pcs_settings_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pcs_settings_nonce'] ) ), 'pcs_settings' )
	) {
		update_option( 'pcs_archive_intro', wp_kses_post( wp_unslash( $_POST['pcs_archive_intro'] ?? '' ) ) );
		update_option( 'pcs_archive_outro', wp_kses_post( wp_unslash( $_POST['pcs_archive_outro'] ?? '' ) ) );
		echo '<div class="notice notice-success"><p>Réglages enregistrés.</p></div>';
	}

	$intro = get_option( 'pcs_archive_intro', '' );
	$outro = get_option( 'pcs_archive_outro', '' );
	?>
	<div class="wrap pcs-directory-admin">
		<h1>Annuaire — Réglages</h1>
		<form method="post">
			<?php wp_nonce_field( 'pcs_settings', 'pcs_settings_nonce' ); ?>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="pcs_archive_intro">Intro page /annuaire/</label></th>
					<td>
						<textarea name="pcs_archive_intro" id="pcs_archive_intro" rows="4" class="large-text"><?php echo esc_textarea( (string) $intro ); ?></textarea>
						<p class="description">Texte d'introduction de la page mère. Vide = texte par défaut.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="pcs_archive_outro">Texte SEO bas de page</label></th>
					<td>
						<textarea name="pcs_archive_outro" id="pcs_archive_outro" rows="6" class="large-text"><?php echo esc_textarea( (string) $outro ); ?></textarea>
						<p class="description">Bas de la page mère. Vide = texte par défaut. HTML simple autorisé.</p>
					</td>
				</tr>
			</table>
			<?php submit_button( 'Enregistrer' ); ?>
		</form>
	</div>
	<?php
}

// ─── Migration : slugs département dept-45 → loiret-45 ────────────────────────

function pcs_directory_migrate_dept_terms(): void {
	if ( get_option( 'pcs_directory_dept_migrated' ) === '1' ) { return; }

	$terms = get_terms( [ 'taxonomy' => 'pcs_dept', 'hide_empty' => false ] );
	if ( is_wp_error( $terms ) ) { return; }

	foreach ( $terms as $term ) {
		$code = '';
		if ( preg_match( '/^dept-(\d{2,3}|2a|2b)$/i', $term->slug, $m ) ) {
			$code = strtolower( $m[1] );
		} elseif ( preg_match( '/-(\d{2,3}|2a|2b)$/i', $term->slug, $m ) ) {
			$code = strtolower( $m[1] );
		} elseif ( preg_match( '/^(\d{2,3}|2a|2b)$/i', $term->name, $m ) ) {
			$code = strtolower( $m[1] );
		}
		if ( '' === $code ) { continue; }

		$name = pcs_directory_dept_name( $code );
		$slug = sanitize_title( $name ) . '-' . $code;

		wp_update_term( $term->term_id, 'pcs_dept', [ 'name' => $name, 'slug' => $slug ] );
		update_term_meta( $term->term_id, '_pcs_dept_code', $code );
	}

	update_option( 'pcs_directory_dept_migrated', '1' );
	flush_rewrite_rules( false );
}
add_action( 'admin_init', 'pcs_directory_migrate_dept_terms' );
