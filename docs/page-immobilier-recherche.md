# Page Immobilier — Recherche & recommandations

## Méthodologie

- **Concurrents audités** : 7 (3 fetchés totalement ou partiellement, 4 reconstitués via WebSearch). **Date** : 21 mai 2026
- **WebFetch réussis** : seloger.com (édito), meilleursagents.com, notaires.fr (section immobilier), bienici.com (très pauvre éditorialement)
- **WebFetch échoués / bloqués** : logic-immo.com (404 sur /guides-immobilier-categorie-conseils/), pap.fr/conseils (403), capital.fr/immobilier (refus claudefetch), notaires.fr/conseils-aux-particuliers (403), seloger.com/guide-immo (403)
- **WebSearch** : 8 requêtes (DPE 2026, PTZ 2026, prix marché 2026, frais notaire, MaPrimeRénov 2026, audit énergétique, primo-accédants, marché locatif tendu, vente délai DPE, coûts cachés)
- **Limites** : sites de plateformes immo bloquent agressivement le scraping. Reconstitution via la SERP et le contenu indexé. Aucun accès keyword planner, volumes estimés à partir des signaux SERP et de l'intent

---

## Phase 1 — Audit concurrentiel

### 1. SeLoger (edito.seloger.com)
H1 : "Conseils d'experts". Quatre axes : transactions (acheter/louer/vendre/construire), financement (crédit/assurance/aides), conseils pratiques (guides + checklists), immobilier local (focus marché par ville). Angle : accompagnement du parcours utilisateur, beaucoup de défiscalisation (Pinel post-mortem, statut LMNP), pousse SeLoger Neuf. Force : autorité institutionnelle, taxonomie claire par étape. Faiblesse : ton corporate, peu de prise de position, beaucoup d'articles "comment vendre une maison héritée rapidement" sans données réelles. Exemples vus : "Caution, GLI, Visale", "Feng shui plantes par pièce", "Toulouse focus marché".

### 2. Logic-Immo
Inaccessible direct, indexation visible : taxonomie similaire SeLoger (Achat, Vente, Location, Crédit, Conseil) mais densité éditoriale plus faible, beaucoup de pages thin générées sur "prix moyen [ville]". Angle : SEO local + plateforme d'annonces. Faiblesse : redirige beaucoup vers annonces, peu de rédactionnel structuré.

### 3. Notaires.fr
Sous-rubriques claires : "Parcours d'achat et de vente", "Acheter", "Vendre", "Se constituer un patrimoine", "Louer", "Construire et aménager". Angle : pédagogie juridique, autorité réglementaire absolue (source officielle). Force : crédibilité maximale sur fiscalité, succession, donation, frais. Faiblesse : ton institutionnel, jamais d'angle "et concrètement ?", schéma figé, mises à jour réglementaires lentes côté éditorial.

### 4. Meilleurs Agents (edito.meilleursagents.com)
Sept rubriques : Actualités, Estimation, Investir, Acheter, Vendre, Louer, Lifestyle. Angle : data-driven (baromètre mensuel des prix par ville), pousse l'outil d'estimation gratuit. Exemples vus : "Les pires erreurs déco qui font fuir les acheteurs", "Les rénovations à privilégier avant de vendre", "Location meublée liste meubles obligatoires", "Dispositif Jeanbrun". Force : data réelle, baromètre cité comme source par la presse. Faiblesse : tout converge vers l'estimation maison, beaucoup d'angles "lifestyle" (terrasse, balcon) qui diluent.

### 5. Bien'ici
Quasi rien d'éditorial : c'est une plateforme d'annonces avec une cartographie. Aucune rubrique conseil structurée publiquement. Faiblesse : irrelevant comme concurrent éditorial.

### 6. PAP (Particulier à Particulier)
Inaccessible direct. Indexation visible : rubriques Acheter / Vendre / Louer / Investir / Crédit / Diagnostic, articles "Vente entre particuliers", "Acheter sans agence", "Modèles de baux". Angle : positionne PAP comme "le défenseur du particulier" contre les agences. Force : modèles de documents légaux téléchargeables, ton pratique. Faiblesse : militantisme anti-agence qui peut fatiguer, fiches juridiques abstraites.

