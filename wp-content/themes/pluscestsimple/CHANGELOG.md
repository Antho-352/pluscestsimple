# Changelog

## [2.2.0] — 2026-05-21

### Pages catégories — 6 patterns dédiés avec contenu rédigé final

Aboutissement du pipeline 4 phases (recherche → copywriting → SEO+relecture → injection) sur les 6 piliers du site :

- **6 patterns Gutenberg natifs** créés dans `/patterns/category-{pilier}.php` :
  - `category-decoration` (« Décoration : ce qui marche vraiment chez vous, et combien ça coûte »)
  - `category-travaux` (« Travaux et rénovation : comprendre avant d'engager 30 000 € »)
  - `category-jardin` (potager + balcon copro + climat 2026)
  - `category-architecture` (ABF + extensions + 2 H2 en profondeur)
  - `category-immobilier` (DPE + coût réel achat + aides 2026, YMYL-compliant)
  - `category-lifestyle` (rangement comparé + bien-être mesurable + recevoir 30 m²)

Chaque pattern contient :
- H1 + intro éditoriale 150-200 mots (MC en 1ère phrase)
- Pattern `disclosure-partners` (bandeau ⓘ)
- Bannière `category-intro` (slot dédié)
- 2-3 sections H2 avec leads 100-150 mots et liens partenaires in-text (`class="pcs-link-partner" rel="sponsored nofollow noopener"`)
- Bannière `category-mid` (slot dédié)
- Query Loop avec `inherit:true` (filtre auto par catégorie courante)
- FAQ avec 3-5 wp:details + Schema FAQ JSON-LD inline
- Section partenaires (6 cartes spécifiques par pilier)
- Maillage interne 6 liens vers autres piliers

### Sources fact-checkées (WebSearch foreground)
- Service-Public.fr, Légifrance, Notaires.fr (immobilier YMYL)
- Anah, France Rénov', Ministère de l'Écologie (travaux)
- Code du patrimoine, code de l'urbanisme (architecture)
- OFB, ADEME, code rural (jardin)
- OQAI, INSEE, ADEME (lifestyle)
- Sites officiels des marques (vérification garanties, prix, disponibilité)

### Modifications init-content.php
- `pcs_init_content()` : pour chaque pilier, tente d'abord d'inliner le pattern spécifique `category-{pilier}`, fallback sur `category-rich` générique si le pattern spécifique n'est pas trouvé
- Personnalisation H1/intro appliquée seulement sur le fallback générique (les patterns spécifiques ont déjà leur H1/intro propres)

### À faire après upload v2.2.0
1. Apparence → Thèmes → Téléverser → pluscestsimple.zip → Remplacer
2. Outils → PCS Init content → bouton rouge « Reset des pages seedées »
3. Les 6 pages pilier sont recréées avec leur contenu spécifique éditable
4. Vérification rapide : ouvrir Pages → Décoration / Travaux / Jardin / Architecture / Immobilier / Lifestyle → contenu visible et éditable bloc par bloc
5. Personnaliser les URLs des partenaires (placeholders `#` → vraies URLs avec tracking d'affiliation)

## [2.1.5] — 2026-05-21

### Partenaires (V2+ validée)
Trois nouveaux composants pour monétisation native, transparence RGPD + ARPP :

- **Pattern `disclosure-partners`** : bandeau d'information en haut de page (ⓘ + lien vers la charte). À insérer sur toute page contenant des liens partenaires
- **Pattern `section-partners`** : section « Notre sélection partenaires » avec 6 cartes éditables (badge, logo texte, nom, pitch, CTA vers le partenaire avec rel="sponsored nofollow noopener")
- **Pattern `charte-partenaires`** : contenu de la page `/charte-partenaires/` (critères de sélection, mécaniques de rémunération, engagement d'indépendance, contact partenariat)
- **Page `/charte-partenaires/`** auto-seedée via init-content (tpl-wide)
- **Convention CSS `.pcs-link-partner`** : liens partenaires in-text avec underline pointillé + picto † discret en super
- **Pattern `category-rich` mis à jour** : disclosure en haut, section partners avant le maillage

### CSS
+~150 lignes pour `.pcs-disclosure-bar`, `.pcs-link-partner`, `.pcs-partners`, `.pcs-partner-card` et variantes.

### À faire après upload v2.1.5
1. Outils → PCS Init content → Reset des pages seedées (récupère la nouvelle structure des pages pilier + crée la page charte-partenaires)
2. Personnaliser les 6 cartes partenaires (texte, marques, URLs) dans le pattern `section-partners` une fois inséré dans chaque page pilier

## [2.1.4] — 2026-05-21

### Fix critique — bannière de la home affichée sur toutes les pages catégories
- **Cause** : le pattern `banner-slot` (générique) contenait `[pcs_banner slot="homepage-mid"]` codé en dur. Le pattern `category-rich` l'utilisait → toutes les pages pilier affichaient la bannière du slot homepage-mid (au lieu de slots dédiés)
- **Fix** : éclatement en 4 patterns dédiés, un par slot :
  - `pluscestsimple/banner-slot` (default = `in-article`)
  - `pluscestsimple/banner-slot-homepage` (= `homepage-mid`)
  - `pluscestsimple/banner-slot-category-intro` (= `category-intro`)
  - `pluscestsimple/banner-slot-category-mid` (= `category-mid`)
- Le pattern `category-rich` utilise désormais `category-intro` (après l'intro) et `category-mid` (au milieu) — le slot `homepage-mid` n'apparaît plus que sur la home
- Retrait du `templateLock: contentOnly` sur le wrapper du banner-slot (l'utilisateur peut éditer le shortcode pour basculer manuellement vers un autre slot si besoin)

## [2.1.3] — 2026-05-21

### Fixes retours user (debug)
- **« Reset des pages seedées » disait "0 page supprimée"** : cause = les pages créées en v2.1.0/v2.1.1 n'ont pas le meta `_pcs_seeded=1` (ajouté seulement après). Fix : matcher aussi par slug (pas que par meta). `pcs_reset_seeded_pages()` regarde maintenant les 11 slugs seedés connus en plus du meta. Les pages de l'historique seront enfin supprimées-recréées
- **Query Loop inaccessible dans « À la une » et « Sélection de la semaine »** : cause = `templateLock: contentOnly` sur le wrapper `<section>` du pattern → Gutenberg considère le pattern comme une « Composition » fermée non-éditable. L'utilisateur voyait « Modifier la composition » au lieu de pouvoir cliquer sur le bloc Query Loop. Fix : retrait du `templateLock` sur le wrapper de `section-featured` et `section-weekly`. Le `lock` reste sur le post-template interne (structure de carte intacte) mais le Query Loop devient directement sélectionnable

## [2.1.2] — 2026-05-21

### Fix critique — pages catégories réellement vides
- **Cause** : le post_content contenait `<!-- wp:pattern {"slug":"..."} /-->` (référence) au lieu du contenu inliné. En éditeur Gutenberg, ça apparaissait comme un bloc « Composition » fermé non éditable. En front, le rendu marchait mais l'utilisateur ne pouvait pas modifier les textes
- **Fix** : nouvelle fonction `pcs_get_pattern_content( $slug )` qui résout le pattern via `WP_Block_Patterns_Registry` et retourne son contenu HTML inliné. Utilisée pour toutes les pages auto-seedées (Accueil, Outils, Annuaire, Travailler avec nous, et les 6 pages pilier)
- Bonus : pages pilier ont désormais le H1 et l'intro pré-remplis avec le label et la description de leur catégorie (au lieu du placeholder « Nom de la catégorie — à remplacer »)
- Bonus : Accueil seedée inclut maintenant les 8 patterns en cascade (hero + 6 piliers + pourquoi + compat + featured + weekly + newsletter + directory-teaser)

## [2.1.1] — 2026-05-21

### Fixes (retours premier déploiement v2.1.0)
- **Fil d'Ariane sur 2 lignes** : ajout de `display:flex` sur le `<ol>` interne de `pcs_breadcrumbs()` (le wrapper `<nav>` était déjà flex mais pas la liste). Les crumbs s'alignent maintenant sur une seule ligne avec wrap auto si overflow
- **Section « Pourquoi Plus c'est simple » visuellement perdue** : ajout de `backgroundColor: surface` pour différencier visuellement la section éditoriale comme un encart
- **Section « À la une » non-configurable** : retrait du `lock` sur le bloc Query. L'utilisateur peut désormais éditer perPage, sticky, taxQuery directement dans l'Inspector. Seul le post-template reste verrouillé (structure de carte intacte). `sticky: "first"` par défaut : si un article est épinglé dans Articles → Éditer → « Épingler cet article », il prend la place du featured ; sinon c'est le plus récent
- **Page « Travailler avec nous » double titre** : init-content assigne désormais `tpl-wide` à cette page (sans header automatique). Le H1 du pattern devient l'unique
- **Page « Annuaire » placeholder vide** : init-content y insère le shortcode `[pcs_directory limit="12"]` + tpl-wide
- **Pages catégories pilier placeholder vide** : init-content y insère désormais le pattern `pluscestsimple/category-rich` au lieu d'un simple `<p>` (l'utilisateur a directement la structure éditable avec Query Loop). L'intro éditoriale passe en `post_excerpt` pour rester accessible
- **Bouton « Reset des pages seedées »** ajouté à la page admin `Outils → PCS Init content` : supprime les pages avec meta `_pcs_seeded=1` et les recrée avec le contenu standard du thème (patterns à jour). Les pages éditées manuellement ou ajoutées par l'utilisateur sont préservées

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
