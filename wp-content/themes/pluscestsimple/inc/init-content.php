<?php
/**
 * Initialisation du contenu structurel : catégories + pages WP.
 *
 * Crée la nouvelle arborescence éditoriale (6 piliers + sous-catégories
 * + pages correspondantes) en respectant la stratégie D1 :
 *   - Slug catégorie suffixé `-cat` (interne, jamais visible)
 *   - Slug page sans suffixe (URL visible côté front)
 *   - Le module `inc/category-base.php` redirige 301 l'archive vers la page
 *
 * Idempotent : peut être ré-exécuté sans casser l'existant.
 * S'exécute une fois à l'init (option `pcs_content_seeded`).
 *
 * @package pluscestsimple
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Définition des piliers, sous-catégories et pages à créer.
 *
 * Format : pilier_slug => [ label, slug_cat, sous_cats[], page_intro ]
 */
function pcs_content_structure(): array {
	return [
		'decoration' => [
			'label'    => 'Décoration',
			'sub_cats' => [
				'par-piece'       => 'Par pièce',
				'styles'          => 'Styles',
				'petits-budgets'  => 'Petits budgets',
			],
			'page_intro' => 'Conseils déco pièce par pièce, styles signatures et idées petit budget. Du concret, du vécu, des choix argumentés.',
		],
		'travaux' => [
			'label'    => 'Travaux',
			'sub_cats' => [
				'par-piece'              => 'Par pièce',
				'gros-oeuvre'            => 'Gros œuvre',
				'renovation-energetique' => 'Rénovation énergétique',
			],
			'page_intro' => 'Tout pour rénover sans se tromper : guides par pièce, repères techniques (DTU, normes), budgets réels et aides publiques.',
		],
		'jardin' => [
			'label'    => 'Jardin',
			'sub_cats' => [
				'amenagement-exterieur' => 'Aménagement extérieur',
				'entretien'             => 'Entretien',
			],
			'page_intro' => 'De l\'aménagement extérieur à l\'entretien saisonnier : repères concrets pour faire pousser et durer.',
		],
		'architecture' => [
			'label'    => 'Architecture',
			'sub_cats' => [
				'styles-epoques' => 'Styles & époques',
				'extensions'     => 'Extensions',
			],
			'page_intro' => 'Comprendre l\'architecture pour mieux rénover : styles, époques et extensions modernes.',
		],
		'immobilier' => [
			'label'    => 'Immobilier',
			'sub_cats' => [
				'acheter'         => 'Acheter',
				'louer-investir'  => 'Louer & investir',
				'vendre'          => 'Vendre',
			],
			'page_intro' => 'Acheter, louer, vendre, investir : repères concrets sur le marché immobilier français, sans baratin.',
		],
		'lifestyle' => [
			'label'    => 'Lifestyle',
			'sub_cats' => [
				'bien-etre-accessoires' => 'Bien-être & accessoires',
				'rangement-nettoyage'   => 'Rangement & nettoyage',
			],
			'page_intro' => 'Bien vivre chez soi : organisation, bien-être et accessoires utiles au quotidien.',
		],
	];
}

/**
 * Pages utilitaires à créer (en plus des piliers).
 *
 * Format : slug => [ title, template, content_placeholder ]
 */
function pcs_content_utility_pages(): array {
	return [
		'accueil' => [
			'title'    => 'Accueil',
			'template' => 'page-templates/tpl-wide.php',
			'content'  => '__ACCUEIL__',
		],
		'le-carnet' => [
			'title'    => 'Le carnet',
			'template' => '',
			'content'  => '<!-- wp:paragraph --><p>Le journal éditorial de Plus c\'est simple : nos derniers articles, par catégorie et par date.</p><!-- /wp:paragraph -->',
		],
		'outils' => [
			'title'    => 'Outils',
			'template' => 'page-templates/tpl-wide.php',
			'content'  => '__OUTILS__',
		],
		'annuaire' => [
			'title'    => 'Annuaire',
			'template' => 'page-templates/tpl-wide.php',
			'content'  => "<!-- wp:heading {\"level\":1} --><h1 class=\"wp-block-heading\">Annuaire des magasins déco / maison</h1><!-- /wp:heading -->\n\n<!-- wp:paragraph --><p>L'annuaire national, filtrable par type et région. Données officielles (Sirene), géocodées, mises à jour.</p><!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->[pcs_directory limit=\"12\"]<!-- /wp:shortcode -->",
		],
		'travailler-avec-nous' => [
			'title'    => 'Travailler avec nous',
			'template' => 'page-templates/tpl-wide.php',
			'content'  => '__PATTERN__:pluscestsimple/page-travailler',
		],
		'charte-partenaires' => [
			'title'    => 'Charte partenaires',
			'template' => 'page-templates/tpl-wide.php',
			'content'  => '__PATTERN__:pluscestsimple/charte-partenaires',
		],
		// Plan du site : page auto-générée par le template tpl-sitemap.php (qui liste
		// pages publiées, catégories non vides, articles récents). Contenu post_content
		// laissé vide → le template prend la main et génère tout dynamiquement.
		'plan-du-site' => [
			'title'    => 'Plan du site',
			'template' => 'page-templates/tpl-sitemap.php',
			'content'  => '',
		],
	];
}