### 7. Capital.fr (immobilier)
Inaccessible (refus claudefetch). Indexation visible : ton presse éco/patrimoine, angle investisseur (rendement, fiscalité, SCPI, défisc). Exemples : "ces villes où la rentabilité dépasse 8 %", "DPE : les passoires invendables". Force : actualité chaude, chiffres. Faiblesse : cible CSP+/investisseur, hors cible PCS.

### Synthèse audit

**Sujets sur-traités (saturés)** :
- "10 conseils pour acheter son premier appartement" génériques
- "Faut-il acheter ou louer ?" sans données chiffrées propres
- Baromètres de prix par ville (data Meilleurs Agents recopiée partout)
- "Dispositif [nom de loi]" pour investisseurs Pinel/LMNP/Denormandie
- "Comment estimer son bien" pour pousser un outil maison
- "Les diagnostics obligatoires" en check-list froide

**Sujets sous-traités (opportunités réelles)** :
- DPE comme variable centrale d'achat ET de vente en 2026 (impact prix de vente, négo possible, calendrier interdiction location G/F/E, réforme méthode au 1er janvier 2026 qui sort 850 000 logements du statut passoire sans travaux)
- Coût réel total d'un achat (notaire + travaux + ravalement + charges + fonds ALUR + taxe foncière) — fragmenté partout, jamais en pilier
- Locataires en 2026 dans un marché à pénurie réelle (-7 % de stock en un an, -30 à -50 % d'annonces longue durée dans grandes villes)
- Bailleur particulier face à la spirale (G interdits depuis 2025, F en 2028, audit énergétique obligatoire à la vente, choix vendre ou rénover)
- Primo-accédant 2026 vrai : apport 15-20 % requis vs 10 % il y a 3 ans, PTZ rouvert aux zones B2 et C, taux 3-3,5 %, méthode pour évaluer sa capacité réelle
- Délai et stratégie de vente quand on a un DPE moyen (E ou D) : -20 % de vitesse vs A/B, surcote possible avec travaux ciblés
- Lecture critique d'un compromis et d'un règlement de copropriété pour un acheteur normal

**Angle différenciant PCS** : seul média qui dit "voilà ce que ça coûte vraiment en 2026 entre l'apport, le notaire, les travaux DPE et les charges, voilà ce que tu as le droit de négocier sur un bien classé F, voilà combien tu paies si tu sors un G du circuit locatif". Anti-investisseur Pinel, pro-ménage normal qui veut un toit qui tient.

---

## Phase 2 — 6 sujets retenus

### Sujet 1 — Acheter sa résidence principale en 2026 (hub Acheter)
- **Pourquoi** : intent #1, marché qui se stabilise (+2 à +3 %), taux à 3-3,5 %, fenêtre d'achat raisonnable
- **Angle PCS** : pas "10 conseils", parcours réel chronologique avec chiffres 2026 (apport requis 15-20 %, capacité d'emprunt impactée par OAT, frais notaire 7-8 % ancien vs 2-3 % neuf)
- **Articles** : Calculer sa capacité d'emprunt réelle 2026, Lire un compromis sans avocat, Visite : 12 points qu'un acheteur normal oublie, Acheter dans l'ancien vs le neuf (vraie comparaison de coût total)

### Sujet 2 — DPE : la variable qui change tout en 2026
- **Pourquoi** : sous-cat brief explicite, transversal achat/vente/location, calendrier réglementaire chaud (G interdits, F en 2028, audit obligatoire vente E/F/G, réforme méthode au 1er janvier 2026)
- **Angle PCS** : pas une fiche "qu'est-ce que le DPE", lecture stratégique du DPE selon ton statut (acheteur, vendeur, bailleur, locataire)
- **Articles** : Acheter un bien classé F : risque, opportunité, négo, Vendre un E/F/G en 2026 (audit obligatoire, surcote travaux), Bailleur d'un G : sortir, rénover ou vendre, Réforme DPE 1er janvier 2026 (qui gagne, qui perd)

### Sujet 3 — Coût réel d'un achat (au-delà du prix affiché)
- **Pourquoi** : sous-traité par concurrents, +15-20 % de surcoût caché, intent fort
- **Angle PCS** : tableau ligne par ligne sur un cas concret (apport, notaire, banque, garantie, courtier, travaux, ravalement à venir, fonds ALUR, taxe foncière, charges)
- **Articles** : Frais de notaire 2026 ancien vs neuf (calcul réel), Charges et fonds ALUR : ce qu'un acheteur doit demander, Ravalement et travaux votés en AG : ce qu'on hérite, Taxe foncière par commune (+ historique de hausse)

### Sujet 4 — Louer & investir : le marché tendu 2026 (sous-cat Louer & investir)
- **Pourquoi** : tension locative inédite (-7 % stock), interdiction G effective depuis 2025, primo-bailleur en doute
- **Angle PCS** : pas Pinel/LMNP/SCPI à la chaîne, vraie question : louer son ancien logement vaut-il encore le coup en 2026 ?
- **Articles** : Louer son ancien logement en 2026 (DPE, charges, fiscalité), Sortir un bien locatif du statut passoire (coût, aides, ROI), GLI/Visale/garant : qui paie quoi en 2026, Location meublée vs nue (cas concrets)

### Sujet 5 — Vendre dans un marché qui repart doucement (sous-cat Vendre)
- **Pourquoi** : délai moyen 98 jours en 2026, DPE pèse 20 % de vitesse, surestimation = +180 jours
- **Angle PCS** : méthode d'estimation honnête (DVF + comparable récent), réparation vs vente en l'état, négociation
- **Articles** : Estimer son bien sans outil d'agence (méthode DVF), Audit énergétique obligatoire : qui paie, quand, combien, Travaux avant vente : ceux qui rapportent, ceux qui non, Compromis et délais : ce que prévoit la loi 2026

### Sujet 6 — Aides et fiscalité 2026 pour un ménage normal
- **Pourquoi** : MaPrimeRénov rouvert 23 février 2026, PTZ élargi B2/C, plafonds revalorisés +8 à +13 %
- **Angle PCS** : pas "toutes les aides" en liste, parcours par profil (primo-accédant, propriétaire qui rénove, bailleur passoire)
- **Articles** : PTZ 2026 (zones, plafonds, montant réel), MaPrimeRénov 2026 (parcours geste vs ampleur, ce qui sort), Aides locales (cumul, conditions), Taxe foncière et travaux : ce qui se déduit

### 3 angles signature PCS

1. **Chiffres 2026 vérifiables** : prix, frais, taux, aides toujours datés et sourcés (Service-Public, Notaires.fr, France Rénov', baromètre Meilleurs Agents, DVF). Pas d'estimation à la louche.
2. **Cadre réglementaire avant conseil** : DPE, calendrier interdictions, audit obligatoire, droits du locataire, devoir du vendeur. Le droit avant la stratégie.
3. **Ménage normal, pas investisseur** : ni Pinel, ni LMNP, ni SCPI. Un couple, une famille, un primo, un bailleur de son ancien logement. Décisions de vie, pas d'optimisation fiscale.

---

## Phase 3 — Structure de page proposée

### H1
"Immobilier : acheter, vendre, louer en France en 2026" (52 char)

### Intro éditoriale (185 mots)

L'immobilier français de 2026 n'a plus rien à voir avec celui de 2020. Les taux à 3,5 %, l'apport requis qui monte à 15-20 %, le DPE devenu variable centrale, le calendrier d'interdiction de location qui avance — G depuis 2025, F en 2028, E en 2034 —, une réforme du mode de calcul du DPE au 1er janvier 2026 qui fait sortir 850 000 logements du statut de passoire sans un coup de marteau. Le marché ne baisse pas comme certains l'espéraient, il se stabilise autour de +2 à +3 %, avec une pénurie locative inédite dans les grandes villes.

Sur Plus c'est simple, on traite l'immobilier comme une décision de vie, pas un placement. Acheter sa résidence principale, vendre un bien hérité, louer son ancien appartement, comprendre ce qu'on récupère vraiment quand le bailleur garde la caution. Pas de Pinel post-mortem, pas de SCPI à la chaîne, pas de "comment doubler son patrimoine".

Vous trouverez ici des guides chronologiques par parcours, des coûts réels chiffrés en euros 2026, et la lecture du droit qui s'applique vraiment à vous — locataire, propriétaire, primo-accédant, bailleur particulier ou vendeur en mauvais DPE.

### Sections H2 (5 sections dans l'ordre)

**Section 1 — Acheter en 2026 : capacité réelle et parcours** (~120 mots)
La capacité d'emprunt s'est durcie. Apport personnel exigé désormais 15 à 20 % contre 10 % il y a trois ans, taux moyen 3 à 3,5 %, taux d'endettement plafonné à 35 % assurance comprise. À cela s'ajoutent 7 à 8 % de frais de notaire dans l'ancien (2 à 3 % dans le neuf), les frais bancaires, parfois un courtier. Le coût total dépasse facilement +15 % du prix affiché. On détaille parcours, calcul, négociation, et les pièges typiques d'un compromis qu'on signe trop vite.
- Calculer sa capacité d'emprunt en 2026 (à créer)
- Frais de notaire ancien vs neuf : calcul réel (à créer)
- Lire et négocier un compromis sans avocat (à créer)
- Visite d'un bien : 12 points qu'on oublie systématiquement (à créer)

**Section 2 — DPE : la variable qui change tout** (~120 mots)
Le DPE n'est plus une étiquette de fond de dossier, c'est la variable qui décide si un bien se vend en 60 ou 130 jours, si on peut continuer à le louer en 2028, si la banque finance, si la négociation est ouverte. Réforme du mode de calcul au 1er janvier 2026 (coefficient électricité passe à 1,9), 850 000 logements sortent du statut passoire sans travaux. Audit énergétique obligatoire à la vente pour les E, F, G en métropole. Calendrier d'interdiction de location strict.
- Acheter un bien classé F en 2026 (à créer)
- Vendre un E, F, G : audit obligatoire et surcote travaux (à créer)
- Bailleur d'un G : sortir, rénover ou vendre (à créer)
- Réforme DPE 1er janvier 2026 : qui y gagne, qui y perd (à créer)

[Bannière category-intro ici]

**Section 3 — Coût réel d'un achat (au-delà du prix affiché)** (~110 mots)
Le prix affiché ne fait pas le total. Frais de notaire 7-8 % dans l'ancien, charges de copropriété qui peuvent dépasser 3 000 €/an en Île-de-France, fonds de travaux ALUR obligatoire, ravalement et toiture votés en AG dont on hérite à l'achat, taxe foncière qui varie de 500 à 2 500 € selon la commune, travaux DPE qui peuvent coûter 15 000 à 60 000 € sur une maison. On chiffre tout ligne par ligne sur des cas concrets pour qu'aucun acheteur ne signe à l'aveugle.
- Charges et fonds ALUR : ce qu'il faut demander avant de signer (à créer)
- Travaux et ravalement votés en AG : ce qu'on hérite (à créer)
- Taxe foncière par commune : ordre de grandeur et historique (à créer)
- Travaux DPE : combien pour passer de G à D, de F à C (à créer)

[Bannière category-mid ici]

**Section 4 — Louer et investir dans un marché tendu** (~115 mots)
La tension locative en 2026 est inédite : -7 % de stock en un an, jusqu'à -30 à -50 % d'annonces longue durée à Paris, Lyon, Nice, Bordeaux. Beaucoup de petits bailleurs sortent leur G du locatif plutôt que d'engager les travaux, d'autres basculent en saisonnier. Pour qui veut louer son ancien logement en 2026, la question n'est plus "Pinel ou pas", c'est "mon bien tiendra-t-il jusqu'en 2028 et avec quel rendement réel après charges, taxes et travaux DPE".
- Louer son ancien logement en 2026 : check-list complète (à créer)
- Sortir un locatif du statut passoire : coût et aides (à créer)
- GLI, Visale, garant : qui paie quoi en 2026 (à créer)
- Location meublée vs nue : vrais cas concrets (à créer)

**Section 5 — Vendre en 2026 et aides à connaître** (~115 mots)
Délai moyen de vente d'une maison en 2026 : 98 jours, mais 60 à Lille et 109 à Nice. Un bien A ou B se vend 20 % plus vite qu'un E. Une surestimation de 10 % rallonge la vente de 180 jours en moyenne. Côté aides, MaPrimeRénov a rouvert son guichet le 23 février 2026 (parcours par geste ou rénovation d'ampleur jusqu'à 80 % de 40 000 €), PTZ élargi aux zones B2 et C, plafonds revalorisés +8 à +13 %. On explique quel dispositif pour quel profil.
- Estimer son bien sans outil d'agence (méthode DVF) (à créer)
- Travaux avant vente : ceux qui rapportent vraiment (à créer)
- PTZ 2026 : zones, plafonds, montant réel (à créer)
- MaPrimeRénov 2026 : parcours par geste vs ampleur (à créer)

### FAQ

**Q1 :** Le prix de l'immobilier va-t-il baisser en 2026 en France ?
**R1 :** Non, pas globalement. Le marché s'est stabilisé après la correction de 2023-2024 et la majorité des grandes villes évoluent dans une fourchette de +1 à +3 %. Certaines villes restent en baisse modérée (Nantes par exemple), d'autres repartent franchement (Marseille, Strasbourg). Les taux de crédit se sont stabilisés autour de 3 à 3,5 % et le nombre d'acheteurs progresse. La fenêtre "baisse généralisée" qu'attendaient certains primo-accédants en 2024 ne s'est pas ouverte.

**Q2 :** Puis-je encore louer un logement classé F ou G en 2026 ?
**R2 :** Les logements G sont interdits à la location depuis le 1er janvier 2025 — ni nouveau bail, ni renouvellement. Les F restent louables jusqu'au 31 décembre 2027 (interdiction au 1er janvier 2028), et les E jusqu'au 31 décembre 2033 (interdiction au 1er janvier 2034). Réforme du DPE au 1er janvier 2026 : la méthode de calcul change pour les logements chauffés à l'électricité (coefficient passe à 1,9), faisant sortir près de 850 000 logements du statut passoire sans travaux. Faites refaire votre DPE si vous êtes en chauffage électrique et classé F ou G.

**Q3 :** Combien coûtent vraiment les frais de notaire en 2026 ?
**R3 :** Dans l'ancien, comptez 7 à 8 % du prix de vente. Dans le neuf, 2 à 3 % seulement. Cette différence vient des droits d'enregistrement : 5,80665 % dans 97 départements (taux maximum), 5,09 % seulement dans l'Indre, l'Isère, le Morbihan et Mayotte. À noter, 80 % de ce qu'on appelle "frais de notaire" sont en fait des taxes reversées à l'État et aux collectivités — le notaire lui-même ne touche que 10 à 15 % du total en émoluments réglementés.

**Q4 :** L'audit énergétique est-il obligatoire pour vendre en 2026 ?
**R4 :** Oui, pour les maisons individuelles classées E, F ou G en métropole. Les F et G depuis avril 2023, les E depuis le 1er janvier 2025. L'audit propose des scénarios de travaux chiffrés et a une durée de validité de cinq ans. À noter, il ne s'applique pas à la vente d'un appartement en copropriété, ni à l'Outre-mer (calendrier décalé). Comptez 500 à 1 500 € selon la surface et la complexité du logement.

**Q5 :** Quelles aides en 2026 pour un primo-accédant ou un propriétaire qui rénove ?
**R5 :** Côté achat : le PTZ a été élargi en 2026 aux zones B2 et C pour le neuf (collectif et maison individuelle), et l'ancien avec travaux représentant 25 % minimum du coût total reste éligible. Plafonds de revenus revalorisés de 8 à 13 %, montant pouvant atteindre 50 % du coût en zone tendue. Côté rénovation : MaPrimeRénov a rouvert le 23 février 2026, deux parcours (par geste ou rénovation d'ampleur jusqu'à 80 % de 40 000 €). L'isolation des murs et les chaudières biomasse sont sorties du parcours par geste depuis le 1er janvier 2026.

### Maillage interne
- Vers **Compatibilimètre** : "Évaluer si un bien immobilier est compatible avec votre budget, vos besoins et vos contraintes"
- Vers pilier **Travaux** : "Quand l'achat devient chantier : rénovation lourde, DPE, gros œuvre"
- Vers pilier **Décoration** : "Une fois acheté ou loué : comment décorer sans casser votre logement"
- Vers article **"Tendances 2026 maison"** : volet immobilier (DPE, marché, ce qui change)
- Lien retour depuis chaque article immobilier vers cette page pilier

---

## Phase 4 — SEO

- **Title tag** : "Immobilier 2026 : acheter, vendre, louer en France — Plus c'est simple" (66 char — à raccourcir à "Immobilier 2026 : acheter, vendre, louer en France" 50 char si limite stricte)
- **Meta description** : "Acheter, vendre, louer un logement en France en 2026. DPE, frais réels, PTZ, MaPrimeRénov, marché : guides chiffrés pour ménages normaux." (140 char)
- **Mot-clé principal** : "immobilier" (très concurrentiel, viser positions 8-20 sur requêtes longue traîne plutôt que la racine)
  - Alternative principale plus accessible : "acheter logement France" / "marché immobilier 2026"
- **Mots-clés secondaires** :
  - "DPE 2026 location" (volume élevé, intent réglementaire chaud)
  - "frais de notaire 2026" (volume élevé, intent transactionnel)
  - "PTZ 2026 conditions" (volume élevé, intent commercial)
  - "primo accédant 2026" (volume moyen, peu concurrentiel sur l'angle "ménage normal")
  - "audit énergétique vente obligatoire" (volume moyen, intent réglementaire)
- **PAA à cibler** :
  - "Le prix de l'immobilier va-t-il baisser en 2026 ?"
  - "Quels logements ne pourront plus être loués en 2026 ?"
  - "Combien d'apport pour acheter en 2026 ?"

---

## Recommandations pour Agent Copywriting

- **Ton** : factuel, direct, daté. Pas de "réalisez votre rêve immobilier". Préférer "voici ce que ça coûte en 2026, voici ce que dit la loi, voici qui paie quoi".
- **Sources d'autorité** : Service-Public.fr, Notaires.fr, France Rénov', economie.gouv.fr, Insee, baromètre Meilleurs Agents (cité, pas recopié), base DVF (Demande de Valeurs Foncières), ADIL (Agence Départementale d'Information sur le Logement).
- **Chiffres 2026 à toujours dater** : taux crédit (3-3,5 %), apport requis (15-20 %), frais notaire (7-8 % ancien, 2-3 % neuf), délai vente moyen (98 jours), DPE calendrier (G 2025, F 2028, E 2034), réforme méthode (1er janvier 2026, coef électricité 1,9), tension locative (-7 % stock annuel).
- **Angles à éviter** : Pinel/LMNP/SCPI/Denormandie pour investisseur ; "10 conseils pour acheter" génériques ; "comment doubler son patrimoine" ; "ces villes où la rentabilité dépasse 8 %" ; baromètres recopiés sans valeur ajoutée ; "comparatif assurance emprunteur" déguisé en conseil.
- **Format** : guides denses chronologiques (parcours d'achat, parcours vente, parcours bailleur), tableaux de coût ligne par ligne, exemples chiffrés sur cas concret (un T3 à Lyon, une maison à Nantes), encadré "ce que dit la loi en 2026" systématique.
- **Anti-IA** : article impossible à résumer par ChatGPT sans perdre les chiffres datés et les cas concrets. Citations de textes réglementaires précis (loi Climat et Résilience, articles du Code de la construction). Exemples vécus, contradictions assumées ("ce qu'on lit partout mais qui est faux en 2026").

---

## Pistes écartées

- **"Investir en SCPI" / "Pinel post-mortem" / "LMNP"** : hors cible PCS, traité partout, public investisseur CSP+.
- **"Acheter à l'étranger / résidence secondaire"** : hors cœur de cible, niche, mieux traité par presse spécialisée.
- **"Crédit immobilier / comparatif banque"** : déguisé en lead-gen partout (Pretto, Meilleurtaux), zone polluée. Ne traiter que côté pédagogique (capacité d'emprunt, taux) pas de comparatif.
- **"Estimation gratuite / outil d'estimation"** : pousse les concurrents vers leur outil maison. Préférer méthode DVF + bon sens + comparable récent.
- **"Déménagement"** : croise lifestyle, peu de SEO premium, périphérique.
- **"Viager / nue-propriété"** : niche, mieux traité par sites spécialisés.
- **"Construction maison neuve / CCMI"** : à laisser à la verticale Travaux, plus pertinent comme pilier propre.

---
