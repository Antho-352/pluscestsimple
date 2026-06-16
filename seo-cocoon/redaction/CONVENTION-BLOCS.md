# Convention — blocs modulaires & infographies dans les articles

S'applique à TOUS les briefs (Vague 1/2/3). Objectif : intégrer les blocs `pcs-article`
(thème ≥ 2.16.6) et les infographies **de façon intelligente** — uniquement là où le
contenu le justifie. Vaut que la rédaction soit faite par Anthony OU par un agent IA.

## Les 5 blocs disponibles (catégorie inserter « Plus c'est simple — Article »)
| Code | Bloc | Quand l'utiliser |
|---|---|---|
| **ESS** | L'essentiel (TL;DR) | **Quasi tout article.** 3-6 puces de synthèse en tête. Vise le featured snippet. |
| **PC** | Points clés | How-to / guides : étapes, critères, points à vérifier. |
| **CIT** | Citation sourcée | **Seulement si citation réelle et vérifiable** (norme, expert, fabricant). Jamais inventée. |
| **CHF** | Chiffres clés | Comparatifs / sujets chiffrés. Alternative légère à l'infographie. |
| **SRC** | Sources | **Obligatoire sur tout article technique** (travaux, normes, chiffres). Sources réelles. |

## Règle d'attribution par type (tag du brief)
- **Tous** → **ESS** en tête (sauf article ultra-court où il ferait doublon).
- **[I] guide/how-to** (par-pièce, aménager, optimiser, styles) → ESS + **PC**.
- **[C] crossover technique** (gros-œuvre, verrière, combles, douche ext., haussmannien) → ESS + PC + **SRC obligatoire** (DTU, réglementation, fabricants).
- **[A] affiliation/comparatif** → ESS + **CHF** (ou tableau) ; SRC si chiffres/normes ; le reste = fiche produit CPT.
- **CIT** → opt-in, surtout sur [C] (citer une norme/un organisme) ou une actu. Si rien de réel à citer → ne pas mettre.
- **SRC** → systématique sur [C] et sur tout [I] qui cite des chiffres/normes ; inutile sur déco pure « plaisir ».

> **Ne PAS reproduire le modèle info.fr** : pas de sourcing fait-par-fait, pas de cadre « faits vérifiés / rédacteur IA ». On garde la substance (synthèse, sources, chiffres), pas le branding actu/IA.

## Infographies — qui fait quoi
Je (Claude) produis les infographies en **SVG** : léger, net, aux couleurs de la charte
(crème / vert forêt / laiton), avec texte alternatif. Une infographie n'est prescrite que
si un visuel **comparatif / process / arbre de décision** apporte une vraie valeur
(compréhension + partage + backlink). **Pas une par article.**

**Workflow selon le rédacteur :**
- **Anthony rédige** → le brief indique les blocs à insérer (via l'inserter) et l'infographie à me demander. Tu écris, tu insères les patterns, tu me dis « génère INFO-X » → je te livre le SVG, tu l'ajoutes (bloc Image ou HTML).
- **Agent IA rédige** → l'agent pose le markup des patterns inline et insère un placeholder `[INFOGRAPHIE: INFO-X]` à l'emplacement voulu ; je génère ensuite le SVG correspondant.

## Catalogue d'infographies (12 ciblées)
| ID | Infographie | Sert l'article |
|---|---|---|
| INFO-1 | Anatomie d'un mur porteur (descente de charges) | Hub mur porteur |
| INFO-2 | Pose d'une verrière (étapes / fixation) | V1-8 |
| INFO-3 | Diviser une pièce : 6 solutions comparées | V1-7 |
| INFO-4 | Combles aménageables : critères (hauteur/pente) | V1-10 |
| INFO-5 | Comparatif matériaux de crédence | V1-12 |
| INFO-6 | Parquet : massif / contrecollé / stratifié | V2-18 |
| INFO-7 | Quelle plante pour quelle lumière | V2-14, V2-24, V2-25 |
| INFO-8 | Bouture : dans l'eau vs terreau | V2-26 |
| INFO-9 | Douche extérieure : alimentation & évacuation | V2-28 |
| INFO-10 | Anatomie d'un appartement haussmannien | V3-34 |
| INFO-11 | Optimiser un studio < 25 m² (plan annoté) | V3-29 |
| INFO-12 | Petite salle de bain optimisée (plan annoté) | V1-1 |

## Ordre d'insertion dans l'article (quand les blocs sont présents)
1. Titre → chapô (extrait, auto) → **ESS**
2. Intro (1-2 §) → corps H2/H3
3. **PC** / **CHF** dans le corps, près de la section concernée
4. **Infographie** près de la section qu'elle illustre
5. **CIT** au fil du texte si pertinent
6. **SRC** en fin d'article (avant les articles liés)

---

## Maillage interne entrant automatique (thème ≥ 2.17.0)
Chaque article publié reçoit automatiquement **jusqu'à 3 liens entrants** depuis d'autres
articles **du même silo** (catégorie principale), dans une zone balisée « Sur le même sujet »
en fin d'article. Édition réelle de `post_content` mais **réversible** (zone `<!--pcs-autolinks-->`).

**Ce que le rédacteur doit faire :** définir l'**ancre cible** de l'article = son mot-clé
(= le champ « KW cible » du brief). Renseigner la meta `_pcs_anchor` si on veut une ancre
différente du titre ; sinon le titre sert d'ancre. C'est ce texte qui apparaît comme lien
dans les autres articles.

**Déclenchement :** auto à la publication (`wp_after_insert_post`). Backfill / contrôle via
**Outils → Maillage interne** (« Reconstruire tout le maillage entrant » / « Supprimer tous
les auto-liens »). Scoping : **intra-silo strict** (cohérence cocon), cap 8 liens sortants/article.

**À tester avant de s'y fier :** lancer « Reconstruire » une fois, inspecter quelques articles,
ajuster. Le bouton « Supprimer tous les auto-liens » annule tout proprement.
