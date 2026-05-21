<?php
/**
 * Homepage content editor.
 * Admin UI (Réglages → Contenu Accueil) to edit Hero + Manifesto without touching files.
 * Values stored in options, consumed by patterns via the existing filters.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

const ARW_HP_OPT_HERO      = 'arw_hp_hero';
const ARW_HP_OPT_MANIFESTO = 'arw_hp_manifesto';

// ─── Menu ────────────────────────────────────────────────────────────────────

add_action( 'admin_menu', function () {
	add_submenu_page(
		ARW_PULSE_ADMIN_SLUG,
		__( 'Contenu Accueil', 'arw-pulse' ),
		__( 'Contenu Accueil', 'arw-pulse' ),
		'manage_options',
		'arw-homepage',
		'arw_pulse_render_homepage_page'
	);
} );

// ─── Settings registration ───────────────────────────────────────────────────

add_action( 'admin_init', function () {
	// Separate groups per tab — critical: shared groups wipe the other option on submit.
	register_setting( 'arw_hp_hero',      ARW_HP_OPT_HERO,      [ 'type' => 'array', 'sanitize_callback' => 'arw_pulse_sanitize_hero',      'default' => [] ] );
	register_setting( 'arw_hp_manifesto', ARW_HP_OPT_MANIFESTO, [ 'type' => 'array', 'sanitize_callback' => 'arw_pulse_sanitize_manifesto', 'default' => [] ] );
} );

function arw_pulse_sanitize_hero( $input ): array {
	$in = is_array( $input ) ? $input : [];
	$clean = [];
	foreach ( [ 'eyebrow', 'date_text', 'description', 'image_alt', 'meta_left', 'meta_right', 'cta_primary_label', 'cta_secondary_label' ] as $k ) {
		$clean[ $k ] = isset( $in[ $k ] ) ? sanitize_text_field( wp_unslash( $in[ $k ] ) ) : '';
	}
	foreach ( [ 'cta_primary_url', 'cta_secondary_url', 'image_url' ] as $k ) {
		$clean[ $k ] = isset( $in[ $k ] ) ? esc_url_raw( wp_unslash( $in[ $k ] ) ) : '';
	}
	// title_html: allow a small whitelist (<br>, <em>, <span>).
	$clean['title_html'] = isset( $in['title_html'] )
		? wp_kses( wp_unslash( $in['title_html'] ), [ 'br' => [], 'em' => [], 'span' => [ 'class' => true ] ] )
		: '';
	foreach ( [ 'image_width', 'image_height' ] as $k ) {
		$clean[ $k ] = isset( $in[ $k ] ) ? max( 0, (int) $in[ $k ] ) : 0;
	}
	$clean['image_id'] = isset( $in['image_id'] ) ? (int) $in['image_id'] : 0;
	return $clean;
}

function arw_pulse_sanitize_manifesto( $input ): array {
	$in = is_array( $input ) ? $input : [];
	return [
		'eyebrow'   => isset( $in['eyebrow'] )   ? sanitize_text_field( wp_unslash( $in['eyebrow'] ) )   : '',
		'sig_name'  => isset( $in['sig_name'] )  ? sanitize_text_field( wp_unslash( $in['sig_name'] ) )  : '',
		'sig_loc'   => isset( $in['sig_loc'] )   ? sanitize_text_field( wp_unslash( $in['sig_loc'] ) )   : '',
		'text_html' => isset( $in['text_html'] )
			? wp_kses( wp_unslash( $in['text_html'] ), [ 'mark' => [], 'em' => [], 'strong' => [], 'br' => [] ] )
			: '',
	];
}

// ─── Bind options → filters (priority 5 : mu-plugin peut override à 10+) ─────

add_filter( 'arw_pulse_hero_front_content', function ( $defaults ) {
	$opt = get_option( ARW_HP_OPT_HERO, [] );
	if ( empty( $opt ) || ! is_array( $opt ) ) { return $defaults; }

	$image_url    = $opt['image_url'] ?? '';
	$image_id     = (int) ( $opt['image_id'] ?? 0 );
	$image_width  = (int) ( $opt['image_width'] ?? 0 );
	$image_height = (int) ( $opt['image_height'] ?? 0 );

	// If image_id set, prefer its src over raw URL (lets WP serve srcset).
	if ( $image_id ) {
		$src = wp_get_attachment_image_src( $image_id, 'full' );
		if ( $src ) {
			$image_url    = $src[0];
			$image_width  = $src[1];
			$image_height = $src[2];
		}
	}

	$merged = $defaults;
	foreach ( [ 'eyebrow', 'date_text', 'title_html', 'description', 'image_alt', 'meta_left', 'meta_right' ] as $k ) {
		if ( ! empty( $opt[ $k ] ) ) { $merged[ $k ] = $opt[ $k ]; }
	}
	if ( $image_url )    { $merged['image_url']    = $image_url; }
	if ( $image_width )  { $merged['image_width']  = $image_width; }
	if ( $image_height ) { $merged['image_height'] = $image_height; }
	if ( ! empty( $opt['cta_primary_label'] ) || ! empty( $opt['cta_primary_url'] ) ) {
		$merged['cta_primary'] = [
			'label' => $opt['cta_primary_label'] ?: $defaults['cta_primary']['label'],
			'url'   => $opt['cta_primary_url']   ?: $defaults['cta_primary']['url'],
		];
	}
	if ( ! empty( $opt['cta_secondary_label'] ) || ! empty( $opt['cta_secondary_url'] ) ) {
		$merged['cta_secondary'] = [
			'label' => $opt['cta_secondary_label'] ?: $defaults['cta_secondary']['label'],
			'url'   => $opt['cta_secondary_url']   ?: $defaults['cta_secondary']['url'],
		];
	}
	return $merged;
}, 5 );

add_filter( 'arw_pulse_manifesto_content', function ( $defaults ) {
	$opt = get_option( ARW_HP_OPT_MANIFESTO, [] );
	if ( empty( $opt ) || ! is_array( $opt ) ) { return $defaults; }
	$merged = $defaults;
	foreach ( [ 'eyebrow', 'text_html', 'sig_name', 'sig_loc' ] as $k ) {
		if ( ! empty( $opt[ $k ] ) ) { $merged[ $k ] = $opt[ $k ]; }
	}
	return $merged;
}, 5 );

// ─── Admin page render ───────────────────────────────────────────────────────

function arw_pulse_render_homepage_page(): void {
	$tab  = isset( $_GET['tab'] ) ? sanitize_key( (string) wp_unslash( $_GET['tab'] ) ) : 'hero';
	if ( ! in_array( $tab, [ 'hero', 'manifesto' ], true ) ) { $tab = 'hero'; }
	$hero = wp_parse_args( get_option( ARW_HP_OPT_HERO, [] ), [
		'eyebrow' => '', 'date_text' => '', 'title_html' => '', 'description' => '',
		'cta_primary_label' => '', 'cta_primary_url' => '',
		'cta_secondary_label' => '', 'cta_secondary_url' => '',
		'image_url' => '', 'image_id' => 0, 'image_alt' => '', 'image_width' => 0, 'image_height' => 0,
		'meta_left' => '', 'meta_right' => '',
	] );
	$mani = wp_parse_args( get_option( ARW_HP_OPT_MANIFESTO, [] ), [
		'eyebrow' => '', 'text_html' => '', 'sig_name' => '', 'sig_loc' => '',
	] );
	wp_enqueue_media();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Contenu de la page d\'accueil', 'arw-pulse' ); ?></h1>

		<nav class="nav-tab-wrapper" style="margin-bottom:1rem">
			<a class="nav-tab <?php echo $tab === 'hero'      ? 'nav-tab-active' : ''; ?>" href="?page=arw-homepage&tab=hero">Hero</a>
			<a class="nav-tab <?php echo $tab === 'manifesto' ? 'nav-tab-active' : ''; ?>" href="?page=arw-homepage&tab=manifesto">Manifesto</a>
		</nav>

		<p style="max-width:720px;color:#50575e">
			<?php esc_html_e( 'Les champs laissés vides utilisent les valeurs par défaut du thème. Un mu-plugin avec filtre priority ≥ 10 a la priorité sur cet écran.', 'arw-pulse' ); ?>
		</p>

		<form method="post" action="options.php">

			<?php if ( $tab === 'hero' ) : ?>
				<?php settings_fields( 'arw_hp_hero' ); ?>
				<?php
				$h = ARW_HP_OPT_HERO;
				$img_preview = '';
				if ( $hero['image_id'] ) {
					$img_preview = wp_get_attachment_image_url( $hero['image_id'], 'medium' ) ?: '';
				} elseif ( $hero['image_url'] ) {
					$img_preview = $hero['image_url'];
				}
				?>
				<table class="form-table" role="presentation">
					<tr>
						<th><label>Eyebrow</label></th>
						<td><input type="text" class="regular-text" name="<?php echo esc_attr( $h ); ?>[eyebrow]" value="<?php echo esc_attr( $hero['eyebrow'] ); ?>" placeholder="№01 · Le média"></td>
					</tr>
					<tr>
						<th><label>Date / édition</label></th>
						<td><input type="text" class="regular-text" name="<?php echo esc_attr( $h ); ?>[date_text]" value="<?php echo esc_attr( $hero['date_text'] ); ?>" placeholder="Édition 2026"></td>
					</tr>
					<tr>
						<th><label>Titre <small style="font-weight:400;color:#787c82">(HTML : &lt;br&gt;, &lt;em&gt;)</small></label></th>
						<td>
							<textarea class="large-text" rows="3" name="<?php echo esc_attr( $h ); ?>[title_html]" placeholder="Titre accroche,<br>&lt;em&gt;mot signature.&lt;/em&gt;"><?php echo esc_textarea( $hero['title_html'] ); ?></textarea>
							<p class="description">Utilise <code>&lt;br&gt;</code> pour retour ligne, <code>&lt;em&gt;mot&lt;/em&gt;</code> pour italique coloré accent.</p>
						</td>
					</tr>
					<tr>
						<th><label>Description</label></th>
						<td><textarea class="large-text" rows="3" name="<?php echo esc_attr( $h ); ?>[description]"><?php echo esc_textarea( $hero['description'] ); ?></textarea></td>
					</tr>
					<tr>
						<th><label>CTA principal</label></th>
						<td>
							<input type="text" style="width:50%" name="<?php echo esc_attr( $h ); ?>[cta_primary_label]" value="<?php echo esc_attr( $hero['cta_primary_label'] ); ?>" placeholder="Lire les articles">
							<input type="url"  style="width:45%;margin-left:4px" name="<?php echo esc_attr( $h ); ?>[cta_primary_url]"   value="<?php echo esc_attr( $hero['cta_primary_url'] ); ?>" placeholder="/blog/">
						</td>
					</tr>
					<tr>
						<th><label>CTA secondaire</label></th>
						<td>
							<input type="text" style="width:50%" name="<?php echo esc_attr( $h ); ?>[cta_secondary_label]" value="<?php echo esc_attr( $hero['cta_secondary_label'] ); ?>" placeholder="Voir la carte">
							<input type="url"  style="width:45%;margin-left:4px" name="<?php echo esc_attr( $h ); ?>[cta_secondary_url]"   value="<?php echo esc_attr( $hero['cta_secondary_url'] ); ?>" placeholder="/carte/">
						</td>
					</tr>
					<tr>
						<th><label>Image hero</label></th>
						<td>
							<div id="arw_hp_img_preview" style="margin-bottom:8px">
								<?php if ( $img_preview ) : ?>
									<img src="<?php echo esc_url( $img_preview ); ?>" style="max-width:280px;height:auto;border-radius:6px;border:1px solid #dcdcde">
								<?php endif; ?>
							</div>
							<button type="button" class="button" id="arw_hp_img_pick">Choisir dans la médiathèque</button>
							<button type="button" class="button" id="arw_hp_img_clear" <?php echo $hero['image_id'] ? '' : 'style="display:none"'; ?>>Retirer</button>
							<input type="hidden" id="arw_hp_img_id"     name="<?php echo esc_attr( $h ); ?>[image_id]"     value="<?php echo (int) $hero['image_id']; ?>">
							<input type="hidden" id="arw_hp_img_url"    name="<?php echo esc_attr( $h ); ?>[image_url]"    value="<?php echo esc_attr( $hero['image_url'] ); ?>">
							<input type="hidden" id="arw_hp_img_width"  name="<?php echo esc_attr( $h ); ?>[image_width]"  value="<?php echo (int) $hero['image_width']; ?>">
							<input type="hidden" id="arw_hp_img_height" name="<?php echo esc_attr( $h ); ?>[image_height]" value="<?php echo (int) $hero['image_height']; ?>">
							<p><label>Alt : <input type="text" style="width:400px;margin-top:6px" name="<?php echo esc_attr( $h ); ?>[image_alt]" value="<?php echo esc_attr( $hero['image_alt'] ); ?>" placeholder="Description de l'image pour accessibilité + SEO"></label></p>
						</td>
					</tr>
					<tr>
						<th><label>Footer meta gauche</label></th>
						<td><input type="text" class="regular-text" name="<?php echo esc_attr( $h ); ?>[meta_left]" value="<?php echo esc_attr( $hero['meta_left'] ); ?>" placeholder="12 produits testés · 48 articles · Mise à jour hebdo"></td>
					</tr>
					<tr>
						<th><label>Footer meta droite</label></th>
						<td><input type="text" class="regular-text" name="<?php echo esc_attr( $h ); ?>[meta_right]" value="<?php echo esc_attr( $hero['meta_right'] ); ?>" placeholder="Paris · Depuis 2025"></td>
					</tr>
				</table>

			<?php elseif ( $tab === 'manifesto' ) : ?>
				<?php settings_fields( 'arw_hp_manifesto' ); ?>
				<?php $m = ARW_HP_OPT_MANIFESTO; ?>
				<table class="form-table" role="presentation">
					<tr>
						<th><label>Eyebrow</label></th>
						<td><input type="text" class="regular-text" name="<?php echo esc_attr( $m ); ?>[eyebrow]" value="<?php echo esc_attr( $mani['eyebrow'] ); ?>" placeholder="№04 · Manifesto"></td>
					</tr>
					<tr>
						<th><label>Texte <small style="font-weight:400;color:#787c82">(HTML : &lt;mark&gt;, &lt;em&gt;, &lt;strong&gt;)</small></label></th>
						<td>
							<textarea class="large-text" rows="6" name="<?php echo esc_attr( $m ); ?>[text_html]"><?php echo esc_textarea( $mani['text_html'] ); ?></textarea>
							<p class="description">Utilise <code>&lt;mark&gt;mot&lt;/mark&gt;</code> pour souligner un terme clé dans la couleur accent.</p>
						</td>
					</tr>
					<tr>
						<th><label>Signature — nom</label></th>
						<td><input type="text" class="regular-text" name="<?php echo esc_attr( $m ); ?>[sig_name]" value="<?php echo esc_attr( $mani['sig_name'] ); ?>" placeholder="La rédaction"></td>
					</tr>
					<tr>
						<th><label>Signature — lieu/édition</label></th>
						<td><input type="text" class="regular-text" name="<?php echo esc_attr( $m ); ?>[sig_loc]" value="<?php echo esc_attr( $mani['sig_loc'] ); ?>" placeholder="Paris · Édition 2026"></td>
					</tr>
				</table>
			<?php endif; ?>

			<?php submit_button(); ?>
		</form>
	</div>

	<?php if ( $tab === 'hero' ) : ?>
	<script>
	(function(){
		var pick=document.getElementById('arw_hp_img_pick'),
			clear=document.getElementById('arw_hp_img_clear'),
			prev=document.getElementById('arw_hp_img_preview'),
			idF=document.getElementById('arw_hp_img_id'),
			urlF=document.getElementById('arw_hp_img_url'),
			wF=document.getElementById('arw_hp_img_width'),
			hF=document.getElementById('arw_hp_img_height'),
			frame;
		pick.addEventListener('click', function(e){
			e.preventDefault();
			if(frame){ frame.open(); return; }
			frame = wp.media({ title: 'Image hero', library: {type:'image'}, multiple: false });
			frame.on('select', function(){
				var a = frame.state().get('selection').first().toJSON();
				idF.value = a.id;
				urlF.value = a.url;
				wF.value = a.width;
				hF.value = a.height;
				prev.innerHTML = '<img src="'+(a.sizes&&a.sizes.medium?a.sizes.medium.url:a.url)+'" style="max-width:280px;height:auto;border-radius:6px;border:1px solid #dcdcde">';
				clear.style.display='';
			});
			frame.open();
		});
		clear.addEventListener('click', function(e){
			e.preventDefault();
			idF.value = 0; urlF.value = ''; wF.value = 0; hF.value = 0;
			prev.innerHTML = '';
			clear.style.display = 'none';
		});
	})();
	</script>
	<?php endif;
}
