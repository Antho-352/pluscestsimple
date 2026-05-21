<?php
/**
 * CPT `arw_compat_rule` — Règles du Compatibilimètre.
 *
 * Une règle = une question type "puis-je faire X dans Y ?" avec verdict + explication + alternatives.
 * Génère une page SEO autonome par règle (long-tail) + alimente l'index JSON statique côté front.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

// ─── Verdict enum ────────────────────────────────────────────────────────────

function arw_maison_verdicts(): array {
	return [
		'compatible'   => __( 'Compatible',          'arw-maison' ),
		'conditional'  => __( 'Sous conditions',     'arw-maison' ),
		'discouraged'  => __( 'Déconseillé',         'arw-maison' ),
		'forbidden'    => __( 'Interdit',            'arw-maison' ),
	];
}

// Couleur d'affichage par verdict (front + admin column).
function arw_maison_verdict_color( string $verdict ): string {
	return [
		'compatible'  => '#1f3a2e',
		'conditional' => '#a78a4d',
		'discouraged' => '#c4512e',
		'forbidden'   => '#9b1d1d',
	][ $verdict ] ?? '#6c6c6c';
}

// ─── Register CPT ────────────────────────────────────────────────────────────

function arw_maison_register_rule_cpt(): void {
	register_post_type( ARW_MAISON_RULE_CPT, [
		'labels' => [
			'name'               => __( 'Compatibilimètre',          'arw-maison' ),
			'singular_name'      => __( 'Règle',                     'arw-maison' ),
			'menu_name'          => __( 'Compatibilimètre',          'arw-maison' ),
			'add_new'            => __( 'Ajouter une règle',         'arw-maison' ),
			'add_new_item'       => __( 'Ajouter une règle',         'arw-maison' ),
			'edit_item'          => __( 'Modifier la règle',         'arw-maison' ),
			'new_item'           => __( 'Nouvelle règle',            'arw-maison' ),
			'search_items'       => __( 'Rechercher une règle',      'arw-maison' ),
			'not_found'          => __( 'Aucune règle',              'arw-maison' ),
			'all_items'          => __( '⚙ Compatibilimètre — Règles', 'arw-maison' ),
		],
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_rest'       => true,
		'menu_icon'          => 'dashicons-yes-alt',
		'supports'           => [ 'title', 'editor', 'excerpt', 'page-attributes', 'revisions' ],
		'has_archive'        => false, // l'archive est servie par la page /compatibilimetre/ (front search).
		'rewrite'            => [ 'slug' => 'compatibilimetre/regle', 'with_front' => false ],
	] );

	// Meta fields exposed to REST.
	$meta_fields = [
		'_arw_rule_verdict'           => 'string',
		'_arw_rule_explanation'       => 'string',
		'_arw_rule_alternatives'      => 'string',
		'_arw_rule_keywords'          => 'string', // CSV des keywords pour fuzzy search
		'_arw_rule_dtu_refs'          => 'string', // refs normes (DTU 51.4, RT2012, etc.)
		// ─── Enrichment v2 (500-700 mots) — populated by enrichment.json importer ─
		'_arw_rule_diagnostic'        => 'string', // "Comment savoir si ça s'applique chez vous"
		'_arw_rule_cost'              => 'string', // "Combien ça coûte" — fourchettes 2026
		'_arw_rule_pro_order'         => 'string', // "Qui appeler en premier"
		'_arw_rule_alternatives_long' => 'string', // version étoffée des alternatives
		'_arw_rule_common_mistakes'   => 'string', // "Erreurs fréquentes"
	];
	foreach ( $meta_fields as $key => $type ) {
		register_post_meta( ARW_MAISON_RULE_CPT, $key, [
			'type'          => $type,
			'single'        => true,
			'show_in_rest'  => true,
			'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
		] );
	}
}

add_action( 'init', 'arw_maison_register_rule_cpt' );

// ─── Meta box ────────────────────────────────────────────────────────────────

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'arw_compat_rule_meta', __( 'Détails de la règle', 'arw-maison' ), 'arw_maison_rule_meta_box', ARW_MAISON_RULE_CPT, 'normal', 'high' );
} );

function arw_maison_rule_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'arw_compat_rule_save', 'arw_compat_rule_nonce' );
	$verdict           = (string) get_post_meta( $post->ID, '_arw_rule_verdict', true );
	$explanation       = (string) get_post_meta( $post->ID, '_arw_rule_explanation', true );
	$alternatives      = (string) get_post_meta( $post->ID, '_arw_rule_alternatives', true );
	$keywords          = (string) get_post_meta( $post->ID, '_arw_rule_keywords', true );
	$dtu_refs          = (string) get_post_meta( $post->ID, '_arw_rule_dtu_refs', true );
	$diagnostic        = (string) get_post_meta( $post->ID, '_arw_rule_diagnostic', true );
	$cost              = (string) get_post_meta( $post->ID, '_arw_rule_cost', true );
	$pro_order         = (string) get_post_meta( $post->ID, '_arw_rule_pro_order', true );
	$alternatives_long = (string) get_post_meta( $post->ID, '_arw_rule_alternatives_long', true );
	$common_mistakes   = (string) get_post_meta( $post->ID, '_arw_rule_common_mistakes', true );
	?>
	<style>
		.arw-rule-meta .row { display: grid; grid-template-columns: 200px 1fr; gap: 12px; align-items: start; padding: 14px 0; border-bottom: 1px solid #eee; }
		.arw-rule-meta .row:last-child { border-bottom: 0; }
		.arw-rule-meta label.field { font-weight: 600; padding-top: 6px; }
		.arw-rule-meta input[type=text],
		.arw-rule-meta select,
		.arw-rule-meta textarea { width: 100%; max-width: 640px; padding: 8px 10px; border: 1px solid #8c8f94; border-radius: 3px; font-size: 14px; }
		.arw-rule-meta textarea { min-height: 80px; resize: vertical; font-family: inherit; }
		.arw-rule-meta .hint { font-size: 12px; color: #6b7280; margin-top: 4px; display: block; }
		.arw-rule-meta .verdict-badge { display: inline-block; padding: 4px 10px; border-radius: 3px; color: #fff; font-weight: 600; font-size: 12px; margin-left: 8px; }
	</style>
	<div class="arw-rule-meta">

		<div class="row">
			<label class="field" for="arw_rule_verdict"><?php esc_html_e( 'Verdict', 'arw-maison' ); ?></label>
			<div>
				<select id="arw_rule_verdict" name="arw_rule_verdict">
					<option value=""><?php esc_html_e( '— choisir —', 'arw-maison' ); ?></option>
					<?php foreach ( arw_maison_verdicts() as $k => $label ) : ?>
						<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $verdict, $k ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
				<?php if ( $verdict ) : ?>
					<span class="verdict-badge" style="background:<?php echo esc_attr( arw_maison_verdict_color( $verdict ) ); ?>"><?php echo esc_html( arw_maison_verdicts()[ $verdict ] ?? '' ); ?></span>
				<?php endif; ?>
				<span class="hint"><?php esc_html_e( 'Détermine la couleur et le pictogramme côté front.', 'arw-maison' ); ?></span>
			</div>
		</div>

		<div class="row">
			<label class="field" for="arw_rule_explanation"><?php esc_html_e( 'Explication courte', 'arw-maison' ); ?></label>
			<div>
				<textarea id="arw_rule_explanation" name="arw_rule_explanation" placeholder="2-4 lignes maximum. Pourquoi ce verdict ?"><?php echo esc_textarea( $explanation ); ?></textarea>
				<span class="hint"><?php esc_html_e( 'Affichée juste sous le verdict. Reste factuelle, pas de marketing.', 'arw-maison' ); ?></span>
			</div>
		</div>

		<div class="row">
			<label class="field" for="arw_rule_alternatives"><?php esc_html_e( 'Alternatives', 'arw-maison' ); ?></label>
			<div>
				<textarea id="arw_rule_alternatives" name="arw_rule_alternatives" placeholder="Si déconseillé/interdit : que faire à la place ? 1-3 alternatives concrètes."><?php echo esc_textarea( $alternatives ); ?></textarea>
				<span class="hint"><?php esc_html_e( 'Indispensable pour les verdicts "déconseillé" / "interdit". Optionnel sinon.', 'arw-maison' ); ?></span>
			</div>
		</div>

		<div class="row">
			<label class="field" for="arw_rule_keywords"><?php esc_html_e( 'Mots-clés (recherche)', 'arw-maison' ); ?></label>
			<div>
				<input type="text" id="arw_rule_keywords" name="arw_rule_keywords" value="<?php echo esc_attr( $keywords ); ?>" placeholder="parquet massif, plancher chauffant, hydraulique, pose collée">
				<span class="hint"><?php esc_html_e( 'Séparés par virgule. Alimentent la recherche fuzzy côté Compatibilimètre. 5-15 mots-clés idéal.', 'arw-maison' ); ?></span>
			</div>
		</div>

		<div class="row">
			<label class="field" for="arw_rule_dtu_refs"><?php esc_html_e( 'Références techniques', 'arw-maison' ); ?></label>
			<div>
				<input type="text" id="arw_rule_dtu_refs" name="arw_rule_dtu_refs" value="<?php echo esc_attr( $dtu_refs ); ?>" placeholder="DTU 51.4, RT2012, NF P 73-201">
				<span class="hint"><?php esc_html_e( 'Optionnel. Affichées en bas de page règle pour crédibilité (DTU, RT, NF…).', 'arw-maison' ); ?></span>
			</div>
		</div>

		<h3 style="margin:24px 0 0;font-size:13px;letter-spacing:0.06em;text-transform:uppercase;color:#6b7280">Enrichissement long format (500-700 mots)</h3>
		<p style="margin:4px 0 12px;color:#6b7280;font-size:12px">Sections additionnelles affichées sur la fiche publique. Importables en lot via <em>Compatibilimètre → Enrichissement</em>.</p>

		<div class="row">
			<label class="field" for="arw_rule_diagnostic"><?php esc_html_e( 'Diagnostic chez vous', 'arw-maison' ); ?></label>
			<div>
				<textarea id="arw_rule_diagnostic" name="arw_rule_diagnostic" placeholder="100-150 mots — comment savoir si ça s'applique chez vous (indices visuels concrets)"><?php echo esc_textarea( $diagnostic ); ?></textarea>
			</div>
		</div>

		<div class="row">
			<label class="field" for="arw_rule_cost"><?php esc_html_e( 'Combien ça coûte', 'arw-maison' ); ?></label>
			<div>
				<textarea id="arw_rule_cost" name="arw_rule_cost" placeholder="80-120 mots — fourchette 2026 + disclaimer"><?php echo esc_textarea( $cost ); ?></textarea>
			</div>
		</div>

		<div class="row">
			<label class="field" for="arw_rule_pro_order"><?php esc_html_e( 'Qui appeler en premier', 'arw-maison' ); ?></label>
			<div>
				<textarea id="arw_rule_pro_order" name="arw_rule_pro_order" placeholder="60-100 mots — ordre d'intervention des pros"><?php echo esc_textarea( $pro_order ); ?></textarea>
			</div>
		</div>

		<div class="row">
			<label class="field" for="arw_rule_alternatives_long"><?php esc_html_e( 'Alternatives détaillées', 'arw-maison' ); ?></label>
			<div>
				<textarea id="arw_rule_alternatives_long" name="arw_rule_alternatives_long" placeholder="80-120 mots — version étoffée des alternatives"><?php echo esc_textarea( $alternatives_long ); ?></textarea>
			</div>
		</div>

		<div class="row">
			<label class="field" for="arw_rule_common_mistakes"><?php esc_html_e( 'Erreurs fréquentes', 'arw-maison' ); ?></label>
			<div>
				<textarea id="arw_rule_common_mistakes" name="arw_rule_common_mistakes" placeholder="80-120 mots — 2-3 pièges concrets observés sur ce chantier"><?php echo esc_textarea( $common_mistakes ); ?></textarea>
			</div>
		</div>

	</div>
	<?php
}

add_action( 'save_post_' . ARW_MAISON_RULE_CPT, function ( int $post_id ): void {
	if ( empty( $_POST['arw_compat_rule_nonce'] ) || ! wp_verify_nonce( $_POST['arw_compat_rule_nonce'], 'arw_compat_rule_save' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

	// Verdict — enum whitelist.
	if ( isset( $_POST['arw_rule_verdict'] ) ) {
		$raw   = sanitize_key( wp_unslash( $_POST['arw_rule_verdict'] ) );
		$clean = array_key_exists( $raw, arw_maison_verdicts() ) ? $raw : '';
		$clean ? update_post_meta( $post_id, '_arw_rule_verdict', $clean ) : delete_post_meta( $post_id, '_arw_rule_verdict' );
	}

	// Text fields (textareas — sanitize_textarea preserves line breaks).
	$textarea_fields = [
		'arw_rule_explanation',
		'arw_rule_alternatives',
		'arw_rule_diagnostic',
		'arw_rule_cost',
		'arw_rule_pro_order',
		'arw_rule_alternatives_long',
		'arw_rule_common_mistakes',
	];
	foreach ( $textarea_fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			$v = sanitize_textarea_field( wp_unslash( $_POST[ $field ] ) );
			$meta_key = '_' . $field;
			$v ? update_post_meta( $post_id, $meta_key, $v ) : delete_post_meta( $post_id, $meta_key );
		}
	}

	foreach ( [ 'arw_rule_keywords', 'arw_rule_dtu_refs' ] as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			$v = sanitize_text_field( wp_unslash( $_POST[ $field ] ) );
			$meta_key = '_' . $field;
			$v ? update_post_meta( $post_id, $meta_key, $v ) : delete_post_meta( $post_id, $meta_key );
		}
	}
} );

// ─── Validation min content : empêche la publication d'une règle thin ───────
// Une règle sans verdict OU avec une explication < 80 chars est repassée en
// "draft" silencieusement + admin notice. Évite l'indexation de pages thin
// auto-générées (Google pénalise le thin content massif).

add_filter( 'wp_insert_post_data', function ( $data, $postarr ) {
	if ( ( $data['post_type'] ?? '' ) !== ARW_MAISON_RULE_CPT ) { return $data; }
	if ( ( $data['post_status'] ?? '' ) !== 'publish' ) { return $data; }
	$post_id = (int) ( $postarr['ID'] ?? 0 );
	if ( ! $post_id ) { return $data; }

	$verdict     = isset( $_POST['arw_rule_verdict'] )     ? sanitize_key( wp_unslash( $_POST['arw_rule_verdict'] ) )            : (string) get_post_meta( $post_id, '_arw_rule_verdict', true );
	$explanation = isset( $_POST['arw_rule_explanation'] ) ? sanitize_textarea_field( wp_unslash( $_POST['arw_rule_explanation'] ) ) : (string) get_post_meta( $post_id, '_arw_rule_explanation', true );

	if ( ! $verdict || mb_strlen( trim( $explanation ) ) < 80 ) {
		$data['post_status'] = 'draft';
		set_transient( 'arw_maison_rule_draft_' . get_current_user_id(), $post_id, 30 );
	}
	return $data;
}, 10, 2 );

add_action( 'admin_notices', function () {
	$user_id = get_current_user_id();
	$post_id = (int) get_transient( 'arw_maison_rule_draft_' . $user_id );
	if ( ! $post_id ) { return; }
	delete_transient( 'arw_maison_rule_draft_' . $user_id );
	echo '<div class="notice notice-warning is-dismissible"><p><strong>Compatibilimètre :</strong> règle repassée en brouillon — verdict requis ET explication ≥ 80 caractères pour publier (anti thin content SEO).</p></div>';
} );

// ─── Admin list columns ──────────────────────────────────────────────────────

add_filter( 'manage_' . ARW_MAISON_RULE_CPT . '_posts_columns', function ( $cols ) {
	return [
		'cb'                                    => $cols['cb'] ?? '',
		'title'                                 => __( 'Règle',     'arw-maison' ),
		'verdict'                               => __( 'Verdict',   'arw-maison' ),
		'taxonomy-' . ARW_MAISON_CATEGORY_TAX   => __( 'Catégorie', 'arw-maison' ),
		'keywords_count'                        => __( 'Mots-clés', 'arw-maison' ),
		'date'                                  => __( 'Date',      'arw-maison' ),
	];
} );

add_action( 'manage_' . ARW_MAISON_RULE_CPT . '_posts_custom_column', function ( $col, $post_id ) {
	switch ( $col ) {
		case 'verdict':
			$v = (string) get_post_meta( $post_id, '_arw_rule_verdict', true );
			if ( ! $v ) {
				echo '<span style="color:#d63638">⚠ manquant</span>';
				break;
			}
			$label = arw_maison_verdicts()[ $v ] ?? $v;
			$color = arw_maison_verdict_color( $v );
			printf( '<span style="background:%s;color:#fff;padding:3px 8px;border-radius:3px;font-size:11px;font-weight:600">%s</span>', esc_attr( $color ), esc_html( $label ) );
			break;
		case 'keywords_count':
			$kw = (string) get_post_meta( $post_id, '_arw_rule_keywords', true );
			$n  = $kw ? count( array_filter( array_map( 'trim', explode( ',', $kw ) ) ) ) : 0;
			echo $n > 0
				? sprintf( '<span style="color:#1f3a2e">%d</span>', $n )
				: '<span style="color:#d63638">0 ⚠</span>';
			break;
	}
}, 10, 2 );

// ─── Quick filter by verdict in admin list ───────────────────────────────────

add_action( 'restrict_manage_posts', function ( $post_type ) {
	if ( $post_type !== ARW_MAISON_RULE_CPT ) { return; }
	$current = isset( $_GET['arw_verdict_filter'] ) ? sanitize_key( wp_unslash( $_GET['arw_verdict_filter'] ) ) : '';
	?>
	<select name="arw_verdict_filter">
		<option value=""><?php esc_html_e( 'Tous verdicts', 'arw-maison' ); ?></option>
		<?php foreach ( arw_maison_verdicts() as $k => $label ) : ?>
			<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $current, $k ); ?>><?php echo esc_html( $label ); ?></option>
		<?php endforeach; ?>
	</select>
	<?php
} );

add_action( 'pre_get_posts', function ( WP_Query $q ) {
	if ( ! is_admin() || ! $q->is_main_query() ) { return; }
	if ( ( $q->get( 'post_type' ) ?? '' ) !== ARW_MAISON_RULE_CPT ) { return; }
	if ( empty( $_GET['arw_verdict_filter'] ) ) { return; }
	$v = sanitize_key( wp_unslash( $_GET['arw_verdict_filter'] ) );
	if ( ! array_key_exists( $v, arw_maison_verdicts() ) ) { return; }
	$mq   = (array) $q->get( 'meta_query' );
	$mq[] = [ 'key' => '_arw_rule_verdict', 'value' => $v ];
	$q->set( 'meta_query', $mq );
} );
