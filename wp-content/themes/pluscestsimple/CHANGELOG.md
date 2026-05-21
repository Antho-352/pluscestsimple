# Changelog

## [2.0.2] — 2026-05-21

### Fixes patterns
- **Pleine largeur** : ajout de `align: full` (+ classe `alignfull`) sur les 5 patterns « Sections » et le Hero. Sans ça, ils étaient contraints à 720px par le wrapper `.pcs-content`
- **"Bloc contient du contenu invalide"** : refonte du markup des patterns pour éviter les inline styles complexes (clamp, letterSpacing inline). Les styles passent désormais par des classes CSS dédiées (`.pcs-section__title`, `.pcs-hero-front__title`, `.pcs-manifesto__title`, etc.). Gutenberg re-sérialise sans diff
- **theme.css** : ajout de ~120 lignes pour les classes des patterns (`.pcs-section`, `.pcs-eyebrow`, `.pcs-section__title`, `.pcs-section__lead`, `.pcs-hero-front`, `.pcs-featured`, `.pcs-newsletter-form`, `.pcs-manifesto`)
- Suppression du `sticky:"only"` sur le Query Loop « À la une » qui n'affichait rien si aucun article épinglé

## [2.0.1] — 2026-05-21

### Fixes
- **Bandeau cookies** : sélecteurs CSS désynchronisés avec le HTML porté (mismatch `.pcs-consent__btn` vs `[data-consent]`). Le bandeau s'affichait sans styles. Refonte du CSS pour cibler les sélecteurs réels (`#pcs-consent`, `.pcs-consent__inner`, `.pcs-consent__text`, `[data-consent]`, `.is-primary`, `.pcs-consent__custom`).

## [2.0.0] — 2026-05-21

### Refonte complète : passage du thème FSE arw-pulse vers thème classique PHP

#### Architecture
- **Bascule FSE → classique PHP.** Templates `header.php`, `footer.php`, `single.php`, `page.php`, `archive.php`, `category.php`, `search.php`, `404.php`, `index.php`, `front-page.php`, `home.php`, `searchform.php`
- **Menu 100% natif** via `wp_nav_menu()`. Suppression du système de sauvegarde/réinjection de `ref` (qui rendait le menu fragile à chaque update). L'assignation menu → emplacement vit dans `theme_mods_pluscestsimple`, mécanisme WP standard, survit indéfiniment aux mises à jour
- **Front-page Gutenberg.** L'accueil n'est plus un shortcode monolithique (`[arw_pulse_front_page]`) — c'est une page WP éditée en blocs natifs avec les patterns du thème
- **Patterns auto-chargés** depuis `patterns/*.php`. Tous en `templateLock: contentOnly` pour éviter qu'un éditeur casse la structure
- **Stratégie D1 pour les catégories.** Page WP éditable avec slug `/decoration/`, archive native suffixée `decoration-cat` qui 301 vers la page

#### Modules métier (portés depuis arw-pulse, préfixe `pcs_`)
- `seo.php`, `schema.php`, `security.php`, `performance.php`, `form.php`, `reading-time.php`, `cookie-consent.php`, `branding.php`, `cleanup.php`, `robots.php`, `affiliate.php`, `image.php`, `structured-data.php`, `legal-defaults.php`, `identity.php`
- Nouveaux : `breadcrumbs.php` (microdata Schema.org BreadcrumbList), `view-transitions.php` (meta tag opt-in), `patterns.php` (catégories d'inserter), `category-base.php` (redirect D1)

#### Design
- Skin **Editorial** intégrée (avant : 1 thème + 6 skins JSON, désormais 1 thème dédié) : ivoire `#f7f1e6` + vert anglais `#1f3a2e` + laiton `#a78a4d`
- Fonts : Inter (body) + Fraunces (display, regular + italic), self-hosted woff2, préchargées
- `theme.json` minimaliste conservé pour exposer les tokens aux blocs Gutenberg

#### Front-end
- `assets/css/theme.css` (~700 lignes, classes `.pcs-*` BEM-light)
- `assets/js/theme.js` (~3 ko vanilla : menu hamburger + reveal + split-text + consent reopener)
- View Transitions API en CSS (`@view-transition`) + meta tag opt-in
- Menu hamburger mobile en CSS pur (≤900px) avec animation cross du burger

#### CPT & DB
- Slug `arw_submission` **préservé** pour ne pas casser les soumissions form existantes (constante `PCS_SUBMISSION_CPT`)
- Slug REST endpoint : `/pcs/v1/submit` (anciennement `/arw/v1/submit`)
- Meta keys SEO et structured data renommées (`_arw_*` → `_pcs_*`)

#### Performance
- CSS : ~24 ko (vs ~36 ko sur arw-pulse, -33%)
- JS externe : 0 (un seul fichier de 3 ko, defer)
- Lighthouse cible : 95+ Performance, 100 SEO, 100 Best Practices, 95+ Accessibility

---

## Avant v2.0.0

Voir l'historique du thème [arw-pulse](https://github.com/Antho-352/pluscestsimple/tree/main/wp-content/themes/pluscestsimple) sur la branche `main` (état de référence pré-refonte).
