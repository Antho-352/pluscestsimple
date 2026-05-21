# ARW Pulse

> Thème WordPress FSE performance-first pour sites éditoriaux, affiliation B2C et lead-gen B2B.
> Lighthouse 100/100/100/100 cible. Zéro bloat. SEO/sécurité de niveau Yoast + Wordfence, sans les plugins.

---

## Business models supportés

| Model | Mécanique | Archétype type |
|---|---|---|
| **Affiliation** | Clic sortant sponsored, commission | Magazine éditorial, outdoor, voyage |
| **Lead magnet** | Email capture via ressource téléchargeable | Cuisine, business |
| **Devis** | Formulaire multi-champs → lead qualifié | Travaux, industrie, agence |
| **Mixte** | Plusieurs en parallèle | La plupart des sites réels |

Chaque model mobilise les mêmes infrastructures SEO/sécu/perf, mais ses widgets signature diffèrent (product cards vs pricing vs quote form). Le thème couvre l'éditorial + les formulaires + l'affiliation. Les CPT niches (Recipe, Service, Project, etc.) viennent via un **pack mu-plugin** dédié quand tu lances un site du type.

## Principes

| Principe | Comment c'est tenu |
|---|---|
| **Un seul thème, N sites** | Code du thème identique partout. Différence par site = une skin (tokens CSS) + un mu-plugin (contenu spécifique) |
| **Pas de Yoast / Rank Math** | Module SEO intégré : titre, meta, OG, Twitter, canonical, JSON-LD |
| **Pas de Wordfence** | Hardening intégré : headers HTTP, rate-limit, anti-enum, CSV-safe, HSTS |
| **Pas de WP Rocket** | Performance native : LCP preload, fetchpriority, font preload, critical CSS inline, scripts defer |
| **Pas de Contact Form 7** | Formulaire natif stocké en CPT `arw_submission`, REST endpoint `/arw/v1/submit`, nonce + honeypot + rate-limit |
| **Zéro dépendance JS** | ~3 kb de JS inline (motion + form + consent). Aucun runtime externe |

---

## Fonctionnalités

### SEO
- Titre personnalisé + meta description par post (meta box admin avec preview Google)
- OG image override, toggle `noindex`, focus keyword
- Canonical pagination-aware
- `rel=prev/next` sur archives
- `Last-Modified` HTTP + support 304
- Apple touch icon + web manifest (`/manifest.webmanifest`)
- Sitemap XML (core) + HTML (shortcode `[arw_sitemap]`)
- `robots.txt` propre (priority 999 = toujours gagnant)

### Données structurées (JSON-LD)
- `Organization` (avec logo — éligibilité Google News)
- `WebSite` + `SearchAction`
- `BreadcrumbList`
- `Article` sur posts (wordCount, dates, author, publisher)
- `CollectionPage` + `ItemList` sur archives/catégories
- `Product` + `ItemList` sur home (CPT `arw_product`)
- `FAQPage` — meta box repeater ou auto-detect blocs `<details>`
- `HowTo` — meta box repeater (titre + étapes)

### Performance
- LCP preload (singular + archive)
- `fetchpriority="high"` sur première image card
- Font preload (Inter + Space Grotesk self-hosted)
- Critical CSS inline (si `assets/css/critical.css` présent)
- Alt text fallback 3 niveaux
- Image sizes optimisées (`arw-hero`, `arw-card`, `arw-square`, `arw-vertical`)
- `sizes` attribute correct sur archives (400px au lieu de 100vw)

### Sécurité
- Headers HTTP : HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy
- Rate-limit : 3/IP/10min + 60 global/h
- Anti CSV injection (export soumissions)
- Anti email header injection (Reply-To strip `\r\n`)
- Blocage `/?author=N` (user enumeration)
- Messages login génériques (no user-exists leak)
- `/.well-known/security.txt` auto-servi
- Capability check + nonce sur chaque save_post
- XML-RPC désactivé, REST users masqué aux anonymes
- Reset DB automatique des templates à chaque version → fichiers du thème toujours autoritaires

