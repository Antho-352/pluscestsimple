<?php
/**
 * Moteur de maillage interne entrant — liens CONTEXTUELS in-body.
 *
 * Objectif : chaque article publié reçoit ≥ 3 liens entrants depuis d'AUTRES
 * articles du MÊME silo (cocon strict). Le lien est inséré DANS LE TEXTE du
 * source (1re occurrence du mot-clé / ancre de la cible), façon éditoriale —
 * poids SEO maximal. Le lien porte la classe `pcs-autolink` + `data-pcs-target`
 * pour rester repérable et 100 % réversible (outil d'undo).
 *
 * Le bloc « À lire dans le même univers » (thème) reste en bas de page ;
 * ce moteur s'occupe uniquement des liens contextuels dans le corps.
 *
 * Scoping : intra-silo (catégorie principale via pcs_post_primary_cat).
 *
 * @package pluscestsimple
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

const PCS_AUTOLINK_TARGET  = 3;   // liens entrants visés par article.
const PCS_AUTOLINK_MAX_OUT = 6;   // liens auto sortants max sur un même article source.

// Ancien marqueur de zone bas de page (versions ≤ 2.17.0) — conservé pour le nettoyage.
const PCS_AUTOLINK_OPEN  = '<!--pcs-autolinks-->';
const PCS_AUTOLINK_CLOSE = '<!--/pcs-autolinks-->';

/** Meta `_pcs_anchor` éditable + exposée à l'API REST (pour piloter l'ancre). */
add_action( 'init', function () {
	register_post_meta( 'post', '_pcs_anchor', [
		'type'              => 'string',
		'single'            => true,
		'show_in_rest'      => true,
		'sanitize_callback' => 'sanitize_text_field',
		'auth_callback'     => fn() => current_user_can( 'edit_posts' ),
	] );
} );

/** Ancre (texte de lien) d'un article cible : meta _pcs_anchor sinon le titre. */
function pcs_autolink_anchor( int $post_id ): string {
	$a = trim( (string) get_post_meta( $post_id, '_pcs_anchor', true ) );
	return $a !== '' ? $a : get_the_title( $post_id );
}

/** wp_update_post avec garde de ré-entrance (évite la boucle wp_after_insert_post). */
function pcs_autolink_save( int $post_id, string $content ): void {
	static $busy = false;
	if ( $busy ) { return; }
	$busy = true;
	wp_update_post( [ 'ID' => $post_id, 'post_content' => $content ] );
	$busy = false;
}

/**
 * Insère un lien contextuel vers la cible dans le corps du source : 1re occurrence
 * de l'ancre, uniquement dans un paragraphe SANS lien existant (anti-imbrication).
 * Retourne true si un lien a été posé.
 */
function pcs_autolink_insert_inline( int $source_id, int $target_id ): bool {
	$anchor = trim( pcs_autolink_anchor( $target_id ) );
	if ( mb_strlen( $anchor ) < 4 ) { return false; }
	$url  = get_permalink( $target_id );
	$post = get_post( $source_id );
	if ( ! $post instanceof WP_Post ) { return false; }
	$content = (string) $post->post_content;

	// Idempotence : déjà un lien (auto ou manuel) vers la cible.
	if ( false !== strpos( $content, 'data-pcs-target="' . $target_id . '"' ) ) { return true; }
	if ( false !== strpos( $content, 'href="' . esc_url( $url ) . '"' ) )       { return true; }

	$open = '<a class="pcs-autolink" data-pcs-target="' . $target_id . '" href="' . esc_url( $url ) . '">';
	$done = false;

	$new = preg_replace_callback(
		'#<p\b([^>]*)>(.*?)</p>#is',
		function ( $m ) use ( $anchor, $open, &$done ) {
			if ( $done ) { return $m[0]; }
			$inner = $m[2];
			// Paragraphe contenant déjà un lien → on saute (évite l'imbrication <a>).
			if ( false !== stripos( $inner, '<a ' ) ) { return $m[0]; }
			$pattern = '/(?<![\p{L}0-9\-])(' . preg_quote( $anchor, '/' ) . ')(?![\p{L}0-9\-])/iu';
			$cnt     = 0;
			$wrapped = preg_replace_callback( $pattern, function ( $mm ) use ( $open, &$cnt ) {
				if ( $cnt > 0 ) { return $mm[0]; }
				$cnt++;
				return $open . $mm[1] . '</a>';
			}, $inner, 1 );
			if ( $cnt > 0 ) { $done = true; return '<p' . $m[1] . '>' . $wrapped . '</p>'; }
			return $m[0];
		},
		$content
	);

	if ( ! $done ) { return false; }
	pcs_autolink_save( $source_id, $new );
	return true;
}

/** Enregistre un lien source → cible (idempotent ; échoue si l'ancre est absente du source). */
function pcs_autolink_link( int $source_id, int $target_id ): bool {
	if ( $source_id === $target_id ) { return false; }
	$out = array_map( 'intval', (array) get_post_meta( $source_id, '_pcs_autolink_out', true ) );
	if ( in_array( $target_id, $out, true ) ) { return true; }
	if ( count( $out ) >= PCS_AUTOLINK_MAX_OUT ) { return false; }

	if ( ! pcs_autolink_insert_inline( $source_id, $target_id ) ) { return false; }

	$out[] = $target_id;
	update_post_meta( $source_id, '_pcs_autolink_out', $out );

	$in = array_map( 'intval', (array) get_post_meta( $target_id, '_pcs_autolink_in', true ) );
	if ( ! in_array( $source_id, $in, true ) ) {
		$in[] = $source_id;
		update_post_meta( $target_id, '_pcs_autolink_in', $in );
	}
	return true;
}

