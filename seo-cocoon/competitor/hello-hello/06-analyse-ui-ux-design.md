# Analyse UI/UX & Design — hello-hello.fr (agent Sonnet)

## Design system
- **Typo** : Lato (corps, humaniste chaleureux) + Montserrat (titres, géométrique doux) + Dancing Script 700 (accents "signature", féminin — jamais en corps). Hiérarchie stricte (1 rôle/fonte).
- **Palette** : fond clair NON-blanc (crème #f6eee7) + **1 signature saturée magenta #f00069** (CTA, liens). Texte quasi-noir #0f0e17. Pastels (vert forêt, bleu doux, lavande) = badges/tags. Le fond crème valorise les photos (le blanc pur tue les images claires).
- Ambiance : magazine lifestyle féminin premium (proche ELLE Déco). Zéro pub display = cohérence d'identité.

## Patterns UI/UX gagnants
- **Menu à facettes (Pièce/Style/Couleur)** = pattern le plus stratégique : 3 entrées pour le même contenu → pages/session élevées. L'axe couleur répond à un intent réel ("déco bleu canard").
- **Grille visuelle mid-home "Quel meuble cherchez-vous ?"** (25 vignettes) = réengagement après 1er scroll, navigation par objet, carrefour visuel.
- **Séquençage home** : featured (crédibilité) → derniers → populaires (preuve sociale) → **CTA service** (visiteur chaud) → grille visuelle (réengagement) → présentation → newsletter (après valeur donnée, pas en popup).
- **Chapô éditorial** sur chaque catégorie (3-5 lignes) = SEO + UX.
- **Footer 4 colonnes** = maillage crawlable + plan de site implicite.

## Ce qu'on ADOPTE (pluscestsimple)
- **Menu à facettes adapté** : axes différents de la déco pure →
  - Par pièce/espace (salon, cuisine, chambre, sdb, jardin, terrasse, combles, cave)
  - **Par projet** (peindre, poser, installer, aménager, rénover, entretenir) ← notre différence technique ; "poser carrelage" = projet "Poser" + pièce "Sdb"
  - Par niveau (débutant / week-end / gros chantier) ← intent que hello-hello n'a pas
  - Implémentation : mega-menu HTML pur, CSS `position:absolute`, **zéro JS**.
- **Structure home** : header → hero éditorial (1+2) → derniers (6) → **zone pub display déclarée (min-height, pas après le fold)** → grille "Quel chantier ?" (16-20 vignettes) → 2 colonnes Déco/Travaux séparées visuellement → bloc crédibilité (3 icônes) → newsletter → footer. **Séparateur visuel déco (crème) / travaux (neutre)** pour gérer la double audience.
- **Footer 4 colonnes** : Par pièce / Travaux / Déco & Jardin / Infos.
- **Typo** : 2 fontes (Lato corps + Montserrat ou Plus Jakarta titres). Script décoratif UNIQUEMENT sur chapôs déco, jamais dans travaux (collision de registre).
- **Couleur signature** : reco **vert forêt profond #1b5e3b / #2d6a4f** — pont déco (nature/jardin) + travaux (sérieux), neutre sur fond clair, pas de conflit avec pubs, différenciant en SERP (vs magenta). Fond crème #f7f0e8.
- **Grille thématique "Quel chantier vous attend ?"** : 12-16 vignettes (peinture, carrelage, parquet, plomberie, élec, isolation, menuiserie, placo, déco salon/chambre, jardin, terrasse). SVG ou photos cohérentes, CSS Grid, 0 JS.
- **Chapô éditorial** systématique sur catégories (150-250 mots, maillage 3-5 silos).

## Ce qu'on NE copie PAS
- Stack Blocksy + Elementor (lourd, LCP 3-4s, CLS). Notre thème custom = avantage perf. Jamais de page builder.
- L'absence de pub display (on en a besoin) → mais l'**intégrer proprement** : blocs délimités, pas dans les 2 premiers scrolls, `min-height` déclaré (CLS=0), entre sections jamais inline dans article court.
- Cocon monodimensionnel déco (notre double silo = avantage).
- Ton féminin/lifestyle dans le silo travaux.
- Carnet d'adresses 56 entrées à court terme (ROI incertain).

## Quick wins vs structurel
**Quick wins (<1 sem)** : chapô éditorial catégories (2h) · footer 4 col (1j) · couleur signature (1h) · fond crème (30min) · min-height emplacements pub anti-CLS (30min) · fil d'Ariane (2h).
**Structurel (1 sprint/item)** : mega-menu à facettes (3-5j) · grille thématique home (2-3j) · restructuration home (2-4j) · différenciation visuelle déco/travaux (3-5j) · système pub display propre CLS=0 (2-3j).