### CPT
- `arw_submission` — soumissions formulaire + export CSV protégé
- `arw_product` — produits affiliés (brand, score, price, SKU, availability, currency, link, badge)
  - Visible uniquement via shortcode `[arw_essential]` / `[arw_products]`

### Admin
- Meta box SEO (titre, description, OG image, noindex, focus keyword)
- Meta box Données structurées (FAQ, HowTo repeaters)
- Réglages → Kit média (stats, palette, fonts, logo pack, PDF)
- Réglages → Mentions légales (placeholders auto-injectés dans patterns)

---

## Architecture

### Carte des modules

```
arw-pulse/
├── functions.php              # Bootstrap + reset DB + enqueue
├── theme.json                 # Tokens (couleurs, fonts, sizes, spacing)
├── style.css                  # Header de thème (version bumpable)
├── styles/                    # Style variations (skins par site)
│   └── urban.json             # Skin citymoto : dark + yellow
├── inc/
│   ├── security.php           # Headers HTTP, anti-enum, security.txt, rate-limit global
│   ├── cleanup.php            # Bloat removal (emoji, embed, xmlrpc, jQuery front)
│   ├── performance.php        # LCP, preloads, fetchpriority, sizes
│   ├── seo.php                # Meta tags, canonical, OG, Twitter, meta box
│   ├── structured-data.php    # FAQ + HowTo repeaters
│   ├── schema.php             # JSON-LD emitter (Article, Product, FAQ, HowTo, ItemList…)
│   ├── image.php              # Tailles + <picture> AVIF/WebP + alt fallback
│   ├── affiliate.php          # Auto rel="sponsored nofollow noopener" + disclosure
│   ├── form.php               # CPT arw_submission + REST /arw/v1/submit + CSV export
│   ├── media-kit.php          # Réglages kit média
│   ├── legal-defaults.php     # Mentions légales + placeholders
│   ├── sitemap.php            # HTML sitemap + shortcode [arw_year]
│   ├── cookie-consent.php     # Bandeau natif + Consent Mode v2
│   ├── motion.php             # JS motion reveal/split/stagger (~2kb)
│   ├── robots.php             # robots.txt (priority 999)
│   ├── branding.php           # Logo SVG fallback, favicon, apple-touch-icon, manifest
│   ├── products.php           # CPT arw_product + shortcode [arw_essential]
│   ├── title.php              # Titre <head> per-type
│   ├── reading-time.php       # Temps de lecture (mb-safe, cached)
│   ├── schema.php             # JSON-LD
│   └── patterns.php           # Categories des patterns
├── templates/                 # Templates FSE
├── parts/                     # Template parts (header, footer, post-meta, related)
├── patterns/                  # Block patterns PHP
└── assets/
    ├── css/
    │   ├── theme.css          # CSS principal (<50kb objectif)
    │   └── editor.css         # CSS éditeur de blocs
    ├── fonts/                 # Variable fonts woff2 self-hosted
    └── images/                # Favicon + touch icons
```

### Couches d'adaptation

```
 ┌─────────────────────────────────────────────────────┐
 │ SITE SPECIFIC (par site)                            │
 │ ─ mu-plugin `arw-site-config-{site}.php`            │
 │   └ Filtres : twitter, sameAs, disclosure, hero…    │
 │ ─ Base de données : menus, catégories, articles     │
 │ ─ Options WP : kit média, mentions légales          │
 ├─────────────────────────────────────────────────────┤
 │ SKIN (par archétype visuel)                         │
 │ ─ styles/urban.json     ← citymoto, dark + yellow   │
 │ ─ styles/industrial.json ← B2B (à venir)            │
 │ ─ styles/craft.json      ← artisan (à venir)        │
 ├─────────────────────────────────────────────────────┤
 │ THEME (partagé partout)                             │
 │ ─ Tout le PHP, tous les templates, tous les patterns│
 │ ─ Identique sur tous les sites                      │
 └─────────────────────────────────────────────────────┘
```

### Où va quoi ?

