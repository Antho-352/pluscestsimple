<?php
/**
 * Moteur de maillage interne entrant.
 *
 * Objectif : chaque article publié reçoit ≥ 3 liens entrants depuis d'AUTRES
 * articles du MÊME silo (cocon strict). L'édition se fait dans le contenu réel
 * (post_content) — donc poids SEO maximal — mais TOUJOURS à l'intérieur d'une zone
 * balisée `<!--pcs-autolinks-->…<!--/pcs-autolinks-->` ajoutée en fin d'article.
 * Le contenu rédigé n'est jamais modifié ; tout est réversible (outil d'undo).
 *
 * Scoping : intra-silo (catégorie principale via pcs_post_primary_cat). Le hub de
 * pilier liste déjà les nouveaux articles dynamiquement (Query Loop) → couvert.
 *
 * @package pluscestsimple
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

const PCS_AUTOLINK_TARGET   = 3;   // liens entrants visés par article.
const PCS_AUTOLINK_MAX_OUT  = 8;   // liens sortants max ajoutés sur un même article source.
const PCS_AUTOLINK_OPEN     = '<!--pcs-autolinks-->';
const PCS_AUTOLINK_CLOSE    = '<!--/pcs-autolinks-->';

/** Ancre (texte de lien) d'un article cible : meta _pcs_anchor sinon le titre. */
function pcs_autolink_anchor( int $post_id ): string {
	$a = trim( (string) get_post_meta( $post_id, '_pcs_anchor', true ) );
	return $a !== '' ? $a : get_the_title( $post_id );
}

/** IDs des articles du MÊME silo que la cible (hors elle-même), publiés. */
function pcs_autolink_silo_siblings( int $target_id ): array {
	$primary = pcs_post_primary_cat( $target_id );
	if ( ! $primary instanceof WP_Term ) { return []; }

	$q = new WP_Query( [
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => 60,
		'post__not_in'        => [ $target_id ],
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'fields'              => 'ids',
		'orderby'             => 'date',
		'order'               => 'DESC',
		'tax_query'           => [ [
			'taxonomy' => 'category',
			'field'    => 'term_id',
			'terms'    => $primary->term_id,
		] ],
	] );
	return $q->posts ?: [];
}

/** Reconstruit la zone balisée d'un article source depuis sa liste de cibles. */
function pcs_autolink_rebuild_block( int $source_id ): void {
	$targets = (array) get_post_meta( $source_id, '_pcs_autolink_out', true );
	$targets = array_values( array_unique( array_filter( array_map( 'intval', $targets ) ) ) );

	$post = get_post( $source_id );
	if ( ! $post instanceof WP_Post ) { return; }
	$content = (string) $post->post_content;

	// Retire l'ancienne zone si présente.
	$content = preg_replace( '/' . preg_quote( PCS_AUTOLINK_OPEN, '/' ) . '.*?' . preg_quote( PCS_AUTOLINK_CLOSE, '/' ) . '/s', '', $content );
	$content = rtrim( $content );

	if ( $targets ) {
		$items = '';
		foreach ( $targets as $tid ) {
			if ( 'publish' !== get_post_status( $tid ) ) { continue; }
			$items .= '<li><a href="' . esc_url( get_permalink( $tid ) ) . '">' . esc_html( pcs_autolink_anchor( $tid ) ) . '</a></li>';
		}
		if ( $items ) {
			$block = PCS_AUTOLINK_OPEN
				. '<aside class="pcs-autolinks"><p class="pcs-autolinks__title">Sur le même sujet</p><ul class="pcs-autolinks__list">'
				. $items . '</ul></aside>'
				. PCS_AUTOLINK_CLOSE;
			$content = $content . "\n\n" . $block;
		}
	}

	pcs_autolink_save( $source_id, $content );
}

/** wp_update_post avec garde de ré-entrance (évite la boucle transition_post_status). */
function pcs_autolink_save( int $post_id, string $content ): void {
	static $busy = false;
	if ( $busy ) { return; }
	$busy = true;
	wp_update_post( [
		'ID'           => $post_id,
		'post_content' => $content,
	] );
	$busy = false;
}

/** Enregistre un lien source → cible (idempotent). */
function pcs_autolink_link( int $source_id, int $target_id ): bool {
	if ( $source_id === $target_id ) { return false; }
	$out = (array) get_post_meta( $source_id, '_pcs_autolink_out', true );
	$out = array_map( 'intval', $out );
	if ( in_array( $target_id, $out, true ) ) { return true; } // déjà lié.
	if ( count( $out ) >= PCS_AUTOLINK_MAX_OUT ) { return false; } // source saturée.

	$out[] = $target_id;
	update_post_meta( $source_id, '_pcs_autolink_out', $out );
	pcs_autolink_rebuild_block( $source_id );

	$in = (array) get_post_meta( $target_id, '_pcs_autolink_in', true );
	$in = array_map( 'intval', $in );
	if ( ! in_array( $source_id, $in, true ) ) {
		$in[] = $source_id;
		update_post_meta( $target_id, '_pcs_autolink_in', $in );
	}
	return true;
}

