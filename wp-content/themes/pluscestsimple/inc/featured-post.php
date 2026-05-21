<?php
/**
 * Featured post — "À la une" sur la home.
 *
 * Permet d'épingler 1 article comme "À la une" dans la section hero de la
 * page d'accueil. Si aucun épinglé, fallback sur le post le plus récent.
 *
 * Comportement :
 *  - Meta box dans la sidebar de l'éditeur d'article (checkbox)
 *  - Un seul article peut être épinglé à la fois (cocher décoche l'ancien)
 *  - Colonne admin avec étoile dorée dans la liste Articles
 *  - Filtre `query_loop_block_query_vars` sur la home :
 *      * hero-featured (perPage:1 offset:0) → utilise l'article épinglé si défini
 *      * grid-magazine (offsets >= 1) → exclut l'article épinglé pour éviter doublon,
 *        décrémente offset de 1 pour récupérer le post sinon perdu
 *  - Force `ignore_sticky_posts=true` sur toutes les queries de la home
 *    (sinon les sticky natifs WP s'incrustent en tête et cassent le tri par date)
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

const ARW_PULSE_FEATURED_META = '_arw_pulse_featured';
const ARW_PULSE_FEATURED_OPT  = 'arw_pulse_featured_post_id';

// ─── Register meta (REST exposed for block editor compat) ────────────────────

add_action( 'init', function () {
	register_post_meta( 'post', ARW_PULSE_FEATURED_META, [
		'type'          => 'boolean',
		'single'        => true,
		'default'       => false,
		'show_in_rest'  => true,
		'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
	] );
} );

// ─── Meta box (sidebar) ──────────────────────────────────────────────────────

add_action( 'add_meta_boxes_post', function () {
	add_meta_box(
		'arw_pulse_featured',
		__( '★ À la une (home)', 'arw-pulse' ),
		'arw_pulse_featured_meta_box',
		'post',
		'side',
		'high'
	);
} );

function arw_pulse_featured_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'arw_pulse_featured_save', 'arw_pulse_featured_nonce' );
	$is_featured         = (bool) get_post_meta( $post->ID, ARW_PULSE_FEATURED_META, true );
	$current_featured_id = arw_pulse_get_featured_post_id();
	?>
	<p style="margin:0 0 8px">
		<label>
			<input type="checkbox" name="arw_pulse_featured" value="1" <?php checked( $is_featured ); ?>>
			<strong><?php esc_html_e( 'Épingler en À la une', 'arw-pulse' ); ?></strong>
		</label>
	</p>
	<p style="color:#6b7280;font-size:12px;margin:0 0 8px">
		<?php esc_html_e( 'Affiche cet article dans la section "À la une" en page d\'accueil. Un seul article peut être épinglé — cocher ici décoche automatiquement l\'ancien.', 'arw-pulse' ); ?>
	</p>
	<?php if ( $current_featured_id && $current_featured_id !== $post->ID ) : ?>
		<p style="margin:8px 0 0;padding:8px;background:#f6f7f7;border-left:3px solid #a78a4d;font-size:12px">
			<strong><?php esc_html_e( 'Actuellement épinglé :', 'arw-pulse' ); ?></strong><br>
			<a href="<?php echo esc_url( get_edit_post_link( $current_featured_id ) ); ?>">
				<?php echo esc_html( get_the_title( $current_featured_id ) ); ?>
			</a>
		</p>
	<?php elseif ( $is_featured ) : ?>
		<p style="margin:8px 0 0;padding:6px 8px;background:#f0f7e8;border-left:3px solid #1f3a2e;font-size:12px;color:#1f3a2e">
			<?php esc_html_e( '✓ Cet article est actuellement À la une.', 'arw-pulse' ); ?>
		</p>
	<?php endif; ?>
	<?php
}

// ─── Save handler : enforce single-featured + cache invalidation ─────────────

add_action( 'save_post_post', function ( int $post_id ): void {
	if ( empty( $_POST['arw_pulse_featured_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['arw_pulse_featured_nonce'] ) ), 'arw_pulse_featured_save' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

	$is_featured = ! empty( $_POST['arw_pulse_featured'] );

	if ( $is_featured ) {
		// Uncheck all other posts marked featured.
		$other_featured = get_posts( [
			'post_type'      => 'post',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => [ [
				'key'     => ARW_PULSE_FEATURED_META,
				'value'   => '1',
				'compare' => '=',
			] ],
			'post__not_in'   => [ $post_id ],
			'no_found_rows'  => true,
		] );
		foreach ( $other_featured as $other_id ) {
			delete_post_meta( $other_id, ARW_PULSE_FEATURED_META );
		}
		update_post_meta( $post_id, ARW_PULSE_FEATURED_META, 1 );
		update_option( ARW_PULSE_FEATURED_OPT, $post_id );
	} else {
		delete_post_meta( $post_id, ARW_PULSE_FEATURED_META );
		if ( (int) get_option( ARW_PULSE_FEATURED_OPT, 0 ) === $post_id ) {
			delete_option( ARW_PULSE_FEATURED_OPT );
		}
	}
} );

// Cache invalidation when featured post is trashed/deleted.
add_action( 'transition_post_status', function ( $new_status, $old_status, $post ) {
	if ( $post->post_type !== 'post' ) { return; }
	if ( $new_status === 'publish' ) { return; }
	$featured_id = (int) get_option( ARW_PULSE_FEATURED_OPT, 0 );
	if ( $featured_id === $post->ID ) {
		delete_option( ARW_PULSE_FEATURED_OPT );
		delete_post_meta( $post->ID, ARW_PULSE_FEATURED_META );
	}
}, 10, 3 );

// ─── Admin column (gold star) ────────────────────────────────────────────────

add_filter( 'manage_posts_columns', function ( $cols ) {
	$new = [];
	foreach ( $cols as $key => $label ) {
		$new[ $key ] = $label;
		if ( $key === 'title' ) {
			$new['arw_featured'] = '<span title="' . esc_attr__( 'À la une', 'arw-pulse' ) . '" style="color:#a78a4d;font-size:14px">★</span>';
		}
	}
	return $new;
} );

add_action( 'manage_posts_custom_column', function ( $col, $post_id ) {
	if ( $col !== 'arw_featured' ) { return; }
	$is = (bool) get_post_meta( $post_id, ARW_PULSE_FEATURED_META, true );
	if ( $is ) {
		echo '<span style="color:#a78a4d;font-size:18px" title="' . esc_attr__( 'À la une', 'arw-pulse' ) . '">★</span>';
	}
}, 10, 2 );

// ─── Helper : resolve current featured post ID ───────────────────────────────

function arw_pulse_get_featured_post_id(): int {
	$cached = (int) get_option( ARW_PULSE_FEATURED_OPT, 0 );
	if ( $cached ) {
		if ( get_post_status( $cached ) === 'publish' ) {
			return $cached;
		}
		delete_option( ARW_PULSE_FEATURED_OPT );
	}
	$matches = get_posts( [
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_query'     => [ [
			'key'     => ARW_PULSE_FEATURED_META,
			'value'   => '1',
			'compare' => '=',
		] ],
		'no_found_rows'  => true,
	] );
	if ( ! empty( $matches ) ) {
		$id = (int) $matches[0];
		update_option( ARW_PULSE_FEATURED_OPT, $id );
		return $id;
	}
	return 0;
}

// ─── Modify Query Loop blocks on the front page ──────────────────────────────

add_filter( 'query_loop_block_query_vars', function ( $query, $block ) {
	if ( ! is_front_page() ) { return $query; }

	// Always ignore sticky posts on the home — we own "featured" via meta.
	$query['ignore_sticky_posts'] = true;

	$per_page = (int) ( $query['posts_per_page'] ?? 0 );
	$offset   = (int) ( $query['offset'] ?? 0 );

	$featured_id = arw_pulse_get_featured_post_id();

	// hero-featured pattern : perPage=1, offset=0
	if ( $per_page === 1 && $offset === 0 ) {
		if ( $featured_id ) {
			$query['post__in'] = [ $featured_id ];
			$query['orderby']  = 'post__in';
			unset( $query['offset'] );
		}
		return $query;
	}

	// grid-magazine queries (offset >= 1) : exclude featured to avoid duplicate.
	if ( $featured_id && $offset >= 1 ) {
		$existing_exclude       = isset( $query['post__not_in'] ) ? (array) $query['post__not_in'] : [];
		$query['post__not_in']  = array_merge( $existing_exclude, [ $featured_id ] );
		// Decrement offset by 1 : sans featured, le post #1 (offset 0) était dans
		// hero-featured. Avec featured, hero-featured ne consomme plus le post #1,
		// donc grid doit décaler de 1 pour ne pas sauter le post le plus récent.
		$query['offset'] = $offset - 1;
	}

	return $query;
}, 10, 2 );
