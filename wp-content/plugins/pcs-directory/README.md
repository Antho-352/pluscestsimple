# Annuaire — pluscestsimple

Plugin WordPress qui construit un annuaire national des magasins déco/maison pour `pluscestsimple.com`.

Démarrage : 3 codes NAF (`47.59A` meubles, `47.59B` autres équipements du foyer, `47.53Z` tapis/sols/moquettes). Architecture extensible à d'autres niches plus tard (bricolage, jardin, etc. — il suffit d'ajouter des codes NAF et de créer les types correspondants).

## Pile technique

- CPT `pcs_etablissement` (slug `/annuaire/etablissement/%name%`, public, REST)
- Taxonomies : `pcs_etab_type` (10 termes seedés), `pcs_etab_region` (13 régions FR + 5 DROM seedées), `pcs_etab_ville` (créée dynamiquement)
- 14 meta keys `_pcs_etab_*` (SIRET, adresse, geo, contact, sources, etc.)
- Pipeline d'import 3 sources : **Sirene** (Recherche Entreprises) + **BAN** (géocodage) + **OSM/Overpass** (enrichissement optionnel)
- Validation humaine obligatoire : tout import passe en `draft`, jamais `publish` auto
- Cron quotidien `pcs_directory_refresh` : re-check SIRET, repasse en draft si drift, trash si cessé
- Schema.org `LocalBusiness` JSON-LD + meta title/description natifs (pas de dépendance Yoast/RankMath)

## Sources de données

| Source | URL | Auth | Usage |
|---|---|---|---|
| Recherche Entreprises (Sirene) | `recherche-entreprises.api.gouv.fr/search` | Non | Liste des établissements actifs par NAF + département |
| Base Adresse Nationale (BAN) | `api-adresse.data.gouv.fr/search/` | Non | Géocodage adresse → lat/lng |
| OpenStreetMap (Overpass) | `overpass-api.de/api/interpreter` | Non | Enrichissement téléphone / site / horaires |

**Aucune source payante** (pas de Google Places).

**Attribution OSM OBLIGATOIRE** : rendue automatiquement dans le footer de chaque fiche établissement (`single-pcs_etablissement.php`).

## Installation

1. Uploader `pcs-directory.zip` via `Extensions > Ajouter > Téléverser`
2. Activer le plugin
3. À l'activation : taxonomies seedées (types + régions), cron quotidien planifié, permaliens flushés. **Aucun import déclenché**.

## Lancer un premier import

1. Aller dans `Annuaire > Importer depuis Sirene`
2. Paramètres recommandés pour démarrer :
   - **Code(s) NAF** : `47.59A,47.59B,47.53Z` (défaut)
   - **Département** : `75` (Paris, pour test rapide)
   - **Nombre maximum** : `50`
   - **Enrichir via OSM** : cocher (recommandé)
3. Cliquer **Lancer l'import**. Durée estimée : ~2-3 min (rate-limit 1 req/s).
4. À la fin, l'écran affiche les compteurs (importés / ignorés / erreurs) et les 20 derniers imports.

URL admin pour lancer un import :
```
/wp-admin/edit.php?post_type=pcs_etablissement&page=pcs-directory-import
```

## Valider les imports

1. Aller dans `Annuaire > À valider` (badge avec compteur de brouillons)
2. Pour chaque ligne :
   - Vérifier le nom (éditable inline)
   - Choisir le type via le select
   - Cocher "Featured" si on veut la mettre en avant
   - Cliquer "Éditer" pour ajouter une photo de devanture + un éditorial libre
3. Cocher les lignes prêtes → **Bulk action** :
   - `Valider et publier` → passe en `publish`, met à jour `last_verified`
   - `Rejeter (corbeille)` → trash

URL admin :
```
/wp-admin/edit.php?post_type=pcs_etablissement&page=pcs-directory-validate
```

## Structure d'un établissement importé

Exemple de meta après import Sirene + BAN + OSM :

