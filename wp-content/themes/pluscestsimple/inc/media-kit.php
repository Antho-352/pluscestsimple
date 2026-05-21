<?php
/**
 * Media kit support.
 * - Registers custom options (stats, audience, engagement, formats pub, partenaires, contact).
 * - Exposes these to patterns via helper arw_pulse_media_kit().
 * - All fields start empty — pattern affiche "—" tant qu'un champ n'est pas rempli.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Register settings, editable via wp-admin → ARW Pulse → Kit média.
 */
add_action( 'admin_menu', function () {
	add_submenu_page(
		ARW_PULSE_ADMIN_SLUG,
		__( 'Kit média', 'arw-pulse' ),
		__( 'Kit média', 'arw-pulse' ),
		'manage_options',
		'arw-media-kit',
		'arw_pulse_render_media_kit_settings'
	);
} );

add_action( 'admin_init', function () {
	register_setting( 'arw_media_kit', 'arw_media_kit', [
		'type'              => 'array',
		'sanitize_callback' => 'arw_pulse_sanitize_media_kit',
		'default'           => [],
	] );
} );

/**
 * Liste exhaustive des champs avec leur type.
 * type ∈ {text,url,email,textarea,select}
 */
function arw_pulse_media_kit_fields(): array {
	return [
		// — 1. Hero (legacy + nouveau) —
		'tagline'                     => 'text',
		'hero_kpi1_value'             => 'text',
		'hero_kpi1_label'             => 'text',
		'hero_kpi2_value'             => 'text',
		'hero_kpi2_label'             => 'text',
		'hero_kpi3_value'             => 'text',
		'hero_kpi3_label'             => 'text',
		// — 2. À propos —
		'about_who'                   => 'textarea',
		'about_angle'                 => 'textarea',
		// — 3. Audience —
		'audience_monthly'            => 'text', // legacy
		'audience_age'                => 'text',
		'audience_gender'             => 'text',
		'audience_geo'                => 'text',
		'audience_interests'          => 'textarea',
		// — 4. Engagement —
		'engagement_time'             => 'text',
		'engagement_scroll'           => 'text',
		'engagement_return'           => 'text',
		'engagement_pages_session'    => 'text',
		// — 5. Top articles —
		'top_articles_mode'           => 'select', // 'manual' | 'auto'
		'top_articles_urls'           => 'textarea',
		// — 6. Réseaux sociaux —
		'social_total'                => 'text', // legacy
		'social_instagram_url'        => 'url',
		'social_instagram_followers'  => 'text',
		'social_tiktok_url'           => 'url',
		'social_tiktok_followers'     => 'text',
		'social_linkedin_url'         => 'url',
		'social_linkedin_followers'   => 'text',
		'social_youtube_url'          => 'url',
		'social_youtube_followers'    => 'text',
		// — 7. Identité (legacy) —
		'topic_focus'                 => 'text',
		'primary_hex'                 => 'text',
		'secondary_hex'               => 'text',
		'font_display'                => 'text',
		'font_body'                   => 'text',
		// — 8. Formats publicitaires (3 slots) —
		'format1_title'               => 'text',
		'format1_desc'                => 'textarea',
		'format1_price'               => 'text',
		'format2_title'               => 'text',
		'format2_desc'                => 'textarea',
		'format2_price'               => 'text',
		'format3_title'               => 'text',
		'format3_desc'                => 'textarea',
		'format3_price'               => 'text',
		// — 9. Partenaires —
		'partners_logos'              => 'textarea',
		'partners_text'               => 'textarea',
		// — 10. Téléchargements —
		'logo_pack_url'               => 'url',
		'media_kit_pdf_url'           => 'url',
		'press_photo_url'             => 'url',
		// — 11. Contact —
		'press_email'                 => 'email',
		'contact_name'                => 'text',
		'contact_role'                => 'text',
		'contact_photo_url'           => 'url',
		'contact_planning'            => 'textarea',
	];
}