/**
 * Récupère le contenu HTML d'un pattern enregistré (avec ses blocs inlinés).
 * Permet d'insérer le pattern "déplié" dans post_content plutôt qu'une
 * simple référence `<!-- wp:pattern -->` qui apparaît comme un bloc fermé
 * dans l'éditeur Gutenberg.
 *
 * Fallback : si le pattern n'est pas trouvé (cache pas peuplé), renvoie la
 * référence WP qui sera résolue dynamiquement au rendu front.
 */
function pcs_get_pattern_content( string $slug ): string {
	if ( class_exists( 'WP_Block_Patterns_Registry' ) ) {
		$reg     = WP_Block_Patterns_Registry::get_instance();
		$pattern = $reg->get_registered( $slug );
		if ( $pattern && ! empty( $pattern['content'] ) ) {
			return (string) $pattern['content'];
		}
	}
	return '<!-- wp:pattern {"slug":"' . esc_attr( $slug ) . '"} /-->';
}

/**
 * Contenu d'une sous-page pilier : H1 + intro éditable + boucle d'articles
 * (bloc Query namespace pcs/cat-loop, filtré côté serveur vers la sous-catégorie).
 *
 * @param string $label Libellé de la sous-catégorie (ex. "Styles").
 * @return string Markup blocs Gutenberg.
 */
function pcs_subpage_content( string $label ): string {
	$h1    = esc_html( $label );
	$intro = esc_html( sprintf( 'Nos articles « %s » : sélection éditoriale. (Personnalise cette intro depuis l\'éditeur.)', $label ) );

	$query  = '<!-- wp:query {"query":{"perPage":12,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","inherit":false},"namespace":"pcs/cat-loop","className":"pcs-section pcs-section--cat-loop"} -->';
	$query .= '<div class="wp-block-query pcs-section pcs-section--cat-loop">';
	$query .= '<!-- wp:post-template {"className":"pcs-card-grid"} -->';
	$query .= '<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9","className":"pcs-card__media"} /-->';
	$query .= '<!-- wp:post-terms {"term":"category","className":"pcs-card__eyebrow"} /-->';
	$query .= '<!-- wp:post-title {"isLink":true,"level":3,"className":"pcs-card__title"} /-->';
	$query .= '<!-- wp:post-excerpt /-->';
	$query .= '<!-- wp:post-date {"format":"j F Y","className":"pcs-card__meta"} /-->';
	$query .= '<!-- /wp:post-template -->';
	$query .= '<!-- wp:query-pagination --><!-- wp:query-pagination-previous /--><!-- wp:query-pagination-numbers /--><!-- wp:query-pagination-next /--><!-- /wp:query-pagination -->';
	$query .= '<!-- wp:query-no-results --><!-- wp:paragraph --><p>Aucun article pour le moment.</p><!-- /wp:paragraph --><!-- /wp:query-no-results -->';
	$query .= '</div><!-- /wp:query -->';

	return "<!-- wp:heading {\"level\":1,\"className\":\"pcs-archive__title\"} -->\n<h1 class=\"wp-block-heading pcs-archive__title\">{$h1}</h1>\n<!-- /wp:heading -->\n\n"
		. "<!-- wp:paragraph -->\n<p>{$intro}</p>\n<!-- /wp:paragraph -->\n\n"
		. $query;
}

/**
 * Lance la création (idempotente) des catégories + pages.
 */
