# Cluster "Styles déco" — architecture candidate (à valider avec volumes Ahrefs)

Source : Google Suggest FR (788 requêtes uniques, fichier deco-styles-suggest.json).
⚠️ Le classement ci-dessous est un **proxy de largeur** (nb de variations Suggest),
PAS un volume. À confirmer/arbitrer avec les volumes Ahrefs France.

## Largeur de demande par style (proxy)
| Style | Variations | Statut candidat |
|---|---|---|
| scandinave | 88 | sous-pilier fort |
| moderne | 88 | transverse (à cadrer) |
| vintage | 70 | sous-pilier fort |
| industriel | 66 | sous-pilier fort |
| campagne/cottage | 62 | sous-pilier |
| bord de mer | 54 | sous-pilier |
| bohème | 48 | sous-pilier |
| cosy/hygge | 44 | sous-pilier |
| rustique | 30 | article enrichi |
| japandi | 26 | sous-pilier (tendance) |
| minimaliste | 25 | article enrichi |
| contemporain | 22 | fusion avec moderne |
| art déco | 10 | article |
| wabi sabi | 10 | article |
| maximaliste | 10 | article |
| méditerranéen | 3 | article |

## Schéma de silo proposé (niveau 3 sous /decoration/styles/)
/decoration/ (pilier)
└── /decoration/styles/ (sous-pilier HUB — existe déjà)
    ├── /decoration/styles/scandinave/   (page style = "money page")
    │   ├── article: salon scandinave
    │   ├── article: chambre scandinave
    │   ├── article: cuisine scandinave
    │   └── article: meubles scandinaves
    ├── /decoration/styles/japandi/
    ├── /decoration/styles/boheme/
    ├── /decoration/styles/industriel/
    ├── /decoration/styles/vintage/
    ├── /decoration/styles/campagne-chic/
    ├── /decoration/styles/bord-de-mer/
    └── /decoration/styles/cosy-hygge/

Motif récurrent confirmé par Suggest : "déco {style} {pièce}" → chaque
style × pièce (salon/chambre/cuisine) est un article de soutien naturel.

## Implication technique
Aujourd'hui pcs_content_structure() gère 2 niveaux de pages (pilier + sous-pilier).
Les pages "style" sont un 3e niveau → il faudra étendre la structure pour les
rendre éditables et maillées automatiquement (même mécanique que les sous-piliers).
