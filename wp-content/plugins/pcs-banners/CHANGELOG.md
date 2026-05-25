# Changelog — pcs-banners

## 1.3.0 — 2026-05-25

### Mode d'affichage par slot (3 états)

Chaque slot a maintenant un **mode d'affichage** configurable depuis l'admin (`Bannières → Emplacements → cliquer sur un slot`). Trois choix :

- **Auto** (défaut) : pub si disponible, sinon placeholder SVG
- **Pub uniquement** : pub si disponible, sinon rien (le thème collapse le layout pour ne pas laisser de vide)
- **Désactivé** : jamais rien, layout collapsé en permanence

API publique :
- `pcs_banner_slot_mode( $slot )` → `'auto'|'banner-only'|'hidden'`
- `pcs_banner_slot_will_render( $slot )` → `bool` (à appeler depuis le thème pour savoir s'il faut garder l'espace dans le layout)

### Placeholders SVG officiels

Les 4 SVG de placeholder (`300x250`, `300x600`, `728x90`, `970x250`) sont déposés dans `assets/placeholders/` et servis automatiquement selon le format IAB du slot. Le HTML est désormais une simple `<img>` (au lieu d'un div stylé), garantissant le bon ratio et le bon rendu sur tous les écrans.

Mapping format ↔ slot (hardcodé dans `pcs_banner_slot_format()`) :
- `*-sidebar` (homepage, article, cat-*) → 300×600
- `homepage-top`, `homepage-mid`, `category-mid` → 970×250
- `category-intro` → 728×90
- `in-article` → 300×250

### Admin — colonne "Mode d'affichage"

La liste des slots (`Bannières → Emplacements`) affiche maintenant le mode en cours pour chaque slot.

### Rétro-compatibilité
`pcs_banner_render( $slot, $with_placeholder = false )` accepte toujours le second argument (sans effet en mode `auto`).

## 1.2.0 — 2026-05-22

### Features

- **Placeholder optionnel pour slots vides** : `pcs_banner_render($slot, $with_placeholder = true)` retourne un bloc visuel discret style "Marie Claire" (fond beige clair, "PUBLICITÉ" + "Placez votre publicité ici") quand aucune bannière n'est trouvée. Permet aux sidebars de la homepage et des articles de garder leur emprise visuelle même sans contenu pub.
- **Nouveau slot `article-sidebar`** : sidebar verticale présente sur toutes les pages article single (thème pluscestsimple v2.6.0+).

### Comportement par défaut inchangé
`pcs_banner_render($slot)` sans second argument retourne toujours chaîne vide quand aucune bannière (rétro-compatibilité totale avec les patterns existants).

## 1.1.0 — 2026-05-22

### Nouveaux slots seedés (homepage thème pluscestsimple v2.5.x)

7 nouveaux termes ajoutés au seed automatique (idempotent — création au prochain `init` si absents) :

- `homepage-top` — bannière full-width au-dessus du héro
- `homepage-sidebar` — sidebar verticale à droite de la grille 2×2
- `cat-sidebar-decoration`, `cat-sidebar-travaux`, `cat-sidebar-jardin`, `cat-sidebar-architecture`, `cat-sidebar-lifestyle` — sidebars verticales des 5 sections catégorie de la homepage

Pas besoin de désactiver/réactiver le plugin : le `add_action('init', 'pcs_banner_seed_default_slots', 11)` les créera automatiquement au premier chargement après la mise à jour.

## 1.0.0 — 2026-05-21

Première release.

- CPT `pcs_banner` avec meta box (URL cible, type, libellé, dates start/end)
- Taxonomy `pcs_banner_slot` (non hiérarchique) + 4 termes seedés par défaut
  (`homepage-mid`, `category-intro`, `category-mid`, `in-article`)
- Bloc Gutenberg server-rendered `pcs/banner-slot` (zéro build, JS inline)
- Shortcode `[pcs_banner slot="..."]`
- Fonction publique `pcs_banner_render( $slot )` exploitable depuis le thème
- Sélection : random pick parmi les bannières actives à l'instant T
- Cache transient 5 min par slot, invalidation auto sur `save_post` / `delete_post`
- Auto `rel="sponsored nofollow noopener"` pour les types `sponsored` et `affilie`
- Étiquette auto au-dessus de l'image : "Sponsorisé", "En partenariat"
  (override via meta `_pcs_banner_label`, type `display` → aucune étiquette par défaut)
- Liste admin enrichie : aperçu 60x40, badges colorés (type, statut temporel),
  URL cliquable, colonnes triables, filtres type/slot/statut
- CSS conditionnel (~50 lignes) — enqueue uniquement si bannière rendue,
  détection via `has_block` + `has_shortcode`, fallback inline footer
- Image `loading="lazy"` + `decoding="async"`
- Hooks d'activation / désactivation (seed des slots + flush cache + flush rewrite)
