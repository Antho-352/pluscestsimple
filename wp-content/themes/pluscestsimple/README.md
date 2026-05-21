# Plus c'est simple — thème WordPress

Thème classique WordPress sur-mesure pour [pluscestsimple.com](https://pluscestsimple.com) — média francophone déco / travaux / immobilier.

**Stack :** WordPress 7.0+ · PHP 8.0+ · zéro dépendance JS · ~3 ko de JS inline · ~700 lignes CSS.

---

## Principes

- **Tout est éditable en Gutenberg natif.** Pas de page builder, pas de surcouche.
- **Menu géré nativement** via `wp_nav_menu()`. L'assignation menu → emplacement survit aux mises à jour du thème (theme_mods, mécanisme WP standard).
- **Fonctionnalité métier dans un plugin** (`arw-pack-maison` pour le Compatibilimètre). Le thème ne gère que la présentation.
- **Performance-first.** Pas de critical CSS bricolé : tokens via `theme.json`, JS différé, fonts self-hosted en woff2 préchargés.
- **SEO/sécurité de niveau Yoast + Wordfence**, mais intégrés au thème : pas de plugin tiers.

---

## Structure

```
pluscestsimple/
├── style.css                    Header du thème (Version, Requires)
├── functions.php                Bootstrap : charge les modules /inc/
├── theme.json                   Tokens couleurs/typo/espacements (vars CSS pour Gutenberg)
├── header.php                   <head>, header HTML, wp_nav_menu('primary')
├── footer.php                   Footer HTML, wp_nav_menu('footer')
├── index.php                    Fallback générique
├── front-page.php               Page d'accueil (Gutenberg, the_content)
├── home.php                     Listing blog si page "Articles" configurée
├── page.php                     Page WP standard
├── single.php                   Article complet
├── category.php                 Archive de catégorie (avec redirect 301 vers page Gutenberg si configurée — stratégie D1)
├── archive.php                  Archives génériques (auteur, date)
├── search.php                   Résultats de recherche
├── 404.php                      Page introuvable
├── searchform.php               Formulaire de recherche
├── template-parts/
│   └── content/
│       ├── card-article.php     Carte d'article réutilisable
│       └── none.php             État vide (search, archive)
├── page-templates/
│   ├── tpl-wide.php             Pleine largeur (sans titre/breadcrumbs)
│   └── tpl-sitemap.php          Plan du site auto-généré
├── patterns/                    Auto-chargés par WP (≥6.0)
│   ├── hero-front.php           Hero accueil
│   ├── section-compatibilimetre.php   Encart promo Compatibilimètre
│   ├── section-featured.php     Article à la une (Query Loop verrouillé)
│   ├── section-weekly.php       Sélection 5 articles (Query Loop verrouillé)
│   ├── section-newsletter.php   Capture email + lead magnet
│   ├── section-pourquoi.php     Manifeste éditorial
│   └── page-mentions.php        Pattern auto-rempli mentions légales
├── inc/                         Modules métier
│   ├── theme-supports.php       add_theme_support + image sizes
│   ├── menus.php                register_nav_menus + helpers + fallbacks
│   ├── enqueue.php              wp_enqueue_style/script
│   ├── cleanup.php              Bloat removal (emoji, embed, xmlrpc, jQuery front)
│   ├── image.php                Tailles + <picture> AVIF/WebP + alt fallback
│   ├── branding.php             Logo SVG fallback, favicon, apple-touch-icon, manifest
│   ├── seo.php                  Meta tags, OG, Twitter, canonical, robots, meta box
│   ├── schema.php               JSON-LD Article, BreadcrumbList, Organization, WebSite
│   ├── breadcrumbs.php          pcs_breadcrumbs() avec microdata Schema.org
│   ├── security.php             Headers HTTP, anti-enum, security.txt, rate-limits
│   ├── performance.php          LCP preload, fetchpriority, font preload, defer
│   ├── robots.php               robots.txt custom (priority 999)
│   ├── reading-time.php         pcs_reading_time() + shortcodes
│   ├── structured-data.php      Meta boxes FAQ + HowTo
│   ├── affiliate.php            Auto rel="sponsored nofollow" + disclosure
│   ├── form.php                 CPT arw_submission + REST /pcs/v1/submit
│   ├── cookie-consent.php       Bandeau natif Consent Mode v2
│   ├── legal-defaults.php       Page admin Mentions légales + placeholders
│   ├── identity.php             Page admin Identité & Social + bindings filtres
│   ├── patterns.php             Catégories de patterns
│   ├── view-transitions.php     Meta tag opt-in
│   └── category-base.php        Retire /category/ + redirect 301 D1 vers page
└── assets/
    ├── css/
    │   ├── theme.css            CSS principal (~700 lignes)
    │   └── editor.css           CSS éditeur Gutenberg
    ├── js/
    │   └── theme.js             ~3 ko : menu hamburger + reveal + split-text + consent
    ├── fonts/
    │   ├── inter-var.woff2      Inter (body)
    │   ├── fraunces-var.woff2   Fraunces (display, regular)
    │   └── fraunces-var-italic.woff2  Fraunces italic
    └── images/
        ├── favicon.svg
        └── favicon-512.png
```

---

## Identité visuelle (skin "Editorial")

| Token | Valeur |
|---|---|
| Fond | `#f7f1e6` (ivoire chaud) |
| Texte | `#0c0e0d` (noir art déco) |
| Accent principal | `#1f3a2e` (vert anglais) |
| Accent secondaire | `#a78a4d` (laiton/bronze) |
| Surface (encarts) | `#efe7d3` |
| Border | `#d8cbb0` |
| Muted (texte secondaire) | `#5b5648` |

| Typo | Famille |
|---|---|
| Body | Inter (var, 300-700) |
| Display (titres) | Fraunces (var, 300-900, italic var inclus) |

Tous ces tokens vivent dans `theme.json` et sont exposés en variables CSS `--wp--preset--*` pour `theme.css` et Gutenberg.

---

## Gestion du menu

L'utilisateur crée et édite ses menus depuis **Apparence → Menus**. Deux emplacements sont déclarés :

- `primary` — header (au-dessus de 900px : inline + animation underline ; en dessous : overlay hamburger)
- `footer` — pied de page (liste verticale)

Si aucun menu n'est assigné, deux fallbacks affichent automatiquement :
- En header : les 6 premières catégories + lien Contact
- En footer : Mentions légales, Contact, Plan du site

L'assignation est persistée dans `theme_mods_pluscestsimple` (mécanisme WP standard). **Elle survit à toutes les mises à jour du thème** sans système d'override.

---

## Stratégie des pages catégories (D1)

Les 4 catégories principales (Décoration, Travaux, Immobilier, Divers) sont éditables en Gutenberg via une **page WP dédiée** :

- Slug page : `/decoration/`, `/travaux/`, etc. (URL visible côté front)
- Slug interne catégorie : `decoration-cat`, `travaux-cat`, etc.
- `inc/category-base.php` redirige automatiquement l'archive `/decoration-cat/` vers `/decoration/` (301)

Pour configurer une nouvelle catégorie en page éditable :

1. Aller dans **Articles → Catégories** et renommer le slug en `<slug>-cat`
2. Créer une **page** avec le slug `<slug>` (sans suffixe)
3. Éditer la page en Gutenberg avec les patterns du thème + un bloc Query Loop filtré sur la catégorie

Le suffixe est configurable via `apply_filters( 'pcs_category_slug_suffix', '-cat' )`.

---

## Filtres d'extension

### SEO & Schema
- `pcs_meta_description`, `pcs_default_og_image`, `pcs_twitter_site`, `pcs_twitter_creator`
- `pcs_organization_same_as`, `pcs_article_schema`, `pcs_breadcrumbs`
- `pcs_robots_meta_index`, `pcs_author_archives_noindex`

### Performance
- `pcs_preload_fonts` (défaut : Inter + Fraunces)
- `pcs_defer_scripts`, `pcs_dns_prefetch`

### Formulaires
- `pcs_form_types`, `pcs_form_to`, `pcs_form_redirect`, `pcs_global_rate_cap`
- Endpoint REST : `/wp-json/pcs/v1/submit`

### Affiliation
- `pcs_auto_disclosure`, `pcs_disclosure_text`

### Sécurité
- `pcs_security_headers`, `pcs_security_contact`, `pcs_csp`

### UX
- `pcs_view_transitions` (true par défaut)
- `pcs_wpm` (220 mots/min par défaut)

### Catégories
- `pcs_category_slug_suffix` (`-cat` par défaut)
- `pcs_category_landing_page_slug` (override fin)

---

## Patterns disponibles

Trois catégories dans l'inserter Gutenberg : **Hero**, **Sections**, **Pages**.

| Slug | Catégorie | Description |
|---|---|---|
| `pluscestsimple/hero-front` | Hero | Hero éditorial accueil (titre oversize + intro + CTA) |
| `pluscestsimple/section-compatibilimetre` | Sections | Encart promo Compatibilimètre |
| `pluscestsimple/section-featured` | Sections | Article à la une (Query Loop, 1 résultat, verrouillé) |
| `pluscestsimple/section-weekly` | Sections | Grille 5 derniers articles (Query Loop verrouillé) |
| `pluscestsimple/section-newsletter` | Sections | Capture email + lead magnet (form vers /pcs/v1/submit) |
| `pluscestsimple/section-pourquoi` | Sections | Manifeste éditorial |
| `pluscestsimple/page-mentions` | Pages | Template auto-rempli mentions légales (placeholders {SIRET}, etc.) |

Tous les patterns utilisent `templateLock: contentOnly` : l'utilisateur peut modifier les textes/images sans pouvoir casser la structure. Si besoin d'éditer la structure : clic droit → « Détacher ».

---

## Page templates personnalisés

Visibles dans **Attributs de page → Modèle** lors de l'édition d'une page :

- **Pleine largeur (sans titre)** — masque le H1 automatique et les breadcrumbs ; le H1 doit être dans le contenu
- **Plan du site** — auto-génère pages + catégories + 60 derniers articles, en plus du contenu éditorial

---

## Page d'accueil

Configurée dans **Réglages → Lecture** → « Une page statique » → choisir une page créée pour l'accueil. La page contient les patterns du thème (hero, sections, etc.). `front-page.php` rend simplement `the_content()`.

Si l'option « Vos derniers articles » est choisie à la place, `index.php` affiche une grille des derniers articles.

---

## Compatibilité

| Élément | Version min | Tested up to |
|---|---|---|
| WordPress | 7.0 | 7.0 |
| PHP | 8.0 | 8.3 |
| Gutenberg | core (intégré) | — |

**Plugin requis** : aucun. **Plugin compatible** : `arw-pack-maison` (Compatibilimètre + lead magnets).

---

## Build / déploiement

```bash
./package.sh
```

Génère `../pluscestsimple.zip` à uploader via **Apparence → Thèmes → Ajouter → Téléverser**.

---

## License

GPL-2.0-or-later · Anthony Russo
