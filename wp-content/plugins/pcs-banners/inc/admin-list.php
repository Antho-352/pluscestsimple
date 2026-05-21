<?php
/**
 * Customisation de la liste admin du CPT pcs_banner.
 *
 * - Colonnes : Aperçu, Titre, Type (badge), Slot, Période (badge statut), URL.
 * - Filtres : type, slot, statut temporel.
 * - Tri : titre (natif), période start, type.
 *
 * @package PCS_Banners
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// ─── Définition des colonnes ─────────────────────────────────────────────────

/**
 * Définit les colonnes de la liste admin.
 *
 * @param array<string, string> $cols Colonnes existantes.
 * @return array<string, string>
 */
function pcs_banner_admin_columns( array $cols ): array {
	$new = [
		'cb'                  => $cols['cb'] ?? '',
		'pcs_banner_preview'  => __( 'Aperçu', 'pluscestsimple' ),
		'title'               => __( 'Titre', 'pluscestsimple' ),
		'pcs_banner_type'     => __( 'Type', 'pluscestsimple' ),
		'pcs_banner_slot'     => __( 'Emplacement', 'pluscestsimple' ),
		'pcs_banner_period'   => __( 'Période', 'pluscestsimple' ),
		'pcs_banner_url'      => __( 'URL', 'pluscestsimple' ),
		'date'                => __( 'Date', 'pluscestsimple' ),
	];
	return $new;
}
add_filter( 'manage_' . PCS_BANNER_CPT . '_posts_columns', 'pcs_banner_admin_columns' );

/**
 * Rend le contenu de chaque colonne custom.
 *
 * @param string $column  Nom de la colonne.
 * @param int    $post_id ID du post.
 * @return void
 */
function pcs_banner_admin_render_column( string $column, int $post_id ): void {
	switch ( $column ) {
		case 'pcs_banner_preview':
			$thumb_id = (int) get_post_thumbnail_id( $post_id );
			if ( $thumb_id ) {
				$src = wp_get_attachment_image_src( $thumb_id, [ 60, 40 ] );
				if ( is_array( $src ) ) {
					printf(
						'<img src="%s" alt="" style="width:60px;height:40px;object-fit:cover;border:1px solid #ddd;border-radius:3px;" />',
						esc_url( $src[0] )
					);
				}
			} else {
				echo '<span style="color:#999;">—</span>';
			}
			break;

		case 'pcs_banner_type':
			$type = (string) get_post_meta( $post_id, '_pcs_banner_type', true );
			if ( '' === $type ) {
				$type = 'display';
			}
			$colors = [
				'display'   => [ 'bg' => '#e3f2fd', 'fg' => '#1565c0', 'label' => __( 'Display', 'pluscestsimple' ) ],
				'sponsored' => [ 'bg' => '#fff3e0', 'fg' => '#e65100', 'label' => __( 'Sponsorisé', 'pluscestsimple' ) ],
				'affilie'   => [ 'bg' => '#e8f5e9', 'fg' => '#2e7d32', 'label' => __( 'Affilié', 'pluscestsimple' ) ],
			];
			$c = $colors[ $type ] ?? $colors['display'];
			printf(
				'<span style="display:inline-block;padding:2px 8px;border-radius:10px;background:%s;color:%s;font-size:11px;font-weight:600;">%s</span>',
				esc_attr( $c['bg'] ),
				esc_attr( $c['fg'] ),
				esc_html( $c['label'] )
			);
			break;

		case 'pcs_banner_slot':
			$terms = get_the_terms( $post_id, PCS_BANNER_SLOT_TAX );
			if ( $terms && ! is_wp_error( $terms ) ) {
				$names = [];
				foreach ( $terms as $term ) {
					$names[] = esc_html( $term->name );
				}
				echo implode( ', ', $names );
			} else {
				echo '<span style="color:#999;">—</span>';
			}
			break;

		case 'pcs_banner_period':
			$start = (string) get_post_meta( $post_id, '_pcs_banner_start_date', true );
			$end   = (string) get_post_meta( $post_id, '_pcs_banner_end_date', true );
			$now   = current_time( 'Y-m-d\TH:i' );

			$status     = 'active';
			$status_lbl = __( 'Actif', 'pluscestsimple' );
			$bg         = '#e8f5e9';
			$fg         = '#2e7d32';

			if ( '' !== $start && $start > $now ) {
				$status     = 'scheduled';
				$status_lbl = __( 'Programmé', 'pluscestsimple' );
				$bg         = '#e1f5fe';
				$fg         = '#01579b';
			} elseif ( '' !== $end && $end < $now ) {
				$status     = 'expired';
				$status_lbl = __( 'Expiré', 'pluscestsimple' );
				$bg         = '#ffebee';
				$fg         = '#c62828';
			}

			printf(
				'<span style="display:inline-block;padding:2px 8px;border-radius:10px;background:%s;color:%s;font-size:11px;font-weight:600;">%s</span><br />',
				esc_attr( $bg ),
				esc_attr( $fg ),
				esc_html( $status_lbl )
			);

			$start_display = '' !== $start ? str_replace( 'T', ' ', $start ) : '—';
			$end_display   = '' !== $end ? str_replace( 'T', ' ', $end ) : '—';
			printf(
				'<small style="color:#666;">%s &rarr; %s</small>',
				esc_html( $start_display ),
				esc_html( $end_display )
			);
			break;

		case 'pcs_banner_url':
			$url = (string) get_post_meta( $post_id, '_pcs_banner_url', true );
			if ( '' !== $url ) {
				printf(
					'<a href="%1$s" target="_blank" rel="noopener" title="%1$s">%2$s</a>',
					esc_url( $url ),
					esc_html( wp_parse_url( $url, PHP_URL_HOST ) ?: $url )
				);
			} else {
				echo '<span style="color:#999;">—</span>';
			}
			break;
	}
}
add_action( 'manage_' . PCS_BANNER_CPT . '_posts_custom_column', 'pcs_banner_admin_render_column', 10, 2 );