function pcs_init_content(): void {
	$structure = pcs_content_structure();

	foreach ( $structure as $slug_root => $data ) {
		// 1. Catégorie pilier (slug suffixé -cat)
		$cat_slug = $slug_root . '-cat';
		$term     = get_term_by( 'slug', $cat_slug, 'category' );

		if ( ! $term ) {
			// Vérifier si une cat existe déjà au slug "nu" (héritée d'une précédente install).
			$legacy = get_term_by( 'slug', $slug_root, 'category' );
			if ( $legacy && ! is_wp_error( $legacy ) ) {
				// Renomme son slug en -cat pour libérer l'URL au profit de la page.
				wp_update_term( $legacy->term_id, 'category', [ 'slug' => $cat_slug ] );
				$cat_id = $legacy->term_id;
			} else {
				$res = wp_insert_term( $data['label'], 'category', [
					'slug'        => $cat_slug,
					'description' => $data['page_intro'],
				] );
				if ( is_wp_error( $res ) ) {
					continue;
				}
				$cat_id = $res['term_id'];
			}
		} else {
			$cat_id = $term->term_id;
		}

		// 2. Sous-catégories (slug "<pilier>-<sub>-cat")
		foreach ( $data['sub_cats'] as $sub_slug => $sub_label ) {
			$full_slug = $slug_root . '-' . $sub_slug . '-cat';
			$existing  = get_term_by( 'slug', $full_slug, 'category' );
			if ( $existing ) {
				continue;
			}
			wp_insert_term( $sub_label, 'category', [
				'slug'   => $full_slug,
				'parent' => $cat_id,
			] );
		}

		// 3. Page pilier (slug nu) avec pattern spécifique au pilier (sinon fallback générique)
		$existing_page = get_page_by_path( $slug_root );
		if ( ! $existing_page ) {
			// Tente d'abord le pattern spécifique au pilier (contient H1, intro, sections, FAQ, partenaires propres).
			$pattern_slug = 'pluscestsimple/category-' . $slug_root;
			$page_content = pcs_get_pattern_content( $pattern_slug );

			// Détection fallback : si le pattern spécifique n'a pas été trouvé, pcs_get_pattern_content
			// renvoie une référence `<!-- wp:pattern ... /-->` (signature : commence par `<!-- wp:pattern`).
			// Dans ce cas on tombe sur le pattern générique category-rich avec personnalisation H1/intro.
			$is_fallback_ref = strpos( $page_content, '<!-- wp:pattern' ) === 0;
			if ( $is_fallback_ref ) {
				$page_content = pcs_get_pattern_content( 'pluscestsimple/category-rich' );
				// Personnalise le H1 et l'intro avec les valeurs de la catégorie (uniquement sur le générique).
				$page_content = preg_replace(
					'/Nom de la catégorie — à remplacer/',
					esc_html( $data['label'] ),
					$page_content,
					1
				);
				$page_content = preg_replace(
					'/Intro éditoriale \(150-250 mots\)[^<]*\./',
					esc_html( $data['page_intro'] ),
					$page_content,
					1
				);
			}

			$page_id = wp_insert_post( [
				'post_title'   => $data['label'],
				'post_name'    => $slug_root,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_excerpt' => $data['page_intro'],
				'post_content' => $page_content,
			] );
			if ( $page_id && ! is_wp_error( $page_id ) ) {
				update_post_meta( $page_id, '_wp_page_template', 'page-templates/tpl-wide.php' );
				update_post_meta( $page_id, '_pcs_seeded', '1' );
			}
		}

		// 3b. Sous-pages éditoriales (enfants du pilier) : /<pilier>/<sous-cat>/
		//     Intro éditable + liste des articles de la sous-catégorie.
		$pillar_page = get_page_by_path( $slug_root );
		if ( $pillar_page instanceof WP_Post ) {
			foreach ( $data['sub_cats'] as $sub_slug => $sub_label ) {
				$sub_existing = get_page_by_path( $slug_root . '/' . $sub_slug );
				if ( $sub_existing instanceof WP_Post ) {
					// Migration ciblée du H1 : ajoute la classe pcs-archive__title (titre en grand)
					// sur les sous-pages créées avant ce correctif. Ne touche qu'au H1, pas à l'intro.
					$c = (string) $sub_existing->post_content;
					if ( strpos( $c, 'pcs-archive__title' ) === false
						&& strpos( $c, '<h1 class="wp-block-heading">' ) !== false ) {
						$c2 = str_replace(
							[ '<!-- wp:heading {"level":1} -->', '<h1 class="wp-block-heading">' ],
							[ '<!-- wp:heading {"level":1,"className":"pcs-archive__title"} -->', '<h1 class="wp-block-heading pcs-archive__title">' ],
							$c
						);
						if ( $c2 !== $c ) {
							wp_update_post( [ 'ID' => $sub_existing->ID, 'post_content' => $c2 ] );
						}
					}
					continue; // déjà créée
				}
				$sub_id = wp_insert_post( [
					'post_title'   => $sub_label,
					'post_name'    => $sub_slug,
					'post_parent'  => $pillar_page->ID,
					'post_status'  => 'publish',
					'post_type'    => 'page',
					'post_excerpt' => $sub_label,
					'post_content' => pcs_subpage_content( $sub_label ),
				] );
				if ( $sub_id && ! is_wp_error( $sub_id ) ) {
					update_post_meta( $sub_id, '_wp_page_template', 'page-templates/tpl-wide.php' );
					update_post_meta( $sub_id, '_pcs_seeded', '1' );
				}
			}
		}
	}

	// 4. Pages utilitaires
	foreach ( pcs_content_utility_pages() as $page_slug => $page_data ) {
		$existing = get_page_by_path( $page_slug );
		if ( $existing ) {
			// Page déjà créée. Mais on s'assure que le template assigné est bien
			// celui défini dans pcs_content_utility_pages() — utile pour les pages
			// créées AVANT que le mapping template soit ajouté (ex: plan-du-site
			// créé en v2.4 sans template, ajouté en v2.8.2). On ne touche jamais
			// au post_content (édition user respectée).
			if ( ! empty( $page_data['template'] ) ) {
				$current_tpl = get_post_meta( $existing->ID, '_wp_page_template', true );
				if ( $current_tpl !== $page_data['template'] ) {
					update_post_meta( $existing->ID, '_wp_page_template', $page_data['template'] );
				}
			}
			continue;
		}
		// Résolution des contenus spéciaux (sentinels) avec patterns inlinés.
		$content = $page_data['content'];
		if ( strpos( $content, '__PATTERN__:' ) === 0 ) {
			$pattern_slug = trim( substr( $content, strlen( '__PATTERN__:' ) ) );
			$content      = pcs_get_pattern_content( $pattern_slug );
		} elseif ( $content === '__ACCUEIL__' ) {
			$slugs   = [ 'hero-front', 'section-pourquoi', 'section-pillars', 'section-compatibilimetre', 'section-featured', 'section-weekly', 'section-newsletter', 'directory-teaser' ];
			$content = '';
			foreach ( $slugs as $s ) {
				$content .= pcs_get_pattern_content( 'pluscestsimple/' . $s ) . "\n\n";
			}
		} elseif ( $content === '__OUTILS__' ) {
			$content  = "<!-- wp:heading {\"level\":1} --><h1 class=\"wp-block-heading\">Nos outils</h1><!-- /wp:heading -->\n\n";
			$content .= "<!-- wp:paragraph --><p>Les outils maison de Plus c'est simple, pour avancer dans vos projets sans pirouette.</p><!-- /wp:paragraph -->\n\n";
			$content .= pcs_get_pattern_content( 'pluscestsimple/section-compatibilimetre' );
		}

		$page_id = wp_insert_post( [
			'post_title'   => $page_data['title'],
			'post_name'    => $page_slug,
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => $content,
		] );
		if ( $page_id && ! is_wp_error( $page_id ) ) {
			update_post_meta( $page_id, '_pcs_seeded', '1' );
			if ( $page_data['template'] ) {
				update_post_meta( $page_id, '_wp_page_template', $page_data['template'] );
			}
		}
	}
}

