# Audit cocon sémantique & maillage interne — pluscestsimple.com

_Date : 2026-06-06 · Méthode : sitemap live + code thème/plugin + fetch de pages réelles._

---

## 1. Où sont tes pages piliers (réponse directe)

Trois endroits, trois rôles :

| Quoi | Où | Rôle |
|------|----|------|
| **Définition (source de vérité)** | `wp-content/themes/pluscestsimple/inc/init-content.php` → fonction `pcs_content_structure()` (lignes 26-80) | Liste les 6 piliers + leurs sous-catégories + intro. C'est LE fichier à éditer pour changer l'arbre. |
| **Contenu éditorial des piliers** | `wp-content/themes/pluscestsimple/patterns/category-{pilier}.php` (6 fichiers) | Le markup réel : H1, intro, sections, FAQ, maillage. C'est ce que tu « relis ». |
| **Pages publiées** | `/decoration/`, `/travaux/`, `/jardin/`, `/architecture/`, `/immobilier/`, `/lifestyle/` | Le rendu live (contenu inliné en base au 1er seed). |

⚠️ **Piège structurel** (déjà rencontré sur les partenaires) : le contenu des patterns est **copié dans la page en base au premier seed**. Modifier un fichier `category-*.php` **ne met PAS à jour** la page déjà créée. Toute correction de pilier nécessite une migration explicite des pages existantes.

---

## 2. Architecture réelle constatée

### 2.1 Le squelette (correct)

```
ACCUEIL
├── Décoration (/decoration/)              PILIER
│   ├── Par pièce        (/decoration/par-piece/)
│   ├── Styles           (/decoration/styles/)
│   └── Petits budgets   (/decoration/petits-budgets/)
├── Travaux (/travaux/)                    PILIER
│   ├── Par pièce        (/travaux/par-piece/)
│   ├── Gros œuvre       (/travaux/gros-oeuvre/)
│   └── Rénovation énergétique
├── Jardin (/jardin/)                      PILIER
│   ├── Aménagement extérieur
│   └── Entretien
├── Architecture (/architecture/)          PILIER
│   ├── Styles & époques
│   └── Extensions
├── Immobilier (/immobilier/)              PILIER
│   ├── Acheter
│   ├── Louer & investir
│   └── Vendre
└── Lifestyle (/lifestyle/)                PILIER
    ├── Bien-être & accessoires
    └── Rangement & nettoyage
```

- **6 piliers + 15 sous-pages** : tous présents dans le sitemap. ✅
- **200 articles** publiés, URLs **à plat** (`/slug-article/`), pas nestés sous le pilier. C'est un choix valide en WP (permaliens plats), mais le silo doit alors être tenu par le **maillage**, pas par l'URL.
- Annuaire boutiques (CPT `pcs_boutique`) + taxonomies dédiées : dans le sitemap. ✅

### 2.2 Verdict global

Le **squelette du cocon existe**. Mais **le maillage qui doit le faire vivre est cassé ou absent** sur les points clés. Aujourd'hui, structurellement, ce n'est pas un cocon : c'est 6 pages piliers décoratives + 200 articles qui ne pointent pas proprement vers elles.

---

## 3. Diagnostic du maillage (les problèmes, par gravité)

### 🔴 P1 — CRITIQUE : les pages piliers n'affichent PAS leur silo

**Preuve** : `/travaux/` et `/immobilier/` affichent **exactement les 15 mêmes articles, même ordre** — les derniers publiés du site, toutes thématiques confondues (on voit du SCI/LMNP et du DPE sur `/travaux/`, de la colle bitumineuse sur `/immobilier/`).

**Conséquence SEO** : aucune page pilier ne concentre la pertinence thématique de son silo. Google voit 6 pages qui listent le même contenu générique → zéro signal de silo, cannibalisation potentielle entre piliers.

**Cause probable** : le filtre serveur `query_loop_block_query_vars` (`inc/category-query-filter.php`) ne s'applique pas sur les pages en base. Les patterns actuels portent bien `namespace:"pcs/cat-loop"` + `inherit:false`, mais **les pages ont été seedées avant** que ce markup soit correct → le bloc Query stocké en base est obsolète. Le filtre ne matche pas → la Query Loop sort les derniers articles globaux.
→ _À confirmer : inspecter le `post_content` réel d'une page pilier (WP-CLI `wp post get <id> --field=content` ou SQL)._

**La catégorisation des articles, elle, fonctionne** : l'article `t2-roubaix` est bien dans `immobilier-louer-investir-cat`. Le problème est uniquement l'affichage côté pilier.

### 🔴 P2 — CRITIQUE : hiérarchie de catégories corrompue

**Preuve** : le fil d'Ariane de `t2-roubaix` est `architecture-cat / immobilier-cat / immobilier-louer-investir-cat`. `immobilier-cat` est imbriqué **sous** `architecture-cat`.

**Cause** : dans `init-content.php`, la branche `$legacy` (ligne ~192) renomme une catégorie pré-existante en `{pilier}-cat` **en conservant son parent d'origine** issu de l'ancienne taxonomie. Les piliers ont hérité de parents croisés.

**Conséquence** : fils d'Ariane faux (mauvais signal Schema.org `BreadcrumbList`), `include_children` qui peut agréger les mauvais articles, URLs de catégorie aberrantes.

### 🟠 P3 — IMPORTANT : maillage des piliers = pilier→pilier uniquement

