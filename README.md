# pluscestsimple.com

Code source du site **pluscestsimple.com** — média francophone déco / travaux / immobilier.

## Stack

- WordPress 7.0+ (PHP 8.0+)
- Thème classique PHP sur-mesure (`wp-content/themes/pluscestsimple/`)
- Plugin métier sur-mesure pour le Compatibilimètre (`wp-content/plugins/arw-pack-maison/`)
- Édition contenu : Gutenberg natif uniquement, pas de page builder

## Structure du repo

```
pluscestsimple/
├── wp-content/
│   ├── themes/
│   │   └── pluscestsimple/      Thème du site
│   └── plugins/
│       └── arw-pack-maison/     Plugin Compatibilimètre (CPT + 201 règles)
└── docs/                         Documentation projet
```

## Branches

- `main` — état de référence du site en production
- `refonte-classique-php` — refonte en cours : passage du thème FSE actuel vers un thème classique PHP standard, normalisation des pages en Gutenberg natif, gestion du menu via `wp_nav_menu()`

## Identité

| Élément | Valeur |
|---|---|
| Directeur de la publication | Anthony Russo |
| Email contact | contact@pluscestsimple.com |
| SIRET | 98497752000019 |
| Hébergeur | OVH |
