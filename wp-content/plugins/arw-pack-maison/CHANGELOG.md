# Changelog — Compatibilimètre (arw-pack-maison)

## [1.0.0] — 2026-05-21

### Adaptation au thème pluscestsimple v2.0+ (classique PHP)

#### Filters renommés (préfixe `arw_pulse_*` → `pcs_*`)
- `arw_pulse_preload_fonts` → `pcs_preload_fonts`
- `arw_pulse_meta_description` → `pcs_meta_description` (×2 dans seo-compat.php)
- `arw_pulse_form_types` → `pcs_form_types`
- `arw_pulse_form_submitted` → `pcs_form_submitted`
- `arw_pulse_breadcrumbs` → `pcs_breadcrumbs`

#### Nettoyage
- Suppression du filter `arw_pulse_front_page_markup` (le pattern home est désormais en Gutenberg via le pattern `pluscestsimple/hero-front` du thème — le shortcode `[arw_maison_compat_tease]` reste disponible pour usage ponctuel)
- Suppression de la dépendance à la constante `ARW_PULSE_ADMIN_SLUG` (7 fichiers nettoyés). Le menu admin du plugin est désormais sous le CPT (`show_in_menu => true`), pas de nesting sous un menu central

#### Compatibilité
- Slug CPT `arw_compat_rule` préservé (zéro breaking change DB)
- Slug taxonomy `arw_compat_category` préservé
- Toutes les meta_keys `_arw_rule_*` préservées
- Admin warning mis à jour : warning si thème actif ≠ `pluscestsimple` ou `arw-pulse` (compatibilité legacy pendant la migration)

---

## [0.9.0] — 2026-05-15

- Lead-magnet : capture email après 3 consultations, envoi PDF par lien signé
- Index JSON statique pour recherche fuzzy zéro requête
- 201 règles importées via CSV (74 + 127)
- Schema JSON-LD Article + BreadcrumbList par règle
- SEO custom : title + meta description par règle et par catégorie

## [0.1.0] — 2026-04-27

- Plugin bootstrap : CPT + taxonomy + meta box + admin columns
- Import CSV bulk avec dry-run
- Page admin Index JSON (rebuild manuel + URL + taille)
