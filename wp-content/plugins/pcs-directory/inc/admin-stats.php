<?php
/**
 * Admin — Annuaire > Statistiques.
 *
 * Affiche :
 *  - Nb boutiques par département
 *  - Nb boutiques par catégorie
 *  - Taux de complétude (website, phone, hours, geo)
 *
 * @package PCS_Directory
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'admin_menu', function (): void {
	add_submenu_page(
		'edit.php?post_type=' . PCS_DIR_CPT,
		'Statistiques annuaire',
		'Statistiques',
		'manage_options',
		'pcs-directory-stats',
		'pcs_directory_admin_stats_render'
	);
} );

function pcs_directory_admin_stats_render(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Accès refusé.' );
	}

	$total_published = (int) wp_count_posts( PCS_DIR_CPT )->publish;

	// Complétude via requête meta.
	global $wpdb;
	$has_website = (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta}
		 WHERE meta_key = %s AND meta_value != '' AND meta_value != '0'",
		'_pcs_website'
	) );
	$has_phone = (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta}
		 WHERE meta_key = %s AND meta_value != '' AND meta_value != '0'",
		'_pcs_phone'
	) );
	$has_hours = (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta}
		 WHERE meta_key = %s AND meta_value != '' AND meta_value != '0'",
		'_pcs_hours'
	) );
	$has_geo = (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta}
		 WHERE meta_key = %s AND meta_value != '' AND meta_value != '0'",
		'_pcs_lat'
	) );

	$pct = fn( int $n ) => $total_published > 0 ? round( $n * 100 / $total_published, 1 ) : 0;

	// Termes par taxonomie.
	$depts = get_terms( [ 'taxonomy' => 'pcs_dept', 'orderby' => 'count', 'order' => 'DESC', 'number' => 20, 'hide_empty' => true ] );
	$cats  = get_terms( [ 'taxonomy' => 'pcs_cat',  'orderby' => 'count', 'order' => 'DESC', 'hide_empty' => true ] );
	$types = get_terms( [ 'taxonomy' => 'pcs_type', 'orderby' => 'count', 'order' => 'DESC', 'hide_empty' => true ] );

	?>
	<div class="wrap pcs-directory-admin">
		<h1>Annuaire — Statistiques</h1>

		<h2>Complétude globale</h2>
		<table class="widefat striped" style="max-width:500px">
			<thead><tr><th>Champ</th><th>N</th><th>%</th></tr></thead>
			<tbody>
				<tr><td>Total boutiques publiées</td><td><strong><?php echo $total_published; ?></strong></td><td>—</td></tr>
				<tr><td>Avec site web</td><td><?php echo $has_website; ?></td><td><?php echo $pct( $has_website ); ?>%</td></tr>
				<tr><td>Avec téléphone</td><td><?php echo $has_phone; ?></td><td><?php echo $pct( $has_phone ); ?>%</td></tr>
				<tr><td>Avec horaires</td><td><?php echo $has_hours; ?></td><td><?php echo $pct( $has_hours ); ?>%</td></tr>
				<tr><td>Géolocalisées</td><td><?php echo $has_geo; ?></td><td><?php echo $pct( $has_geo ); ?>%</td></tr>
			</tbody>
		</table>

		<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:2rem;margin-top:2rem;max-width:960px">

			<div>
				<h2>Par département (top 20)</h2>
				<table class="widefat striped">
					<thead><tr><th>Dép.</th><th>Boutiques</th></tr></thead>
					<tbody>
						<?php if ( is_array( $depts ) ) : foreach ( $depts as $term ) : ?>
							<tr>
								<td><a href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo esc_html( $term->name ); ?></a></td>
								<td><?php echo (int) $term->count; ?></td>
							</tr>
						<?php endforeach; endif; ?>
					</tbody>
				</table>
			</div>

			<div>
				<h2>Par catégorie</h2>
				<table class="widefat striped">
					<thead><tr><th>Catégorie</th><th>N</th></tr></thead>
					<tbody>
						<?php if ( is_array( $cats ) ) : foreach ( $cats as $term ) : ?>
							<tr>
								<td><?php echo esc_html( $term->name ); ?></td>
								<td><?php echo (int) $term->count; ?></td>
							</tr>
						<?php endforeach; endif; ?>
					</tbody>
				</table>
			</div>

			<div>
				<h2>Par type</h2>
				<table class="widefat striped">
					<thead><tr><th>Type</th><th>N</th></tr></thead>
					<tbody>
						<?php if ( is_array( $types ) ) : foreach ( $types as $term ) : ?>
							<tr>
								<td><?php echo esc_html( $term->name ); ?></td>
								<td><?php echo (int) $term->count; ?></td>
							</tr>
						<?php endforeach; endif; ?>
					</tbody>
				</table>
			</div>

		</div>
	</div>
	<?php
}
