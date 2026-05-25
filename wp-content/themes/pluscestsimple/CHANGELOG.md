# Changelog

## [2.9.0] — 2026-05-25

### SEO — consolidation des catégories vers pages piliers

Le site avait un sérieux problème de duplicate content : les archives WP de catégories piliers (ex: `/decoration-cat/`, `/decoration-cat/decoration-par-piece-cat/`) étaient publiques et indexées par Google, créant des doublons avec les pages piliers CMS (`/decoration/`).

**Nouveau fichier `inc/category-redirects.php` :**

1. **301 redirect** des archives catégorie piliers (parents ET enfants) vers la page pilier correspondante :
   - `/decoration-cat/` → `/decoration/`
   - `/decoration-cat/decoration-par-piece-cat/` → `/decoration/`
   - `/travaux-cat/travaux-renovation-cat/` → `/travaux/`
   - etc.

2. **301 redirect** des URLs "flat" 404 (qui ne devraient pas exister mais ont pu être indexées) → page pilier :
   - `/decoration-par-piece-cat/` (404) → `/decoration/`
   - `/travaux-par-piece-cat/` (404) → `/travaux/`

3. **Filet `noindex, follow`** sur les archives catégorie pilier qui échapperaient au redirect (sécurité défense en profondeur).

4. **Exclusion du sitemap XML WP** (`wp-sitemap.xml`) : les catégories piliers sont retirées du sitemap → Google ne les découvre plus du tout.

Helpers publics :
- `pcs_pilier_slugs()` → liste des slugs piliers (source unique : `pcs_content_structure()`)
- `pcs_find_pilier_root( $cat_slug )` → trouve le pilier racine pour un slug catégorie

### Plan du site (page front-end)

