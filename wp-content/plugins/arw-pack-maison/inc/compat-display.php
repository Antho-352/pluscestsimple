<?php
/**
 * Shortcodes du Compatibilimètre — rendu front-end.
 *
 *  - [arw_compatibilimetre]       : page de recherche (input + filtres + résultats)
 *  - [arw_compat_rule_detail]     : carte détail (utilisée dans single-arw_compat_rule.html)
 *  - [arw_compat_related limit=3] : règles liées (même catégorie)
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

// ─── Helpers ─────────────────────────────────────────────────────────────────

function arw_maison_verdict_label( string $v ): string {
	$map = arw_maison_verdicts();
	return $map[ $v ] ?? $v;
}

function arw_maison_verdict_icon( string $v ): string {
	return [
		'compatible'  => '✓',
		'conditional' => '◐',
		'discouraged' => '!',
		'forbidden'   => '✕',
	][ $v ] ?? '?';
}

function arw_maison_enqueue_compat_assets(): void {
	wp_enqueue_style( 'arw-maison-compat' );
	wp_enqueue_script( 'arw-maison-compat' );
}

// ─── [arw_compatibilimetre] : index search page ──────────────────────────────

add_shortcode( 'arw_compatibilimetre', function () {
	arw_maison_enqueue_compat_assets();

	// Inject the JSON index URL into JS via inline data.
	wp_add_inline_script(
		'arw-maison-compat',
		'window.arwMaisonIndexURL = ' . wp_json_encode( arw_maison_index_url() ) . ';',
		'before'
	);

	// Categories for filter chips.
	$cats = get_terms( [
		'taxonomy'   => ARW_MAISON_CATEGORY_TAX,
		'hide_empty' => true,
		'orderby'    => 'name',
	] );
	if ( is_wp_error( $cats ) ) { $cats = []; }

	$verdicts = arw_maison_verdicts();

	ob_start();
	?>
	<section class="arw-compat" aria-label="<?php esc_attr_e( 'Compatibilimètre', 'arw-maison' ); ?>">

		<p class="arw-compat__lead"><?php esc_html_e( 'Vérifiez si votre projet est compatible, sous quelles conditions, ou à éviter. Tapez votre question ou parcourez par catégorie.', 'arw-maison' ); ?></p>

		<div class="arw-compat__search">
			<label for="arw-compat-q" class="screen-reader-text"><?php esc_html_e( 'Rechercher une règle', 'arw-maison' ); ?></label>
			<input type="search" id="arw-compat-q" placeholder="<?php esc_attr_e( 'Tapez votre question : parquet, mur porteur, douche italienne…', 'arw-maison' ); ?>" autocomplete="off" autocorrect="off" spellcheck="false" />
		</div>

		<div class="arw-compat__filters" role="group" aria-label="<?php esc_attr_e( 'Filtres', 'arw-maison' ); ?>">

			<div class="arw-compat__filter-row">
				<span class="arw-compat__filter-label"><?php esc_html_e( 'Catégorie', 'arw-maison' ); ?></span>
				<div class="arw-compat__chips" data-filter="category">
					<button type="button" class="arw-chip is-active" data-value=""><?php esc_html_e( 'Toutes', 'arw-maison' ); ?></button>
					<?php foreach ( $cats as $cat ) : ?>
						<button type="button" class="arw-chip" data-value="<?php echo esc_attr( $cat->slug ); ?>"><?php echo esc_html( $cat->name ); ?></button>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="arw-compat__filter-row">
				<span class="arw-compat__filter-label"><?php esc_html_e( 'Verdict', 'arw-maison' ); ?></span>
				<div class="arw-compat__chips" data-filter="verdict">
					<button type="button" class="arw-chip is-active" data-value=""><?php esc_html_e( 'Tous', 'arw-maison' ); ?></button>
					<?php foreach ( $verdicts as $k => $label ) : ?>
						<button type="button" class="arw-chip arw-chip--verdict-<?php echo esc_attr( $k ); ?>" data-value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $label ); ?></button>
					<?php endforeach; ?>
				</div>
			</div>

		</div>

		<div class="arw-compat__count" aria-live="polite" data-count></div>

		<ul class="arw-compat__results" role="list" data-results></ul>

		<div class="arw-compat__empty" hidden>
			<p><?php esc_html_e( 'Aucune règle ne correspond.', 'arw-maison' ); ?></p>
			<p class="arw-compat__empty-hint"><?php esc_html_e( 'Essayez : « parquet », « verrière », « VMC », « plan de travail »…', 'arw-maison' ); ?></p>
		</div>

		<?php
		// SEO + accessibilité : index statique de toutes les règles, regroupées
		// par catégorie, en vrais <a href> crawlables. Sans cet index, les pages
		// règles sont orphelines (chips JS ne sont pas des liens HTML).
		$all_rules = get_posts( [
			'post_type'      => ARW_MAISON_RULE_CPT,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		] );
		$by_cat = [];
		foreach ( $all_rules as $r ) {
			$terms = wp_get_post_terms( $r->ID, ARW_MAISON_CATEGORY_TAX );
			$slug  = ( ! is_wp_error( $terms ) && ! empty( $terms ) ) ? $terms[0]->slug : '_uncategorized';
			$by_cat[ $slug ][] = $r;
		}
		if ( $all_rules ) :
			?>
			<aside class="arw-compat__index" aria-label="<?php esc_attr_e( 'Index complet des règles', 'arw-maison' ); ?>">
				<details>
					<summary><?php printf( esc_html__( 'Index complet : %d règles par catégorie', 'arw-maison' ), count( $all_rules ) ); ?></summary>
					<?php foreach ( $cats as $cat ) :
						if ( empty( $by_cat[ $cat->slug ] ) ) { continue; } ?>
						<h3 class="arw-compat__index-cat">
							<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a>
						</h3>
						<ul class="arw-compat__index-list">
							<?php foreach ( $by_cat[ $cat->slug ] as $r ) : ?>
								<li><a href="<?php echo esc_url( get_permalink( $r ) ); ?>"><?php echo esc_html( get_the_title( $r ) ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					<?php endforeach; ?>
				</details>
			</aside>
		<?php endif; ?>

	</section>
	<?php
	return (string) ob_get_clean();
} );

// Render a fixed side widget (sticky sidebar) on Compatibilimètre pages.
// Visible at all times during scroll on desktop (≥1200px), perfect for long
// pages of 200+ rules. Hidden on mobile (relies on inline encart on rule pages).
add_action( 'wp_footer', function () {
	if ( ! function_exists( 'arw_maison_lead_settings' ) ) { return; }
	$settings = arw_maison_lead_settings();
	if ( empty( $settings['enabled'] ) ) { return; }

	$page_id  = (int) get_option( ARW_MAISON_INDEX_PAGE_OPT, 0 );
	$is_index = $page_id && is_page( $page_id );
	$is_rule  = is_singular( ARW_MAISON_RULE_CPT );

	if ( ! $is_index && ! $is_rule ) { return; }

	echo do_shortcode( '[arw_lead_form variant="side"]' );
}, 5 );

// ─── Inject lead-magnet config + nonce to JS (rule pages only) ───────────────

function arw_maison_inject_lead_config(): void {
	if ( ! function_exists( 'arw_maison_lead_settings' ) ) { return; }

	$settings = arw_maison_lead_settings();

	$config = [
		'enabled'       => (bool) $settings['enabled'],
		'threshold'     => (int) $settings['threshold'],
		'restUrl'       => esc_url_raw( rest_url( 'arw/v1/submit' ) ),
		'nonce'         => wp_create_nonce( 'wp_rest' ),
		'modalTitle'    => $settings['modal_title'],
		'modalText'     => $settings['modal_text'],
		'modalButton'   => $settings['modal_button'],
		'modalConsent'  => $settings['modal_consent'],
		'currentRuleId' => is_singular( ARW_MAISON_RULE_CPT ) ? get_the_ID() : 0,
	];

	wp_add_inline_script(
		'arw-maison-compat',
		'window.arwMaisonLead = ' . wp_json_encode( $config ) . ';',
		'before'
	);
}

// ─── [arw_compat_rule_detail] : single rule renderer ─────────────────────────

add_shortcode( 'arw_compat_rule_detail', function () {
	if ( ! is_singular( ARW_MAISON_RULE_CPT ) ) { return ''; }

	arw_maison_enqueue_compat_assets();
	arw_maison_inject_lead_config();

	$post_id           = get_the_ID();
	$verdict           = (string) get_post_meta( $post_id, '_arw_rule_verdict', true );
	$explanation       = (string) get_post_meta( $post_id, '_arw_rule_explanation', true );
	$alternatives      = (string) get_post_meta( $post_id, '_arw_rule_alternatives', true );
	$dtu_refs          = (string) get_post_meta( $post_id, '_arw_rule_dtu_refs', true );
	$diagnostic        = (string) get_post_meta( $post_id, '_arw_rule_diagnostic', true );
	$cost              = (string) get_post_meta( $post_id, '_arw_rule_cost', true );
	$pro_order         = (string) get_post_meta( $post_id, '_arw_rule_pro_order', true );
	$alternatives_long = (string) get_post_meta( $post_id, '_arw_rule_alternatives_long', true );
	$common_mistakes   = (string) get_post_meta( $post_id, '_arw_rule_common_mistakes', true );

	// If alternatives_long is populated, prefer it over the short alternatives.
	$alternatives_to_show = $alternatives_long ?: $alternatives;

	if ( ! $verdict ) { return ''; }

	$label = arw_maison_verdict_label( $verdict );
	$icon  = arw_maison_verdict_icon( $verdict );

	// Catégorie principale.
	$cats     = wp_get_post_terms( $post_id, ARW_MAISON_CATEGORY_TAX );
	$category = ! is_wp_error( $cats ) && ! empty( $cats ) ? $cats[0] : null;

	ob_start();
	?>
	<article class="arw-rule arw-rule--<?php echo esc_attr( $verdict ); ?>">

		<?php if ( $category ) : ?>
			<a href="<?php echo esc_url( get_term_link( $category ) ); ?>" class="arw-rule__category">
				<?php echo esc_html( $category->name ); ?>
			</a>
		<?php endif; ?>

		<h1 class="arw-rule__title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h1>

		<div class="arw-rule__verdict" role="status">
			<span class="arw-rule__verdict-icon" aria-hidden="true"><?php echo esc_html( $icon ); ?></span>
			<span class="arw-rule__verdict-label"><?php echo esc_html( $label ); ?></span>
		</div>

		<?php if ( $explanation ) : ?>
			<div class="arw-rule__block arw-rule__block--explanation">
				<h2 class="arw-rule__block-title"><?php esc_html_e( 'Pourquoi ?', 'arw-maison' ); ?></h2>
				<div class="arw-rule__block-body"><?php echo wp_kses_post( wpautop( $explanation ) ); ?></div>
			</div>
		<?php endif; ?>

		<?php if ( $diagnostic ) : ?>
			<div class="arw-rule__block arw-rule__block--diagnostic">
				<h2 class="arw-rule__block-title"><?php esc_html_e( 'Comment savoir si ça s\'applique chez vous', 'arw-maison' ); ?></h2>
				<div class="arw-rule__block-body"><?php echo wp_kses_post( wpautop( $diagnostic ) ); ?></div>
			</div>
		<?php endif; ?>

		<?php if ( $cost ) : ?>
			<div class="arw-rule__block arw-rule__block--cost">
				<h2 class="arw-rule__block-title"><?php esc_html_e( 'Combien ça coûte', 'arw-maison' ); ?></h2>
				<div class="arw-rule__block-body"><?php echo wp_kses_post( wpautop( $cost ) ); ?></div>
			</div>
		<?php endif; ?>

		<?php if ( $pro_order ) : ?>
			<div class="arw-rule__block arw-rule__block--pro-order">
				<h2 class="arw-rule__block-title"><?php esc_html_e( 'Qui appeler en premier', 'arw-maison' ); ?></h2>
				<div class="arw-rule__block-body"><?php echo wp_kses_post( wpautop( $pro_order ) ); ?></div>
			</div>
		<?php endif; ?>

		<?php if ( $alternatives_to_show ) : ?>
			<div class="arw-rule__block arw-rule__block--alternatives">
				<h2 class="arw-rule__block-title"><?php esc_html_e( 'Alternatives', 'arw-maison' ); ?></h2>
				<div class="arw-rule__block-body"><?php echo wp_kses_post( wpautop( $alternatives_to_show ) ); ?></div>
			</div>
		<?php endif; ?>

		<?php if ( $common_mistakes ) : ?>
			<div class="arw-rule__block arw-rule__block--mistakes">
				<h2 class="arw-rule__block-title"><?php esc_html_e( 'Erreurs fréquentes', 'arw-maison' ); ?></h2>
				<div class="arw-rule__block-body"><?php echo wp_kses_post( wpautop( $common_mistakes ) ); ?></div>
			</div>
		<?php endif; ?>

		<?php if ( $dtu_refs ) : ?>
			<aside class="arw-rule__refs">
				<span class="arw-rule__refs-label"><?php esc_html_e( 'Références techniques', 'arw-maison' ); ?> :</span>
				<span class="arw-rule__refs-list"><?php echo esc_html( $dtu_refs ); ?></span>
			</aside>
		<?php endif; ?>

		<p class="arw-rule__back">
			<a href="<?php echo esc_url( home_url( '/compatibilimetre/' ) ); ?>">← <?php esc_html_e( 'Retour au Compatibilimètre', 'arw-maison' ); ?></a>
		</p>

	</article>

	<?php echo do_shortcode( '[arw_lead_form variant="compact"]' ); ?>

	<?php
	// Lead magnet modal — rendered hidden, JS toggles it when threshold is reached.
	$lead_settings = function_exists( 'arw_maison_lead_settings' ) ? arw_maison_lead_settings() : null;
	if ( $lead_settings && ! empty( $lead_settings['enabled'] ) ) :
	?>
	<div class="arw-lead-modal" data-lead-modal hidden>
		<div class="arw-lead-modal__backdrop" data-lead-dismiss></div>
		<div class="arw-lead-modal__panel" role="dialog" aria-modal="true" aria-labelledby="arw-lead-title">

			<div class="arw-lead-modal__view" data-lead-view="form">
				<p class="arw-lead-modal__eyebrow"><?php esc_html_e( 'Plus c\'est simple', 'arw-maison' ); ?></p>
				<h2 id="arw-lead-title" class="arw-lead-modal__title"><?php echo esc_html( $lead_settings['modal_title'] ); ?></h2>
				<p class="arw-lead-modal__text"><?php echo esc_html( $lead_settings['modal_text'] ); ?></p>

				<form class="arw-lead-modal__form" data-lead-form novalidate>
					<label for="arw-lead-email" class="screen-reader-text"><?php esc_html_e( 'Adresse email', 'arw-maison' ); ?></label>
					<input
						type="email"
						id="arw-lead-email"
						name="email"
						required
						autocomplete="email"
						placeholder="<?php esc_attr_e( 'votre.email@exemple.fr', 'arw-maison' ); ?>"
					>

					<!-- honeypot -->
					<input type="text" name="website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">

					<label class="arw-lead-modal__consent">
						<input type="checkbox" name="consent" required>
						<span><?php echo esc_html( $lead_settings['modal_consent'] ); ?></span>
					</label>

					<button type="submit" class="arw-lead-modal__submit">
						<?php echo esc_html( $lead_settings['modal_button'] ); ?>
					</button>

					<p class="arw-lead-modal__error" data-lead-error hidden></p>
				</form>

				<button type="button" class="arw-lead-modal__later" data-lead-dismiss>
					<?php esc_html_e( 'Plus tard', 'arw-maison' ); ?>
				</button>
			</div>

			<div class="arw-lead-modal__view arw-lead-modal__view--success" data-lead-view="success" hidden>
				<p class="arw-lead-modal__eyebrow"><?php esc_html_e( 'Envoyé', 'arw-maison' ); ?></p>
				<h2 class="arw-lead-modal__title"><?php esc_html_e( 'Vérifiez votre boîte mail.', 'arw-maison' ); ?></h2>
				<p class="arw-lead-modal__text"><?php esc_html_e( 'Le guide PDF a été envoyé. Le lien expire dans 24 heures. Si vous ne le voyez pas, regardez dans les spams.', 'arw-maison' ); ?></p>
				<button type="button" class="arw-lead-modal__submit" data-lead-close-success>
					<?php esc_html_e( 'Continuer la lecture', 'arw-maison' ); ?>
				</button>
			</div>

		</div>
	</div>
	<?php endif; ?>
	<?php
	return (string) ob_get_clean();
} );

// ─── [arw_lead_form] : standalone inline form (home / index / rule pages) ──

add_shortcode( 'arw_lead_form', function ( $atts ) {
	if ( ! function_exists( 'arw_maison_lead_settings' ) ) { return ''; }
	$settings = arw_maison_lead_settings();
	if ( empty( $settings['enabled'] ) ) { return ''; }

	$atts = shortcode_atts( [
		'variant' => 'full', // full | compact | side
		'eyebrow' => __( 'Guide PDF gratuit', 'arw-maison' ),
		'title'   => __( '12 erreurs qui coûtent cher en rénovation', 'arw-maison' ),
		'text'    => __( 'Synthèse des règles à connaître avant de signer un devis. PDF de 26 pages, sans publicité.', 'arw-maison' ),
	], $atts, 'arw_lead_form' );

	arw_maison_enqueue_compat_assets();
	arw_maison_inject_lead_config();

	$variant       = sanitize_html_class( $atts['variant'] );
	$variant_class = 'arw-lead-inline arw-lead-inline--' . $variant;
	$is_side       = ( $variant === 'side' );

	// Side variant uses tighter copy.
	if ( $is_side ) {
		$atts['title'] = __( '12 erreurs coûteuses avant chaque chantier', 'arw-maison' );
		$atts['text']  = __( '26 pages. Les pièges les plus fréquents, expliqués sans détour. Désabonnement 1 clic.', 'arw-maison' );
	}

	ob_start();
	?>
	<aside class="<?php echo esc_attr( $variant_class ); ?>" data-lead-inline<?php echo $is_side ? ' data-lead-side' : ''; ?>>

		<?php if ( $is_side ) : ?>
			<button type="button" class="arw-lead-inline__close" data-lead-side-close aria-label="<?php esc_attr_e( 'Fermer', 'arw-maison' ); ?>">×</button>
		<?php endif; ?>

		<div class="arw-lead-inline__form-state" data-lead-inline-form-state>
			<p class="arw-lead-inline__eyebrow"><?php echo esc_html( $atts['eyebrow'] ); ?></p>
			<h2 class="arw-lead-inline__title"><?php echo esc_html( $atts['title'] ); ?></h2>
			<p class="arw-lead-inline__text"><?php echo esc_html( $atts['text'] ); ?></p>

			<form class="arw-lead-inline__form" data-lead-inline-form novalidate>
				<div class="arw-lead-inline__field-row">
					<label for="arw-lead-inline-email-<?php echo esc_attr( wp_unique_id() ); ?>" class="screen-reader-text"><?php esc_html_e( 'Adresse email', 'arw-maison' ); ?></label>
					<input
						type="email"
						name="email"
						required
						autocomplete="email"
						placeholder="<?php esc_attr_e( 'votre.email@exemple.fr', 'arw-maison' ); ?>"
					>
					<button type="submit" class="arw-lead-inline__submit">
						<?php echo esc_html( $is_side ? __( 'Recevoir', 'arw-maison' ) : $settings['modal_button'] ); ?>
					</button>
				</div>

				<input type="text" name="website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">

				<label class="arw-lead-inline__consent">
					<input type="checkbox" name="consent" required>
					<span><?php echo esc_html( $is_side ? __( 'J\'accepte de recevoir le guide et la lettre mensuelle.', 'arw-maison' ) : $settings['modal_consent'] ); ?></span>
				</label>

				<p class="arw-lead-inline__error" data-lead-inline-error hidden></p>
			</form>
		</div>

		<div class="arw-lead-inline__success-state" data-lead-inline-success hidden>
			<p class="arw-lead-inline__eyebrow"><?php esc_html_e( 'Envoyé', 'arw-maison' ); ?></p>
			<h2 class="arw-lead-inline__title"><?php esc_html_e( 'Vérifiez votre boîte mail.', 'arw-maison' ); ?></h2>
			<p class="arw-lead-inline__text"><?php esc_html_e( 'Le guide PDF a été envoyé. Le lien expire dans 24 heures. Si vous ne le voyez pas, regardez dans les spams.', 'arw-maison' ); ?></p>
		</div>

	</aside>
	<?php
	return (string) ob_get_clean();
} );

// ─── [arw_compat_related] : related rules (same category) ────────────────────

add_shortcode( 'arw_compat_related', function ( $atts ) {
	if ( ! is_singular( ARW_MAISON_RULE_CPT ) ) { return ''; }

	$atts = shortcode_atts( [ 'limit' => 4 ], $atts, 'arw_compat_related' );
	$post_id = get_the_ID();

	$cats = wp_get_post_terms( $post_id, ARW_MAISON_CATEGORY_TAX, [ 'fields' => 'ids' ] );
	if ( is_wp_error( $cats ) || empty( $cats ) ) { return ''; }

	$related = get_posts( [
		'post_type'      => ARW_MAISON_RULE_CPT,
		'post_status'    => 'publish',
		'posts_per_page' => max( 1, (int) $atts['limit'] ),
		'orderby'        => 'rand',
		'post__not_in'   => [ $post_id ],
		'tax_query'      => [ [
			'taxonomy' => ARW_MAISON_CATEGORY_TAX,
			'field'    => 'term_id',
			'terms'    => $cats,
		] ],
	] );

	if ( empty( $related ) ) { return ''; }

	arw_maison_enqueue_compat_assets();

	ob_start();
	?>
	<section class="arw-related" aria-label="<?php esc_attr_e( 'Règles liées', 'arw-maison' ); ?>">
		<h2 class="arw-related__title"><?php esc_html_e( 'Dans la même catégorie', 'arw-maison' ); ?></h2>
		<ul class="arw-related__list" role="list">
			<?php foreach ( $related as $r ) :
				$v   = (string) get_post_meta( $r->ID, '_arw_rule_verdict', true );
				$lbl = arw_maison_verdict_label( $v );
				$ico = arw_maison_verdict_icon( $v );
				?>
				<li class="arw-related__item">
					<a href="<?php echo esc_url( get_permalink( $r->ID ) ); ?>" class="arw-related__link arw-related__link--<?php echo esc_attr( $v ); ?>">
						<span class="arw-related__verdict" aria-hidden="true"><?php echo esc_html( $ico ); ?></span>
						<span class="arw-related__title-link"><?php echo esc_html( get_the_title( $r->ID ) ); ?></span>
						<span class="arw-related__verdict-label"><?php echo esc_html( $lbl ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>
	<?php
	return (string) ob_get_clean();
} );
