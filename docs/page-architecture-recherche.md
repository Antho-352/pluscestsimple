# Page Architecture — Recherche & recommandations

## Méthodologie

- **Concurrents audités** : 7 (2 fetchés directement, 5 reconstitués via WebSearch + sources tierces). **Date** : 21 mai 2026
- **WebFetch réussis** : archdaily.com, maisonapart.com (page architecture-patrimoine)
- **WebFetch échoués** : architectes.org (timeout), cotemaison.fr/architecture (bloqué), maisons-de-l-architecte.com (ECONNREFUSED), monjardin-mamaison.fr/maison (404), detail.de (403)
- **WebSearch** : 9 requêtes (styles régionaux, pavillon 70-80, extension bois/parpaing, surélévation, architecte 150 m², ABF, CAUE, PLU/emprise, diagnostic structure)
- **Limites** : pas de version FR sur ArchDaily ni Detail (références internationales seulement) ; reconstitution indirecte pour les médias FR bloqués. Volumes SEO inférés via signaux SERP, pas d'accès keyword planner.

---

## Phase 1 — Audit concurrentiel

### 1. ArchDaily (international, pas de version FR)
Rubriques : Projects (résidentiel, hospitality, culturel), News & Articles, Products & BIM, Professionals, Awards, Events. Angles : durabilité, matériaux, diversité globale, pratiques émergentes, impact social. Ton **pro accessible** — pas grand public, viseur architectes et étudiants. Pas d'angle "comment je rénove ma maison". **Force** : autorité mondiale, photos pro. **Faiblesse** : aucun ancrage France, pas de réglementation, inutilisable pour un propriétaire d'une longère en Bretagne. **Opportunité PCS** : pas un concurrent direct, mais source d'inspiration sur la qualité visuelle.

### 2. Architectes.org (Ordre des architectes)
Rubriques principales : Trouver un architecte (annuaire), FAQ pour particuliers, Démarches, Actualités pro. Ton **institutionnel**, écrit par l'Ordre. Cible mixte : architectes (l'essentiel) + particuliers (sections FAQ). **Force** : autorité légale (cadre réglementaire fiable), annuaire géolocalisé. **Faiblesse** : ton corporate, articles "Trouver un architecte" passe-partout, pas de cas pratiques. **Opportunité PCS** : citable comme source d'autorité sur le cadre légal (recours obligatoire 150 m², missions architecte).

### 3. Maisons de l'Architecte (maisons-de-l-architecte.com)
Site bloqué au fetch, reconstitué : éditeur grand public faisant la promotion de maisons signées par des architectes (constructions neuves). Angles : maisons d'architecte clé en main, projets esthétiques, témoignages clients. Ton **promotionnel-grand public**. **Force** : photos pro, mise en avant de projets concrets. **Faiblesse** : pas de pédagogie sur le bâti existant, pas de patrimoine, focus neuf seulement. **Opportunité PCS** : zone aveugle complète sur rénovation, ABF, contraintes — PCS peut prendre tout cet espace.

### 4. Detail.de (référence pro allemande)
Rubriques : projets internationaux, magazine technique, détails de construction, théorie. Ton **ultra-pro**, public architectes diplômés. Format : plans coupes détaillées, analyses de matériaux, articles longs en allemand/anglais. **Force** : référence absolue pour la précision technique. **Faiblesse** : illisible pour un propriétaire, payant en partie, anglo/germanique. **Opportunité PCS** : pas un concurrent — citable comme caution scientifique sur des détails de construction (toiture, isolation murs anciens).

### 5. Côté Maison (cotemaison.fr/architecture)
Site bloqué, reconstitué via SERP et Pinterest officiel. Rubrique Architecture : "10 architectes à suivre en 2026", reportages chez des architectes, photos d'intérieurs signés, focus sur projets remarquables (label Architecture Contemporaine Remarquable). Ton **lifestyle premium**, public CSP+. **Force** : photos pro, signature éditoriale. **Faiblesse** : zéro utilité pour propriétaire qui veut comprendre son bâti, zéro réglementation, zéro budget. **Opportunité PCS** : tout l'espace utilitaire est libre.