/**
 * Supprime toutes les pages seedées pour permettre une recréation propre.
 * Match par DEUX critères (OR) :
 *   1. meta `_pcs_seeded='1'` (pages créées en v2.1.1+)
 *   2. slug correspondant à un slug seedé connu (pages créées en v2.1.0
 *      ou v2.1.1 avant l'ajout du meta — sinon le reset ne voit rien)
 *
 * ⚠️ Si l'utilisateur a édité ces pages (intro perso, sections ajoutées),
 * les modifs sont perdues. C'est le tradeoff pour récupérer les nouveaux
 * patterns du thème dans les pages.
 */
function pcs_reset_seeded_pages(): int {
	// Liste des slugs gérés par le seed.
	$seeded_slugs = array_keys( pcs_content_structure() );
	$seeded_slugs = array_merge( $seeded_slugs, array_keys( pcs_content_utility_pages() ) );

	$ids = [];

	// Critère 1 : pages avec meta _pcs_seeded=1
	$by_meta = get_posts( [
		'post_type'      => 'page',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'meta_key'       => '_pcs_seeded',
		'meta_value'     => '1',
		'fields'         => 'ids',
		'no_found_rows'  => true,
	] );
	$ids = array_merge( $ids, $by_meta );

	// Critère 2 : pages dont le slug correspond à un slug seedé connu
	foreach ( $seeded_slugs as $slug ) {
		$page = get_page_by_path( $slug, OBJECT, 'page' );
		if ( $page instanceof WP_Post ) {
			$ids[] = $page->ID;
		}
	}

	$ids   = array_unique( array_map( 'intval', $ids ) );
	$count = 0;
	foreach ( $ids as $id ) {
		if ( $id > 0 ) {
			wp_delete_post( $id, true );
			++$count;
		}
	}
	return $count;
}

