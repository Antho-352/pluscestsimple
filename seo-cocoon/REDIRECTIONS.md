# Plan de redirection — restructure 4 piliers (2026-06)

## A. URLs qui CHANGENT → redirections nécessaires (✅ déjà gérées en code, v2.15.0)
| Ancienne URL | Cible | Code | 301 |
|---|---|---|---|
| `/immobilier/` | `/` (accueil) | retired-pillars.php | ✅ |
| `/immobilier/acheter/`, `/louer-investir/`, `/vendre/` | `/` | retired-pillars.php (ancêtre) | ✅ |
| `/lifestyle/` (+ sous-pages) | `/decoration/` | retired-pillars.php | ✅ |
| `/decoration/petits-budgets/` | `/decoration/` | retired-pillars.php | ✅ |
| Archives catégories `immobilier-*`, `lifestyle-*`, `decoration-petits-budgets-*` | idem | retired-pillars.php | ✅ |

## B. URLs qui NE changent PAS → AUCUNE redirection
- **Tous les articles** (permaliens plats `/slug/`), y compris :
  - ceux **recatégorisés** : `renonce-t3-fissures` (→ Travaux), articles lifestyle (→ déco/rangement). _La catégorie change, l'URL non._
  - ceux **noindexés** : 9 articles finance immo (restent à `/slug/`, juste désindexés).
- `/jardin/` : label renommé « Jardin & extérieur », **slug inchangé**.
- Pages piliers : `/decoration/`, `/travaux/`, `/jardin/`, `/architecture/` — inchangées.

## C. URLs à CRÉER → pas de redirection (nouveau contenu, créé par le moteur en brouillon)
`/decoration/par-piece/{salon,cuisine,chambre-enfant,salle-a-manger,salle-de-bain,bureau,entree}/`
`/decoration/styles/{vintage,coloree}/` · `/architecture/styles-epoques/haussmannien/`
`/decoration/rangement-organisation/`
→ Aucune redirection (ces URLs n'existaient pas). Publier au fil de la rédaction.

## D. Faut-il du `.htaccess` ? (optionnel)
Les redirections code sont **précises et suffisantes**. Mais elles dépendent du thème actif.
Pour des 301 **indépendantes du thème** (plus robustes, niveau serveur), ajouter dans `.htaccess`
**AVANT** le bloc `# BEGIN WordPress** (patterns stricts pour ne PAS attraper les articles
type `/immobilier-magazine-xxx/`) :

```apache
# Redirections structure — piliers retirés (301)
RedirectMatch 301 ^/immobilier(/.*)?$            /
RedirectMatch 301 ^/lifestyle(/.*)?$             /decoration/
RedirectMatch 301 ^/decoration/petits-budgets(/.*)?$  /decoration/
```
⚠️ Le `(/.*)?$` est volontaire : il matche `/immobilier` et `/immobilier/...` mais **pas** `/immobilier-xyz/`.
Si tu mets le .htaccess, tu peux laisser le code aussi (redondance inoffensive) ou retirer retired-pillars
de la redirection (garder juste sa partie triage articles).

## E. Suivi (rien d'obligatoire, mais propre)
1. **Menu header** (manuel) : retirer les items « Immobilier » / « Lifestyle » dans Apparence → Menus. _(Le footer est dynamique, déjà nettoyé.)_
2. **GSC** : les 301 sont découvertes automatiquement. Optionnel : resoumettre le sitemap ;
   surveiller que les URLs retirées sortent de l'index et que les articles finance passent en « Exclue par noindex ».
3. **Liens internes** : d'éventuels liens d'anciens articles vers `/immobilier/` etc. → 301 (OK).
   Pas urgent de les réécrire (juste éviter les chaînes à terme).
4. **Anciennes pages** `/immobilier/`, `/lifestyle/` : existent toujours (redirigées). Tu peux les
   **mettre à la corbeille** plus tard (optionnel, la redirection .htaccess survivrait ; la redirection
   code non — si tu trashes ET retires le .htaccess, ça ferait du 404 → garde l'un des deux).

## Résumé
- **Redirections nécessaires** : 3 racines (immobilier, lifestyle, petits-budgets) + leurs archives → **déjà en code**.
- **Aucune URL d'article à modifier/rediriger.**
- **Aucune URL à créer manuellement** (le moteur s'en charge).
- Action manuelle réelle : **nettoyer le menu header**.