function arw_pulse_sanitize_media_kit( $input ) {
	$clean = [];
	if ( ! is_array( $input ) ) { $input = []; }
	foreach ( arw_pulse_media_kit_fields() as $field => $type ) {
		$val = $input[ $field ] ?? '';
		switch ( $type ) {
			case 'url':      $clean[ $field ] = esc_url_raw( $val ); break;
			case 'email':    $clean[ $field ] = is_email( $val ) ? sanitize_email( $val ) : ''; break;
			case 'textarea': $clean[ $field ] = sanitize_textarea_field( $val ); break;
			case 'select':
				if ( $field === 'top_articles_mode' ) {
					$clean[ $field ] = in_array( $val, [ 'manual', 'auto' ], true ) ? $val : 'manual';
				} else {
					$clean[ $field ] = sanitize_text_field( $val );
				}
				break;
			default: $clean[ $field ] = sanitize_text_field( $val );
		}
	}
	return $clean;
}

function arw_pulse_render_media_kit_settings() {
	$v = get_option( 'arw_media_kit', [] );
	$f = function ( $k ) use ( $v ) { return esc_attr( $v[ $k ] ?? '' ); };
	$ta = function ( $k ) use ( $v ) { return esc_textarea( $v[ $k ] ?? '' ); };
	$opt_name = 'arw_media_kit';
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Kit média', 'arw-pulse' ); ?></h1>
		<p>Configure les contenus de la page <code>/kit-media/</code>. Laisse vide ce que tu n'as pas — le rendu affichera <code>—</code> à la place. Aucune donnée inventée.</p>
		<form method="post" action="options.php">
			<?php settings_fields( 'arw_media_kit' ); ?>

			<h2>1. Hero</h2>
			<table class="form-table" role="presentation">
				<tr><th>Tagline</th><td><input type="text" class="regular-text" name="<?php echo esc_attr( $opt_name ); ?>[tagline]" value="<?php echo $f( 'tagline' ); ?>"></td></tr>
				<?php foreach ( [ 1, 2, 3 ] as $i ) : ?>
					<tr><th>KPI <?php echo $i; ?> — Valeur</th><td><input type="text" name="<?php echo esc_attr( $opt_name ); ?>[hero_kpi<?php echo $i; ?>_value]" value="<?php echo $f( "hero_kpi{$i}_value" ); ?>" placeholder="ex: 25 000"></td></tr>
					<tr><th>KPI <?php echo $i; ?> — Label</th><td><input type="text" name="<?php echo esc_attr( $opt_name ); ?>[hero_kpi<?php echo $i; ?>_label]" value="<?php echo $f( "hero_kpi{$i}_label" ); ?>" placeholder="ex: visites/mois"></td></tr>
				<?php endforeach; ?>
			</table>

			<h2>2. À propos</h2>
			<table class="form-table" role="presentation">
				<tr><th>Qui édite</th><td><textarea rows="3" class="large-text" name="<?php echo esc_attr( $opt_name ); ?>[about_who]"><?php echo $ta( 'about_who' ); ?></textarea></td></tr>
				<tr><th>Angle éditorial</th><td><textarea rows="3" class="large-text" name="<?php echo esc_attr( $opt_name ); ?>[about_angle]"><?php echo $ta( 'about_angle' ); ?></textarea></td></tr>
				<tr><th>Thématiques phares (legacy)</th><td><input type="text" class="regular-text" name="<?php echo esc_attr( $opt_name ); ?>[topic_focus]" value="<?php echo $f( 'topic_focus' ); ?>" placeholder="moto urbaine, équipement, assurance"></td></tr>
			</table>

			<h2>3. Profil audience</h2>
			<table class="form-table" role="presentation">
				<tr><th>Audience mensuelle</th><td><input type="text" class="regular-text" name="<?php echo esc_attr( $opt_name ); ?>[audience_monthly]" value="<?php echo $f( 'audience_monthly' ); ?>" placeholder="150 000 VU / mois"></td></tr>
				<tr><th>Tranche d'âge</th><td><input type="text" class="regular-text" name="<?php echo esc_attr( $opt_name ); ?>[audience_age]" value="<?php echo $f( 'audience_age' ); ?>" placeholder="25-44 ans"></td></tr>
				<tr><th>Genre</th><td><input type="text" class="regular-text" name="<?php echo esc_attr( $opt_name ); ?>[audience_gender]" value="<?php echo $f( 'audience_gender' ); ?>" placeholder="60% F / 40% H"></td></tr>
				<tr><th>Géographie</th><td><input type="text" class="regular-text" name="<?php echo esc_attr( $opt_name ); ?>[audience_geo]" value="<?php echo $f( 'audience_geo' ); ?>" placeholder="France 75% / BE 12% / CH 8%"></td></tr>
				<tr><th>Centres d'intérêt</th><td><textarea rows="2" class="large-text" name="<?php echo esc_attr( $opt_name ); ?>[audience_interests]"><?php echo $ta( 'audience_interests' ); ?></textarea></td></tr>
			</table>

			<h2>4. Engagement</h2>
			<table class="form-table" role="presentation">
				<tr><th>Temps de lecture moyen</th><td><input type="text" name="<?php echo esc_attr( $opt_name ); ?>[engagement_time]" value="<?php echo $f( 'engagement_time' ); ?>" placeholder="4min 12s"></td></tr>
				<tr><th>Scroll moyen</th><td><input type="text" name="<?php echo esc_attr( $opt_name ); ?>[engagement_scroll]" value="<?php echo $f( 'engagement_scroll' ); ?>" placeholder="78%"></td></tr>
				<tr><th>Taux de retour</th><td><input type="text" name="<?php echo esc_attr( $opt_name ); ?>[engagement_return]" value="<?php echo $f( 'engagement_return' ); ?>" placeholder="42%"></td></tr>
				<tr><th>Pages / session</th><td><input type="text" name="<?php echo esc_attr( $opt_name ); ?>[engagement_pages_session]" value="<?php echo $f( 'engagement_pages_session' ); ?>" placeholder="2,7"></td></tr>
			</table>

			<h2>5. Top articles</h2>
			<table class="form-table" role="presentation">
				<tr><th>Mode</th><td>
					<?php $mode = $v['top_articles_mode'] ?? 'manual'; ?>
					<select name="<?php echo esc_attr( $opt_name ); ?>[top_articles_mode]">
						<option value="manual" <?php selected( $mode, 'manual' ); ?>>Manuel (URLs ci-dessous)</option>
						<option value="auto" <?php selected( $mode, 'auto' ); ?>>Auto (5 derniers publiés)</option>
					</select>
				</td></tr>
				<tr><th>URLs (1 par ligne)</th><td><textarea rows="5" class="large-text" name="<?php echo esc_attr( $opt_name ); ?>[top_articles_urls]" placeholder="https://...&#10;https://..."><?php echo $ta( 'top_articles_urls' ); ?></textarea></td></tr>
			</table>

			<h2>6. Réseaux sociaux</h2>
			<table class="form-table" role="presentation">
				<tr><th>Communauté totale (legacy)</th><td><input type="text" class="regular-text" name="<?php echo esc_attr( $opt_name ); ?>[social_total]" value="<?php echo $f( 'social_total' ); ?>" placeholder="25 000 abonnés"></td></tr>
				<?php foreach ( [ 'instagram' => 'Instagram', 'tiktok' => 'TikTok', 'linkedin' => 'LinkedIn', 'youtube' => 'YouTube' ] as $key => $label ) : ?>
					<tr><th><?php echo esc_html( $label ); ?> — URL</th><td><input type="url" class="regular-text" name="<?php echo esc_attr( $opt_name ); ?>[social_<?php echo $key; ?>_url]" value="<?php echo $f( "social_{$key}_url" ); ?>"></td></tr>
					<tr><th><?php echo esc_html( $label ); ?> — Followers</th><td><input type="text" name="<?php echo esc_attr( $opt_name ); ?>[social_<?php echo $key; ?>_followers]" value="<?php echo $f( "social_{$key}_followers" ); ?>" placeholder="12 400"></td></tr>
				<?php endforeach; ?>
			</table>

			<h2>7. Identité visuelle</h2>
			<table class="form-table" role="presentation">
				<tr><th>Couleur primaire (hex)</th><td><input type="text" class="regular-text" name="<?php echo esc_attr( $opt_name ); ?>[primary_hex]" value="<?php echo $f( 'primary_hex' ); ?>" placeholder="#ffd600"></td></tr>
				<tr><th>Couleur secondaire (hex)</th><td><input type="text" class="regular-text" name="<?php echo esc_attr( $opt_name ); ?>[secondary_hex]" value="<?php echo $f( 'secondary_hex' ); ?>"></td></tr>
				<tr><th>Police display</th><td><input type="text" class="regular-text" name="<?php echo esc_attr( $opt_name ); ?>[font_display]" value="<?php echo $f( 'font_display' ); ?>" placeholder="Big Shoulders Display"></td></tr>
				<tr><th>Police body</th><td><input type="text" class="regular-text" name="<?php echo esc_attr( $opt_name ); ?>[font_body]" value="<?php echo $f( 'font_body' ); ?>" placeholder="Inter"></td></tr>
			</table>

			<h2>8. Formats publicitaires (3 slots)</h2>
			<table class="form-table" role="presentation">
				<?php foreach ( [ 1, 2, 3 ] as $i ) : ?>
					<tr><th>Format <?php echo $i; ?> — Titre</th><td><input type="text" class="regular-text" name="<?php echo esc_attr( $opt_name ); ?>[format<?php echo $i; ?>_title]" value="<?php echo $f( "format{$i}_title" ); ?>" placeholder="Article sponso"></td></tr>
					<tr><th>Format <?php echo $i; ?> — Description</th><td><textarea rows="2" class="large-text" name="<?php echo esc_attr( $opt_name ); ?>[format<?php echo $i; ?>_desc]"><?php echo $ta( "format{$i}_desc" ); ?></textarea></td></tr>
					<tr><th>Format <?php echo $i; ?> — Tarif</th><td><input type="text" name="<?php echo esc_attr( $opt_name ); ?>[format<?php echo $i; ?>_price]" value="<?php echo $f( "format{$i}_price" ); ?>" placeholder="à partir de 800€"></td></tr>
				<?php endforeach; ?>
			</table>

			<h2>9. Partenaires</h2>
			<table class="form-table" role="presentation">
				<tr><th>Logos URLs (1 par ligne)</th><td><textarea rows="5" class="large-text" name="<?php echo esc_attr( $opt_name ); ?>[partners_logos]" placeholder="https://.../logo-marque.png"><?php echo $ta( 'partners_logos' ); ?></textarea></td></tr>
				<tr><th>Texte d'intro</th><td><textarea rows="2" class="large-text" name="<?php echo esc_attr( $opt_name ); ?>[partners_text]" placeholder="Ils nous ont fait confiance"><?php echo $ta( 'partners_text' ); ?></textarea></td></tr>
			</table>

			<h2>10. Téléchargements</h2>
			<table class="form-table" role="presentation">
				<tr><th>Logo pack (ZIP)</th><td><input type="url" class="regular-text" name="<?php echo esc_attr( $opt_name ); ?>[logo_pack_url]" value="<?php echo $f( 'logo_pack_url' ); ?>"></td></tr>
				<tr><th>Kit PDF</th><td><input type="url" class="regular-text" name="<?php echo esc_attr( $opt_name ); ?>[media_kit_pdf_url]" value="<?php echo $f( 'media_kit_pdf_url' ); ?>"></td></tr>
				<tr><th>Photo presse</th><td><input type="url" class="regular-text" name="<?php echo esc_attr( $opt_name ); ?>[press_photo_url]" value="<?php echo $f( 'press_photo_url' ); ?>"></td></tr>
			</table>

			<h2>11. Contact</h2>
			<table class="form-table" role="presentation">
				<tr><th>Email presse</th><td><input type="email" class="regular-text" name="<?php echo esc_attr( $opt_name ); ?>[press_email]" value="<?php echo $f( 'press_email' ); ?>"></td></tr>
				<tr><th>Nom</th><td><input type="text" class="regular-text" name="<?php echo esc_attr( $opt_name ); ?>[contact_name]" value="<?php echo $f( 'contact_name' ); ?>"></td></tr>
				<tr><th>Fonction</th><td><input type="text" class="regular-text" name="<?php echo esc_attr( $opt_name ); ?>[contact_role]" value="<?php echo $f( 'contact_role' ); ?>"></td></tr>
				<tr><th>Photo (URL)</th><td><input type="url" class="regular-text" name="<?php echo esc_attr( $opt_name ); ?>[contact_photo_url]" value="<?php echo $f( 'contact_photo_url' ); ?>"></td></tr>
				<tr><th>Planning / dispo</th><td><textarea rows="2" class="large-text" name="<?php echo esc_attr( $opt_name ); ?>[contact_planning]" placeholder="Réponse sous 48h ouvrées"><?php echo $ta( 'contact_planning' ); ?></textarea></td></tr>
			</table>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Accessor.
 */
function arw_pulse_media_kit( $key = null, $default = '' ) {
	$all = get_option( 'arw_media_kit', [] );
	if ( null === $key ) { return $all; }
	return $all[ $key ] ?? $default;
}
