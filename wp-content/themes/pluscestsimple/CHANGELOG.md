# Changelog

## [2.1.0] — 2026-05-21

### Phase 2.5 — Arborescence éditoriale
- `inc/init-content.php` : module idempotent qui crée automatiquement les 6 catégories pilier (Décoration, Travaux, Jardin, Architecture, Immobilier, Lifestyle) + leurs 22 sous-catégories + les pages WP correspondantes (Accueil, Le carnet, Outils, Annuaire, Travailler avec nous, et une page par pilier). Respect strict de la stratégie D1 (slug catégorie `-cat` suffixé, slug page nu)
- Page admin `Outils → PCS Init content` : aperçu de la structure cible + bouton de re-seed

### Phase 3 — Patterns éditoriaux (9 nouveaux)
- `pluscestsimple/block-faq` : FAQ accordéon natif (`core/details`), 3 questions par défaut
- `pluscestsimple/banner-slot` : emplacement bannière (intègre le plugin Bannières via shortcode `[pcs_banner slot="..."]`)
- `pluscestsimple/before-after` : format signature anti-IA (2 colonnes images + texte structuré + facts list)
- `pluscestsimple/directory-teaser` : encart pleine largeur pointant vers l'annuaire
- `pluscestsimple/pillar-card` : carte d'entrée vers une catégorie
- `pluscestsimple/section-pillars` : grille des 6 cartes pilier (pour la home)
- `pluscestsimple/section-editorial` : section éditoriale standard (titre + lead + liste de liens articles + "tout voir")
- `pluscestsimple/newsletter-capture` : variante compacte du formulaire newsletter (inline article)
- `pluscestsimple/category-rich` : template complet d'une page catégorie (intro + bannière + 3 sections éditoriales + Query Loop + FAQ + maillage)
- `pluscestsimple/page-travailler` : page « Travailler avec nous » avec 3 cartes d'offres + formulaire de brief + email direct
- `pluscestsimple/article-pilier-tendances` : structure d'un article pilier annuel avec CTA de téléchargement du PDF

### Phase 5 — Newsletter RGPD
- Checkbox de consentement explicite (required) ajoutée à `section-newsletter` et `newsletter-capture`
- Lien vers Mentions légales dans le label de consentement
- CSS dédié `.pcs-newsletter-form__consent` (clear contrast sur fond sombre + clair)

### Phase 6 — Lead Resources (mécanique générique PDF par email)
- `inc/lead-resources.php` : CPT `pcs_lead_resource` accessible via Outils → Ressources. Champs : titre, fichier PDF (uploadé manuellement dans `wp-content/uploads/pcs-resources/private/`), form_type associé, sujet email, corps email (HTML avec placeholder `{LINK}`)
- Hook `pcs_form_submitted` : si une ressource matche le form_type soumis, génère un token signé (24h TTL) et envoie l'email avec le lien de téléchargement
- REST endpoint `/wp-json/pcs/v1/lead-download?t=TOKEN` : stream le PDF privé (vérification token + TTL)
- Dossier privé créé automatiquement à la 1ère init avec `.htaccess deny` (anti-accès direct)

### CSS
- +~200 lignes : `.pcs-section--faq` (accordéon), `.pcs-banner-wrap`, `.pcs-before-after`, `.pcs-pillar-card`, `.pcs-link-list`, `.pcs-section__more`, `.pcs-offers`, `.pcs-contact-form`, `.pcs-newsletter-form__consent`, `.pcs-newsletter-inline`

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
