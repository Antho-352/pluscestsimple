# Stratégie SEO / cocon sémantique — pluscestsimple.com

_Cadre : monétisation AUDIENCE + DISPLAY (volume × pages/session) · périmètre TRAVAUX + DÉCO · intention éditoriale stricte (exclure SERP commerciales)._
_Issu de l'agent SEO stratège (analyse GSC + Suggest + Haloscan + inventaire 157 articles). Date : 2026-06._

> ⚠️ **Mise à jour vs audit** : l'audit AUDIT-COCON-2026-06.md décrit le maillage comme cassé (P1-P4).
> Ces bugs ont été **corrigés depuis** (thème v2.9.7→2.10.0) : filtrage silo des piliers ✅, hiérarchie catégories ✅,
> maillage pilier→sous-pilier ✅, articles liés intra-silo ✅, pages styles 3e niveau ✅.
> La « Vague 0 = réparer le maillage » est donc **en grande partie faite**. Reste : créer les **pages-hub de cluster** et y rattacher l'existant.

---

## 1. Diagnostic GSC — où est la traction réelle

**La force du site = TRAVAUX technique, pas la déco.**
- muralière 706 clics (pos 4.6) · baguettes d'angle 296 · entretoise charpente 197 · plinthe invisible placo 196 · colle amiante 193 · occulter velux 152 · mur porteur 1970 60.
- La déco existante plafonne à 0-2 clics. → On capitalise d'abord sur le technique (ROI le plus rapide), la déco se construit en parallèle (cocon styles déjà lancé).

**Pépites position 5-15 à grosses impressions = trafic display quasi immédiat (à renforcer en priorité) :**
| Page | Impressions | Position | Action |
|---|---:|---:|---|
| hauteur penderie | 5 573 | 8.4 | enrichir + mailler → podium |
| décaper peinture métal | 4 100 | 16 | réécrire/approfondir |
| parpaing poids | 4 305 | 10.5 | enrichir |
| meuleuse ou ponceuse | 2 860 | 14 | approfondir comparatif éditorial |
| gris 2900 menuiseries | 3 433 | 9.2 | enrichir |
| coffrage dalle béton | 2 612 | 10 | enrichir |