| Élément | Emplacement | Pourquoi |
|---|---|---|
| Logic métier (SEO, schema, form, security) | Thème | Partagé, stable |
| Structure HTML (patterns, templates) | Thème | Structure = réutilisable |
| Couleurs, fontes, spacings | `styles/*.json` | Identité visuelle par archétype |
| Textes d'accueil (hero, manifesto) | mu-plugin | Spécifique au site |
| Handles sociaux, SIRET, email presse | mu-plugin + options admin | Spécifique au site |
| Articles, catégories, produits | Base de données | Contenu éditorial |

---

## Démarrer un nouveau site

Voir [`docs/NEW-SITE.md`](docs/NEW-SITE.md) pour la procédure pas-à-pas (15 min).

En résumé :

1. Installer ARW Pulse sur la nouvelle install WP
2. Activer une skin existante (Apparence → Personnaliser → Styles) ou créer `styles/<nouveau>.json`
3. Copier [`docs/example-site-config.php`](docs/example-site-config.php) dans `wp-content/mu-plugins/` sous le nom `{site}-config.php`
4. Éditer les valeurs (handles, textes, contacts)
5. Remplir Réglages → Kit média + Mentions légales
6. Uploader logo (Personnaliser → Identité du site)

---

## Créer une nouvelle skin

Un skin est un override de tokens pur. Aucun PHP, aucun HTML, aucune CSS supplémentaire.

1. Copier `styles/urban.json` en `styles/ma-skin.json`
2. Modifier :
   - `settings.color.palette` (couleurs)
   - `settings.typography.fontFamilies` (si fonts différentes)
   - `settings.custom.shadow`, `settings.custom.radius`, etc.
3. Dans WP admin → Apparence → Éditeur → Styles → Parcourir les styles → choisir la skin

Les patterns et templates reprennent automatiquement les nouveaux tokens via les variables CSS générées par WP (`--wp--preset--color--*`).

---

## Reference : filtres configurables

Tous les filtres listés ci-dessous sont appelables depuis un mu-plugin pour personnaliser le comportement sans toucher au thème.

### Identité & SEO

| Filtre | Par défaut | Usage |
|---|---|---|
| `arw_pulse_meta_description` | Auto (excerpt ou content) | Meta description site-wide |
| `arw_pulse_default_og_image` | Empty | URL image OG fallback |
| `arw_pulse_twitter_site` | `''` | Handle `@site` |
| `arw_pulse_twitter_creator` | `''` | Handle `@author` |
| `arw_pulse_organization_same_as` | `[]` | URLs sociales (schema Organization) |
| `arw_pulse_article_schema` | Array | Modifier le JSON-LD Article avant émission |
| `arw_pulse_product_schema` | Array | Modifier le JSON-LD Product |
| `arw_pulse_breadcrumbs` | Array | Modifier le fil d'Ariane |
| `arw_pulse_robots_sitemaps` | `[wp-sitemap.xml]` | Ajouter sitemaps custom au robots.txt |

### Performance

| Filtre | Par défaut | Usage |
|---|---|---|
| `arw_pulse_preload_fonts` | Inter + Space Grotesk | URLs fonts à preloader |
| `arw_pulse_defer_scripts` | `[wp-embed]` | Handles à différer |
| `arw_pulse_dns_prefetch` | `[]` | Domaines à prefetch |

### Formulaires

| Filtre | Par défaut | Usage |
|---|---|---|
| `arw_pulse_form_types` | `[contact, newsletter, quote, press, affiliate-inquiry]` | Types de formulaires acceptés |
| `arw_pulse_form_to` | admin_email | Destinataire email par type |
| `arw_pulse_form_redirect` | `/merci-{type}/` | URL de redirection après submit |
| `arw_pulse_global_rate_cap` | 60 | Cap global submissions/h |

### Affiliation

| Filtre | Par défaut | Usage |
|---|---|---|
| `arw_pulse_auto_disclosure` | `true` | Injection auto du disclaimer |
| `arw_pulse_disclosure_text` | FR/FTC | Texte du disclaimer |

### Produits

