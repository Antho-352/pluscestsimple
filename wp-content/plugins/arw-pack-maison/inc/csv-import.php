<?php
/**
 * Import CSV bulk pour les règles du Compatibilimètre.
 *
 * Admin → Compatibilimètre → Import CSV
 * Format attendu : title;category_slug;verdict;explanation;alternatives;keywords;dtu_refs
 * Première ligne = entêtes (ignorée).
 * Séparateur configurable (défaut ;).
 *
 * Comportement :
 *  - Si une règle avec le même slug existe : update.
 *  - Sinon : create.
 *  - Si la catégorie n'existe pas : crée le terme.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

const ARW_MAISON_IMPORT_CAP = 'manage_options';

add_action( 'admin_menu', function () {
	$parent = defined( 'ARW_PULSE_ADMIN_SLUG' ) ? ARW_PULSE_ADMIN_SLUG : 'edit.php?post_type=' . ARW_MAISON_RULE_CPT;
	add_submenu_page(
		$parent,
		__( 'Import CSV règles', 'arw-maison' ),
		__( 'Import CSV',        'arw-maison' ),
		ARW_MAISON_IMPORT_CAP,
		'arw-maison-csv-import',
		'arw_maison_csv_import_page'
	);
}, 30 );

function arw_maison_csv_import_page(): void {
	if ( ! current_user_can( ARW_MAISON_IMPORT_CAP ) ) {
		wp_die( __( 'Accès refusé.', 'arw-maison' ) );
	}

	$result = null;

	if ( isset( $_POST['arw_csv_import_nonce'] ) && wp_verify_nonce( $_POST['arw_csv_import_nonce'], 'arw_csv_import' ) ) {
		$result = arw_maison_handle_csv_upload();
	}

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Import CSV — Règles Compatibilimètre', 'arw-maison' ); ?></h1>

		<?php if ( $result ) : ?>
			<div class="notice notice-<?php echo $result['error'] ? 'error' : 'success'; ?>">
				<p><?php echo wp_kses_post( $result['message'] ); ?></p>
				<?php if ( ! empty( $result['errors'] ) ) : ?>
					<ul style="margin-left:20px;list-style:disc">
						<?php foreach ( $result['errors'] as $err ) : ?>
							<li><?php echo esc_html( $err ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div style="background:#fff;padding:20px;border:1px solid #c3c4c7;max-width:900px;margin-top:20px">
			<h2 style="margin-top:0"><?php esc_html_e( 'Format attendu', 'arw-maison' ); ?></h2>
			<p><?php esc_html_e( 'CSV UTF-8, séparateur point-virgule, première ligne = entêtes.', 'arw-maison' ); ?></p>
			<pre style="background:#f0f0f1;padding:12px;font-size:12px;overflow:auto;border-radius:3px">title;category_slug;verdict;explanation;alternatives;keywords;dtu_refs
"Parquet massif sur plancher chauffant ?";"sols";"conditional";"Possible si essence stable (chêne, châtaignier) ≤14mm, pose collée, montée en T° progressive.";"Stratifié compatible PCBT, parquet contrecollé";"parquet, massif, plancher chauffant, hydraulique";"DTU 51.2"
"Casser un mur porteur soi-même ?";"structure";"forbidden";"Étude béton armé obligatoire + IPN dimensionné par BET. Risque effondrement + non-assurance.";"Faire intervenir un BET structure (300-800€), demander DP en mairie";"casser, mur porteur, BET, IPN";"NF P 06-001"</pre>

			<h3><?php esc_html_e( 'Verdicts acceptés', 'arw-maison' ); ?></h3>
			<ul style="list-style:disc;margin-left:20px">
				<?php foreach ( arw_maison_verdicts() as $k => $label ) : ?>
					<li><code><?php echo esc_html( $k ); ?></code> → <?php echo esc_html( $label ); ?></li>
				<?php endforeach; ?>
			</ul>

			<h3><?php esc_html_e( 'Catégories', 'arw-maison' ); ?></h3>
			<p><?php esc_html_e( 'Si le slug catégorie n\'existe pas, il sera créé automatiquement.', 'arw-maison' ); ?></p>
		</div>

		<form method="post" enctype="multipart/form-data" style="background:#fff;padding:20px;border:1px solid #c3c4c7;max-width:900px;margin-top:20px">
			<?php wp_nonce_field( 'arw_csv_import', 'arw_csv_import_nonce' ); ?>
			<h2 style="margin-top:0"><?php esc_html_e( 'Téléverser un fichier CSV', 'arw-maison' ); ?></h2>
			<p>
				<input type="file" name="arw_csv_file" accept=".csv" required>
			</p>
			<p>
				<label>
					<input type="checkbox" name="arw_csv_dry_run" value="1">
					<?php esc_html_e( 'Mode "dry-run" (simulation, n\'écrit rien)', 'arw-maison' ); ?>
				</label>
			</p>
			<p>
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Importer', 'arw-maison' ); ?></button>
			</p>
		</form>
	</div>
	<?php
}

function arw_maison_handle_csv_upload(): array {
	if ( empty( $_FILES['arw_csv_file']['tmp_name'] ) || $_FILES['arw_csv_file']['error'] !== UPLOAD_ERR_OK ) {
		return [ 'error' => true, 'message' => __( 'Erreur de téléversement.', 'arw-maison' ), 'errors' => [] ];
	}

	$tmp = $_FILES['arw_csv_file']['tmp_name'];
	if ( ! is_uploaded_file( $tmp ) ) {
		return [ 'error' => true, 'message' => __( 'Fichier invalide.', 'arw-maison' ), 'errors' => [] ];
	}

	// Size guard : 2 MB max.
	if ( filesize( $tmp ) > 2 * MB_IN_BYTES ) {
		return [ 'error' => true, 'message' => __( 'Fichier trop gros (max 2 MB).', 'arw-maison' ), 'errors' => [] ];
	}

	$dry_run = ! empty( $_POST['arw_csv_dry_run'] );

	$fh = fopen( $tmp, 'r' );
	if ( ! $fh ) {
		return [ 'error' => true, 'message' => __( 'Impossible de lire le fichier.', 'arw-maison' ), 'errors' => [] ];
	}

	// Read first line (header). Strip UTF-8 BOM if present.
	$header_line = fgets( $fh );
	if ( $header_line === false ) {
		fclose( $fh );
		return [ 'error' => true, 'message' => __( 'Fichier vide.', 'arw-maison' ), 'errors' => [] ];
	}
	$header_line = preg_replace( '/^\xEF\xBB\xBF/', '', $header_line );

	// Detect delimiter (; ou ,).
	$delim       = ( substr_count( $header_line, ';' ) >= substr_count( $header_line, ',' ) ) ? ';' : ',';

	// Parse header.
	$header = str_getcsv( trim( $header_line ), $delim );
	$header = array_map( 'trim', $header );
	$header = array_map( 'strtolower', $header );

	$expected = [ 'title', 'category_slug', 'verdict', 'explanation', 'alternatives', 'keywords', 'dtu_refs' ];
	foreach ( $expected as $col ) {
		if ( ! in_array( $col, $header, true ) ) {
			fclose( $fh );
			return [
				'error'   => true,
				'message' => sprintf( __( 'Colonne manquante : <code>%s</code>.', 'arw-maison' ), esc_html( $col ) ),
				'errors'  => [],
			];
		}
	}

	$idx = array_flip( $header );

	$created = 0;
	$updated = 0;
	$skipped = 0;
	$errors  = [];
	$line_no = 1;

	while ( ( $row = fgetcsv( $fh, 0, $delim ) ) !== false ) {
		$line_no++;
		// Skip empty lines.
		if ( count( $row ) === 1 && trim( $row[0] ?? '' ) === '' ) { continue; }

		$title        = trim( (string) ( $row[ $idx['title'] ]         ?? '' ) );
		$cat_slug     = sanitize_key( trim( (string) ( $row[ $idx['category_slug'] ] ?? '' ) ) );
		$verdict      = sanitize_key( trim( (string) ( $row[ $idx['verdict'] ]      ?? '' ) ) );
		$explanation  = trim( (string) ( $row[ $idx['explanation'] ]   ?? '' ) );
		$alternatives = trim( (string) ( $row[ $idx['alternatives'] ]  ?? '' ) );
		$keywords     = trim( (string) ( $row[ $idx['keywords'] ]      ?? '' ) );
		$dtu_refs     = trim( (string) ( $row[ $idx['dtu_refs'] ]      ?? '' ) );

		if ( $title === '' ) {
			$errors[] = sprintf( __( 'Ligne %d : titre vide, ignorée.', 'arw-maison' ), $line_no );
			$skipped++;
			continue;
		}

		if ( ! array_key_exists( $verdict, arw_maison_verdicts() ) ) {
			$errors[] = sprintf( __( 'Ligne %d : verdict invalide "%s".', 'arw-maison' ), $line_no, esc_html( $verdict ) );
			$skipped++;
			continue;
		}

		// Slug du post = sanitize_title du titre (idempotent).
		$slug = sanitize_title( $title );

		// Find existing.
		$existing = get_posts( [
			'post_type'      => ARW_MAISON_RULE_CPT,
			'name'           => $slug,
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		] );

		$post_data = [
			'post_type'    => ARW_MAISON_RULE_CPT,
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_status'  => 'publish',
			'post_content' => '', // contenu enrichi via meta
		];

		if ( $dry_run ) {
			$existing ? $updated++ : $created++;
			continue;
		}

		if ( $existing ) {
			$post_data['ID'] = $existing[0];
			$post_id         = wp_update_post( $post_data, true );
			$updated++;
		} else {
			$post_id = wp_insert_post( $post_data, true );
			$created++;
		}

		if ( is_wp_error( $post_id ) ) {
			$errors[] = sprintf( __( 'Ligne %d : %s', 'arw-maison' ), $line_no, $post_id->get_error_message() );
			continue;
		}

		// Meta — defense in depth : sanitize à la frontière d'entrée même si
		// le rendu front filtre via wp_kses_post. Évite que des consommateurs
		// tiers (export JSON, REST) reçoivent du HTML brut.
		update_post_meta( $post_id, '_arw_rule_verdict',      sanitize_key( $verdict ) );
		update_post_meta( $post_id, '_arw_rule_explanation',  sanitize_textarea_field( $explanation ) );
		update_post_meta( $post_id, '_arw_rule_alternatives', sanitize_textarea_field( $alternatives ) );
		update_post_meta( $post_id, '_arw_rule_keywords',     sanitize_text_field( $keywords ) );
		update_post_meta( $post_id, '_arw_rule_dtu_refs',     sanitize_text_field( $dtu_refs ) );

		// Catégorie.
		if ( $cat_slug ) {
			$term = term_exists( $cat_slug, ARW_MAISON_CATEGORY_TAX );
			if ( ! $term ) {
				$term = wp_insert_term( ucfirst( str_replace( '-', ' ', $cat_slug ) ), ARW_MAISON_CATEGORY_TAX, [ 'slug' => $cat_slug ] );
			}
			if ( ! is_wp_error( $term ) ) {
				$term_id = is_array( $term ) ? (int) $term['term_id'] : (int) $term;
				wp_set_post_terms( $post_id, [ $term_id ], ARW_MAISON_CATEGORY_TAX, false );
			}
		}
	}

	fclose( $fh );

	$msg = $dry_run
		? sprintf( __( '<strong>Dry-run</strong> : %d à créer, %d à mettre à jour, %d ignorées.', 'arw-maison' ), $created, $updated, $skipped )
		: sprintf( __( '<strong>Import terminé</strong> : %d créées, %d mises à jour, %d ignorées.', 'arw-maison' ), $created, $updated, $skipped );

	return [
		'error'   => false,
		'message' => $msg,
		'errors'  => $errors,
	];
}
