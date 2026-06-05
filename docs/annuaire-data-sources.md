# Annuaire déco — sources de données gratuites (recherche)

> Recherche menée le 2026-06-03. Objectif : annuaire exhaustif des magasins déco/meubles/maison FR, données riches, budget 0 €, pas de Google Places.

## Stack recommandée (en couches)

| Couche | Source | Rôle | Champs | Manque |
|--------|--------|------|--------|--------|
| 1. Squelette | **SIRENE** (bulk CSV mensuel, data.gouv.fr) | Couverture nationale 100% légale | nom légal, SIRET/SIREN, adresse complète, coords Lambert93, tranche effectifs, NAF | tél, web, horaires |
| 2. Géo | **BAN** (api-adresse.data.gouv.fr) | Géocoder l'adresse → lat/lng WGS84 | lat/lng | — |
| 3. Contact | **OSM / Overpass** (dump Geofabrik France, ODbL) | **Seule source gratuite avec horaires** | phone, website, opening_hours, brand | remplissage ~30-40% urbain |
| 3bis. Contact+ | **Overture Maps Places** (Parquet S3 + DuckDB, CDLA-Permissive-2.0) | Web/tél additionnels (unifie Meta+Foursquare+AllThePlaces) | name, address, website, phone, category, confidence | **pas d'horaires** |
| 4. Enseignes | **Wikidata SPARQL** (CC0) | Grandes enseignes + site officiel | nom chaîne, site, logo | — |

**NAF déco retenus** : `4759A` (meubles), `4759B` (équipement foyer), `4752B` (bricolage/quincaillerie déco), `4778C` (autres commerces spécialisés — déco/cadeaux), `7410Z` (design/déco d'intérieur). À affiner.

## Décision clé : bulk SIRENE > API

Le **fichier Stock SIRENE** (téléchargement complet mensuel) contourne entièrement les murs de l'API Recherche Entreprises (`per_page ≤ 25`, `page × per_page ≤ 10 000`). On filtre les NAF déco en local → dataset national d'un coup. L'API reste utile uniquement pour le refresh ponctuel par SIRET.

## 2 sources "sleeper"

1. **BDCOM APUR** (Paris, ODbL) — recensement exhaustif de TOUS les commerces parisiens en rez-de-chaussée, nomenclature 224 catégories, millésime 2023. Idéale pour la couverture Paris, ignorée des annuaires concurrents.
2. **Overture Maps Places via DuckDB** — ~10 lignes de SQL extraient les POI furniture/déco France avec `phone` + `website` depuis le parquet S3 AWS, schéma unifié.

## Alertes légales

- **PagesJaunes = INTERDIT** : protection sui generis du producteur de base de données (condamnations documentées). Ne pas scraper.
- **ODbL (OSM)** : Share-Alike sur les bases dérivées **redistribuées**. → Ne PAS proposer l'annuaire en export CSV téléchargeable sans validation juridique. L'affichage web d'un annuaire reste OK (usage, pas redistribution de base).
- **CC0 (Wikidata)** / **CDLA-Permissive (Overture)** : permissifs, republication OK.

## Horaires = point faible

Aucune source gratuite ne couvre bien les horaires sauf OSM (~30-40%). Pour les fiches prioritaires, enrichissement via **scraping du site web de la boutique** (trouvé via OSM/Overture) — tâche idéale pour l'agent Hermes, à valider par échantillon.

## Couverture attendue (ordre de grandeur)

- SIRENE : ~exhaustif (dizaines de milliers d'établissements actifs sur les NAF déco).
- Avec tél/web : ~30-50% après croisement OSM + Overture.
- Avec horaires : ~30% (OSM) + enrichissement scraping ciblé.
