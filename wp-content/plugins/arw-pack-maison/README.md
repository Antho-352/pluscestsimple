# Compatibilimètre — pluscestsimple

Plugin WordPress sur-mesure pour le site [pluscestsimple.com](https://pluscestsimple.com). Implémente le **Compatibilimètre** : 201 règles techniques (DTU + normes) sur la compatibilité des matériaux et techniques de rénovation, avec recherche fuzzy zéro-requête (index JSON statique), pages SEO long-tail auto-générées, et lead-magnet "12 erreurs qui coûtent 10 000 €".

**Compatible avec** : thème [pluscestsimple v2.0+](../../themes/pluscestsimple).

---

## Fonctionnalités

- **CPT `arw_compat_rule`** (slug `compatibilimetre/regle/%name%`) : une règle = un cas concret. Verdict (compatible / sous conditions / déconseillé / interdit), explication, alternatives, refs DTU, diagnostic, coût, ordre des pros, erreurs fréquentes.
- **Taxonomy `arw_compat_category`** (slug `compatibilimetre/categorie/%name%`, hierarchical) : sols, murs, cloisons, SDB, cuisine, structure, extérieur, chauffage, électricité, plomberie — créées automatiquement à l'activation.
- **Import CSV** : page admin pour importer 80-100 règles d'un coup (dry-run + mapping verdict).
- **Index JSON statique** : régénéré au save/delete/term-update. Servi au front pour recherche fuzzy zéro requête DB.
- **Shortcodes** : `[arw_compatibilimetre]` (recherche + grille), `[arw_compat_rule_detail]` (fiche individuelle), `[arw_compat_related]` (règles liées).
- **JSON-LD** : Article + BreadcrumbList custom pour chaque fiche règle.
- **SEO** : title `<règle> — <verdict> | <site>`, meta description `<verdict> — <130 chars d'explication>`. Snippets Google actionnables.
- **Lead-magnet** : capture email après 3 consultations de règle (blur + modal), envoi PDF "12 erreurs" par lien signé one-shot (token 24h).
- **Sync Brevo** : à venir.

---

## Installation

1. `./package.sh` → génère `../arw-pack-maison.zip`
2. WP admin → Plugins → Ajouter → Téléverser → `arw-pack-maison.zip` → Activer
3. À l'activation : crée la taxonomy + 10 termes par défaut, la page `/compatibilimetre/`, copie le PDF par défaut dans `wp-content/uploads/arw-maison/private/`
4. Importer les 201 règles via **Règles Compatibilimètre → Import CSV** (les 2 CSV à la racine du plugin : `seed-rules.csv` + `seed-rules-batch2.csv`)

---

## Intégration avec le thème pluscestsimple

Le plugin s'appuie sur les hooks publics du thème (préfixés `pcs_`) :

| Hook | Usage |
|---|---|
| `pcs_preload_fonts` | Override la liste de fonts à preload (Inter + Fraunces) |
| `pcs_meta_description` | Meta description custom pour fiches règle + taxonomy compat |
| `pcs_breadcrumbs` | Fil d'Ariane Schema.org enrichi : Accueil > Compatibilimètre > Catégorie > Fiche |
| `pcs_form_types` | Whitelist du form_type `lead-magnet` pour le module form du thème |
| `pcs_form_submitted` | Hook post-submit : génère le token, envoie l'email avec lien PDF |

Aucun couplage dur : si le thème pluscestsimple n'est pas actif, un warning admin est affiché. Le plugin reste fonctionnel mais le rendu est dégradé (pas de préchargement fonts, breadcrumbs basiques, meta descriptions standards).

---

## CSV — format d'import

```
title;category_slug;verdict;explanation;alternatives;keywords;dtu_refs
"Parquet massif sur plancher chauffant ?";"sols";"conditional";"Possible si essence stable…";"Stratifié compatible PCBT…";"parquet, plancher chauffant";"DTU 51.2"
```

Verdicts acceptés : `compatible`, `conditional`, `discouraged`, `forbidden`.

---

## License

GPL-2.0-or-later · Anthony Russo
