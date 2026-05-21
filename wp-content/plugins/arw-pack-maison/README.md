# ARW Pack — Maison

Plugin d'extension du thème **ARW Pulse** pour les sites maison / travaux / déco (premier site cible : pluscestsimple.com).

Ajoute :

- **Compatibilimètre** — outil signature : règles "puis-je faire X dans Y ?" avec verdict (compatible / sous conditions / déconseillé / interdit), explication, alternatives, refs DTU/normes.
- CPT `arw_compat_rule` + taxonomy `arw_compat_category` (sols, murs, cuisine, structure, etc.).
- Import CSV bulk pour entrer 80-100 règles d'un coup.
- Génération automatique d'un index JSON statique (servi au front pour recherche fuzzy zéro requête).
- (à venir Phase 3) page `/compatibilimetre/` avec recherche, page auto-générée par règle (long-tail SEO).
- (à venir Phase 4) lead magnet — capture email après 3 consultations.
- (à venir Phase 5) sync Brevo — push API contact à chaque submit.
- (à venir Phase 6) home pattern via filtre `arw_pulse_front_page_markup`.

## État (v0.1.0 — Phase 2)

- ✓ Plugin bootstrap
- ✓ CPT + meta box
- ✓ Taxonomy + termes par défaut
- ✓ Admin list columns + filtre par verdict
- ✓ Import CSV bulk (avec dry-run)
- ✓ Index JSON régénéré au save / delete / set_terms
- ✓ Page admin "Index JSON" (rebuild manuel + URL + taille)

## Installation

1. `./package.sh` → génère `../arw-pack-maison.zip`
2. WP admin → Plugins → Ajouter → Téléverser → `arw-pack-maison.zip` → Activer
3. Vérifier : menu **ARW Pulse → Compatibilimètre** apparaît
4. Vérifier : menu **ARW Pulse → Catégories** liste les 10 termes par défaut
5. Importer un CSV via **ARW Pulse → Import CSV** (template ci-dessous)

## CSV — format

```
title;category_slug;verdict;explanation;alternatives;keywords;dtu_refs
"Parquet massif sur plancher chauffant ?";"sols";"conditional";"Possible si essence stable…";"Stratifié compatible PCBT…";"parquet, plancher chauffant";"DTU 51.2"
```

Verdicts acceptés : `compatible`, `conditional`, `discouraged`, `forbidden`.

Catégories par défaut : `sols`, `murs`, `cloisons`, `sdb`, `cuisine`, `structure`, `exterieur`, `chauffage`, `electricite`, `plomberie` — créées automatiquement si manquantes.

## Architecture

Structure conforme au pattern documenté dans `arw-pulse/docs/` :

```
arw-pack-maison/
├── arw-pack-maison.php       # Plugin header + bootstrap + activation
├── package.sh                # Zip → ../arw-pack-maison.zip
├── inc/
│   ├── taxonomies.php        # arw_compat_category + termes par défaut
│   ├── cpt-compat-rule.php   # CPT + meta box + admin columns + filtre verdict
│   ├── csv-import.php        # Page admin "Import CSV"
│   └── json-export.php       # Index JSON statique + page admin "Index JSON"
└── (Phase 3+) patterns/, templates/, assets/
```

Le pack respecte les règles d'intégration thème ↔ pack :
- Aucun `require_once` du thème (guards `defined()` partout).
- `show_in_menu` utilise `ARW_PULSE_ADMIN_SLUG` si défini, fallback sinon.
- Aucune modification du thème pour ce site.