// ─── Colonnes triables ───────────────────────────────────────────────────────

/**
 * Marque les colonnes triables.
 *
 * @param array<string, string> $sortable Colonnes triables existantes.
 * @return array<string, string>
 */
function pcs_banner_admin_sortable_columns( array $sortable ): array {
	$sortable['pcs_banner_type']   = 'pcs_banner_type';
	$sortable['pcs_banner_period'] = 'pcs_banner_period_start';
	return $sortable;
}
add_filter( 'manage_edit-' . PCS_BANNER_CPT . '_sortable_columns', 'pcs_banner_admin_sortable_columns' );

/**
 * Applique le tri sur les colonnes meta.
 *
 * @param WP_Query $query Query courante.
 * @return void
 */
function pcs_banner_admin_orderby( WP_Query $query ): void {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( $query->get( 'post_type' ) !== PCS_BANNER_CPT ) {
		return;
	}

	$orderby = $query->get( 'orderby' );

	if ( 'pcs_banner_type' === $orderby ) {
		$query->set( 'meta_key', '_pcs_banner_type' );
		$query->set( 'orderby', 'meta_value' );
	} elseif ( 'pcs_banner_period_start' === $orderby ) {
		$query->set( 'meta_key', '_pcs_banner_start_date' );
		$query->set( 'orderby', 'meta_value' );
	}
}
add_action( 'pre_get_posts', 'pcs_banner_admin_orderby' );

// ─── Filtres (dropdown au-dessus de la liste) ────────────────────────────────

/**
 * Affiche les dropdowns de filtre : type, slot, statut temporel.
 *
 * @param string $post_type Post type courant.
 * @return void
 */