La section « Voir aussi » des pages piliers (`category-decoration.php` l.260-283) ne contient que **6 liens vers les autres piliers + le compatibilimètre**. Aucun lien descendant éditorialisé vers les **sous-pages** ni vers les **articles phares** du silo. Le pilier ne distribue pas son jus vers sa longue traîne.

### 🟠 P4 — IMPORTANT : articles → aucun lien remontant éditorial

`single.php` ne contient **ni section "articles liés" ni lien contextuel vers le pilier/sous-pilier**. Seuls existent :
- le fil d'Ariane (remonte via la catégorie, mais voir P2) ;
- l'eyebrow catégorie, qui pointe vers l'archive `*-cat` → **301 vers le pilier** (1 hop, fonctionne mais sale).

Une section « Publications similaires » apparaît pourtant en live (4-5 liens, plutôt bien ciblés) : elle vient d'un **plugin tiers non identifié dans le thème** (à tracer — probablement un reliquat). Le maillage latéral ne doit pas dépendre d'un plugin fantôme.

### 🟡 P5 — Hygiène de contenu : articles hors-sujet / dilution

Parmi les 200 articles, plusieurs cassent la cohérence thématique : `fourtoutici` (partage de fichiers), `joy-lamp`, `didier-mathus-immobilier`, `crottin-loire-fromage-chevre`, `shockgarden-blog-jardin`, `my-extrabat`, `outils-club-elec-fr`, `proxichantier-fr`… Beaucoup ressemblent à du contenu sponsorisé/lien sortant. Ça dilue l'autorité topique des silos.

### 🟡 P6 — Catégorie « divers » résiduelle

Le sitemap des catégories ne montre qu'**une** catégorie publique : `/divers/`. Reliquat à supprimer ou réaffecter (les `*-cat` sont correctement exclus du sitemap).

---

## 4. Le cocon cible (définition propre)

### 4.1 Règle des 3 niveaux

```
PILIER (page money, intent large, /pilier/)
   ▲ remonte                              ▼ distribue
SOUS-PILIER (page intent moyen, /pilier/sous/)
   ▲ remonte                              ▼ distribue
ARTICLE longue traîne (intent précis, /slug/)
```

### 4.2 Lois de maillage (à tenir mécaniquement)

| Page | Doit lier vers (descendant) | Doit lier vers (remontant) | Latéral |
|------|------------------------------|-----------------------------|---------|
| **Pilier** | ses 2-3 sous-piliers + ses 6-12 meilleurs articles (Query Loop filtrée sur SON silo) | — (c'est la racine) | les 5 autres piliers (1 lien chacun, dans « Voir aussi ») |
| **Sous-pilier** | ses articles (Query Loop filtrée sur la sous-cat) | son pilier (lien contextuel en intro + fil d'Ariane) | les sous-piliers frères du même pilier |
| **Article** | — | son sous-pilier ET son pilier (lien contextuel dans le corps + fil d'Ariane correct) | 3-5 articles du **même silo** (pas global) |

### 4.3 Principes

1. **Un article = un silo.** Catégorie principale unique = son sous-pilier. Pas de multi-catégorisation cross-silo.
2. **Le maillage latéral reste intra-silo.** Les « articles liés » d'un article déco pointent vers de la déco, pas vers du crédit immobilier.
3. **Les liens remontants sont contextuels**, dans le texte, ancre descriptive (pas juste le fil d'Ariane).
4. **Le pilier filtre réellement son silo** (P1 réglé) sinon tout le reste est cosmétique.
5. **Fil d'Ariane = vérité hiérarchique** (P2 réglé).

---

## 5. Plan de correction priorisé

1. **P2 — Réparer la hiérarchie des catégories** : forcer `parent = 0` sur les 6 `*-cat` piliers, vérifier que chaque `*-{sous}-cat` a pour parent son pilier. Migration idempotente dans `init-content.php`.
2. **P1 — Réparer le filtrage des piliers** : migration qui réécrit les blocs `wp:query` des pages piliers/sous-pages déjà en base (même mécanique que la migration partenaires), pour garantir `namespace:"pcs/cat-loop"` + `inherit:false`. Vérifier ensuite `/decoration/ ≠ /travaux/`.
3. **P4 — Maillage article** : ajouter dans `single.php` une section « À lire dans le même univers » = 3-5 articles **de la même catégorie** (WP_Query `category__in` sur la cat principale), + un lien contextuel vers le sous-pilier/pilier. Supprime la dépendance au plugin fantôme.
4. **P3 — Maillage pilier descendant** : enrichir la section « Voir aussi » des piliers avec liens vers leurs sous-piliers + 3 articles phares du silo.
5. **P5/P6 — Hygiène** : auditer les ~15 articles hors-sujet (noindex, réaffectation ou suppression), supprimer la catégorie `divers`.
6. **(option) Google Search Console** : pour prioriser par impressions/position réelles (cannibalisation, requêtes par silo), il me faut soit un export CSV (Performances → par page + par requête), soit l'accès. Le code + sitemap suffisent pour la structure ; la GSC affine la priorisation éditoriale.

---

## 6. Ce qu'il me faut de toi pour aller plus loin

- **Validation du cocon cible** (section 4) : on part là-dessus ?
- **GSC** : tu m'exportes un CSV Performances (16 mois, par page ET par requête) ? Ça permet de détecter la cannibalisation réelle et de prioriser quels articles renforcent quel pilier.
- **Feu vert** pour implémenter les correctifs P1→P4 (les structurels), qui sont du code thème + migrations.