```
post_title       : "Conforama Paris République"
post_status      : "draft"
post_excerpt     : "Conforama Paris République — magasin situé à Paris."

_pcs_etab_siret         : "33223300100123"
_pcs_etab_adresse       : "57 Boulevard de la Villette"
_pcs_etab_code_postal   : "75010"
_pcs_etab_ville         : "Paris"
_pcs_etab_region        : "ile-de-france"
_pcs_etab_lat           : 48.876034
_pcs_etab_lng           : 2.367412
_pcs_etab_telephone     : "+33 1 42 39 12 34"      (OSM)
_pcs_etab_site_web      : "https://www.conforama.fr"  (OSM)
_pcs_etab_horaires_text : "Mo-Sa 10:00-20:00"         (OSM, format OSM brut)
_pcs_etab_is_featured   : "0"
_pcs_etab_sources       : ["sirene", "ban", "osm"]
_pcs_etab_last_verified : "2026-05-21"
_pcs_etab_naf_code      : "47.59A"

Taxonomies :
  pcs_etab_region : ["ile-de-france"]
  pcs_etab_ville  : ["paris"]
  pcs_etab_type   : []   (à classer manuellement par le validateur)
```

## Cron quotidien

Tâche `pcs_directory_refresh` planifiée en `daily`.

À chaque tick : récupère les 20 établissements publiés les plus anciens (`last_verified` ASC), re-check leur SIRET contre Sirene :
- **SIRET introuvable ou cessé** → trash automatique
- **Diff sur nom / adresse / CP / ville** → repasse en `draft` (validation manuelle requise)
- **Pas de drift** → met simplement à jour `last_verified`

Vérifier que WP-Cron tourne (sinon installer un cron système qui hit `wp-cron.php`).

## Shortcode

```
[pcs_directory type="meubles" region="ile-de-france" limit="9" filters="yes"]
```

Tous les attributs sont optionnels. `filters="no"` masque la barre de filtres.

## Customisation

- **Carte interactive** : le plugin émet un placeholder `<div class="pcs-directory-map" data-lat data-lng>`. Pour activer Leaflet : ajouter Leaflet CSS/JS via wp_enqueue (CDN ou self-hosted), puis appeler `window.pcsDirectoryActivateMap(el)` (déjà défini dans `assets/js/directory.js`).
- **Types par défaut** : modifier `pcs_directory_seed_default_types()` dans `inc/taxonomies.php` puis désactiver/réactiver le plugin pour seeder de nouveaux termes.
- **Mapping NAF → Type** : v2, en option. Actuellement, le validateur humain classe.

## Conventions

- Préfixe fonctions : `pcs_directory_*`
- Préfixe meta keys : `_pcs_etab_*`
- Préfixe constantes : `PCS_DIR_*`
- Préfixe CSS : `.pcs-directory-*`
- Text domain : `pluscestsimple`
- Commentaires : français

## Fichiers

```
pcs-directory/
  pcs-directory.php       Bootstrap (constantes, requires, hooks activation/deactivation)
  README.md
  CHANGELOG.md
  package.sh              Genere ../pcs-directory.zip
  inc/
    cpt.php               CPT + 14 meta + meta box + colonnes admin
    taxonomies.php        3 taxos + seed types + seed regions + mapping dept->region
    admin-import.php      Page Annuaire > Importer depuis Sirene
    admin-validate.php    Page Annuaire > A valider + bulk actions
    import-sirene.php     wp_remote_get API Recherche Entreprises
    import-ban.php        wp_remote_get API BAN
    import-osm.php        wp_remote_post Overpass
    import-runner.php     Orchestrateur (dedup SIRET, geocode, enrich, insert draft)
    render.php            Helpers cards + filtres + filter main query
    shortcode.php         [pcs_directory]
    cron.php              Cron daily refresh
    seo.php               JSON-LD LocalBusiness + title + meta description
  templates/
    archive-pcs_etablissement.php
    single-pcs_etablissement.php
  assets/
    css/directory.css
    js/directory.js
```

## Rate-limits

- Sirene : 7 req/s recommandé. On respecte avec `sleep(1)` entre établissements.
- BAN : 50 req/s. Très en dessous.
- Overpass : 2 requêtes lourdes simultanées max/IP. `sleep(1)` entre appels + timeout court (15-20s).

Pause de 5s entre batches de 50 établissements (configurable dans `inc/import-runner.php`).

## Légal

- **Données Sirene** : open data, licence ouverte 2.0 (data.gouv.fr).
- **Données BAN** : ODbL.
- **Données OSM** : ODbL — attribution obligatoire, rendue dans le footer de chaque fiche.
- **Photos** : non importées automatiquement (à uploader manuellement en validation).