- **Création automatique** de la page `plan-du-site` au chargement du thème (ajoutée à `pcs_content_utility_pages()`) avec le template `tpl-sitemap.php` assigné automatiquement.
- **Mise à jour du template sur pages existantes** : si la page `plan-du-site` existait déjà mais sans template assigné (ex: créée manuellement par l'admin), le seeder force désormais `_wp_page_template = page-templates/tpl-sitemap.php`. Le `post_content` reste intact.

Le template `tpl-sitemap.php` (déjà présent) liste automatiquement :
- Toutes les pages publiées (via `wp_list_pages()`)
- Toutes les catégories non vides (via `get_categories()`)
- Les 60 articles les plus récents (via `get_posts()`)

Le contenu se met à jour à chaque visite (live queries WP, pas de cache).

### Sitemap XML pour Google
Le sitemap XML est généré automatiquement par WP core (depuis 5.5) à `/wp-sitemap.xml`. Il liste pages, articles, et catégories (sauf les catégories piliers maintenant exclues). Pas d'action requise — déclaré dans `robots.txt` automatiquement.

## [2.8.1] — 2026-05-25

### Homepage

- **Hero title** : `max-width: 720px → 1100px` → titres longs sur 2 lignes au lieu de 3.
- **"Les + lus" → "Les plus lus"** : changement de wording (le rendu `+` stylisé en accent était jugé peu lisible).

### Hover effect — images du site

Effet sobre/moderne appliqué à toutes les images cliquables (hero, sel, une, cat-hero, cat-cards, .pcs-card) au survol :
- `transform: scale(1.05)` (zoom léger, +66% vs ancien 1.03)
- `filter: brightness(0.92) saturate(1.08)` (légère assombrissement + saturation accrue pour effet "magazine")
- Transition fluide sur `transform` et `filter`

### Articles single

- **Newsletter en bas de chaque article** : nouveau bloc fond vert (réutilisation des styles `.pcs-home__newsletter`), placé après l'article et avant les commentaires. Titre "Vous avez aimé cet article ?" + lead newsletter. Formulaire complet avec consent RGPD.
- CSS refactor : les sélecteurs newsletter (fond vert, input/bouton clairs) sont désormais partagés entre `.pcs-home__newsletter` et `.pcs-article__newsletter`.

### Plugin pcs-directory — fix layout filtres

Le CSS générique du thème `.pcs-content form { flex-direction: column; max-width: 560px }` écrasait le `display: grid` du formulaire de filtres du plugin annuaire → filtres empilés verticalement au lieu d'être alignés en ligne. Ajout d'un override spécifique `.pcs-content .pcs-directory-filters` pour restaurer le grid 4 colonnes natif.

### Note carte annuaire
Le placeholder de la carte (`<div class="pcs-directory-map">`) est rendu par le plugin mais Leaflet n'est pas chargé par défaut. Pour activer la carte interactive, voir `pcs-directory/README.md` § "Carte interactive". À traiter dans une itération séparée.

## [2.8.0] — 2026-05-25

### Pages catégorie — "À la une" : layout asymétrique 1 grand + 2 petits

Refonte du layout de la section `.pcs-section--cat-top` (Query Loop "À la une" en haut des pages catégorie). CSS pur, aucun changement de pattern requis.

**Cas standard — 3 articles (le plus fréquent) :**
```
┌─────────────────────┬──────────────┐
│                     │ Article 2    │
│   Article 1         │   (16/9)     │
│   (image 4/3        ├──────────────┤
│    + titre XL)      │ Article 3    │
│                     │   (16/9)     │
└─────────────────────┴──────────────┘
       2fr                 1fr
```
- Grid `2fr 1fr`, gap 1.5rem
- Article 1 : `grid-row: span 2`, image en aspect-ratio `4/3` (au lieu de `16/9`) → image naturellement plus haute pour matcher visuellement les 2 cards empilées à droite
- Articles 2 et 3 : tailles thumbnail standards (16/9)
- Titre de l'article 1 passe en font-size `xl` pour matcher l'importance visuelle

**Cas 2 articles :** détecté via `:has(> li:nth-child(2):last-child)` → bascule auto en 2 colonnes égales 1fr+1fr (aspect-ratio 16/9 standard pour les deux)

**Cas 1 article (edge case) :** détecté via `:has(> li:only-child)` → bloc centré max-width 700px, image en 16/9 (court, "pas plus de hauteur" comme demandé), card prend plus de largeur que dans une grille 3-cols.

**Responsive < 900px :** colonne unique stack pour tous les cas.

### Note technique
Le sélecteur `:has()` est requis (support : Chrome 105+, Safari 15.4+, Firefox 121+ — disponible chez >95% des visiteurs en 2026). Sur les navigateurs plus anciens, fallback gracieux vers le layout 3-articles standard.

## [2.7.4] — 2026-05-25

### Pages catégories — section "À la une" : fix hauteur des images

**Cause racine identifiée :** la section `.pcs-section--cat-top` (Query Loop "À la une", 3 articles) n'avait **aucun layout grid** sur son `wp-block-post-template`. Comportement WordPress par défaut → empilement vertical en une seule colonne → chaque article prend toute la largeur du container (1400px) → image 16/9 = ~787px de haut par article = images énormes.

La règle CSS existante `.pcs-section--cat-loop .wp-block-post-template { display: grid }` ne s'appliquait qu'à la section "Tous les articles" en bas, pas à "À la une" en haut.

### Fix double :
1. **CSS** : sélecteur étendu à `.pcs-section--cat-top` → la grille 3 colonnes s'applique aussi sur "À la une" → images automatiquement à 1/3 de la largeur (~390px sur container 1400px) au lieu de 100%
2. **Patterns** : ajout de `"layout":{"type":"grid","columnCount":3}` sur le `wp:post-template` des 7 patterns catégorie → pour les futurs imports/resets, l'éditeur Gutenberg verra directement la grille

**Aucun Reset nécessaire** sur les pages existantes : le fix CSS s'applique immédiatement après upload du thème.

## [2.7.3] — 2026-05-25

### Images cards — hauteur réduite + qualité fixée

**Pages catégorie (piliers) — section "À la une" :**
- Aspect ratio cards : `4/3 → 16/9` → cards 33% plus courtes en hauteur (moins de scroll requis pour passer à la section suivante)
- Image size source : `post-thumbnail` (1200×630 croppé en 4/3) → `pcs-card-wide` (800×450 natif 16/9, **aucun crop nécessaire**) → image nette
- 7 patterns mis à jour : decoration, travaux, jardin, architecture, immobilier, lifestyle, rich

**Homepage — sections catégorie (grille 2×2) :**
- Image size : `medium` (300px) → `pcs-card` (800×600 natif 4/3)
- Cause du flou : les cards 2×2 font ~400px de large à l'écran, l'image source 300px était **upscalée 33%** → flou. Avec `pcs-card` 800px, l'image est downscalée → nette.

**Homepage — section "À la une" (grille 2×2 avec/sans sidebar) :**
- Image size : `medium_large` (768px) → `pcs-card` (800×600, ratio 4/3 natif aligné sur le display)

### Note
Les autres usages de `.pcs-card` (`home.php` Le carnet, `category.php` archive native, `archive.php`) conservent le 4/3 — seuls les patterns piliers passent en 16/9.

## [2.7.2] — 2026-05-25

### Largeur du contenu — refonte globale

Le vrai bottleneck était dans `theme.json` : `contentSize: 720px` plafonnait TOUS les blocs Gutenberg + tous les `.pcs-content` à 720px, indépendamment du max-width du container parent. C'est pour ça que les articles paraissaient à 50% du viewport même avec un container à 1320px.

**Changements `theme.json` :**
- `contentSize: 720px → 1100px` (la prose des articles/pages passe à 1100px max)
- `wideSize: 1180px → 1400px` (les blocs `alignwide` passent à 1400px max)

**Changements layouts :**
- `.pcs-article-layout` (avec sidebar) max-width: 1320 → 1500px → article colonne ~1144px
- `.pcs-article-layout.no-sidebar` max-width: 1100 → 1400px → ~78% sur 1920 / 97% sur 1440
- `.pcs-home__selection-layout.no-sidebar` (grille 2×2 sans pub) max-width: 900 → **760px** (réduction demandée)
- `.pcs-home__selection-grid` gap: 1.25rem → **2rem** (plus d'espace entre cards 2×2)
- `.pcs-home__cat-layout.no-sidebar` même traitement (760px + gap 2rem)

### Impact

- **Articles** : prose 1100px (76% sur 1440 viewport, 57% sur 1920 — limite éditoriale pour la lisibilité, voir note ci-dessous)
- **Pages catégorie / piliers** (page.php) : même chose, contenu prose à 1100px
- **Homepage 2×2 sans pub** : nettement plus petit et plus aéré, images mieux dimensionnées

### Note sur la lisibilité prose
1100px de largeur prose ≈ 110 caractères/ligne à 18px de font-size, ce qui dépasse l'optimal éditorial (50-75 chars). C'est un choix assumé suite à la demande utilisateur "75-80% de largeur de page". Si tu trouves les lignes trop longues à la lecture, il suffit de remettre `theme.json` `contentSize` à 800-900px.

## [2.7.1] — 2026-05-25

### Homepage

- **Bannière top** : format 1240×125 (au lieu de 970×250) — aligne la largeur sur celle du héro. CSS `.pcs-home__banner-top` réécrit (max-width 1240px, centrage, padding cohérent).
- **Images héro & cat-hero** : passage de `large` (1024px max) à `full` (taille originale) → fin du pixel-art quand l'image est étirée sur tout le container.
- **Cap max-width 900px** sur `.pcs-home__cat-layout.no-sidebar` et `.pcs-home__selection-layout.no-sidebar` → quand la sidebar est désactivée, le contenu se recentre proprement (plus d'images étirées).
- **Titres sections** "À la une" / "Tendance" et noms catégorie : font-size +20% (`clamp(1.5, 3vw, 2.25rem)` → `clamp(1.75, 3.5vw, 2.625rem)`) et `font-weight: 300 → 700`.

### Pages article

- **Container élargi à 1320px** (au lieu de 1180px) avec padding latéral réduit (`clamp(0.75rem, 2.5vw, 1.5rem)`) → article ~970px avec sidebar, ~1100px sans (≈ 3/4 du viewport sur 1440px).
- **Retrait du temps de lecture brut** (`pcs_reading_time()`) qui affichait juste "6" à côté de la date — confusion utilisateur. La fonction reste disponible via le shortcode `[pcs_reading_time]` qui formate proprement "6 min de lecture".

## [2.7.0] — 2026-05-25

### Layout adaptatif selon le mode d'affichage des slots publicitaires

Le thème détecte maintenant pour chaque sidebar pub si elle va rendre du contenu (`pcs_banner_slot_will_render`) et collapse le layout en conséquence quand le slot est désactivé.

**Templates modifiés :**
- `front-page.php` — sidebar "À la une" (`homepage-sidebar`) et sidebars des 5 sections catégorie (`cat-sidebar-*`) conditionnelles
- `single.php` — sidebar article (`article-sidebar`) conditionnelle

**CSS modificateurs (3 layouts grid) :**
- `.pcs-article-layout.has-sidebar` → grid 1fr+300px ; `.no-sidebar` → bloc simple `max-width: 900px` recentré
- `.pcs-home__selection-layout.has-sidebar` → grid 1fr+300px ; `.no-sidebar` → bloc simple (grille 2×2 prend toute la largeur)
- `.pcs-home__cat-layout.has-sidebar` → grid 1fr+300px ; `.no-sidebar` → bloc simple (article principal + 2×2 prennent toute la largeur)

**Comportement :**
- Mode `auto` (slot par défaut) → sidebar affiche placeholder ou pub réelle → layout en 2 colonnes
- Mode `banner-only` sans pub → sidebar disparaît → contenu se recentre (pas d'espace vide)
- Mode `hidden` → sidebar jamais rendue → contenu se recentre

### ⚠️ Action requise
Mettre à jour le plugin pcs-banners vers **v1.3.0** (sinon les fonctions `pcs_banner_slot_will_render` n'existent pas — fallback safe à `true` mais aucun collapse).

## [2.6.0] — 2026-05-22

### Homepage — ajustements + renommage tags

- **Héro plus haut + image moins coupée** : `aspect-ratio: 16/5 → 16/6`, `max-width: 1100px → 1240px`.
- **Titre héro plus petit** : `clamp(1.75rem, 4vw, 3rem) → clamp(1.4rem, 3vw, 2.25rem)` (~25% plus petit).
- **Titres de section** : ajout de "À la une" avant la grille 2×2 et "Tendance" avant les 2 featured. Classe `.pcs-home__section-header` + `.pcs-home__section-title` (display font, bordure foreground).
- **⚠️ Renommage tags WP** (breaking — re-taguer les articles existants) :
  - `pcs-selection` (4 articles grille 2×2) → **`pcs-une`**
  - `pcs-une` (2 featured) → **`pcs-tendance`**
  - `pcs-hero` et `pcs-plus-lu` inchangés

### Pages article — layout 2 colonnes

- **Article élargi** : retrait du `max-width: 720px` historique. Désormais layout grid `1fr 300px` (article main + sidebar), avec `max-width: 1180px` global du container. L'article prend ~830px (vs 720px avant) et la sidebar 300px.
- **Sidebar pub sticky** : `position: sticky; top: 6rem` — affiche le slot `article-sidebar` (créé par le plugin pcs-banners v1.2.0).
- **Responsive < 900px** : sidebar passe sous l'article.

### ⚠️ Action requise sur le plugin pcs-banners
Mise à jour vers v1.2.0 obligatoire pour :
- Nouveau slot `article-sidebar` (pages article)
- Placeholder visuel quand un slot est vide (fond beige clair "PUBLICITÉ — Placez votre publicité ici")

## [2.5.2] — 2026-05-22

### Homepage — ajustements design

- **Héro réduit ~30%** : `aspect-ratio: 16/7 → 16/5`, `max-width: 1100px` (n'occupe plus toute la largeur), border-radius `sm`.
- **Titre héro centré** : `text-align: center` sur `.pcs-home__hero-caption`, `margin: 0 auto` sur le titre.
- **Newsletter fond vert** : `background: var(--wp--preset--color--accent)` (#1f3a2e). Texte clair, input clair, bouton accent-secondary (laiton). Override des couleurs pour fond vert (eyebrow, lead, consent).
- **Wording newsletter** : "GUIDE GRATUIT" → "NEWSLETTER", nouveau titre tendances/astuces 2×/semaine, nouveau lead, "Recevoir le guide" → "Recevoir la newsletter", consent simplifié.

### ⚠️ Action requise sur le plugin pcs-banners
Le plugin doit être mis à jour vers v1.1.0 pour enregistrer les 7 nouveaux slots (homepage-top, homepage-sidebar, cat-sidebar-{decoration,travaux,jardin,architecture,lifestyle}). Sans ça, les sidebars/bannières des sections homepage ne peuvent pas être créées depuis l'admin.

## [2.5.1] — 2026-05-22

### Hotfix critique

- **Erreur fatale PHP sur front-page.php** : chaîne `'J'accepte…Plus c'est simple.'` contenait deux apostrophes non échappées dans une chaîne entre apostrophes → parse error fatal → "Erreur critique sur ce site". Fix : passage en chaîne double `"J'accepte…"`.

## [2.5.0] — 2026-05-22

### Nouvelle page d'accueil (front-page.php)

Refonte complète du template PHP. Plus de Gutenberg/`the_content()` sur la homepage — layout 100% PHP dynamique.

**Structure :**
1. Bannière pub top (slot `homepage-top`, conditionnelle si vide)
2. Héro pleine largeur — dernier article tagué `pcs-hero`
3. Grille 2×2 + sidebar pub (slot `homepage-sidebar`) — 4 articles tagués `pcs-selection`
4. Deux featured (2/3 + 1/3) — tagués `pcs-une` (1er = grand, 2e = petit)
5. Section newsletter (HTML direct, plus de Gutenberg pattern)
6. Les + lus (9 max) — tagués `pcs-plus-lu`
7. 5 sections catégorie (Décoration, Travaux, Jardin, Architecture, Lifestyle) — automatiques (derniers articles) avec sidebar pub `cat-sidebar-{slug}`

**Tags WP à utiliser dans l'éditeur d'article :**
- `pcs-hero` → article héro (1 max)
- `pcs-selection` → 4 articles grille 2×2
- `pcs-une` → 2 featured (publiés du plus récent au plus ancien)
- `pcs-plus-lu` → jusqu'à 9 articles "Les + lus"

**CSS :** +300 lignes `.pcs-home__*` — full responsive (900px → 1 colonne, 600px → grilles 1 colonne)

## [2.4.5] — 2026-05-22

### Fixes & features

- **Double chevron menu (cause racine définitive)** : la règle `::after` chevron (`▾`) ne redéfinissait pas `position`, `left`, `right`, `bottom`, `height`, `background` hérités de la règle underline animé → le `▾` était rendu deux fois (inline + position:absolute). Fix : ajout des resets explicites `position: static; left: auto; right: auto; bottom: auto; height: auto; background: none;` dans la règle chevron.
- **Formulaire newsletter — champ email gigantesque** : la règle `.pcs-content form { flex-direction: column }` (from front-page.php wrapper `.pcs-content`) écrasait le layout row du `.pcs-newsletter-form`. Fix : surcharge explicite `.pcs-content .pcs-newsletter-form { flex-direction: row; max-width: none }` + reset `display/width` sur l'input email.
- **Section newsletter — pas de padding-top** : la règle `.pcs-section + .pcs-section { padding-block-start: 0 }` annulait le padding. Fix : `padding-block: clamp(4rem, 8vw, 7rem) !important` directement sur `.pcs-section--newsletter`.
- **Footer — réseaux sociaux Pinterest & Facebook** : nouveau menu location `social` (Apparence → Menus → "Réseaux sociaux (footer)"). Icônes SVG auto-détectées depuis le domaine de l'URL. Instagram aussi supporté. CSS : boutons ronds avec hover accent.

## [2.4.4] — 2026-05-22

### Fixes

- **Double chevron menu (fix définitif)** : la règle générique `.pcs-nav .menu-item-has-children > a::before` (triangle CSS) était en conflit de spécificité avec l'override `!important` de v2.4.3. Correction propre : sélecteur restreint à `.pcs-nav:not(.pcs-nav--primary)` — le `::before` triangle n'est **jamais** activé sur la nav primaire, éliminant tout conflit de cascade.
- **Espace intro → "À la une" réduit** : `.pcs-banner-wrap--category-intro { margin-block: 0.5rem }` — le slot bannière vide générait `margin-block: 2rem` même sans contenu.
- **Cards partenaires — badge chevauche le nom** : `padding-top: 1.5rem → 2.5rem` sur `.pcs-partner-card` — le badge `position: absolute; top: 0.5rem` ne déborde plus sur le H3.

## [2.4.3] — 2026-05-22

### Fixes

- **Double chevron menu (définitif)** : la cause racine était un conflit entre deux règles CSS — le `::before` triangle CSS (règle générique `.pcs-nav .menu-item-has-children > a::before`) ET le `::after` caractère `▾` (règle spécifique `.pcs-nav--primary`). Correction : ajout de `.pcs-nav--primary .menu-item-has-children > a::before { display: none !important; content: none !important; }` pour supprimer le triangle dupliqué. Un seul chevron désormais.
- **Espacement intro → "À la une" réduit de moitié** : ajout de `.pcs-section--cat-top { padding-block-start: clamp(1.5rem, 3vw, 2.5rem) !important; }` (était `clamp(3rem, 6vw, 5rem)` hérité de `.pcs-section`).

## [2.4.2] — 2026-05-22

### Fixes 4 retours user
- **Double chevron menu** : passage de `visibility:hidden` à `display:none` sur `.sub-menu`. Quand caché, le sub-menu n'occupe AUCUN espace DOM → aucun fantôme rendu sous l'item parent. Réapparition au hover via `display:block`. Animation fade retirée (compromis acceptable pour la lisibilité)
- **Partenaires : double nom (logo + h3)** : masquage du `.pcs-partner-card__logo` (le texte logo dupliquait le nom dans 99% des cas). Le H3 nom suffit. Pour remettre un vrai logo image, utiliser `wp:image` dans le pattern et override cette règle
- **Sous-sections trop espacées** : règle spécifique `.pcs-section--subcat` avec padding plus serré (1.25-2rem au lieu des 3-4.5rem générique). Reset margin-top du titre interne pour éviter cumul
- **« À la une » : trop d'articles en live** : RAPPEL — le pattern v2.4.0+ a deux Query Loops séparés (`perPage:3` pour À la une et `perPage:12` pour Tous les articles). Si l'utilisateur édite manuellement le perPage de « À la une » à 1 et que ça ne tient pas en live, c'est que **la page n'a pas été reset depuis v2.4.0** — utiliser Outils → PCS Init content → Reset des pages seedées

## [2.4.1] — 2026-05-22

### Fixes retours user
- **Double chevron menu** : limitation stricte du `::after` au PREMIER niveau de menu uniquement (sélecteurs `> .pcs-nav__list >` et `> ul >`). Sous-menus déforcés (`content: none !important`). Plus de chevron fantôme sous les items parents
- **Cards articles trop espacées** : `.pcs-card` gap réduit de 0.75rem à 0.4rem. Espacement image/eyebrow/titre/date resserré (marges explicites)
- **5 cartes piliers sur 2 lignes** : forçage 5 colonnes dès 900px (au lieu de 1024px), gap réduit, `!important` sur grid-template-columns pour overrider toute règle WP. Aussi padding section ajusté
- **Sections fond — padding-top trop faible** : règles ciblées avec `padding-top: clamp(3rem, 6vw, 4.5rem) !important` sur `.pcs-section--manifesto`, `.pcs-partners`, `.pcs-section--maillage`, `.pcs-section--directory`. Ajout reset margin-top sur premier enfant pour éviter cumul

### Note importante pour le "À la une" trop d'articles
Le pattern category-X v2.4.0 a bien `perPage:3` pour la section "À la une" et `perPage:12 offset:3` pour "Tous les articles". Si tu vois trop d'articles dans la première section, c'est probablement que **la page n'a pas été Reset depuis v2.4.0** — elle utilise encore l'ancien pattern (1 seul Query Loop de 9 articles sans séparation).
→ **Outils → PCS Init content → Reset des pages seedées** doit être déclenché après chaque upload pour récupérer la nouvelle structure.

## [2.4.0] — 2026-05-22

### Nouvelle structure des pages catégories
Validation visuelle du user, ordre final :
1. Intro éditoriale
2. Bannière category-intro
3. « À la une » — 3 premiers articles (Query Loop #11, offset 0)
4. Bouton « Voir tous les articles → » qui scroll vers ancre `#all-articles`
5. Sous-section 1 (texte + lien partenaire + bouton CTA vers sous-cat)
6. Bannière category-mid
7. Sous-section 2 (idem)
8. Sous-section 3 (s'il y en a)
9. « Tous les articles » avec ancre `#all-articles` — 12 articles (offset 3 pour éviter doublon avec le top)
10. FAQ + Schema JSON-LD
11. Maillage « Voir aussi »
12. Section partenaires
13. **Disclosure partenaires DÉPLACÉ tout en bas** (était en haut avant)

### Nouvelle nomenclature des sous-catégories
- **Décoration** : Par pièce, Styles, Petits budgets
- **Travaux** : Par pièce, Gros œuvre, Rénovation énergétique
- **Jardin** : Aménagement extérieur, Entretien (de 4 sous-cat à 2)
- **Architecture** : Styles & époques, Extensions (de 3 à 2, Rénovation du patrimoine retirée)
- **Immobilier** : Acheter, Louer & investir, Vendre (inchangé)
- **Lifestyle** : Bien-être & accessoires, Rangement & nettoyage (Recevoir retirée)

### Cards Query Loop simplifiées
- Plus de `wp:post-excerpt` dans les Query Loops des pages catégories — juste image, catégorie (eyebrow), titre, date

### CSS
- **Double chevron menu fixé** : masquage des icônes natives WP (`.wp-block-navigation__submenu-icon`, `> svg`, `button.wp-block-navigation-submenu__toggle`) — notre chevron `▾` via `::after` reste l'unique source
- **5 cartes piliers sur 1 ligne** : grid 5 colonnes forcé sur desktop, H3 réduit à `font-size:lg`, gap resserré
- **Padding sections fond** : règle générique `.pcs-section.has-background { padding-block: clamp(2.5rem, 5vw, 4rem) }` — fix l'effet "collé en haut" sur manifeste, partenaires, directory teaser
- **Section partenaires** : padding internal augmenté + margin top resserré

## [2.3.0] — 2026-05-22

### Refactor structurel pages catégories — sections = sous-catégories WordPress

**Le malentendu corrigé** : les sections H2 des pages pilier n'étaient pas des "mini-articles" mais des **vraies sous-catégories WP** (Pièces, Styles, Petit budget pour Décoration, etc.). Chaque section présente désormais brièvement sa sous-cat avec liens partenaires intégrés + bouton CTA vers la sous-catégorie WP.

#### 6 patterns category-{pilier} refactorisés
- Sections H2 : **20 sous-catégories** au total (3-4 par pilier) avec H2 = nom sous-cat, lead 60-90 mots avec 1-2 liens partenaires in-text, bouton CTA `is-style-outline` vers `/<slug-sous-cat>-cat/`
- Plus de listes fictives de titres d'articles à créer
- Section maillage : `align="wide"` + grille 2-3 colonnes avec espacement
- Section Query Loop "Tous les articles" : `display:grid` forcé en 3 colonnes (2 sur tablette, 1 sur mobile), `perPage:9` conservé

#### Filter PHP Query Loop catégorie
- `inc/category-query-filter.php` : hook `query_loop_block_query_vars` qui détecte les pages pilier (slug ∈ decoration/travaux/jardin/architecture/immobilier/lifestyle) et injecte automatiquement un `tax_query` ciblant la catégorie `<slug>-cat`. Plus besoin de connaître le term ID dans le pattern. Compatible WP 7.0+.

#### Menu dropdown desktop
- Sous-menus cachés par défaut (`opacity:0`, `visibility:hidden`)
- Visibles au `:hover` ou `:focus-within` du parent avec animation fade + slide
- Chevron `▾` ajouté après les items avec sous-menu (rotation au hover)
- Bordure + ombre + background sur le sous-menu (carte flottante)
- Mobile : pas de dropdown, sous-menus inline dans l'overlay (comportement WP standard)

#### Formulaires
- Styles génériques `.pcs-content form` et `.pcs-page__content form` pour tout `<form>` brut dans le contenu (Contact, formulaires inline) — input/label/textarea/submit alignés en colonne, focus accent

## [2.2.1] — 2026-05-21

### Fixes retours user
- **Formulaire Contact moche** : ajout de styles génériques `.pcs-content form` et `.pcs-page__content form` (input + label + textarea + button submit en colonne, padding cohérent, focus accent). Tout form HTML brut dans le contenu Gutenberg est désormais stylé automatiquement, plus besoin de classe dédiée
- **Pattern section-pillars** : passage de 6 à 5 cartes (Immobilier retiré), titre H2 « Cinq terrains, une méthode », layout grid avec minimumColumnWidth pour rendu auto sur les écrans
- **Pattern section-pourquoi** : contentSize passé de 760 à 1080px pour respirer plus en largeur (le manifeste reste centré mais sur 1080 au lieu de 760)
- **Ordre __ACCUEIL__** : section-pourquoi remontée avant section-pillars dans la liste auto-seed (manifeste avant les piliers, plus narratif)

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