### 6. Mon Jardin Ma Maison (monjardin-mamaison.fr)
Site bloqué (URL retournant 404 sur la rubrique maison). Reconstitué : magazine grand public mêlant maison et jardin, dominante jardin. Angles maison faibles, peu d'architecture pure. **Force** : marque connue (presse magazine). **Faiblesse** : architecture quasi absente, sujet jardin prioritaire. **Opportunité PCS** : pas un vrai concurrent sur l'architecture.

### 7. Maison à Part (maisonapart.com — rubrique Architecture & Patrimoine)
Rubrique Architecture & Patrimoine accessible. Sous-rubriques : Intérieurs (57), Jardins/terrasses (11), Tertiaire (23), Architecture, Design, Métiers d'art. Articles types : "Une résidence à Tours labellisée 'architecture contemporaine remarquable'", "Journées du patrimoine : demeures de personnes célèbres", "Casa Franca, résidence d'artiste en terre crue à Paris", lauréats Rubans du Patrimoine. Ton **informatif-enthousiaste**, vulgarisateur cultivé. **Force** : couverture événementielle pro, prix d'architecture, valorisation des matériaux durables (bois, terre crue, brique). **Faiblesse** : c'est du reportage, pas de la prise de décision propriétaire. Pas de "puis-je faire mon extension ?", pas de méthode ABF, pas de prix au m². **Opportunité PCS** : la zone "décision propriétaire" est libre, c'est exactement la cible PCS.

### Synthèse audit

**Sujets sur-traités (saturés)** :
- Annuaires architectes (architectes.org, architectes-pour-tous.fr, plateformes type Hemea/Architecteo)
- Reportages "belles maisons d'architecte" sans process ni prix réel
- Tendances architecture 2026 génériques (warm minimalism, biophilie, matériaux nobles — recopiés)
- Listes "Tour de France des maisons typiques" purement touristiques
- Prix d'extension au m² sur des sites lead-gen (Hemea, Greenkub, Travauxavenue) — tous présentent les mêmes fourchettes sans dire ce qui plombe vraiment un budget

**Sujets sous-traités (opportunités réelles)** :
- **Reconnaître son style de maison pour mieux rénover** : article rare et grand public, traité par des blogs immobiliers ou de promoteurs régionaux, jamais en pilier
- **ABF expliqué simplement au propriétaire** : sujet anxiogène, traité par blogs d'avocats ou d'architectes (jargon), jamais "voilà ce que tu peux/ne peux pas, et comment recourir"
- **Extension réalisable vs impossible** : tout le monde dit "PLU + 40 m² + 150 m²", personne ne dit "si ton CES est saturé, c'est mort, voilà comment vérifier"
- **Isolation murs anciens (pierre, pisé, colombages)** : sujet technique traité par les artisans bio (matériaux-naturels.fr), absent des grands médias maison
- **CAUE gratuit** : ressource publique méconnue, citée nulle part dans les médias maison
- **Pavillon années 70-80** : sujet immense (60 % du parc) traité de manière utilitaire par les sites de pros (La Maison Saint-Gobain, IZI by EDF), aucun angle critique grand public
- **Architecture régionale comme contrainte de rénovation** (pas folklore) : "tu as une longère, voilà ce que ça implique pour l'isolation, la couverture, les ouvertures"

**Angle différenciant PCS** : seul média qui part du logement réel du lecteur ("voici comment reconnaître ton bâti, voici ce que tu peux faire dedans, voici qui appeler gratuitement, voici les pièges qui coûtent cher"). Anti-reportage chic, anti-annuaire promo, pro-propriétaire éclairé.

---

## Phase 2 — 6 sujets retenus

### Sujet 1 — Reconnaître son style de maison (pilier)
- **Pourquoi** : 60 % du parc résidentiel français est antérieur à 1990, pourtant le lecteur ne sait souvent pas nommer son bâti. Et sans nommer, impossible de rénover juste.
- **Angle PCS** : guide visuel ancré dans la rénovation ("si vous avez X, alors les contraintes sont Y, les pièges Z"), pas une fiche touristique
- **Articles** : Haussmannien (façade, hauteur sous plafond, moulures), Art déco/années 30 (béton armé, géométries, encadrements), pavillon années 70-80 (parpaing creux, amiante, passoire thermique), maisons régionales (longère, mas, maison basque, chaumière, alsacienne à colombages — par contrainte de rénovation), bourgeoise XIXᵉ provinciale (différente du haussmannien)

