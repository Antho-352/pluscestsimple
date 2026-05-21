<?php
/**
 * Identité & Social — UI admin pour toutes les valeurs site-specific qui
 * étaient jadis dans un mu-plugin / site-config.
 *
 * Couvre :
 *  - Handles Twitter (site + creator)
 *  - URLs sociales (Instagram, Facebook, LinkedIn, YouTube, Twitter, TikTok, Pinterest)
 *    → injectées dans Organization.sameAs (rich results Google)
 *  - Image OG fallback (article sans image à la une)
 *  - Texte disclaimer affiliation
 *  - Routing email des formulaires (par type)
 *  - DNS prefetch (domaines externes critiques)
 *
 * Les valeurs alimentent les filtres via priority 5 → un plugin peut override à ≥10.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

const ARW_IDENT_OPT = 'arw_identity';

// ─── Defaults ────────────────────────────────────────────────────────────────

function arw_pulse_identity_defaults(): array {
	return [
		'twitter_site'       => '',
		'twitter_creator'    => '',
		'social_twitter'     => '',
		'social_instagram'   => '',
		'social_facebook'    => '',
		'social_linkedin'    => '',
		'social_youtube'     => '',
		'social_tiktok'      => '',
		'social_pinterest'   => '',
		'og_image_id'        => 0,
		'disclosure_text'    => '',
		'form_to_contact'    => '',
		'form_to_press'      => '',
		'form_to_quote'      => '',
		'form_to_newsletter' => '',
		'form_to_affiliate'  => '',
		'dns_prefetch'       => '',
	];
}

function arw_pulse_identity(): array {
	return wp_parse_args( (array) get_option( ARW_IDENT_OPT, [] ), arw_pulse_identity_defaults() );
}

// ─── Admin menu ──────────────────────────────────────────────────────────────

add_action( 'admin_menu', function () {
	add_submenu_page(
		ARW_PULSE_ADMIN_SLUG,
		__( 'Identité & Social', 'arw-pulse' ),
		__( 'Identité & Social', 'arw-pulse' ),
		'manage_options',
		'arw-identity',
		'arw_pulse_render_identity_page'
	);
} );

// ─── Settings registration ───────────────────────────────────────────────────

add_action( 'admin_init', function () {
	register_setting( 'arw_identity_grp', ARW_IDENT_OPT, [
		'type'              => 'array',
		'sanitize_callback' => 'arw_pulse_sanitize_identity',
		'default'           => arw_pulse_identity_defaults(),
	] );
} );

function arw_pulse_sanitize_identity( $input ): array {
	$in    = is_array( $input ) ? $input : [];
	$clean = arw_pulse_identity_defaults();

	// Twitter handles: strip @, whitespace, and non-allowed chars.
	foreach ( [ 'twitter_site', 'twitter_creator' ] as $k ) {
		$raw = isset( $in[ $k ] ) ? wp_unslash( (string) $in[ $k ] ) : '';
		$raw = ltrim( trim( $raw ), '@' );
		$raw = preg_replace( '/[^A-Za-z0-9_]/', '', $raw );
		$clean[ $k ] = mb_substr( $raw, 0, 15 ); // Twitter max = 15
	}

	// Social URLs.
	foreach ( [ 'social_twitter', 'social_instagram', 'social_facebook', 'social_linkedin', 'social_youtube', 'social_tiktok', 'social_pinterest' ] as $k ) {
		$clean[ $k ] = isset( $in[ $k ] ) ? esc_url_raw( wp_unslash( (string) $in[ $k ] ) ) : '';
	}

	// OG image id.
	$clean['og_image_id'] = isset( $in['og_image_id'] ) ? (int) $in['og_image_id'] : 0;

	// Disclosure text (plain, no HTML).
	$clean['disclosure_text'] = isset( $in['disclosure_text'] )
		? sanitize_textarea_field( wp_unslash( (string) $in['disclosure_text'] ) )
		: '';

	// Email routing.
	foreach ( [ 'form_to_contact', 'form_to_press', 'form_to_quote', 'form_to_newsletter', 'form_to_affiliate' ] as $k ) {
		$raw = isset( $in[ $k ] ) ? sanitize_email( wp_unslash( (string) $in[ $k ] ) ) : '';
		$clean[ $k ] = is_email( $raw ) ? $raw : '';
	}

	// DNS prefetch: one URL per line, up to 20.
	if ( isset( $in['dns_prefetch'] ) ) {
		$lines = preg_split( '/\r\n|\r|\n/', (string) wp_unslash( $in['dns_prefetch'] ) );
		$urls  = [];
		foreach ( $lines as $line ) {
			$url = esc_url_raw( trim( $line ) );
			if ( $url ) { $urls[] = $url; }
			if ( count( $urls ) >= 20 ) { break; }
		}
		$clean['dns_prefetch'] = implode( "\n", $urls );
	}

	return $clean;
}

// ─── Bind options → filters (priority 5 : plugin peut override à 10+) ────────

add_filter( 'arw_pulse_twitter_site', function ( $default ) {
	$v = arw_pulse_identity()['twitter_site'];
	return $v ? '@' . $v : $default;
}, 5 );

add_filter( 'arw_pulse_twitter_creator', function ( $default ) {
	$v = arw_pulse_identity()['twitter_creator'];
	return $v ? '@' . $v : $default;
}, 5 );

add_filter( 'arw_pulse_organization_same_as', function ( $defaults ) {
	$v = arw_pulse_identity();
	$urls = array_filter( [
		$v['social_twitter'],
		$v['social_instagram'],
		$v['social_facebook'],
		$v['social_linkedin'],
		$v['social_youtube'],
		$v['social_tiktok'],
		$v['social_pinterest'],
	] );
	return array_values( array_unique( array_merge( (array) $defaults, $urls ) ) );
}, 5 );

add_filter( 'arw_pulse_default_og_image', function ( $default ) {
	$id = (int) arw_pulse_identity()['og_image_id'];
	if ( ! $id ) { return $default; }
	$src = wp_get_attachment_image_src( $id, 'large' );
	return $src ? $src[0] : $default;
}, 5 );

add_filter( 'arw_pulse_disclosure_text', function ( $default ) {
	$v = arw_pulse_identity()['disclosure_text'];
	return $v ?: $default;
}, 5 );

add_filter( 'arw_pulse_form_to', function ( $email, $form_type ) {
	$v   = arw_pulse_identity();
	$key = 'form_to_' . str_replace( '-inquiry', '', str_replace( '-', '_', $form_type ) );
	return ! empty( $v[ $key ] ) ? $v[ $key ] : $email;
}, 5, 2 );

add_filter( 'arw_pulse_dns_prefetch', function ( $defaults ) {
	$raw = (string) arw_pulse_identity()['dns_prefetch'];
	if ( ! $raw ) { return $defaults; }
	$urls = array_filter( array_map( 'trim', explode( "\n", $raw ) ) );
	return array_values( array_unique( array_merge( (array) $defaults, $urls ) ) );
}, 5 );

// ─── Admin page render ───────────────────────────────────────────────────────

function arw_pulse_render_identity_page(): void {
	$v = arw_pulse_identity();
	$og_id  = (int) $v['og_image_id'];
	$og_url = $og_id ? wp_get_attachment_image_url( $og_id, 'medium' ) : '';
	wp_enqueue_media();
	$o = ARW_IDENT_OPT;
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Identité & Social', 'arw-pulse' ); ?></h1>
		<p style="max-width:780px;color:#50575e">
			<?php esc_html_e( 'Ces champs alimentent automatiquement le schema Organization, les meta OG/Twitter, le routing email des formulaires et le disclaimer d\'affiliation. Un plugin custom peut toujours les écraser via filtre PHP.', 'arw-pulse' ); ?>
		</p>

		<form method="post" action="options.php">
			<?php settings_fields( 'arw_identity_grp' ); ?>

			<h2><?php esc_html_e( 'Twitter / X', 'arw-pulse' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th><label>Handle du site</label></th>
					<td>
						<span style="color:#787c82">@</span>
						<input type="text" name="<?php echo esc_attr( $o ); ?>[twitter_site]" value="<?php echo esc_attr( $v['twitter_site'] ); ?>" placeholder="lecampdubivouaqueur" maxlength="15">
						<p class="description">Utilisé dans <code>twitter:site</code>.</p>
					</td>
				</tr>
				<tr>
					<th><label>Handle du créateur</label></th>
					<td>
						<span style="color:#787c82">@</span>
						<input type="text" name="<?php echo esc_attr( $o ); ?>[twitter_creator]" value="<?php echo esc_attr( $v['twitter_creator'] ); ?>" placeholder="anthonyrusso" maxlength="15">
					</td>
				</tr>
			</table>

			<h2><?php esc_html_e( 'Réseaux sociaux', 'arw-pulse' ); ?></h2>
			<p class="description" style="margin-bottom:12px"><?php esc_html_e( 'URLs complètes. Injectées dans le schema Organization (sameAs) — renforce la confiance Google.', 'arw-pulse' ); ?></p>
			<table class="form-table" role="presentation">
				<?php foreach ( [
					'social_twitter'   => [ 'Twitter / X',  'https://twitter.com/…' ],
					'social_instagram' => [ 'Instagram',    'https://www.instagram.com/…' ],
					'social_facebook'  => [ 'Facebook',     'https://www.facebook.com/…' ],
					'social_linkedin'  => [ 'LinkedIn',     'https://www.linkedin.com/…' ],
					'social_youtube'   => [ 'YouTube',      'https://www.youtube.com/@…' ],
					'social_tiktok'    => [ 'TikTok',       'https://www.tiktok.com/@…' ],
					'social_pinterest' => [ 'Pinterest',    'https://www.pinterest.com/…' ],
				] as $k => [ $label, $placeholder ] ) : ?>
				<tr>
					<th><label><?php echo esc_html( $label ); ?></label></th>
					<td><input type="url" class="regular-text" name="<?php echo esc_attr( $o ); ?>[<?php echo esc_attr( $k ); ?>]" value="<?php echo esc_attr( $v[ $k ] ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>"></td>
				</tr>
				<?php endforeach; ?>
			</table>

			<h2><?php esc_html_e( 'Image sociale par défaut (OG fallback)', 'arw-pulse' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th><label>Image</label></th>
					<td>
						<div id="arw_ident_og_prev" style="margin-bottom:8px">
							<?php if ( $og_url ) : ?>
								<img src="<?php echo esc_url( $og_url ); ?>" style="max-width:300px;height:auto;border-radius:6px;border:1px solid #dcdcde">
							<?php endif; ?>
						</div>
						<button type="button" class="button" id="arw_ident_og_pick">Choisir une image</button>
						<button type="button" class="button" id="arw_ident_og_clear" <?php echo $og_id ? '' : 'style="display:none"'; ?>>Retirer</button>
						<input type="hidden" id="arw_ident_og_id" name="<?php echo esc_attr( $o ); ?>[og_image_id]" value="<?php echo (int) $og_id; ?>">
						<p class="description">Utilisée pour les partages réseaux quand un article n'a pas d'image à la une. Idéal : 1200×630 px.</p>
					</td>
				</tr>
			</table>

			<h2><?php esc_html_e( 'Disclaimer affiliation', 'arw-pulse' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th><label>Texte du disclaimer</label></th>
					<td>
						<textarea class="large-text" rows="3" name="<?php echo esc_attr( $o ); ?>[disclosure_text]" placeholder="Cet article contient des liens d'affiliation. Si vous achetez via ces liens, [nom du site] peut percevoir une commission sans surcoût pour vous."><?php echo esc_textarea( $v['disclosure_text'] ); ?></textarea>
						<p class="description">Injecté automatiquement en haut des articles qui contiennent des liens affiliation. Laisser vide pour utiliser le texte générique par défaut.</p>
					</td>
				</tr>
			</table>

			<h2><?php esc_html_e( 'Routing email des formulaires', 'arw-pulse' ); ?></h2>
			<p class="description" style="margin-bottom:12px"><?php esc_html_e( 'Email destinataire selon le type de formulaire. Laisser vide → email admin du site.', 'arw-pulse' ); ?></p>
			<table class="form-table" role="presentation">
				<?php foreach ( [
					'form_to_contact'    => 'Contact général',
					'form_to_press'      => 'Presse',
					'form_to_quote'      => 'Demandes de devis',
					'form_to_newsletter' => 'Inscriptions newsletter',
					'form_to_affiliate'  => 'Partenariats / affiliation',
				] as $k => $label ) : ?>
				<tr>
					<th><label><?php echo esc_html( $label ); ?></label></th>
					<td><input type="email" class="regular-text" name="<?php echo esc_attr( $o ); ?>[<?php echo esc_attr( $k ); ?>]" value="<?php echo esc_attr( $v[ $k ] ); ?>" placeholder="<?php echo esc_attr( str_replace( 'form_to_', '', $k ) ); ?>@…"></td>
				</tr>
				<?php endforeach; ?>
			</table>

			<h2><?php esc_html_e( 'DNS prefetch', 'arw-pulse' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th><label>Domaines externes à prefetch</label></th>
					<td>
						<textarea class="large-text code" rows="4" name="<?php echo esc_attr( $o ); ?>[dns_prefetch]" placeholder="https://www.google-analytics.com&#10;https://cdn.example.com"><?php echo esc_textarea( $v['dns_prefetch'] ); ?></textarea>
						<p class="description">Un par ligne, max 20. Utile si le site appelle des ressources externes critiques (analytics, CDN custom…).</p>
					</td>
				</tr>
			</table>

			<?php submit_button(); ?>
		</form>
	</div>

	<script>
	(function(){
		var pick=document.getElementById('arw_ident_og_pick'),
			clear=document.getElementById('arw_ident_og_clear'),
			prev=document.getElementById('arw_ident_og_prev'),
			idF=document.getElementById('arw_ident_og_id'),
			frame;
		pick.addEventListener('click', function(e){
			e.preventDefault();
			if(frame){ frame.open(); return; }
			frame = wp.media({ title: 'Image OG par défaut', library: {type:'image'}, multiple: false });
			frame.on('select', function(){
				var a = frame.state().get('selection').first().toJSON();
				idF.value = a.id;
				prev.innerHTML = '<img src="'+(a.sizes&&a.sizes.medium?a.sizes.medium.url:a.url)+'" style="max-width:300px;height:auto;border-radius:6px;border:1px solid #dcdcde">';
				clear.style.display='';
			});
			frame.open();
		});
		clear.addEventListener('click', function(e){
			e.preventDefault();
			idF.value = 0;
			prev.innerHTML = '';
			clear.style.display = 'none';
		});
	})();
	</script>
	<?php
}