| Filtre | Par défaut | Usage |
|---|---|---|
| `arw_pulse_enable_products` | `true` | Activer/désactiver entièrement le module Produits (CPT + shortcodes + schema) |
| `arw_pulse_currency` | `EUR` | Devise par défaut (fallback) |
| `arw_pulse_home_products_count` | 6 | Nombre de produits sur la home |

### Contenu patterns démo (surcharge par site)

| Filtre | Clés array |
|---|---|
| `arw_pulse_hero_front_content` | `eyebrow`, `date_text`, `title_html`, `description`, `cta_primary`, `cta_secondary`, `image_url`, `image_alt`, `image_width`, `image_height`, `meta_left`, `meta_right` |
| `arw_pulse_manifesto_content` | `eyebrow`, `text_html`, `sig_name`, `sig_loc` |

### Sécurité

| Filtre | Par défaut | Usage |
|---|---|---|
| `arw_pulse_security_headers` | Array | Override les headers HTTP |
| `arw_pulse_security_contact` | mailto:admin_email | Contact pour security.txt |

### UX / Animation

| Filtre | Par défaut | Usage |
|---|---|---|
| `arw_pulse_view_transitions` | `true` | Activer View Transitions API |
| `arw_pulse_wpm` | 220 | Mots/min pour reading time |

---

## Shortcodes

| Shortcode | Sortie |
|---|---|
| `[arw_essential]` | Section complète "L'Essentiel" (6 produits) |
| `[arw_products limit=6 cat="casque"]` | Grille produits (filtrable par taxo) |
| `[arw_sitemap]` | Plan du site HTML |
| `[arw_year]` | Année courante |
| `[arw_reading_time]` | Temps de lecture (posts uniquement) |
| `[arw_updated]` | Date de mise à jour (si ≥1j après publication) |

---

## Publier une mise à jour

1. Bump `style.css` → `Version: X.Y.Z`
2. `./package.sh` → génère `arw-pulse.zip`
3. Upload via WP admin → Apparence → Thèmes → Ajouter → Téléverser → écraser l'existant
4. **Important** : à la prochaine visite, le thème efface automatiquement les templates/template_parts en DB. Les fichiers du ZIP deviennent autoritaires
5. Purger le cache Cloudflare (Caching → Purge Everything)

---

## Performance targets

| Métrique | Cible | v1.1.0 typique |
|---|---|---|
| Lighthouse Performance | ≥ 95 | 90-98 selon contenu |
| Lighthouse SEO | 100 | 100 |
| Lighthouse Best Practices | 100 | 100 |
| Lighthouse Accessibility | ≥ 95 | 95-100 |
| LCP | ≤ 1.2s | ~1.2-1.9s (dépend poids image) |
| TBT | ≤ 200ms | ~80-100ms |
| CLS | 0 | 0 |
| CSS total | ≤ 50kb | ~36kb |
| JS inline | ≤ 5kb | ~3kb |
| JS externe | 0 | 0 |

---

## Roadmap

### Court terme
- [x] Refactor `hero-front.php` + `manifesto.php` : texte via filtres (v1.1.1)
- [x] Filter `arw_pulse_enable_products` + toggle admin (v1.1.1)
- [ ] Style variation `editorial.json` (voyage / déco / serif)
- [ ] Style variation `field.json` (outdoor / sans condensed / terre)
- [ ] Style variation `corporate.json` (B2B / agence / bold)

### Moyen terme
- [ ] Pack `arw-pack-recipes` (cuisine) — CPT Recipe + schema + admin repeaters ingrédients/étapes
- [ ] Pack `arw-pack-services` (agence/formation) — CPT Service + schema + pricing patterns
- [ ] Pack `arw-pack-projects` (travaux) — CPT Project + before/after + quote multi-step
- [ ] Génération OG image auto (fallback SVG → PNG via GD)
- [ ] Schema Review (produits reviewed avec pros/cons)

### Long terme
- [ ] Schema Event (site évènements)
- [ ] Schema RealEstateListing (site immobilier)
- [ ] Multi-step form system (devis scopés)

---

## Changelog

Voir [`CHANGELOG.md`](CHANGELOG.md).

---

## License

GPL-2.0-or-later · Anthony Russo