### Sujet 2 — ABF : décoder les Bâtiments de France
- **Pourquoi** : sujet anxiogène pour 30 % du parc en secteur protégé (centres-villes anciens, abords monuments, sites patrimoniaux remarquables). Personne n'explique simplement.
- **Angle PCS** : ce qui déclenche un avis ABF, ce qui passe/passe pas (volets, menuiseries, enduits, tuiles), comment recourir auprès du préfet de région sous 2 mois, comment préparer un projet pour maximiser l'acceptation
- **Articles** : Comment savoir si vous êtes en secteur ABF, Changer fenêtres et volets en secteur protégé, Ravalement façade et enduit imposé (chaux vs ciment), Recours contre un avis ABF (modèle + délais), Préparer un dossier qui passe ABF du premier coup

### Sujet 3 — Extension : faisable, contrainte, impossible
- **Pourquoi** : projet rêvé n°1 des propriétaires, sujet sur-traité côté "prix m²", sous-traité côté "ça passe ou pas"
- **Angle PCS** : vérifier la faisabilité AVANT de payer un architecte. Diagnostic PLU (CES, emprise au sol, prospect, hauteur, gabarit), seuils d'autorisation (5/20/40/150 m²), choix structurel (bois 1 800-3 000 €/m² vs parpaing 1 200-2 000 €/m² vs véranda 900-3 500 €/m²)
- **Articles** : Lire son PLU pour savoir si l'extension est possible, Bois vs parpaing vs véranda (vrai comparatif chiffré), Seuil 150 m² et architecte obligatoire (exceptions), Surélévation maison individuelle (faisabilité technique + prix 1 500-5 000 €/m²), Refus d'extension : recours et adaptation mineure

### Sujet 4 — Rénover une maison ancienne sans la massacrer
- **Pourquoi** : énorme angle mort. Les propriétaires de pierre, pisé, colombages détruisent leur bâti à coups d'isolation cimentée et de PVC. Aucun grand média ne le dit clairement.
- **Angle PCS** : perspirance, choix d'isolant respirant (chaux-chanvre, laine de bois, terre-paille), refus du polystyrène expansé, enduits chaux vs ciment, menuiseries bois vs PVC, diagnostic structure avant tout
- **Articles** : Pourquoi isoler un mur en pierre avec du polystyrène est une faute, Enduit chaux vs ciment (et comment voir ce qui est en place), Isoler une maison à colombages sans la pourrir, Maison en pisé : règles spécifiques, Diagnostic structurel avant rénovation (qui appeler, prix)

### Sujet 5 — Pavillon années 70-80 : que faire avec
- **Pourquoi** : ~60 % du parc résidentiel pavillonnaire français, vrais problèmes (passoire thermique, amiante, plomb), aucun pilier grand public dédié, juste des articles "rénovation maison années 70" produits par des sites lead-gen
- **Angle PCS** : reconnaître l'époque, vérifier amiante (dalles vinyle, isolants, fibrociment) et plomb (peintures), prioriser les travaux (toiture > menuiseries > murs > ventilation), choisir entre rénover et démolir/reconstruire
- **Articles** : Identifier un pavillon des années 70 (signes visuels), Amiante dans les pavillons : où chercher, diagnostic obligatoire, prix dépose, Ordre des travaux pour rénover un pavillon, Rénover ou raser-reconstruire : critères de choix, Pavillon en parpaing creux : isolation par l'extérieur vs intérieur

