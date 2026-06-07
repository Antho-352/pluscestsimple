# Priorisation pages "styles déco" — données Haloscan FR (2026-06)

VolH = volume clickstream Haloscan · ADS = volume Google Keyword Planner
allintitle = pages avec la requête exacte en title (concurrence) · CPC = enjeu business

## Têtes de style
| Style | ADS | VolH | allintitle | Diff. | CPC | Verdict |
|---|---:|---:|---:|---|---:|---|
| scandinave | 2900 | 126 | 11 100 | Difficile | .48 | PAGE — flagship |
| industrielle | 2400 | 65 | 8 140 | Moyen+++ | .49 | PAGE |
| bohème | 1300 | 1000 | 12 900 | Difficile | .39 | PAGE |
| vintage | 1300 | 59 | 150 000 | Difficile+++ | .51 | longue traîne only |
| bord de mer | 1000 | 50 | 5 740 | Moyen++ | .37 | PAGE (tier 2) |
| minimaliste | 1000 | 77 | 4 740 | Moyen | .47 | PAGE |
| contemporaine | 880 | 49 | 4 930 | Moyen | .70 | article/tier 2 |
| rustique | 590 | 536 | 6 410 | Moyen++ | .47 | PAGE (sous-coté) |
| cosy | 320 | 29 | 5 600 | Moyen++ | .51 | article |
| campagne chic | 260 | 260 | 2 600 | Facile | .27 | QUICK WIN |
| japandi | 260 | 22 | 771 | Facile+++ | .44 | QUICK WIN (tendance) |
| méditerranéenne | 480 | 30 | 387 | Facile+++ | .56 | QUICK WIN (SERP vide) |
| wabi sabi | 390 | 17 | N/A | N/A | .38 | article tendance |
| art déco intérieur | 210 | 11 | N/A | N/A | .18 | article |

## Croisements style × pièce (= articles de soutien sous chaque page style)
| Requête | ADS | VolH | allintitle | Diff. | CPC |
|---|---:|---:|---:|---|---:|
| salon scandinave | 2900 | 126 | 27 200 | Difficile | .33 |
| chambre bohème | 2900 | 400 | 6 180 | Moyen++ | .41 |
| cuisine scandinave | 1900 | 158 | 16 000 | Difficile | 1.77 |
| salon industriel | 2400 | 65 | 20 800 | Difficile | .56 |
| cuisine industrielle | 2400 | 126 | 9 270 | Moyen+++ | .82 |
| chambre scandinave | 1600 | 1600 | 16 600 | Difficile | .54 |
| cuisine scandinave | 1900 | 158 | 16 000 | Difficile | 1.77 |
| salon bohème | 1000 | 100 | 7 570 | Moyen+++ | .38 |

## Roadmap de construction
### Tier 1 — Pages money (volume + business), à attaquer d'abord
1. SCANDINAVE — anchor, alimente salon/cuisine/chambre scandinave (gros volume)
2. INDUSTRIEL — 2400 vol, salon/cuisine industriel (CPC élevé)
3. BOHÈME — 1300 + chambre bohème 2900

### Tier 2 — Quick wins (SERP peu concurrentielle → ranking rapide)
4. MÉDITERRANÉENNE (allintitle 387 = quasi vide)
5. JAPANDI (Facile+++, tendance montante)
6. CAMPAGNE CHIC (Facile, KGR 10)

### Tier 3 — Pages secondaires
7. MINIMALISTE, 8. RUSTIQUE (VolH 536 réel), 9. BORD DE MER

### Articles seulement (pas de page)
vintage (allintitle 150k), contemporaine, cosy, wabi sabi, art déco

## Enjeu business
- CPC max sur les CUISINES (scandinave 1.77, industrielle 0.82) → affiliation mobilier/cuisine forte. Prioriser les articles "cuisine {style}".
- Scandinave + industriel = combo volume + valeur + affiliation déco.

## Architecture cible
/decoration/ (pilier)
└── /decoration/styles/ (sous-pilier HUB existant → liste les styles)
    ├── /decoration/styles/scandinave/   (PAGE style)
    │   ├── salon scandinave (article)
    │   ├── cuisine scandinave (article, CPC fort)
    │   └── chambre scandinave (article)
    ├── /decoration/styles/industriel/
    ├── /decoration/styles/boheme/
    ├── /decoration/styles/mediterraneenne/  (quick win)
    ├── /decoration/styles/japandi/          (quick win)
    └── /decoration/styles/campagne-chic/    (quick win)

Implication technique : pages "style" = 3e niveau. Le système gère 2 niveaux
(pilier + sous-pilier). À étendre pour rendre ces pages éditables + maillées auto.

---
# MISE À JOUR — Filtre INTENTION (analyse SERP, 2026-06)

Priorité = VOLUME + DIFFICULTÉ + **intention éditoriale** (pas commerciale).
CPC élevé / SERP e-commerce → l'éditorial ne rank pas → EXCLU.

## Vérifié sur SERP réelle
| Requête | SERP dominée par | Verdict |
|---|---|---|
| déco scandinave | mixte (Ouest-France, JDF, IKEA ideas + e-comm) | éditorial JOUABLE |
| salon bohème | 100% média/blogs | ÉDITORIAL ✅ |
| chambre scandinave | 100% média/blogs (20Min, Houzz, Westwing inspi) | ÉDITORIAL ✅ |
| déco méditerranéenne | 100% média (Elle, Côté Maison, Houzz) | ÉDITORIAL ✅ quick win |
| déco japandi | média/blogs + qq e-comm | ÉDITORIAL JOUABLE |
| salon industriel | 100% média/blogs | ÉDITORIAL ✅ |
| cuisine scandinave | cuisinistes lourds (Mobalpa, Inova) | RISQUÉ ⚠ |
| cuisine industrielle | 100% cuisinistes (Schmidt, AvivA, Cuisinella) | EXCLU ❌ |

## Règle générale
- "déco {style}", "{style} salon", "{style} chambre" → ON (éditorial)
- "{style} cuisine", "meuble {style} pas cher" → OFF (commercial e-comm/cuisinistes)

## Plan FINAL (intention-filtré)
PAGES STYLE (hub éditorial + articles salon/chambre, PAS cuisine) :
- Tier 1 : SCANDINAVE (déco 2900 + chambre 1600 + salon 2900) ·
           BOHÈME (déco 1300 + chambre 2900 + salon 1000) ·
           INDUSTRIEL (déco 2400 + salon 2400)
- Quick wins : MÉDITERRANÉEN (allintitle 387) · JAPANDI · CAMPAGNE CHIC
- Tier 2 : MINIMALISTE (1000) · RUSTIQUE (VolH 536) · BORD DE MER (1000)

ARTICLES par page (éditorial) : salon {style}, chambre {style}.
EXCLUS : toutes les "cuisine {style}", tout "{style} pas cher".
