# Bannières — pluscestsimple

Plugin WordPress de gestion des bannières publicitaires pour
**pluscestsimple.com**. Pensé pour le thème classique PHP `pluscestsimple`
(v2.0.2+). Compatible avec n'importe quel thème, mais le rendu est calibré
pour celui-là.

Trois types pris en charge : **Display** (publicité classique), **Sponsorisé**,
**Affilié / Partenariat**. Les types non-display reçoivent automatiquement
le `rel="sponsored nofollow"` et une étiquette éditoriale au-dessus de l'image.

## Installation

1. `./package.sh` (depuis `wp-content/plugins/pcs-banners/`) génère
   `wp-content/plugins/pcs-banners.zip`.
2. Dans WP : **Extensions → Ajouter → Téléverser → pcs-banners.zip → Activer**.
3. Aller dans **Bannières** (menu admin, icône mégaphone) pour créer ta
   première bannière.

À l'activation, les 4 emplacements (slots) par défaut sont créés :

| Slug             | Nom                          | Usage                                |
| ---------------- | ---------------------------- | ------------------------------------ |
| `homepage-mid`   | Accueil — milieu de page     | Accueil, entre deux sections         |
| `category-intro` | Catégorie — après intro      | Page catégorie, après l'intro        |
| `category-mid`   | Catégorie — milieu           | Page catégorie, au milieu            |
| `in-article`     | Article — in-text            | Article, in-text                     |

Tu peux en ajouter d'autres depuis **Bannières → Emplacements**.

## Créer une bannière

**Bannières → Ajouter** :

- **Titre interne** : usage admin uniquement (pas affiché front).
- **Image** : via *Image mise en avant*. Voir formats recommandés ci-dessous.
- **URL cible** : URL ouverte au clic (nouvelle fenêtre).
- **Type** : `Display` / `Sponsorisé` / `Affilié`.
- **Libellé** (optionnel) : surcharge le libellé auto. Laisse vide pour utiliser
  `Sponsorisé` / `En partenariat` selon le type. Type `display` → aucune
  étiquette par défaut.
- **Période** : dates start / end (datetime-local). Laisser vide = pas de
  contrainte de date.
- **Emplacement (taxonomy `pcs_banner_slot`)** : coche un ou plusieurs slots.

## Utilisation côté éditeur

### Bloc Gutenberg

Insérer le bloc **Emplacement bannière** (catégorie *Design*). Dans le panneau
Inspector, choisir le slot dans le dropdown. Aperçu live en édition via
`ServerSideRender`. Le rendu front est server-side.

### Shortcode (patterns, content legacy, widgets)

```text
[pcs_banner slot="homepage-mid"]
```

### Appel direct depuis le thème

```php
if ( function_exists( 'pcs_banner_render' ) ) {
    echo pcs_banner_render( 'category-mid' );
}
```

## Sélection de la bannière à afficher

Pour un slot donné :

1. Lecture du cache transient `pcs_banner_<slot>` (TTL **5 min**).
2. Sinon : query du CPT `pcs_banner`, status `publish`, term du slot.
3. Filtre PHP : `start_date <= now` (ou vide) ET `end_date >= now` (ou vide).
4. **Random pick** parmi les actifs (rotation simple en cas de plusieurs).
5. Mise en cache du résultat (5 min). Cache vidé sur `save_post_pcs_banner`
   et `delete_post`.

## Auto rel + étiquette

| Type      | `rel`                                | Étiquette par défaut |
| --------- | ------------------------------------ | -------------------- |
| display   | `noopener`                           | _(aucune)_           |
| sponsored | `sponsored nofollow noopener`        | `Sponsorisé`         |
| affilie   | `sponsored nofollow noopener`        | `En partenariat`     |

Override : le champ **Libellé** sur l'écran d'édition prend toujours le pas.

## Formats recommandés (info éditoriale)

| Emplacement          | Format        | Taille (px)   |
| -------------------- | ------------- | ------------- |
| Bandeau large (haut) | Billboard     | 970 × 250     |
| Bandeau desktop      | Leaderboard   | 728 × 90      |
| Mobile               | Large mobile  | 320 × 100     |
| Encart in-article    | Rectangle     | 300 × 250     |

Le plugin n'impose aucune dimension : tu uploades n'importe quelle image,
le HTML respecte les attributs `width`/`height` réels. L'image est
servie en `loading="lazy"` + `decoding="async"`.

## Perf & cache

- **Cache transient 5 min** par slot. 1 seule query DB par cycle de cache.
- **CSS conditionnel** : enqueue uniquement si bannière rendue (~50 lignes).
- **0 JS front** (le bloc utilise du JS éditeur uniquement, pas d'asset front).
- **Invalidation** : automatique sur `save_post_pcs_banner` + `delete_post`.
- **Image lazy** + `decoding="async"`.

## Admin — liste des bannières

Colonnes :

- **Aperçu** (60×40, thumbnail)
- **Titre**
- **Type** (badge couleur)
- **Emplacement**
- **Période** (badge statut : Actif / Programmé / Expiré + plage start→end)
- **URL** (cliquable, ouvre nouvelle fenêtre)
- **Date**

Filtres dans la barre de tri : par type, par slot, par statut temporel.
Tri custom : titre (natif), période (sur `start_date`), type.

## Internationalisation

Text-domain : `pluscestsimple` (cohérent avec le thème). Strings en français
par défaut. Aucun fichier `.po`/`.mo` fourni dans cette release — les
chaînes passent par `__()` pour une éventuelle traduction future.

## Conventions

- Fonctions : `pcs_banner_*`
- Meta keys : `_pcs_banner_*`
- Constantes : `PCS_BANNER_*`
- CSS : `.pcs-banner-*` (BEM-light)
- CPT : `pcs_banner` — Taxonomy : `pcs_banner_slot`
- Bloc : `pcs/banner-slot` — Shortcode : `[pcs_banner]`

## Structure du plugin

```
pcs-banners/
├── pcs-banners.php          Header + bootstrap + load modules
├── README.md                Ce fichier
├── CHANGELOG.md
├── package.sh               Build du zip (nom fixe, overwrite)
├── block.json               Métadonnées du bloc pcs/banner-slot
├── inc/
│   ├── cpt.php              CPT pcs_banner + meta boxes + invalidation cache
│   ├── taxonomies.php       Taxonomy pcs_banner_slot + 4 termes seedés
│   ├── render.php           pcs_banner_render() + cache + sélection
│   ├── block.php            register_block_type + JS éditeur inline
│   ├── shortcode.php        [pcs_banner slot="..."]
│   ├── admin-list.php       Colonnes + filtres + tri admin
│   └── assets.php           Enqueue CSS conditionnel
└── assets/
    └── css/banners.css      Styles front (~50 lignes)
```

## Désinstallation

Désactiver le plugin via **Extensions → Désactiver**. Les bannières, les
emplacements et les meta restent en base (pas de purge destructive). Le cache
transient est vidé et les permalinks sont rechargés à la désactivation.
