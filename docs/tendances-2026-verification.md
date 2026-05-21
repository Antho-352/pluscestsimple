# Vérification Tendances 2026 — Agent 2

## Méthodologie

**LIMITATION CRITIQUE DE CETTE SESSION** : les outils réseau (`WebFetch`, `WebSearch`, `curl` via Bash) ont été refusés par les permissions de la session. La vérification factuelle externe (ouverture des URLs Agent 1, croisement de sources primaires) n'a pas pu être réalisée comme demandé par le brief.

Ce rapport contient donc :
1. **Analyse de cohérence interne** des chiffres de l'Agent 1 (vraisemblance, recoupements internes, signaux de risque)
2. **Identification des chiffres à haut risque** qui doivent impérativement être revérifiés manuellement avant copywriting
3. **Identification des chiffres à risque faible** (sources institutionnelles citées, plage de valeurs plausible)
4. **Plan de vérification résiduel** à exécuter manuellement ou via un agent ayant les permissions WebFetch/WebSearch

- URLs testées : **0** (permissions refusées)
- Sources croisées externes : **0** (permissions refusées)
- Date de l'analyse : 21 mai 2026
- Analyste : Agent 2 (mode dégradé — vérification interne uniquement)

---

## Tendance 1 — La rénovation énergétique d'ampleur s'impose

### Chiffres analysés (cohérence interne)

| Chiffre Agent 1 | Plausibilité | Signaux de risque | Verdict provisoire |
|---|---|---|---|
| 120 306 rénovations d'ampleur 2025 | Plausible. Compatible avec l'historique 65 939 (2024) qui circulait. Cible 2024 dépassée. | Chiffre précis (5 chiffres significatifs) → vient probablement d'un bilan officiel. Source primaire Anah PDF non lue dans cette session. | ⚠️ À CONFIRMER manuellement sur PDF Anah avant publication |
| 307 731 rénovations totales 2025 | Plausible. Cohérent avec un ratio rénovations d'ampleur / total = 120 306 / 307 731 ≈ 39 % qui correspond exactement au chiffre cité par Agent 1. | Cohérence parfaite avec le 39 %, donc les deux chiffres viennent du même bilan. | ⚠️ À CONFIRMER (même source à valider) |
| 4,39 Md€ d'aides 2025 | Plausible. Budget annuel MaPrimeRénov' historique 2-4 Md€. Une hausse à 4,39 Md€ reflète le poids des rénovations d'ampleur (panier moyen plus élevé). | Risque arrondi. Bilan PDF officiel = source unique. | ⚠️ À CONFIRMER |
| 39 % rénovations d'ampleur dans les aides 2025 vs 27 % (2024) vs 11 % (2023) | Progression continue cohérente. La hausse 11→27→39 % suit une logique de pilotage Anah vers les gestes lourds. | Vraisemblable. | ⚠️ À CONFIRMER (cohérent avec ratios ci-dessus) |
| 70 % bénéficiaires modestes/très modestes au S1 2025 | Plausible. Le recentrage post-suspension estivale visait précisément les ménages modestes. | Source Cozynergy = acteur commercial. À recouper avec Anah direct. | ⚠️ À CONFIRMER avec source Anah primaire |
| 54 % très modestes / 16 % modestes sur rénovations d'ampleur | Plausible (somme = 70 %, cohérence interne avec ligne précédente). | Idem ci-dessus. | ⚠️ À CONFIRMER |
| Audit énergétique obligatoire vente classe E depuis 1er janvier 2025 | Conforme à la loi Climat & Résilience. F et G depuis avril 2023 = correct. D à partir de 2034 = correct. | Information juridique stable, peu de risque d'erreur. | ✅ Probablement correct (source service-public.gouv.fr fiable) |
| Coût audit 800-1500 € + validité 5 ans | Plage cohérente avec marché. Validité 5 ans = correcte (texte officiel). | Plage large mais réaliste. | ✅ Probablement correct |
| 300 000 projets Leroy Merlin / objectif 500 000 d'ici 2030 | Chiffre marketing communiqué entreprise. À prendre tel quel avec attribution claire. | C'est de la communication corporate, pas de la statistique publique. | ✅ Cite-le tel quel avec "selon Leroy Merlin" |
| Plafonds 30 000 €/40 000 € parcours accompagné depuis 30 septembre 2025 | Conforme aux annonces ministérielles connues. | Stable. | ✅ Probablement correct |
| DPE 2026 : coefficient électricité de 2,3 à 1,9 depuis 1er janvier 2026 | Conforme aux arrêtés DPE publiés. | Stable juridiquement. | ✅ Probablement correct |

