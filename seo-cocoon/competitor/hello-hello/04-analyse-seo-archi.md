# Analyse SEO & Architecture — hello-hello.fr (agent Sonnet)

## Cocon multi-axes (4 axes + objets)
- Pièce `/pieces/` · Style `/styles/` · Couleur `/couleurs/` · Éditorial transversal `/interieurs/ /conseils-et-diy/ /decorer/` · + axe objet (`/canape/ /luminaire/ /tapis/`).
- Efficacité : un article (ex. "déco scandinave chambre") couvre 3 intentions et se lie depuis pièce + style + couleur → maillage à facettes, autorité de surface. 8 couleurs × 10 pièces × 9 styles = 240 croisements signalés sans créer 240 pages thin.

## Pages catégories (pattern gagnant)
H1 = nom cat · **chapô éditorial 3-6 lignes** (indexable, rassure) · cartes articles + pagination · **maillage latéral dense dans le chapô** vers cat adjacentes · breadcrumb. → la cat devient une vraie page SEO.

## Dette de taxonomie à NE PAS copier
Doublons : `/chambre-2/` vs `/pieces/chambre/` ; `/cuisine-2/`+`/cuisine-3/`+`/pieces/cuisine/` ; `/vintage/`+`/styles/decoration-vintage/` ; `/non-classe/` ; `/slider-home/` `/layout/` (artefacts) ; enfants fragmenté ×3. → croissance non gouvernée = dette SEO. Notre init-content.php programmatique nous en protège.

## Plan d'adoption pluscestsimple
- **A. /styles/** (en cours) : continuer, avec chapô + maillage latéral comme eux.
- **B. /decoration/couleurs/** (Vague 3) : 8 couleurs ; quick win 4 (terracotta, vert, bleu, noir). Requêtes éditoriales pures ("déco terracotta", "chambre vert").
- **C. /decoration/par-piece/{salon,chambre,sdb,cuisine}/** : hubs pièce MIXTES déco+travaux (sections "travaux dans ce salon" → pont vers silo travaux).
- **D. Axe objet/meuble (canapé, luminaire…) : IGNORER** — intention d'achat dominée par e-comm, incompatible avec notre modèle display.
- **E. Avant-Après / Home tour : adopter l'INTENTION** → "avant-après travaux" (mur porteur ouvert, sdb rénovée) : photo avant/après + récit technique + chiffres. S'insère dans clusters charpente/maçonnerie.

## Ce qu'on fait MIEUX
Force = intention technique (muralière, parpaing, placo) où hello-hello est ABSENT (cat /travaux/ plate). Terrain vierge éditorial = avantage décisif. Garder la rigueur anti-cannibalisation.

## Réorg post-immobilier (3 piliers)
Supprimer `immobilier`, absorber `architecture` dans `travaux` (extensions + styles-epoques = angle réno).
```
/travaux/ : gros-oeuvre · par-piece · renovation-energetique · [+]extensions · [+]styles-epoques
/decoration/ : styles · par-piece · couleurs(NOUVEAU) · petits-budgets
/jardin/ : amenagement-exterieur · entretien
(lifestyle = 4e pilier discret, faible investissement)
```

## Comparatif (extrait)
| | hello-hello | pluscestsimple |
|---|---|---|
| Force | déco/style/objets/inspiration | travaux technique |
| Articles | 1420 | ~157 |
| Axes cocon | 4 + objets | 1 (styles) + pièces partiel |
| Monétisation | affiliation+partenariats+coaching | display (+affiliation possible) |
| Travaux technique | absent (avantage pour nous) | force |
| Gouvernance taxo | faible (doublons) | forte (programmatique) |

## Résumé
Cocon multi-axes mature sur la déco pure, pages cat bien optimisées (chapô+maillage), 1420 articles. Dette = doublons non nettoyés + absence totale sur le travaux technique. Pour nous : prioriser Vague 1 (clusters techniques où on domine sans concurrent), + 3 adoptions (chapô+maillage latéral sur les pages styles, axe /couleurs/, pages-pièce mixtes déco/travaux), + réorg 3 piliers. Ignorer l'axe objet (e-comm). Différenciation = travaux technique + styles déco éditoriaux, deux espaces que hello-hello ne couvre pas.
