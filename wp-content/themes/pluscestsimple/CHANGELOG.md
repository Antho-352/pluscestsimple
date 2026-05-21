# Changelog

## [1.7.2] - 2026-04-28

### SEO — Audit complet 4 sites + correctifs P0/P1/P2/P3
- **`<head>`** : meta charset UTF-8 + viewport forcés (priorité 1, indépendant config WP)
- **OG image** : fallback hardcodé sur custom_logo puis Site Icon si identité non configurée — plus jamais aucune page sans `og:image`
- **OG image dimensions** : récupération réelle via `wp_get_attachment_image_src` (au lieu de hardcoder 1200×630)
- **OG locale** : `fr` → `fr_FR` automatique (BCP 47)
- **OG type** : tous CPT singuliers émettent `article` (au lieu de `website` par défaut)
- **Twitter handle** : sanitisation forcée du préfixe `@`
- **Meta description** : cap dur à 160 chars + expansion shortcode si contenu = `[shortcode]` brut
- **Canonical search** : pagination-aware (`?s=foo&paged=2` distinct de `?s=foo`)
- **BreadcrumbList** : émise sur la home (1 crumb « Accueil »), `item` omis si URL vide (anti Schema invalide)
- **Robots meta** : filter `arw_pulse_robots_meta_index` extensible
- **Author archives** : noindex global par défaut (filter `arw_pulse_author_archives_noindex`)
- **Trailing slash** : redirect 301 uniforme aligné avec canonical
- **Hn auto-fix** : transforme H2 en H3 quand 5+ H2 consécutifs détectés sans H3 (article 64×H2 → vraie hiérarchie)
- **External links** : `rel="noopener noreferrer"` automatique sur `the_content`
- **robots.txt** : nouveau filter `arw_pulse_robots_disallow` permettant aux packs d'ajouter leurs règles
- **Featured images** : auto-wrap en `<picture>` AVIF/WebP si fichiers existent à côté du JPG/PNG

### Sécurité
- **CSP** : Content-Security-Policy basique ajoutée (default-src 'self', frame-ancestors 'self', form-action 'self'). Filter `arw_pulse_csp` pour adapter.

### Schema
- **Product/AggregateRating** : refus d'émission si `rating_count < 5` (anti self-serving review markup, risque pénalité Google)

### Refactor
- **Templates single + single-review** : extraction du pattern réutilisable `arw-pulse/article-header` (breadcrumbs + terms + H1 + post-meta), élimine la duplication

## [1.6.0] - 2026-04-27

### Skins
- New skin `editorial.json` — art déco moderne (ivoire chaud + vert anglais + laiton, Fraunces serif). Cible : magazines de référence, pluscestsimple.com option A.
- New skin `brut.json` — brutaliste doux (béton crème + terracotta brûlé + ardoise, Space Grotesk). Cible : éditorial différenciant, pluscestsimple.com option B.

## [1.1.1] - 2026-04-23

### Adaptability
- Hero-front pattern: content via `arw_pulse_hero_front_content` filter (no more hardcoded text)
- Manifesto pattern: content via `arw_pulse_manifesto_content` filter
- Products module: activable/désactivable via filter `arw_pulse_enable_products` + admin toggle (Settings → ARW Pulse)
- Documentation: business model × archetype matrix, 3-layer architecture (base + skin + pack + site-config)

## [1.1.0] - 2026-04-23

### Security
- HTTP headers: HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy
- Block `/?author=N` user enumeration for anonymous visitors
- Generic login errors (no user-exists leak)
- `/.well-known/security.txt` auto-served
- Global rate-limit (60 submissions/h) on top of per-IP (3/10min)
- CSV injection guard in submissions export (formula prefix escape)
- Email header injection strip on Reply-To
- Availability/currency enum whitelist on products

## [1.0.9]

### Added
- FAQ repeater meta box → `FAQPage` JSON-LD
- HowTo repeater meta box → `HowTo` JSON-LD
- Product CPT fields: SKU, availability, currency, price (schema numeric)
- Product ItemList schema on home (CPT is private → emitted via [arw_essential] context)
- MB-safe word count (UTF-8 regex)

## [1.0.8]

### Added
- Canonical pagination-aware
- Noindex toggle per post
- OG image override in meta box (media picker)
- Focus keyword field
- Twitter site/creator via filter
- Last-Modified HTTP header + 304 conditional GET
- Apple touch icon + web manifest (`/manifest.webmanifest`)
- Alt text fallback (3 levels)
- Theme-color meta
- Publisher logo in Article schema (Google News eligibility)
- CollectionPage + ItemList schema on archives
- Product schema with enum-validated availability
- Updated date display (only if ≥1d after publication)
- Reading time display via shortcode

## [1.0.7]

### Fixed
- Hotfix: re-added `global-styles` dequeue was breaking all FSE theme.json variables

## [1.0.6]

### Added
- Archive LCP preload (first featured image)
- fetchpriority=high on first archive card image
- `imagesrcset` + correct `sizes` on archive images (400px vs 100vw)
- Defer wp-embed
- Global styles SVG duotone removed (unused overhead)
- robots.txt filter priority 999 (strips plugin/Cloudflare-added directives)

## [1.0.5]

### Added
- SEO meta box: title, meta description, char counter, Google preview

## [1.0.4]

### Added
- Auto DB template/template_part reset on version bump (theme files always win)
- Main `contentSize` 960px on single templates (articles wider on PC)
- Media kit page width: 1120px

### Fixed
- Hero title: reduced clamp max to avoid overflow
- Menu overlay padding reduced
- Footer logo (site-logo instead of site-title text)

## [1.0.3]

### Fixed
- Mobile menu overlay trapped by header `backdrop-filter` — moved to `::before`
- Menu z-index above cookie banner
- Cookie banner mobile compact

## [1.0.2] — [1.0.0]

### Added
- Initial release: templates, patterns, SEO base, schema base, form, products, cookie consent