### URLs sources Agent 1 à tester (non testées en session)

- `https://www.anah.gouv.fr/sites/default/files/2026-03/202603_MPR-BilanTrimestriel-25T4.pdf` — **PRIORITÉ 1** : source primaire de tous les chiffres clés. À ouvrir manuellement.
- `https://www.actu-environnement.com/ae/news/anah-bilan-aides-MaPrimeRenov-premier-semestre-2025-renovations-energetiques-logements-46579.php4`
- `https://www.effy.fr/pro/actualite/maprimerenov-bilanT4-2025`
- `https://www.empruntis.com/financement/actualites/2026/chiffres-anah-2025-une-dynamique-reelle-malgre-les-incertitudes-autour-maprimerenov-20862/`
- `https://www.cozynergy.com/conseils-subventions/bilan-maprimerenov-2025-s1`
- `https://www.service-public.gouv.fr/particuliers/vosdroits/F37110`
- `https://france-renov.gouv.fr/actualites/maprimerenov-pour-une-renovation-d-ampleur-les-nouvelles-conditions-au-30-septembre-2025`
- `https://www.immonot.com/vente-immobiliere/r53-a-3542/DPE-2026-tout-savoir-sur-la-reforme-du-diagnostic-de-performance-energetique.html`
- `https://www.lsa-conso.fr/une-approche-pragmatique-castorama-leroy-merlin-et-mr-bricolage-aident-les-francais-a-renover-leur-logement-malgre-les-obstacles,464842`

### Corrections recommandées

- Pour les 4 chiffres "Anah 2025" (120 306, 307 731, 4,39 Md€, 39 %) : si la vérification PDF Anah n'est pas faite avant publication, **reformuler en qualitatif** : "plus de 100 000 rénovations d'ampleur en 2025" / "la rénovation d'ampleur devient majoritaire en valeur dans MaPrimeRénov'". Pas de chiffres précis sans source primaire ouverte.
- Le chiffre "objectif initial de 100 000" pour 2025 doit être attribué : cible gouvernementale ou cible Anah ? À préciser.

---

## Tendance 2 — La maison qui doit encaisser canicule et inondation

### Chiffres analysés (cohérence interne)

