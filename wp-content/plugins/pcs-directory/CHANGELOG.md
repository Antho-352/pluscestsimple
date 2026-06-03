# Changelog — Annuaire pluscestsimple

## 1.1.0 — 2026-06-03

### Fix — import cassé (HTTP 400)
- L'API Recherche Entreprises plafonne `per_page` à 25. Le code envoyait 50 → `HTTP 400`. Corrigé dans `import-sirene.php` (cap 25) et `import-runner.php` (`$per_page = 25`).
- Le message d'erreur API (champ `erreur`) est désormais remonté dans le détail au lieu d'un « HTTP 400 » aveugle.

### Feat — Import national complet (asynchrone, résumable)
- Nouveau module `inc/import-national.php`. Contourne le double plafond de l'API (`per_page ≤ 25` ET `page × per_page ≤ 10 000`) en partitionnant la requête par département (101 partitions, chacune < 10 000 résultats).
- Machine à états persistée (`pcs_directory_national_state`) : reprise automatique après timeout/crash.
- Ticks bornés (~20 s) auto-propulsés via cron single-event + loopback admin-ajax non bloquant → tourne en tâche de fond sans dépendre du trafic.
- UI admin : bouton « Démarrer l'import national », barre de progression live (poll AJAX), pause/reprise/réinitialisation.
- Dédup globale par SIRET, tout en `draft` (validation humaine inchangée).
- L'ancien import synchrone devient « Import ciblé (test qualité) ».

## 1.0.0 — 2026-05-21

Version initiale.

### CPT & taxonomies
- CPT `pcs_etablissement` (slug `/annuaire/etablissement/%name%`), public, REST.
- 14 meta keys `_pcs_etab_*` (siret, adresse, code_postal, ville, region, lat, lng, telephone, site_web, horaires_text, is_featured, sources, last_verified, naf_code).
- Taxonomie `pcs_etab_type` (hierarchical) — 10 termes seedés à l'activation : Meubles, Décoration, Cuisine équipée, Salle de bain, Outillage & bricolage, Jardin & extérieur, Luminaires, Textile maison, Tapis & sols, Cadeaux & objets déco.
- Taxonomie `pcs_etab_region` (hierarchical) — 13 régions métropolitaines + 5 DROM seedées.
- Taxonomie `pcs_etab_ville` (non-hierarchical) — créée dynamiquement à l'import.
- Mapping département (2 chiffres) → slug région inclus pour rattacher chaque établissement à sa région sans appel API supplémentaire.

### Pipeline d'import
- `pcs_directory_import_sirene()` — GET `recherche-entreprises.api.gouv.fr/search` (pas d'auth).
- `pcs_directory_geocode_ban()` — GET `api-adresse.data.gouv.fr/search/` (pas d'auth).
- `pcs_directory_enrich_osm()` — POST `overpass-api.de/api/interpreter` (Overpass QL, pas d'auth).
- `pcs_directory_run_import()` — orchestrateur : dédup SIRET, géocode si manquant, enrichissement OSM optionnel, `wp_insert_post( post_status='draft' )` avec metas + taxos.
- Rate-limit `sleep(1)` entre appels API, pause 5s entre batches de 50.
- Statut import = `draft` systématiquement. Validation humaine obligatoire.
- Log des 20 dernières exécutions dans option `pcs_directory_import_log`.

### Admin
- Page `Annuaire > Importer depuis Sirene` (`admin-import.php`) : formulaire NAF + département + max + checkbox enrichissement OSM, affichage log.
- Page `Annuaire > À valider` (`admin-validate.php`) : liste drafts, édition rapide inline (titre + type + featured), bulk actions "Valider et publier" / "Rejeter".
- Badge compteur de brouillons dans le menu.
- Colonnes admin custom dans la liste des établissements : SIRET, Ville, Sources, Featured.
- Meta box de 14 champs sur l'écran d'édition.

### Cron
- Tâche `pcs_directory_refresh` planifiée daily à l'activation.
- À chaque tick : re-vérifie 20 établissements publiés (les plus anciens par `last_verified`).
- Drift sur nom/adresse/CP/ville → repasse en draft.
- SIRET introuvable ou état != "A" (cessé) → trash.

### Frontend
- Template archive : grille filtrable (type/région/ville) + pagination + tri (récents/alpha). Mobile = liste compacte (image gauche, texte droite).
- Template single : header avec badges (type + featured) + adresse + carte placeholder + contact + horaires + sources + dernière vérification + attribution OSM.
- Carte = `<div class="pcs-directory-map" data-lat data-lng>` (placeholder Leaflet, activable via `window.pcsDirectoryActivateMap()`).
- Filtres : taxonomies dans `pre_get_posts` + tri secondaire avec featured en tête.

### SEO
- JSON-LD `LocalBusiness` émis dans `wp_head` sur les single publish (name, url, address, geo, telephone, sameAs, openingHours, image).
- `<title>` custom : `<nom> — <type> à <ville> — Annuaire pluscestsimple`.
- Meta description : excerpt + type + ville (160 chars max).

### Shortcode
- `[pcs_directory type="" region="" ville="" limit="12" filters="yes"]`.

### Assets
- `assets/css/directory.css` : grille responsive, cards, fiche, filtres, pagination, badges featured/type, map placeholder. ~370 lignes.
- `assets/js/directory.js` : toggle filtres mobile + hook activation Leaflet. ~50 lignes, vanilla, pas de dépendance.

### Attribution & légal
- Attribution OSM obligatoire dans le footer de chaque fiche single.
- Pas de Google Places (payant) : exclusivement sources publiques gratuites.
