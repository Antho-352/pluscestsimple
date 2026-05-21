<?php
/**
 * Génération de l'index JSON statique des règles du Compatibilimètre.
 *
 * Stratégie : à chaque save/delete d'une règle, on régénère un fichier JSON unique
 * dans wp-content/uploads/arw-maison/rules-index.json. Ce fichier est servi au front
 * pour la recherche fuzzy côté client (zéro requête WP, cacheable Cloudflare).
 *
 * Format JSON :
 *   {
 *     "version": "2026-04-27T20:00:00Z",
 *     "rules": [
 *       {
 *         "id": 123, "title": "...", "slug": "...", "url": "https://…/compatibilimetre/regle/...",
 *         "verdict": "compatible", "category": "sols",
 *         "explanation": "...", "alternatives": "...",
 *         "keywords": ["parquet","plancher chauffant",...]
 *       }
 *     ]
 *   }
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

const ARW_MAISON_INDEX_DIR  = 'arw-maison';
const ARW_MAISON_INDEX_FILE = 'rules-index.json';

// ─── Helpers ─────────────────────────────────────────────────────────────────

function arw_maison_index_path(): string {
	$uploads = wp_get_upload_dir();
	$dir     = trailingslashit( $uploads['basedir'] ) . ARW_MAISON_INDEX_DIR;
	if ( ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );
		// Protect direct listing (Apache).
		$htaccess = $dir . '/.htaccess';
		if ( ! file_exists( $htaccess ) ) {
			file_put_contents( $htaccess, "Options -Indexes\n<FilesMatch \"\\.json$\">\n  Header set Cache-Control \"public, max-age=300\"\n</FilesMatch>\n" );
		}
	}
	return $dir . '/' . ARW_MAISON_INDEX_FILE;
}

function arw_maison_index_url(): string {
	$uploads = wp_get_upload_dir();
	return trailingslashit( $uploads['baseurl'] ) . ARW_MAISON_INDEX_DIR . '/' . ARW_MAISON_INDEX_FILE;
}

// ─── Generation ──────────────────────────────────────────────────────────────

function arw_maison_generate_index(): array {
	$rules = [];

	$q = new WP_Query( [
		'post_type'      => ARW_MAISON_RULE_CPT,
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
		'no_found_rows'  => true,
	] );

	foreach ( $q->posts as $post ) {
		$verdict      = (string) get_post_meta( $post->ID, '_arw_rule_verdict', true );
		$keywords_csv = (string) get_post_meta( $post->ID, '_arw_rule_keywords', true );

		// Skip rules without verdict (incomplete drafts).
		if ( ! $verdict ) { continue; }

		$keywords = array_values( array_filter( array_map( 'trim', explode( ',', $keywords_csv ) ) ) );

		// Catégorie principale (premier terme).
		$cats     = wp_get_post_terms( $post->ID, ARW_MAISON_CATEGORY_TAX, [ 'fields' => 'slugs' ] );
		$category = ! is_wp_error( $cats ) && ! empty( $cats ) ? (string) $cats[0] : '';

		$rules[] = [
			'id'           => $post->ID,
			'title'        => $post->post_title,
			'slug'         => $post->post_name,
			'url'          => get_permalink( $post->ID ),
			'verdict'      => $verdict,
			'category'     => $category,
			'explanation'  => (string) get_post_meta( $post->ID, '_arw_rule_explanation', true ),
			'alternatives' => (string) get_post_meta( $post->ID, '_arw_rule_alternatives', true ),
			'keywords'     => $keywords,
		];
	}

	$payload = [
		'version' => gmdate( 'c' ),
		'count'   => count( $rules ),
		'rules'   => $rules,
	];

	$path  = arw_maison_index_path();
	$json  = wp_json_encode( $payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	$bytes = file_put_contents( $path, $json, LOCK_EX );

	return [
		'path'  => $path,
		'url'   => arw_maison_index_url(),
		'count' => count( $rules ),
		'bytes' => $bytes !== false ? (int) $bytes : 0,
	];
}

// ─── Triggers ────────────────────────────────────────────────────────────────

// On save of a rule (create or update).
add_action( 'save_post_' . ARW_MAISON_RULE_CPT, function ( $post_id, $post, $update ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) { return; }
	if ( get_post_status( $post_id ) === 'auto-draft' ) { return; }
	// Defer to shutdown to ensure all meta is saved (notably from CSV import / REST).
	add_action( 'shutdown', 'arw_maison_generate_index', 99 );
}, 99, 3 );

add_action( 'before_delete_post', function ( $post_id ) {
	if ( get_post_type( $post_id ) !== ARW_MAISON_RULE_CPT ) { return; }
	add_action( 'shutdown', 'arw_maison_generate_index', 99 );
} );

add_action( 'set_object_terms', function ( $post_id, $terms, $tt_ids, $taxonomy ) {
	if ( $taxonomy !== ARW_MAISON_CATEGORY_TAX ) { return; }
	if ( get_post_type( $post_id ) !== ARW_MAISON_RULE_CPT ) { return; }
	add_action( 'shutdown', 'arw_maison_generate_index', 99 );
}, 10, 4 );

// ─── Manual rebuild button (admin) ───────────────────────────────────────────

add_action( 'admin_menu', function () {
	$parent = 'edit.php?post_type=' . ARW_MAISON_RULE_CPT;
	add_submenu_page(
		$parent,
		__( 'Index Compatibilimètre', 'arw-maison' ),
		__( 'Index JSON',             'arw-maison' ),
		'manage_options',
		'arw-maison-index',
		'arw_maison_index_admin_page'
	);
}, 31 );

function arw_maison_index_admin_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( __( 'Accès refusé.', 'arw-maison' ) ); }

	$result = null;
	if ( isset( $_POST['arw_index_nonce'] ) && wp_verify_nonce( $_POST['arw_index_nonce'], 'arw_index_rebuild' ) ) {
		$result = arw_maison_generate_index();
	}

	$path   = arw_maison_index_path();
	$url    = arw_maison_index_url();
	$exists = file_exists( $path );
	$size   = $exists ? size_format( filesize( $path ) ) : '—';
	$mtime  = $exists ? wp_date( 'd/m/Y H:i:s', filemtime( $path ) ) : '—';

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Index JSON Compatibilimètre', 'arw-maison' ); ?></h1>

		<?php if ( $result ) : ?>
			<div class="notice notice-success">
				<p><?php printf( esc_html__( 'Index régénéré : %d règles, %s.', 'arw-maison' ), (int) $result['count'], esc_html( size_format( $result['bytes'] ) ) ); ?></p>
			</div>
		<?php endif; ?>

		<div style="background:#fff;padding:20px;border:1px solid #c3c4c7;max-width:900px;margin-top:20px">
			<table class="form-table">
				<tr>
					<th><?php esc_html_e( 'Statut', 'arw-maison' ); ?></th>
					<td><?php echo $exists ? '<span style="color:#1f3a2e">✓ Présent</span>' : '<span style="color:#d63638">⚠ Absent</span>'; ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'URL', 'arw-maison' ); ?></th>
					<td><a href="<?php echo esc_url( $url ); ?>" target="_blank"><?php echo esc_html( $url ); ?></a></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Taille', 'arw-maison' ); ?></th>
					<td><?php echo esc_html( $size ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Dernière régénération', 'arw-maison' ); ?></th>
					<td><?php echo esc_html( $mtime ); ?></td>
				</tr>
			</table>

			<form method="post" style="margin-top:20px">
				<?php wp_nonce_field( 'arw_index_rebuild', 'arw_index_nonce' ); ?>
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Régénérer l\'index maintenant', 'arw-maison' ); ?></button>
			</form>

			<p style="margin-top:20px;color:#6b7280;font-size:13px">
				<?php esc_html_e( 'L\'index est régénéré automatiquement à chaque création / modification / suppression de règle. Le bouton ci-dessus est un fallback manuel.', 'arw-maison' ); ?>
			</p>
		</div>
	</div>
	<?php
}