| Chiffre Agent 1 | Plausibilité | Signaux de risque | Verdict provisoire |
|---|---|---|---|
| 86 % Français confrontés à un aléa climatique sur 10 ans | Plausible. Ipsos / Qualitel = méthodologie connue. Échantillon 3 680 = robuste. | Étude grand public Qualitel publiée, faible risque. | ✅ Probablement correct |
| 74 % aux vagues de chaleur | Cohérent avec multiplication des canicules 2018-2025. | Idem. | ✅ Probablement correct |
| 66 % inconfort thermique chez eux | Cohérent. | Idem. | ✅ Probablement correct |
| 42 % logement insuffisamment protégé contre la chaleur | Plausible. | Idem. | ✅ Probablement correct |
| 27,4 % maisons équipées clim 2025 / 12,6 % appartements | Plausible (taux historique ~25 % maisons en hausse continue). | Source Hellowatt = acteur commercial. Plage cohérente avec étude Ademe-Ipsos antérieures. | ⚠️ À RECOUPER avec source primaire (Ademe ou Insee enquête logement) |
| 910 420 PAC air-air vendues 2023 (+13 % vs 2022) | Plausible. Marché PAC en boom post-2022 (crise énergie). | **L'Agent 1 a signalé lui-même que ce chiffre vient d'Hellowatt sans accès à la source primaire (Uniclima ou Observ'ER probables).** | ❌ FRAGILE — à recouper avec Uniclima / Observ'ER ou supprimer |
| 20 % propriétaires ont fait/planifié travaux adaptation | Plausible. | Source Qualitel = fiable. | ✅ Probablement correct |
| 34 % parmi sinistrés | Plausible (effet déclencheur du sinistre). | Idem. | ✅ Probablement correct |
| Travaux envisagés : isolation 43 % / eau pluviale 32 % / toiture 29 % / volets 28 % | Plausible. Hiérarchie cohérente. | Idem. | ✅ Probablement correct |
| 7 °C d'écart maison bien isolée vs mal isolée — ADEME juin 2025 | Vraisemblable (étude ADEME existante sur protection thermique). | Chiffre rond, à formuler "jusqu'à 7 °C" comme fait Agent 1. | ⚠️ À CONFIRMER citation ADEME exacte |
| 7 Md€ marché CVC France 2025 | Ordre de grandeur cohérent. | Hellowatt = secondaire. | ⚠️ Acceptable si attribué clairement |
| Juin 2025 record consommation clim 5 ans | Plausible (canicule juin 2025 historique). | Hellowatt seule source citée. | ⚠️ À CONFIRMER avec RTE ou Enedis si possible |
| 30 000 installations PAC air/eau aidées MaPrimeRénov' au S1 2025 | Plausible. | Source Cozynergy = secondaire. | ⚠️ À CONFIRMER avec Anah |

### Corrections recommandées

- Supprimer le chiffre **910 420 PAC** ou le remplacer par "près d'un million de PAC air-air vendues en 2023" avec mention "selon les acteurs de la filière".
- Pour la consommation clim juin 2025, vérifier sur le **Bilan Mensuel RTE** (publication officielle) avant publication.

---

## Tendance 3 — Past Reveals Future (Maison&Objet)

### Chiffres analysés (cohérence interne)

| Chiffre Agent 1 | Plausibilité | Signaux de risque | Verdict provisoire |
|---|---|---|---|
| 2 294 marques exposantes M&O janvier 2026 | Plausible (M&O historiquement 2000-2500 exposants). | **Risque majeur signalé par Agent 1 : site M&O renvoie 403, chiffres viennent de Harmonies Magazine (secondaire).** | ❌ NON VÉRIFIÉ — chiffre exposants à confirmer obligatoirement via communiqué officiel M&O ou Salonséquipement.fr |
| 543 nouveaux exposants | Plausible. | Idem ci-dessus. | ❌ NON VÉRIFIÉ |
| 67 300 visiteurs | Cohérent avec éditions récentes (60-80 k visiteurs pros habituels). | Idem. | ❌ NON VÉRIFIÉ |
| 148 pays représentés | Plausible. | Idem. | ❌ NON VÉRIFIÉ |
| +10 % exposants Belgique / +30 % Chine | Plausible. | Idem. | ❌ NON VÉRIFIÉ |
| 200 M€ retombées Île-de-France | Estimation économique, ordre de grandeur typique pour grand salon. | Source unique = Harmonies Magazine. | ❌ NON VÉRIFIÉ |
| Thème "Past Reveals Future" + 4 manifestes | Information éditoriale, vérifiable simplement. | Site officiel M&O à consulter pour confirmation. | ⚠️ À CONFIRMER (info simple) |
| Harry Nuriev (Crosby Studios) Designer of the Year 2026 | Information ponctuelle. | Annonce officielle M&O. | ⚠️ À CONFIRMER (info simple) |
| Sarah Lavoine x Maisons du Monde collection Tamsa | Info marketing. | Source Home Magazine. | ✅ Probablement correct (vérifier via site maisonsdumonde.com) |

### Corrections recommandées

- **CRITIQUE** : ne PAS publier les chiffres 2 294 marques / 67 300 visiteurs / 148 pays sans avoir ouvert le communiqué officiel post-salon M&O. Si non trouvé, reformuler en qualitatif : "plus de 2 000 marques exposantes et plus de 60 000 visiteurs professionnels selon le bilan post-salon de Maison&Objet".
- Pour Harry Nuriev, vérifier orthographe et titre exact via site M&O ou article presse spécialisée (AD Magazine, Intramuros).
- L'angle éditorial sur la tendance (palette terracotta/sable/safran, céramique, artisanat) est qualitatif et faiblement risqué — il peut partir.

---

## Tendance 4 — Seconde main meuble

### Chiffres analysés (cohérence interne)

| Chiffre Agent 1 | Plausibilité | Signaux de risque | Verdict provisoire |
|---|---|---|---|
| Marché seconde main France 2024 : 7 Md€, +12 % vs 2023 | Plausible. Estimations seconde.media / BPI convergent autour de 7 Md€. | Note : ce chiffre couvre toute la seconde main (mode, tech, mobilier), pas seulement le mobilier. | ⚠️ À ATTRIBUER clairement : "toutes catégories confondues" |
| Multiplication par 2 entre 2019 et 2024 | Plausible. Trajectoire cohérente avec données BPI. | Idem. | ✅ Acceptable avec attribution |
| Plus de 50 % acheteurs en ligne ont acquis du seconde main en 12 mois | Plausible (étude Fevad / OpinionWay régulières). | Source seconde.media. | ⚠️ À CONFIRMER avec source primaire (Fevad / OpinionWay) |
| 58 % considèrent occasion comme écoresponsable | Plausible. | Idem. | ⚠️ À CONFIRMER |
| Selency : 300 000+ produits / 2,5 M visiteurs/mois / 10 000+ vendeurs | **Risque signalé par Agent 1** : "data marketing reprise par Galerie Déco, pas de source primaire SimilarWeb". | Chiffre 2,5 M/mois = data marketing entreprise, non auditable. | ⚠️ ATTRIBUER explicitement à Selency ou supprimer le chiffre visiteurs |
| Label Emmaüs : 180+ boutiques / 2 M articles | Plausible (ordre de grandeur entreprise). | Source label-emmaus.co = primaire (site officiel). | ✅ Probablement correct |

### Corrections recommandées

- Préciser que les 7 Md€ = toutes catégories de seconde main confondues (mode, tech, mobilier, déco), pas mobilier seul. Sinon le chiffre est trompeur.
- Pour Selency : reformuler "Selency revendique plus de 300 000 produits et plusieurs millions de visites mensuelles" sans citer le chiffre précis 2,5 M.
- Agent 1 signale que le segment mobilier/déco isolé dans les 7 Md€ n'a pas de chiffre publié. **Ne pas l'inventer**. Conserver formulation qualitative.

---

## Tendance 5 — Construction neuve baisse, rénovation absorbe

### Chiffres analysés (cohérence interne)

| Chiffre Agent 1 | Plausibilité | Signaux de risque | Verdict provisoire |
|---|---|---|---|
| 277 230 mises en chantier avril 2025 - mars 2026, -20 % vs moyenne 5 ans | Plausible. SDES publie ces chiffres mensuellement. | Source SDES = primaire officielle. | ✅ Probablement correct |
| 381 486 permis sur 12 mois roulants fév 2025-jan 2026, -7,8 % | Plausible. Cohérent avec décélération du marché 2024-2025. | Source Batiweb reprenant SDES. | ✅ Probablement correct |
| Indice BT01 = 135,1 en février 2026 vs 133,7 décembre 2025 | Plausible. Insee publie BT01 mensuellement. Progression mensuelle ~1 % = cohérente. | Source Insee = primaire. | ✅ Probablement correct |
| +35 % vs 2015 pour BT01 | À recouper. **Agent 1 signale lui-même** : "repris d'un seul article (Vertuoza), à confirmer via série Insee si on veut le citer". | Calcul direct possible depuis Insee série BT01. | ⚠️ À RECALCULER directement depuis série Insee |
| RE2020 seuils 2025 : -17 % Ic Construction maisons (530 kg CO₂/m²) / -12 % collectif (650 kg CO₂/m²) | Plausible. Conforme aux arrêtés RE2020 publiés. | Source Ordre des Architectes = fiable. | ✅ Probablement correct |
| Suppression modulation Mided | Plausible. | Idem. | ✅ Probablement correct |
| 945 000 transactions ancien sur 12 mois roulants | Plausible. Notaires de France publie ces chiffres trimestriellement. | Source primaire. | ✅ Probablement correct |
| +0,7 % prix T3 2025 | Plausible (stabilisation post-baisse 2023-2024). | Idem. | ✅ Probablement correct |
| Volumes -25 % vs 2021 | Plausible (pic historique 1,2 M transactions en 2021). | Idem. | ✅ Probablement correct |
| Marché bricolage 2024 : -4,3 % à 22,1 Md€ (GSB) | Plausible. Inoha/FMB publient ces chiffres annuellement. | Source primaire Inoha (PDF). | ✅ Probablement correct |
| -3,4 % au S1 2025 | Plausible. | Source negoce.zepros (presse pro). | ✅ Probablement correct |
| 18 250 logements bois construits 2024 (-17 %), part marché 6,6 % | Plausible. FCBA publie enquête annuelle. | Source FCBA = primaire filière. | ✅ Probablement correct |
| Filière bois 4,6 Md€ CA, 29 000 emplois | Plausible. | Idem. | ✅ Probablement correct |
| PPT obligatoire copros +15 ans depuis 1er janvier 2025 | Conforme à la loi Climat & Résilience. | Source Service-Public.fr. | ✅ Probablement correct |
| Marché ITE 750 000 logements 2024 (doublé vs 2020), -1,3 % S1 2025 | Plausible. | Batiactu/Uniso. | ⚠️ À VÉRIFIER : "750 000 isolés en 2024" vs "doublé vs 2020" = ambigu. C'est 750 000 par AN en 2024, ou stock cumulé ? À clarifier. |

### URLs sources Agent 1 à tester

- `https://www.statistiques.developpement-durable.gouv.fr/construction-de-logements-resultats-fin-mars-2026-france-entiere`
- `https://www.batiweb.com/actualites/conjoncture/construction-de-logements-recul-des-permis-et-des-mises-en-chantier-en-janvier-48353`
- `https://www.insee.fr/fr/statistiques/2015347`
- `https://www.architectes.org/actualites/re2020-de-nouveaux-seuils-carbone-en-2025-2028-et-2031-91736`
- `https://www.notaires.fr/fr/bilan_immobilier_annuel`
- `https://www.inoha.org/wp-content/uploads/2025/04/250429-CP-FMB-INOHA-Chiffres-Marche-bricolage-2024-VDef.pdf`
- `https://negoce.zepros.fr/actu-generale/inoha-annonce-nouveau-recul-marche-habitat-34-2025`
- `https://www.ffbatiment.fr/actualites-batiment/actualite/enquete-nationale-de-la-construction-bois-2025`
- `https://www.service-public.gouv.fr/particuliers/vosdroits/F36760`
- `https://www.batiactu.com/edito/specialiste-ite-uniso-france-2025-est-restee-annee-73288.php`

### Corrections recommandées

- Clarifier le chiffre **ITE 750 000 logements** : préciser "750 000 logements isolés par l'extérieur sur l'année 2024 selon Uniso" pour lever toute ambiguïté stock/flux.
- Recalculer le **+35 % indice BT01 vs 2015** directement depuis la série Insee si publication chiffrée du PDF.

---

## Synthèse

### Tendances solides (peuvent partir en copywriting telles quelles, avec qualifications mineures)

- **Tendance 5 (construction neuve baisse / rénovation absorbe)** : sources principalement institutionnelles (SDES, Insee, Notaires de France, FCBA, Inoha). Très peu de chiffres fragiles.
- **Tendance 2 (canicule/inondation), partie Qualitel** : étude Ipsos publique avec méthodologie connue. Sortir Hellowatt et 910 420 PAC.
- **Tendance 1 obligations légales** (audit énergétique, DPE, interdiction location G) : info juridique stable.

### Tendances à corriger avant copywriting (chiffres à reformuler en qualitatif)

- **Tendance 1 Anah 2025** (120 306 / 307 731 / 4,39 Md€) : reformuler en "plus de 100 000 rénovations d'ampleur" si le PDF Anah n'est pas confirmé manuellement.
- **Tendance 2 marché PAC** : retirer le 910 420 ou attribuer "selon les acteurs de la filière".
- **Tendance 4 Selency 2,5 M visiteurs** : attribuer "selon Selency" ou reformuler.
- **Tendance 4 marché 7 Md€** : préciser "toutes catégories seconde main confondues".

### Tendances à supprimer (sources insuffisantes ou trop faibles)

- **Tendance 3 chiffres M&O (2 294 marques / 67 300 visiteurs / 148 pays)** : si non vérifiés via communiqué officiel post-salon, à reformuler en qualitatif uniquement. La partie éditoriale (thème, designer, palette) peut rester.

---

## Recommandations pour Agent 3 (audit concurrence)

Axes éditoriaux à explorer chez les concurrents :
- **Côté Maison / Houzz / Maison à Part / Côté Travaux** : regarder comment ils traitent les 5 tendances. Spécifiquement, comment ils gèrent l'instabilité MaPrimeRénov' 2026 (incertitude budgétaire). Cherchent-ils à rassurer, ou à mettre en garde ?
- **ChooseYourBoss / Hellio / Effy** : positionnement "acteurs accompagnement rénovation énergétique" — quels arguments ils utilisent pour rénovation d'ampleur vs gestes simples.
- **Blogs ESS et seconde main** : Selency (blog), Label Emmaüs (rubrique conseils), Leboncoin (catégorie maison). Quelle narration sur la seconde main mobilier ?
- **Sites confort d'été / climatisation** : Daikin France, Atlantic, ADEME-Mon Logement. Comment articulent-ils "économie d'énergie" (rénovation classique) vs "confort d'été" (adaptation climat) ?
- **Sites tendances déco 2026** : Maison Créative, Marie Claire Maison, Côté Maison, AD Magazine. Quelle restitution post-M&O 2026 ?

Question stratégique à creuser : la **niche éditoriale de Plus c'est simple** = ton "anti-hype", démystification, pas de jargon marketing. À quel concurrent ressemble-t-il le moins ? Quelle place reste à prendre ?

---

## Recommandations pour Agent 4 (copywriting)

### Chiffres à utiliser tels quels (après vérification visuelle simple par l'Agent 4 sur les sources institutionnelles)

- Mises en chantier 277 230 / permis 381 486 (SDES, vérification rapide)
- Indice BT01 135,1 (Insee, vérification rapide)
- RE2020 seuils 2025 (arrêté publié)
- Notaires : 945 000 transactions / +0,7 % T3 2025
- Bricolage : 22,1 Md€ / -4,3 % en 2024 (PDF Inoha)
- Bois : 18 250 / 6,6 % / 4,6 Md€ (FCBA)
- Qualitel : 86 % / 74 % / 66 % / 42 % / 20 % / 34 % (étude publique Ipsos)
- Coût audit 800-1500 € / validité 5 ans
- Plafonds parcours accompagné 30 000 € / 40 000 €
- DPE 2026 coefficient 2,3 → 1,9
- Audit énergétique vente classe E depuis 1er janvier 2025

### Chiffres à reformuler en qualitatif (si vérification primaire impossible)

- "Plus de 100 000 rénovations d'ampleur aidées en 2025" (au lieu de 120 306) → si le PDF Anah n'est pas confirmé
- "Près de 4 milliards d'euros d'aides Anah en 2025" (au lieu de 4,39 Md€) → idem
- "La rénovation d'ampleur devient majoritaire en valeur dans MaPrimeRénov' (39 % en 2025 selon les bilans Anah, contre 27 % en 2024)" → cite Anah et la dynamique
- "Plus de 2 000 marques exposantes et plusieurs dizaines de milliers de visiteurs professionnels au salon Maison&Objet janvier 2026" (au lieu de 2 294 / 67 300) → si bilan post-salon non confirmé
- "Près d'un million de pompes à chaleur air-air vendues annuellement en France" → au lieu du chiffre précis Hellowatt
- "Marché français de la seconde main estimé à environ 7 milliards d'euros en 2024, toutes catégories confondues" → clarifier le périmètre

### Sources à citer en pied de page du PDF

Sources institutionnelles à mettre en avant en priorité :
- Anah, bilan 2025 (MaPrimeRénov')
- SDES — ministère de la Transition écologique
- Insee (indice BT01)
- Notaires de France
- Service-Public.fr (texte de loi)
- Qualitel/Ipsos, Baromètre 2025
- ADEME
- FCBA (filière bois)
- Inoha/FMB (bricolage)
- Maison&Objet, communiqué officiel janvier 2026 *(à confirmer)*

Sources secondaires à mentionner avec parcimonie (presse pro et plateformes commerciales) :
- Batiweb, Batiactu, Batirama, Effy, Hellowatt, LSA Conso, BPI France
- Cozynergy, Empruntis, Actu-Environnement

---

## ALERTE — Conditions de validité du présent rapport

Ce rapport a été produit **sans accès réseau** (WebFetch, WebSearch, curl Bash refusés par permissions de session). Aucune URL n'a été ouverte ni aucun chiffre confirmé par lecture de source primaire.

Avant publication du PDF tendances 2026, l'Agent 4 ou l'humain superviseur **doit impérativement** :

1. Ouvrir le PDF Anah `202603_MPR-BilanTrimestriel-25T4.pdf` et confirmer les chiffres 120 306 / 307 731 / 4,39 Md€ / 39 % / 70 % modestes
2. Trouver le communiqué officiel post-Maison&Objet janvier 2026 et confirmer 2 294 marques / 67 300 visiteurs / 148 pays — sinon reformulation qualitative obligatoire
3. Vérifier sur economie.gouv.fr ou France Rénov' qu'aucune annonce gouvernementale postérieure au 21 mai 2026 ne modifie le dispositif MaPrimeRénov' 2026 (situation politique instable)
4. Confirmer le 910 420 PAC ou supprimer le chiffre
5. Confirmer le 2,5 M visiteurs/mois Selency ou reformuler