function pcs_banner_admin_filters( string $post_type ): void {
	if ( PCS_BANNER_CPT !== $post_type ) {
		return;
	}

	// Filtre par type.
	$current_type = isset( $_GET['pcs_banner_type'] ) ? sanitize_key( wp_unslash( $_GET['pcs_banner_type'] ) ) : '';
	$types        = [
		''          => __( 'Tous les types', 'pluscestsimple' ),
		'display'   => __( 'Display', 'pluscestsimple' ),
		'sponsored' => __( 'Sponsorisé', 'pluscestsimple' ),
		'affilie'   => __( 'Affilié', 'pluscestsimple' ),
	];
	echo '<select name="pcs_banner_type">';
	foreach ( $types as $value => $label ) {
		printf(
			'<option value="%s" %s>%s</option>',
			esc_attr( $value ),
			selected( $current_type, $value, false ),
			esc_html( $label )
		);
	}
	echo '</select>';

	// Filtre par slot (term).
	$current_slot = isset( $_GET['pcs_banner_slot_filter'] ) ? sanitize_key( wp_unslash( $_GET['pcs_banner_slot_filter'] ) ) : '';
	$slots        = pcs_banner_get_all_slots();
	echo '<select name="pcs_banner_slot_filter">';
	printf(
		'<option value="" %s>%s</option>',
		selected( $current_slot, '', false ),
		esc_html__( 'Tous les emplacements', 'pluscestsimple' )
	);
	foreach ( $slots as $slug => $name ) {
		printf(
			'<option value="%s" %s>%s</option>',
			esc_attr( $slug ),
			selected( $current_slot, $slug, false ),
			esc_html( $name )
		);
	}
	echo '</select>';

	// Filtre par statut temporel.
	$current_status = isset( $_GET['pcs_banner_status'] ) ? sanitize_key( wp_unslash( $_GET['pcs_banner_status'] ) ) : '';
	$statuses       = [
		''          => __( 'Tous les statuts', 'pluscestsimple' ),
		'active'    => __( 'Actif', 'pluscestsimple' ),
		'scheduled' => __( 'Programmé', 'pluscestsimple' ),
		'expired'   => __( 'Expiré', 'pluscestsimple' ),
	];
	echo '<select name="pcs_banner_status">';
	foreach ( $statuses as $value => $label ) {
		printf(
			'<option value="%s" %s>%s</option>',
			esc_attr( $value ),
			selected( $current_status, $value, false ),
			esc_html( $label )
		);
	}
	echo '</select>';
}
add_action( 'restrict_manage_posts', 'pcs_banner_admin_filters' );

/**
 * Applique les filtres au query principal.
 *
 * @param WP_Query $query Query courante.
 * @return void
 */
function pcs_banner_admin_apply_filters( WP_Query $query ): void {
	global $pagenow;
	if ( ! is_admin() || 'edit.php' !== $pagenow ) {
		return;
	}
	if ( ! $query->is_main_query() ) {
		return;
	}
	if ( $query->get( 'post_type' ) !== PCS_BANNER_CPT ) {
		return;
	}

	$meta_query = $query->get( 'meta_query' );
	if ( ! is_array( $meta_query ) ) {
		$meta_query = [];
	}

	// Filtre type.
	if ( ! empty( $_GET['pcs_banner_type'] ) ) {
		$meta_query[] = [
			'key'   => '_pcs_banner_type',
			'value' => sanitize_key( wp_unslash( $_GET['pcs_banner_type'] ) ),
		];
	}

	// Filtre statut temporel.
	$now = current_time( 'Y-m-d\TH:i' );
	if ( ! empty( $_GET['pcs_banner_status'] ) ) {
		$status = sanitize_key( wp_unslash( $_GET['pcs_banner_status'] ) );
		if ( 'active' === $status ) {
			$meta_query[] = [
				'relation' => 'AND',
				[
					'relation' => 'OR',
					[ 'key' => '_pcs_banner_start_date', 'value' => $now, 'compare' => '<=' ],
					[ 'key' => '_pcs_banner_start_date', 'value' => '', 'compare' => '=' ],
					[ 'key' => '_pcs_banner_start_date', 'compare' => 'NOT EXISTS' ],
				],
				[
					'relation' => 'OR',
					[ 'key' => '_pcs_banner_end_date', 'value' => $now, 'compare' => '>=' ],
					[ 'key' => '_pcs_banner_end_date', 'value' => '', 'compare' => '=' ],
					[ 'key' => '_pcs_banner_end_date', 'compare' => 'NOT EXISTS' ],
				],
			];
		} elseif ( 'scheduled' === $status ) {
			$meta_query[] = [
				'key'     => '_pcs_banner_start_date',
				'value'   => $now,
				'compare' => '>',
			];
		} elseif ( 'expired' === $status ) {
			$meta_query[] = [
				'key'     => '_pcs_banner_end_date',
				'value'   => $now,
				'compare' => '<',
			];
		}
	}

	if ( ! empty( $meta_query ) ) {
		$query->set( 'meta_query', $meta_query );
	}

	// Filtre slot (taxonomy).
	if ( ! empty( $_GET['pcs_banner_slot_filter'] ) ) {
		$tax_query   = $query->get( 'tax_query' );
		if ( ! is_array( $tax_query ) ) {
			$tax_query = [];
		}
		$tax_query[] = [
			'taxonomy' => PCS_BANNER_SLOT_TAX,
			'field'    => 'slug',
			'terms'    => sanitize_key( wp_unslash( $_GET['pcs_banner_slot_filter'] ) ),
		];
		$query->set( 'tax_query', $tax_query );
	}
}
add_action( 'pre_get_posts', 'pcs_banner_admin_apply_filters' );