/** Garantit jusqu'à N liens entrants pour la cible, depuis les articles du silo qui citent son ancre. */
function pcs_autolink_ensure_inbound( int $target_id, int $needed = PCS_AUTOLINK_TARGET ): int {
	if ( 'publish' !== get_post_status( $target_id ) || 'post' !== get_post_type( $target_id ) ) { return 0; }
	$anchor  = trim( pcs_autolink_anchor( $target_id ) );
	$primary = pcs_post_primary_cat( $target_id );
	if ( mb_strlen( $anchor ) < 4 || ! $primary instanceof WP_Term ) { return 0; }

	$in   = array_filter( array_map( 'intval', (array) get_post_meta( $target_id, '_pcs_autolink_in', true ) ), fn( $id ) => 'publish' === get_post_status( $id ) );
	$have = count( $in );
	if ( $have >= $needed ) { return $have; }

	// Sources = articles du même silo qui CONTIENNENT l'ancre. Les plus anciens d'abord (jus + autorité).
	$q = new WP_Query( [
		'post_type'           => 'post',
		'post_status'         => 'publish',
		's'                   => $anchor,
		'posts_per_page'      => 30,
		'post__not_in'        => array_merge( [ $target_id ], $in ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'fields'              => 'ids',
		'orderby'             => 'date',
		'order'               => 'ASC',
		'tax_query'           => [ [ 'taxonomy' => 'category', 'field' => 'term_id', 'terms' => $primary->term_id ] ],
	] );
	foreach ( (array) $q->posts as $source_id ) {
		if ( $have >= $needed ) { break; }
		if ( pcs_autolink_link( (int) $source_id, $target_id ) ) { $have++; }
	}
	return $have;
}

// ─── Déclenchement automatique à la publication d'un nouvel article ────────────

add_action( 'wp_after_insert_post', function ( $post_id, $post, $update, $post_before ) {
	if ( ! $post instanceof WP_Post || 'post' !== $post->post_type ) { return; }
	if ( 'publish' !== $post->post_status ) { return; }
	if ( $post_before instanceof WP_Post && 'publish' === $post_before->post_status ) { return; }
	if ( ! apply_filters( 'pcs_autolink_enabled', true ) ) { return; }
	pcs_autolink_ensure_inbound( (int) $post_id );
}, 20, 4 );

// ─── Nettoyage : retire les liens auto (inline + ancienne zone bas de page) ─────

function pcs_autolink_purge_source( int $source_id ): void {
	$post = get_post( $source_id );
	if ( $post instanceof WP_Post ) {
		$content = (string) $post->post_content;
		// Déballe les liens inline auto : <a class="pcs-autolink" ...>X</a> -> X
		$content = preg_replace( '#<a class="pcs-autolink"[^>]*>(.*?)</a>#is', '$1', $content );
		// Compat : retire l'ancienne zone balisée bas de page (versions ≤ 2.17.0).
		$content = preg_replace( '/' . preg_quote( PCS_AUTOLINK_OPEN, '/' ) . '.*?' . preg_quote( PCS_AUTOLINK_CLOSE, '/' ) . '/s', '', $content );
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
			foreach ( $ids as $id ) { pcs_autolink_ensure_inbound( (int) $id ); }
			echo '<div class="notice notice-success"><p>Maillage reconstruit sur ' . count( $ids ) . ' articles.</p></div>';
		} elseif ( 'purge' === $action ) {
			$ids = get_posts( [ 'post_type' => 'post', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_pcs_autolink_out' ] );
			foreach ( $ids as $id ) { pcs_autolink_purge_source( (int) $id ); }
			foreach ( get_posts( [ 'post_type' => 'post', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_pcs_autolink_in' ] ) as $id ) {
				delete_post_meta( (int) $id, '_pcs_autolink_in' );
			}
			echo '<div class="notice notice-success"><p>Tous les liens auto ont été retirés.</p></div>';
		}
	}

	echo '<div class="wrap"><h1>Maillage interne automatique</h1>';
	echo '<p>Chaque article publié reçoit jusqu\'à ' . PCS_AUTOLINK_TARGET . ' liens entrants <strong>contextuels</strong> (dans le texte) depuis d\'autres articles du même silo qui citent son mot-clé. L\'ancre = meta <code>_pcs_anchor</code> (sinon le titre). Réversible.</p>';
	echo '<form method="post" style="display:inline-block;margin-right:1rem">';
	wp_nonce_field( 'pcs_autolink' );
	echo '<input type="hidden" name="pcs_autolink_action" value="rebuild">';
	echo '<button class="button button-primary">Reconstruire tout le maillage entrant</button></form>';
	echo '<form method="post" style="display:inline-block" onsubmit="return confirm(\'Retirer tous les liens auto ?\')">';
	wp_nonce_field( 'pcs_autolink' );
	echo '<input type="hidden" name="pcs_autolink_action" value="purge">';
	echo '<button class="button">Supprimer tous les liens auto</button></form>';
	echo '</div>';
}