### Sujet 6 — Ressources publiques gratuites pour propriétaire
- **Pourquoi** : sous-utilisées massivement (CAUE, ADIL, France Rénov', Anah), et personne ne les liste ensemble côté grand public maison
- **Angle PCS** : guide pratique avec coordonnées départementales, ce qu'on peut obtenir gratuitement avant de signer un devis
- **Articles** : CAUE : architecte conseil gratuit (comment ça marche en vrai), France Rénov' et MaPrimeRénov' (cas où c'est utile), ADIL pour les questions juridiques travaux, Anah pour le bâti ancien

### 3 angles signature PCS

1. **Diagnostic avant projet** : "avant de rêver à ton extension, vérifie ton PLU et ton CES" — méthode obligatoire d'identification du bâti avant tout conseil
2. **Bâti ancien respiré** : refus assumé des solutions cimentées/PVC sur bâti pierre-pisé-colombages, même si elles dominent le marché grand public — angle technique fort hérité de l'écoconstruction, jamais transposé en média mainstream
3. **Gratuité publique** : CAUE, ADIL, France Rénov', recours préfet contre ABF — toutes les ressources gratuites listées, jamais évoquées par Côté Maison ou Maison à Part

---

## Phase 3 — Structure de page proposée

### H1
"Architecture : comprendre votre bâti pour mieux rénover" (54 char)

### Intro éditoriale (185 mots)

Avant d'arracher une moulure, de boucher une fenêtre cintrée ou de coller du polystyrène sur un mur en pierre, il faut savoir ce qu'on a entre les mains. C'est l'idée qui guide cette rubrique : l'architecture n'est pas un sujet de magazine, c'est la première étape d'une rénovation qui ne ruine pas un logement.

On part de votre maison réelle. Un haussmannien de 1880 ne se rénove pas comme un pavillon des années 75. Une longère en pisé n'aime pas le ciment. Un bien en secteur ABF impose des volets et des menuiseries précises. Une extension de 38 m² ne passe pas le même Cerfa qu'une extension de 41 m². Ces nuances valent des milliers d'euros et la santé physique du bâti.

Vous trouverez ici de quoi identifier le style et l'époque de votre maison, comprendre ce que vous pouvez et ne pouvez pas faire (PLU, ABF, surface), rénover sans abîmer le patrimoine, et tirer parti des ressources publiques gratuites trop peu utilisées (CAUE, ADIL). Que vous habitiez une bourgeoise XIXᵉ ou un pavillon d'après-guerre, l'objectif est le même : décider mieux, dépenser moins, ne pas casser ce qui tient.

### Sections H2 (5 sections dans l'ordre)

**Section 1 — Reconnaître son style et son époque** (~110 mots)
La première erreur en rénovation, c'est de plaquer une recette générique sur un bâti qu'on n'a pas identifié. Un immeuble haussmannien (1850-1914), un pavillon des années 30 en béton armé, une maison régionale (longère normande, mas provençal, etxe basque, maison alsacienne à colombages) ou un pavillon d'après-guerre n'imposent ni les mêmes contraintes ni les mêmes opportunités. Nos guides partent du visuel — façade, ouvertures, matériaux, toiture — pour vous aider à dater et nommer votre logement, puis listent les implications concrètes : ce qui se modifie, ce qui se conserve, ce qui dimensionne le budget.
- Reconnaître un immeuble haussmannien (et ce que ça impose)
- Maison des années 30 : Art déco, béton armé, géométries
- Pavillon des années 70-80 : signes visuels et limites
- Maisons régionales : longère, mas, basque, alsacienne, chaumière
- Bourgeoise XIXᵉ provinciale : ce qui la différencie d'un haussmannien

**Section 2 — ABF, PLU, autorisations : ce que vous avez le droit de faire** (~115 mots)
Avant tout projet visible depuis l'extérieur, deux niveaux de règles : le PLU communal (emprise au sol, hauteur, gabarit, matériaux imposés), et — pour 30 % du parc en secteur protégé — l'avis de l'Architecte des Bâtiments de France. Changer une fenêtre, repeindre un volet, poser une véranda peut être bloqué pour une couleur ou un matériau. On décortique : comment savoir si vous êtes concerné, ce que l'ABF accepte ou refuse, comment recourir au préfet de région sous 2 mois, et comment lire un PLU sans être urbaniste.
- Êtes-vous en secteur ABF ? Comment vérifier
- Changer volets et menuiseries en secteur protégé
- Lire son PLU : CES, emprise au sol, prospect, hauteur
- Recours contre un avis ABF défavorable (modèle + délais)
- CAUE : architecte conseil gratuit avant tout projet

[Bannière category-intro ici]

**Section 3 — Extensions : faisable, contraint, impossible** (~115 mots)
Tout le monde rêve d'agrandir, beaucoup essaient, certains se prennent un refus de permis. Les seuils d'autorisation (déclaration préalable entre 5 et 40 m² en zone PLU, permis de construire au-delà, architecte obligatoire si total ≥ 150 m²) ne sont qu'une partie. Le PLU local peut bloquer un projet par le coefficient d'emprise au sol, la hauteur, le prospect par rapport au voisin. Comparatif chiffré bois/parpaing/véranda, surélévation, faisabilité technique (les fondations supportent-elles ?).
- Vérifier la faisabilité PLU avant de payer un architecte
- Extension bois (1 800-3 000 €/m²) vs parpaing (1 200-2 000 €/m²) vs véranda (900-3 500 €/m²)
- Seuil 150 m² et architecte obligatoire : exceptions
- Surélévation : faisabilité technique et prix (1 500-5 000 €/m²)
- Refus d'extension : recours et adaptation mineure

[Bannière category-mid ici]

**Section 4 — Rénover du bâti ancien sans le détruire** (~120 mots)
La grande erreur de la rénovation française, c'est d'appliquer aux murs en pierre, pisé ou colombages les mêmes techniques qu'à un pavillon béton. Résultat : condensation, salpêtre, dégradation accélérée d'un bâti qui tenait debout depuis trois siècles. Le principe à retenir : un mur ancien doit respirer (perspirance). Donc enduit à la chaux (pas ciment), isolant respirant (chaux-chanvre, laine de bois, terre-paille), menuiseries bois plutôt que PVC. Avant tout : diagnostic structure pour vérifier murs porteurs, fondations, charpente.
- Pourquoi le polystyrène détruit un mur en pierre
- Enduit chaux vs ciment : différences et coûts
- Isoler une maison à colombages sans la pourrir
- Maison en pisé : règles spécifiques (Lyonnais, Dauphiné, Bourgogne)
- Diagnostic structurel avant rénovation : qui appeler, prix

**Section 5 — Pavillon des années 70-80 : que faire de ce parc immense** (~110 mots)
Près de 60 % du parc pavillonnaire français date de cette période. Maisons souvent énergivores, parfois amiantées (dalles vinyle, fibrociment toiture, isolants), construites en parpaing creux mal isolé. Mais la structure est saine, la disposition fonctionnelle, l'emprise au sol confortable. Question fréquente : on rénove ou on rase et on reconstruit ? On donne les critères de décision, l'ordre des priorités travaux (toiture, menuiseries, isolation, ventilation), et le diagnostic obligatoire amiante avant tout chantier.
- Identifier un pavillon années 70-80 (signes visuels)
- Amiante : où chercher, diagnostic obligatoire, coût dépose
- Ordre des travaux pour rénover un pavillon
- Rénover ou raser-reconstruire : critères de choix
- ITE vs ITI sur parpaing creux : que choisir

### FAQ

**Q1 :** Comment savoir si ma maison est en secteur ABF (Architecte des Bâtiments de France) ?
**R1 :** Trois moyens : consulter le PLU de votre commune (rubrique servitudes), utiliser le géoportail Atlas des Patrimoines du ministère de la Culture (gratuit en ligne), ou demander à votre mairie. Vous êtes concerné si votre bien est dans le périmètre d'un monument historique (500 m de rayon classique, parfois adapté), dans un Site Patrimonial Remarquable, ou un site classé. Dans ce cas, tous travaux visibles depuis l'extérieur — changement de fenêtre, peinture de volet, ravalement, véranda — passent par un avis de l'ABF.

**Q2 :** À partir de quelle surface l'architecte est-il obligatoire pour mon extension ?
**R2 :** À partir de 150 m² de surface de plancher totale après travaux. Si votre maison fait 120 m² et que vous ajoutez 35 m², le total dépasse 150 m² : architecte obligatoire pour le permis de construire. Exception : seules les personnes physiques construisant pour elles-mêmes peuvent s'en passer en dessous de ce seuil. Une SCI familiale, un propriétaire bailleur, ou toute société doivent recourir à un architecte quelle que soit la surface. Les travaux relevant d'une déclaration préalable (moins de 40 m² en zone PLU) ne sont jamais soumis à cette obligation.

**Q3 :** Comment isoler un mur ancien en pierre sans l'abîmer ?
**R3 :** Surtout pas avec du polystyrène expansé ni d'enduit ciment : ces matériaux bloquent la migration de vapeur d'eau et provoquent condensation et dégradation du bâti. Préférer des isolants respirants côté intérieur (enduit chaux-chanvre, laine de bois, terre-paille) en épaisseur modérée (8-12 cm max), associés à un enduit de finition à la chaux. Côté extérieur, ITE déconseillée sur pierre apparente (risque de "manchonnage" du mur). Avant tout : diagnostic d'humidité et de structure. Le CAUE de votre département peut vous orienter gratuitement.

**Q4 :** Mon extension est-elle techniquement possible sur mon terrain ?
**R4 :** Vérifiez d'abord trois éléments dans votre PLU local : le coefficient d'emprise au sol (CES, par exemple 0,3 sur 1 000 m² = 300 m² maximum constructibles), la hauteur maximale autorisée, et les règles de prospect (distance aux limites séparatives). Si votre maison existante consomme déjà l'essentiel du CES, l'extension peut être bloquée. Pour 100-300 €, un géomètre ou un bureau d'études peut faire la vérification. Gratuit : un rendez-vous au CAUE de votre département avec photos et plan cadastral.

**Q5 :** Combien coûte la rénovation complète d'une maison ancienne en France ?
**R5 :** Entre 1 500 et 2 500 €/m² pour une rénovation complète (sols, murs, plafonds, électricité, plomberie, isolation, menuiseries), selon l'état structurel et la région. Si gros œuvre touché (reprise de fondations, ouverture de murs porteurs, charpente), comptez 2 500 à 4 000 €/m². Un diagnostic structurel préalable (1 000-3 000 € selon surface) évite les très mauvaises surprises : fondations fragilisées, mérule, charpente attaquée. Pour un pavillon des années 70 plus simple : 1 000-1 800 €/m² pour une rénovation thermique + esthétique.

### Maillage interne
- Vers **Compatibilimètre** : "Évaluer si votre projet d'extension ou rénovation est compatible avec votre bâti, votre PLU et votre budget"
- Vers pilier **Travaux** : "Du gros œuvre à la finition : entreprises, devis, calendrier"
- Vers pilier **Immobilier** : "Acheter une maison ancienne : ce que la façade et la structure révèlent avant l'offre"
- Vers pilier **Décoration** : "Une fois le bâti respecté, la décoration suit le style de la maison"
- Lien retour depuis chaque article architecture vers cette page pilier

---

## Phase 4 — SEO

- **Title tag** : "Architecture : comprendre son bâti pour rénover — Plus c'est simple" (60 char, à raccourcir si besoin → "Architecture maison : reconnaître et rénover — Plus c'est simple" / 60 char)
- **Meta description** : "Reconnaître son style de maison, décoder ABF et PLU, rénover sans abîmer un bâti ancien : guides concrets pour propriétaires en France." (143 char)
- **Mot-clé principal** : "architecture maison" (~30-60k/mois FR estimé, concurrentiel mais intent mixte propriétaire/pro)
- **Mots-clés secondaires** :
  - "reconnaître style maison" (longue traîne qualifiée, faible concurrence)
  - "architecte des bâtiments de france" (volume moyen, intent juridique fort, peu de contenu grand public)
  - "extension maison autorisation" (volume élevé, intent commercial)
  - "rénover maison ancienne" (volume très élevé, ultra concurrentiel mais bonne longue traîne)
  - "isoler mur pierre" (volume moyen, niche technique, conversion forte)
- **PAA à cibler** :
  - "Comment savoir si ma maison est dans un secteur ABF ?"
  - "Quelle autorisation pour une extension de maison en 2026 ?"
  - "Comment reconnaître le style architectural d'une maison ?"

---

## Recommandations pour Agent Copywriting

- **Ton** : factuel, technique-accessible, anti-magazine. Pas de "le charme inimitable du bâti ancien". Préférer "voici comment dater votre maison, voici ce qui se rénove, voici les pièges qui coûtent cher".
- **Sources d'autorité** : Service-Public.fr (urbanisme, démarches), ministère de la Culture (ABF, recours), Ordre des architectes (cadre légal), Anah (bâti ancien, MaPrimeRénov'), CAUE départementaux (conseil gratuit), Insee (parc résidentiel), DPE/diagnostics réglementés.
- **Ressources à citer systématiquement** : CAUE (architecte conseil gratuit), ADIL (juridique travaux), France Rénov' (aides), Atlas des Patrimoines (vérifier zone ABF), Géoportail urbanisme (consulter PLU).
- **Acteurs métier à citer** : bureaux d'études structure (diagnostic murs porteurs), entreprises éco-construction (chaux, chanvre), diagnostiqueurs certifiés (amiante, plomb, DPE), géomètres-experts.
- **Angles à éviter** : "Maisons d'architecte de rêve" ; tendances 2026 génériques sans recul ; reportages chic sans prix réels ni démarches concrètes ; promotion déguisée de plateformes d'architectes en ligne ; "comment trouver son architecte" sans angle propre (sur-traité).
- **Format** : guides denses, sommaire ancré, schémas/illustrations pour reconnaissance visuelle (façades, modénatures), prix systématiques au m² ou par poste, ressources publiques en encadré.
- **Anti-IA** : article impossible à résumer par ChatGPT sans perdre l'essentiel. Détails techniques précis (pourcentages, dates de réglementation, articles de loi cités, prix par fourchette), exemples régionaux nommés (longère du Cotentin, mas du Lubéron, etc.), contradictions explicites avec le discours dominant ("contrairement à ce qu'on lit partout, l'ITE en polystyrène sur mur pierre est une faute technique").

---

## Pistes écartées

- **"Belles maisons d'architecte 2026"** : reportage chic, hors ADN PCS (décision > inspiration). Côté Maison fait ça.
- **"Trouver un architecte"** : déjà saturé par architectes.org, Architecteo, Hemea, plateformes lead-gen. Aucun angle propre à creuser.
- **"Tendances architecture 2026"** : recopié partout (warm minimalism, biophilie, matériaux nobles). Préférer un article critique unique sur ce qui dure dans le résidentiel français.
- **"Architecture contemporaine internationale"** : ArchDaily, Detail font mieux. Hors cible propriétaire français.
- **"Maisons préfabriquées / containers"** : niche, hors-sujet propriétaire courant. À traiter ponctuellement si extension/dépendance.
- **"Histoire de l'architecture française"** : académique, sans utilité décisionnelle pour le lecteur cible.
- **"Tourisme et patrimoine"** : sujet voyage, croise sans servir.

---

## Récapitulatif (200 mots)

**6 sujets retenus** : (1) Reconnaître son style de maison — guide visuel par époque et région orienté rénovation ; (2) ABF décodé pour le propriétaire — savoir si on est concerné, ce qui passe, comment recourir ; (3) Extensions faisables vs impossibles — diagnostic PLU avant projet, comparatif chiffré bois/parpaing/véranda, surélévation ; (4) Rénover du bâti ancien sans le détruire — perspirance, chaux vs ciment, isolants respirants, refus du polystyrène ; (5) Pavillon années 70-80 — amiante, ordre des travaux, rénover ou raser ; (6) Ressources publiques gratuites (CAUE, ADIL, Anah, France Rénov').

**3 angles signature** : diagnostic obligatoire avant projet (lire PLU, dater son bâti, vérifier ABF) ; bâti ancien respiré (refus assumé des techniques cimentées sur pierre/pisé/colombages) ; gratuité publique systématiquement citée (CAUE, recours préfet contre ABF).

**Title proposé** : "Architecture maison : reconnaître et rénover — Plus c'est simple" (60 char).

**Mot-clé principal** : "architecture maison" (volume estimé 30-60k/mois FR, intent propriétaire dominant).

**3 risques** : (1) confusion lecteur entre rubrique Architecture et rubrique Travaux — délimiter clairement (Architecture = comprendre, décider ; Travaux = exécuter) ; (2) tentation de virer académique/jargon sur ABF, pisé, perspirance — exiger vulgarisation stricte avec exemples concrets ; (3) dépendance forte au cadre légal qui évolue (seuils PLU, MaPrimeRénov') — prévoir maintenance annuelle des articles avec date de dernière mise à jour visible.
