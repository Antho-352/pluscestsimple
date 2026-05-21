<?php
/**
 * Enregistrement du bloc Gutenberg pcs/banner-slot (server-rendered).
 *
 * Le bloc est piloté par block.json à la racine du plugin. Le render PHP
 * délègue à pcs_banner_render( $slot ).
 *
 * Côté éditeur, on génère un petit script inline qui expose la liste des
 * slots disponibles dans un <SelectControl> Inspector — pas de build,
 * pas de dépendance npm.
 *
 * @package PCS_Banners
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Enregistre le bloc pcs/banner-slot avec render_callback PHP.
 *
 * @return void
 */
function pcs_banner_register_block(): void {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	register_block_type(
		PCS_BANNER_DIR . '/block.json',
		[
			'render_callback' => 'pcs_banner_block_render_callback',
		]
	);
}
add_action( 'init', 'pcs_banner_register_block', 20 );

/**
 * Callback de rendu côté front du bloc pcs/banner-slot.
 *
 * @param array<string, mixed> $attributes Attributs du bloc (slot).
 * @return string HTML rendu.
 */
function pcs_banner_block_render_callback( array $attributes ): string {
	$slot = isset( $attributes['slot'] ) ? sanitize_key( (string) $attributes['slot'] ) : 'homepage-mid';
	return pcs_banner_render( $slot );
}

/**
 * Enqueue l'éditeur JS du bloc (inline, zéro build).
 * Ajoute un Inspector Controls avec dropdown des slots existants.
 *
 * @return void
 */
function pcs_banner_enqueue_block_editor(): void {
	$handle = 'pcs-banner-block-editor';

	// Script vide enregistré pour qu'on puisse y attacher du inline.
	wp_register_script(
		$handle,
		'', // pas de fichier physique : on injecte tout en inline.
		[ 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n' ],
		PCS_BANNER_VERSION,
		true
	);

	// Liste des slots disponibles pour le SelectControl.
	$slots   = pcs_banner_get_all_slots();
	$options = [];
	foreach ( $slots as $slug => $name ) {
		$options[] = [
			'label' => $name . ' (' . $slug . ')',
			'value' => $slug,
		];
	}

	$slots_json = wp_json_encode( $options );

	$inline = <<<JS
( function ( wp ) {
	if ( ! wp || ! wp.blocks || ! wp.element ) { return; }
	var el                   = wp.element.createElement;
	var Fragment             = wp.element.Fragment;
	var registerBlockType    = wp.blocks.registerBlockType;
	var InspectorControls    = wp.blockEditor.InspectorControls;
	var useBlockProps        = wp.blockEditor.useBlockProps;
	var PanelBody            = wp.components.PanelBody;
	var SelectControl        = wp.components.SelectControl;
	var ServerSideRender     = wp.serverSideRender ? wp.serverSideRender.default || wp.serverSideRender : null;
	var __                   = wp.i18n.__;

	var slotOptions = {$slots_json};

	registerBlockType( 'pcs/banner-slot', {
		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps = useBlockProps();

			var preview = ServerSideRender
				? el( ServerSideRender, {
						block: 'pcs/banner-slot',
						attributes: { slot: attributes.slot }
				} )
				: el( 'div', {}, __( 'Aperçu indisponible.', 'pluscestsimple' ) );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Réglages bannière', 'pluscestsimple' ), initialOpen: true },
						el( SelectControl, {
							label: __( 'Emplacement (slot)', 'pluscestsimple' ),
							value: attributes.slot,
							options: slotOptions,
							onChange: function ( newSlot ) { setAttributes( { slot: newSlot } ); }
						} )
					)
				),
				el( 'div', blockProps, preview )
			);
		},
		save: function () { return null; } // server-rendered.
	} );
} )( window.wp );
JS;

	wp_add_inline_script( $handle, $inline );
	wp_enqueue_script( $handle );

	// On ne dépend pas explicitement de wp-server-side-render dans wp_register_script
	// pour rester compatible avec les vieilles versions. On l'enqueue séparément si dispo.
	if ( wp_script_is( 'wp-server-side-render', 'registered' ) ) {
		wp_enqueue_script( 'wp-server-side-render' );
	}
}
add_action( 'enqueue_block_editor_assets', 'pcs_banner_enqueue_block_editor' );