/**
 * Auto-déclenche le seed une seule fois.
 * Pour re-déclencher : supprimer l'option `pcs_content_seeded` en DB.
 */
add_action( 'init', function () {
	if ( get_option( 'pcs_content_seeded' ) === PCS_VERSION ) {
		return;
	}
	pcs_init_content();
	update_option( 'pcs_content_seeded', PCS_VERSION );
}, 99 );

/**
 * Page admin "Re-seed content" sous Outils → Plus c'est simple.
 * Utile en cas de rollback ou si le seed initial a manqué quelque chose.
 */
add_action( 'admin_menu', function () {
	add_submenu_page(
		'tools.php',
		__( 'Plus c\'est simple — Init content', 'pluscestsimple' ),
		__( 'PCS Init content', 'pluscestsimple' ),
		'manage_options',
		'pcs-init-content',
		'pcs_init_content_page'
	);
} );

function pcs_init_content_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( isset( $_POST['pcs_reseed'] ) && check_admin_referer( 'pcs_reseed_content' ) ) {
		delete_option( 'pcs_content_seeded' );
		pcs_init_content();
		update_option( 'pcs_content_seeded', PCS_VERSION );
		echo '<div class="notice notice-success"><p>' . esc_html__( 'Contenu re-seedé avec succès. Catégories et pages vérifiées.', 'pluscestsimple' ) . '</p></div>';
	}
	if ( isset( $_POST['pcs_reset_pages'] ) && check_admin_referer( 'pcs_reseed_content' ) ) {
		$deleted = pcs_reset_seeded_pages();
		delete_option( 'pcs_content_seeded' );
		pcs_init_content();
		update_option( 'pcs_content_seeded', PCS_VERSION );
		printf(
			'<div class="notice notice-success"><p>%d page(s) auto-seedée(s) supprimée(s) et recréée(s) avec le contenu standard du thème.</p></div>',
			$deleted
		);
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Plus c\'est simple — Initialisation du contenu', 'pluscestsimple' ); ?></h1>
		<p><?php esc_html_e( 'Crée (ou met à jour) la structure éditoriale : 6 catégories pilier + sous-catégories + pages WP correspondantes. Idempotent — ne touche pas aux contenus existants.', 'pluscestsimple' ); ?></p>
		<form method="post" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:center">
			<?php wp_nonce_field( 'pcs_reseed_content' ); ?>
			<button type="submit" name="pcs_reseed" class="button button-primary">
				<?php esc_html_e( 'Re-seed (skip existant)', 'pluscestsimple' ); ?>
			</button>
			<button type="submit" name="pcs_reset_pages" class="button button-secondary" onclick="return confirm('Supprime les pages auto-seedées (meta _pcs_seeded=1) et les recrée avec le contenu standard du thème (patterns à jour). Les pages éditées manuellement et celles que TU as ajoutées sont préservées. Continuer ?');" style="color:#d63638">
				<?php esc_html_e( 'Reset des pages seedées', 'pluscestsimple' ); ?>
			</button>
		</form>
		<p class="description"><strong>Re-seed</strong> : ajoute les éléments manquants seulement (catégories, sous-cat, pages absentes). <strong>Reset</strong> : supprime les pages créées par le seed et les recrée — utilisé après une mise à jour du thème pour récupérer les nouveaux patterns dans les pages pilier.</p>

		<h2><?php esc_html_e( 'Structure cible', 'pluscestsimple' ); ?></h2>
		<table class="widefat striped">
			<thead><tr><th>Pilier</th><th>Slug catégorie</th><th>Slug page</th><th>Sous-catégories</th></tr></thead>
			<tbody>
			<?php foreach ( pcs_content_structure() as $slug => $data ) : ?>
				<tr>
					<td><strong><?php echo esc_html( $data['label'] ); ?></strong></td>
					<td><code><?php echo esc_html( $slug . '-cat' ); ?></code></td>
					<td><code>/<?php echo esc_html( $slug ); ?>/</code></td>
					<td>
						<?php foreach ( $data['sub_cats'] as $sub_slug => $sub_label ) : ?>
							<?php echo esc_html( $sub_label ); ?> <code><?php echo esc_html( $slug . '-' . $sub_slug . '-cat' ); ?></code><br>
						<?php endforeach; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}