**Contenus qui rankent au fond faute de page mère** (signal qu'il manque un hub) :
- traitement bois charpente (1026 imp, pos 42) · charpente lamellé-collé (1067 imp, pos 33) · humidité causes solutions (pos 40).

---

## 2. Pages mères / hubs prioritaires à créer

Priorité n°1 : **construire les pages-hub de cluster manquantes** et y fédérer les 157 articles existants (pas produire du neuf orphelin).

| Hub à créer | Pilier/sous-pilier | Pourquoi |
|---|---|---|
| **Charpente** (hub) | travaux/gros-oeuvre | site déjà n°1-4 (entretoise, muralière) ; 25-30 articles dispersés à fédérer |
| **Maçonnerie / parpaing** | travaux/gros-oeuvre | parpaing, mur, chape, coffrage rankent déjà |
| **Placo / cloison** | travaux/par-piece | plinthe placo, renforcer placo, lambris placo |
| **Parquet & sols** | travaux/par-piece | poser parquet, rayure, vitrificateur, lino |
| **Humidité / salpêtre** | travaux/gros-oeuvre | « humidité causes solutions » à promouvoir EN hub |
| **Hub /decoration/styles/** | déco | ✅ existe déjà (pages styles 3e niveau en cours) |

---

## 3. Clusters de contenu (cocon détaillé)

### TRAVAUX — gros œuvre (autorité prioritaire)
- **Cluster CHARPENTE** (hub → articles) : pièces de charpente, types (traditionnelle/fermette/américaine/lamellé-collé), traitement bois, vrillettes, panne/blochet/entretoise, dimensionnement. _[plusieurs existent déjà → fédérer]_
- **Cluster MUR / MAÇONNERIE** : mur porteur (abattre, ouverture, calcul charge), parpaing (dosage, poids, monter, chevilles), mâchefer, fissures, crépir, enduit.
- **Cluster DALLE / FONDATION** : couler dalle, coffrage, ferraillage, chape sur plancher chauffant.

### TRAVAUX — par pièce & finitions
- **Cluster PLACO/CLOISON**, **Cluster PARQUET/SOLS**, **Cluster HUMIDITÉ/SALPÊTRE**.
- Tous [VOLUME À VALIDER HALOSCAN] sauf ce que la GSC confirme déjà.

### DÉCO — styles (data Haloscan réelle, déjà arbitrée)
- Pages styles : quick wins d'abord (**méditerranéen** allintitle 387 = SERP quasi vide, **japandi** 771, **campagne-chic**), puis Tier 1 (**scandinave/industriel/bohème**).
- Articles soutien : `salon {style}`, `chambre {style}` ✅ éditorial. **`cuisine {style}` et `{style} pas cher` EXCLUS** (SERP cuisinistes/e-comm).

### DÉCO — par pièce / couleurs (à valider Haloscan)
- déco salon, déco chambre, couleur chambre adulte, aménager petite chambre, déco couloir, déco wc. [VOLUME À VALIDER HALOSCAN]

---

## 4. Plan de maillage interne (display = maximiser pages/session)

- **Étoile de cluster** : chaque hub ↔ tous ses articles de soutien (descendant + remontant).
- **Pont depuis les pages qui rankent déjà** (muralière, baguettes d'angle, entretoise) vers les nouveaux contenus du MÊME cluster → transfert d'autorité immédiat. _Action concrète : ajouter dans muralière/entretoise des liens contextuels vers le futur hub Charpente._
- **Bloc « À lire dans le même univers »** en pied d'article (✅ déjà livré v2.9.8, intra-silo).
- **Hub → pépites** : lier les pages mères vers les pépites position 5-15 pour les pousser.
- Règle d'or : maillage **intra-silo** (pas de lien charpente → déco) sauf pont éditorial justifié.

---

## 5. Anti-cannibalisation (vs 157 articles existants)

- **muralière** : NE PAS multiplier les pages. Consolider `muraliere-calcul-charges` (faible, 1 clic) dans/vers la page muralière forte. 1 seule page money par intention.
- **humidité** : promouvoir `humidite-causes-solutions` EN hub, ne pas créer de doublon.
- **Styles déco** : différencier strictement `page-style` (déco {style}) vs `salon {style}` vs `déco salon` vs `couleur salon` — intentions distinctes, angles distincts, liens entre elles (pas concurrence).
- Vérifier chaque nouveau sujet contre l'inventaire avant rédaction.

---

## 6. Autorité topique — devenir LA référence

1. **LA CHARPENTE** (cible n°1) : thème technique peu couvert par les grands médias, où le site est déjà n°1-4. Couverture exhaustive (pièces / types / traitements / dimensionnement) → ~25-30 contenus en étoile. C'est le pari de domination le plus atteignable.
2. **LE MUR / MAÇONNERIE** (n°2) : parpaing, mur porteur, fissures, enduits.
3. **LES STYLES DÉCO** (n°3) : cocon styles complet (pages + salon/chambre).

---

## 7. Hygiène (signaux à nettoyer)

Sortir du cocon ~10 articles hors-sujet qui diluent l'autorité topique : fourtoutici, joy-lamp, my-extrabat, didier-mathus-immobilier, crottin-loire-fromage-chevre, shockgarden, outils-club-elec, proxichantier, etc. → noindex ou suppression/réaffectation.

---

## 8. Annexe — Opportunités « marques déco » (2ᵉ/3ᵉ/4ᵉ zone)

PAS Ikea/Leroy Merlin/Maisons du Monde (trop concurrentiel). Marques de niche, rattachées aux pages-style pour le maillage. **Tous [VOLUME À VALIDER HALOSCAN].**

| Marque | Univers | Angle de contenu recommandé |
|---|---|---|
| Tikamoon | bois massif | avis / alternative à La Redoute Int. |
| Tolix | industriel | présentation marque + iconique chaise A |
| Ferm Living | scandinave/japandi | présentation + sélection produits |
| Lampe Gras | luminaire industriel | test produit iconique |
| Le Monde Sauvage | bohème | avis marque + alternatives |
| Fermob | mobilier couleur | présentation (rattacher jardin aussi) |
| Atelier Vime | rotin/bohème | marque + tendance rotin |
| HKliving, Bloomingville, Madam Stoltz | déco nordique/bohème | comparatif marques |
| Maison Sarah Lavoine | déco chic FR | avis + style « bleu Sarah » |
| Jamini, Caravane, AM.PM | textile/déco | sélection + angle |
| (à compléter : céramique, luminaire, mobilier bois de niche) | | |

Angles types : « avis marque X », « X vaut-il le prix », « alternatives à [grosse marque] », « marque X par produit phare ». Cibler les marques avec SERP éditoriale (avis/test), pas e-comm pur.

---

## 9. Roadmap d'exécution

- **Vague 0 — maillage** : ✅ FAIT (v2.9.7→2.10.0). Reste : créer les hubs de cluster (§2) + ponts depuis pages fortes.
- **Vague 1 — capitaliser le technique** : hubs Charpente + Maçonnerie + fédération des articles existants + renfort des 6 pépites (§1). _ROI le plus rapide._
- **Vague 2 — déco styles** : publier les pages quick-win (méditerranéen, japandi, campagne-chic) puis Tier 1 (scandinave/industriel/bohème) + articles salon/chambre.
- **Vague 3 — extension clusters** : placo, parquet, humidité, déco par pièce/couleurs (selon volumes Haloscan).
- **Vague 4 — réno énergétique & outillage** : angle éditorial pur seulement (DPE explicatif, fonctionnement PAC — jamais devis/prix/aides). Risque commercial → en dernier.
- **Transverse** : annexe marques (selon volumes), hygiène (nettoyage hors-sujet).