/** Garantit jusqu'à N liens entrants pour la cible, depuis ses frères de silo. */
function pcs_autolink_ensure_inbound( int $target_id, int $needed = PCS_AUTOLINK_TARGET ): int {
	if ( 'publish' !== get_post_status( $target_id ) || 'post' !== get_post_type( $target_id ) ) { return 0; }

	$in = array_filter( (array) get_post_meta( $target_id, '_pcs_autolink_in', true ), fn( $id ) => 'publish' === get_post_status( (int) $id ) );
	$have = count( $in );
	if ( $have >= $needed ) { return $have; }

	foreach ( pcs_autolink_silo_siblings( $target_id ) as $source_id ) {
		if ( $have >= $needed ) { break; }
		if ( in_array( (int) $source_id, array_map( 'intval', $in ), true ) ) { continue; }
		if ( pcs_autolink_link( (int) $source_id, $target_id ) ) { $have++; }
	}
	return $have;
}

// ─── Déclenchement automatique à la publication d'un nouvel article ────────────
// wp_after_insert_post : garantit que catégories + metas sont déjà enregistrées
// (contrairement à transition_post_status qui peut précéder le set des taxonomies).

add_action( 'wp_after_insert_post', function ( $post_id, $post, $update, $post_before ) {
	if ( ! $post instanceof WP_Post || 'post' !== $post->post_type ) { return; }
	if ( 'publish' !== $post->post_status ) { return; }
	// Nouvelle publication uniquement (pas une simple mise à jour d'un article déjà publié).
	if ( $post_before instanceof WP_Post && 'publish' === $post_before->post_status ) { return; }
	if ( ! apply_filters( 'pcs_autolink_enabled', true ) ) { return; }
	pcs_autolink_ensure_inbound( (int) $post_id );
}, 20, 4 );

// ─── Nettoyage : retire la zone balisée + les metas pour un article ────────────

function pcs_autolink_purge_source( int $source_id ): void {
	$post = get_post( $source_id );
	if ( $post instanceof WP_Post ) {
		$content = preg_replace( '/' . preg_quote( PCS_AUTOLINK_OPEN, '/' ) . '.*?' . preg_quote( PCS_AUTOLINK_CLOSE, '/' ) . '/s', '', (string) $post->post_content );
		pcs_autolink_save( $source_id, rtrim( $content ) );
	}
	delete_post_meta( $source_id, '_pcs_autolink_out' );
}

// ─── Outil d'administration (Outils → Maillage interne) ────────────────────────

add_action( 'admin_menu', function () {
	add_management_page( 'Maillage interne', 'Maillage interne', 'manage_options', 'pcs-autolink', 'pcs_autolink_admin_page' );
} );

function pcs_autolink_admin_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) { return; }

	if ( isset( $_POST['pcs_autolink_action'] ) && check_admin_referer( 'pcs_autolink' ) ) {
		$action = sanitize_key( $_POST['pcs_autolink_action'] );
		if ( 'rebuild' === $action ) {
			$ids = get_posts( [ 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids' ] );
			$done = 0;
			foreach ( $ids as $id ) { pcs_autolink_ensure_inbound( (int) $id ); $done++; }
			echo '<div class="notice notice-success"><p>Maillage reconstruit sur ' . (int) $done . ' articles.</p></div>';
		} elseif ( 'purge' === $action ) {
			$ids = get_posts( [ 'post_type' => 'post', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_pcs_autolink_out' ] );
			foreach ( $ids as $id ) { pcs_autolink_purge_source( (int) $id ); }
			$targets = get_posts( [ 'post_type' => 'post', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_pcs_autolink_in' ] );
			foreach ( $targets as $id ) { delete_post_meta( (int) $id, '_pcs_autolink_in' ); }
			echo '<div class="notice notice-success"><p>Tous les auto-liens ont été retirés.</p></div>';
		}
	}

	echo '<div class="wrap"><h1>Maillage interne automatique</h1>';
	echo '<p>Chaque article publié reçoit jusqu\'à ' . PCS_AUTOLINK_TARGET . ' liens entrants depuis d\'autres articles du même silo, dans une zone « Sur le même sujet » en fin d\'article. Réversible.</p>';
	echo '<form method="post" style="display:inline-block;margin-right:1rem">';
	wp_nonce_field( 'pcs_autolink' );
	echo '<input type="hidden" name="pcs_autolink_action" value="rebuild">';
	echo '<button class="button button-primary">Reconstruire tout le maillage entrant</button></form>';
	echo '<form method="post" style="display:inline-block" onsubmit="return confirm(\'Retirer tous les auto-liens ?\')">';
	wp_nonce_field( 'pcs_autolink' );
	echo '<input type="hidden" name="pcs_autolink_action" value="purge">';
	echo '<button class="button">Supprimer tous les auto-liens</button></form>';
	echo '</div>';
}
