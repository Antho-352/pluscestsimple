# Structure V2 — pluscestsimple.com (cocon resserré, 2026-06)

Resserrage d'angle + cocon déco multi-axes (inspiré hello-hello, différencié par travaux technique + architecture).

## Piliers finaux (4)
```
DÉCORATION  /decoration/
  par-piece (hub)  → salon · chambre · chambre-enfant · cuisine · salle-a-manger · salle-de-bain · bureau · entree
  styles (hub)     → scandinave · japandi · boheme · industriel · mediterraneen · campagne-chic · vintage · coloree
  rangement-organisation   (NOUVEAU — rapatrie le contenu utile de lifestyle)
  [couleurs]               (différé, conditionnel volumes Haloscan)
  ✗ petits-budgets         (RETIRÉ — modificateur d'intention, pas un silo)

TRAVAUX  /travaux/   (inchangé — notre force)
  par-piece · gros-oeuvre · renovation-energetique
  hubs en cours : mur-porteur, charpente, maçonnerie…

JARDIN  /jardin/   → label renommé « Jardin & extérieur » (absorbe tout l'outdoor, pas de pièce extérieur en déco)
  amenagement-exterieur · entretien

ARCHITECTURE  /architecture/   (GARDÉ — différenciateur : hello-hello ne l'a pas)
  styles-epoques  → + page cluster « haussmannien » (carrefour archi × déco × travaux : style, moulures, parquet point de Hongrie, rénovation appart haussmannien)
  extensions      (cluster fort : « extension maison » VolH 13 875)
```

## Retirés
- **LIFESTYLE** : pilier supprimé. Contenu rangement/organisation rapatrié → `/decoration/rangement-organisation/`. (Ménage/nettoyage : articles, pas de silo.)
- **IMMOBILIER** : pilier supprimé. Articles triés :
  - `renonce-t3-fissures-structurelles` → travaux/gros-oeuvre (recatégorisé)
  - éventuel éditorial « habiter » (quatre capitales) → architecture ou noindex
  - 9 articles finance pure (SCI/LMNP, notaire, crédit, rendement, investissement, agence, Roubaix, Saint-Malo, DPE-22k) → **noindex** (hors-sujet, ~0 clic, conservés mais désindexés)

## Garde-fous (Opus)
- **Pas de thin content** : les ~16 pages pièces+styles créées en **BROUILLON**, publiées une par une **quand elles ont du contenu**. Le menu/cocon n'expose que les pages **publiées**.
- **Séquence** : travaux technique reste prioritaire (≥60 % effort). Le cocon déco se remplit en parallèle.
- **Pages positionnées** : aucune réécriture de corps ; uniquement taxonomie/redirections.

## Menu
- Header : ajouter **« Être publié »** → page `travailler-avec-nous` (partenariats + soumettre un projet/reportage).

## Plan de migration (à exécuter en une passe)
1. `init-content.php` `pcs_content_structure()` : décoration (par-piece, styles, rangement-organisation ; retrait petits-budgets) ; jardin (label « Jardin & extérieur ») ; retrait lifestyle + immobilier ; architecture conservé.
2. Moteur (style-pages / hub-pages) : registre des 8 pièces + 8 styles + haussmannien en **brouillon** (catégories 3e niveau + filtrage + maillage auto).
3. `inc/retired-pillars.php` : 301 `/immobilier/` → accueil, `/lifestyle/` → `/decoration/`, `/decoration/petits-budgets/` → `/decoration/` ; archives catégories retirées → noindex + 301.
4. Triage articles : recatégorisation (fissures→travaux ; rangement→déco) + **noindex** des 9 finance.
5. Menu fallback : ajout « Être publié ».
6. Footer (déjà dynamique) : se met à jour seul.
7. Test idempotence du seed.
