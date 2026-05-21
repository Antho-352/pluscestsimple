# Spinner un nouveau site en 15 minutes

Cette procédure suppose :
- Un WP vide fraîchement installé (PHP 8.0+, WP 6.4+)
- Accès FTP/SFTP + wp-admin
- Cloudflare devant le domaine (recommandé)

---

## 1. Durcissement `wp-config.php` (1 min)

Avant toute chose, colle ces constantes dans `wp-config.php` (au-dessus de `/* That's all, stop editing! */`) :

```php
define( 'DISALLOW_FILE_EDIT',  true );
define( 'DISALLOW_FILE_MODS',  true );  // retirer si tu veux installer des plugins via admin
define( 'FORCE_SSL_ADMIN',     true );
define( 'WP_AUTO_UPDATE_CORE', 'minor' );
define( 'WP_DEBUG_DISPLAY',    false );
define( 'WP_HOME',    'https://ton-site.fr' );
define( 'WP_SITEURL', 'https://ton-site.fr' );
```

---

## 2. Installer le thème (2 min)

- `Apparence → Thèmes → Ajouter → Téléverser` → choisir `arw-pulse.zip`
- Activer

Vérifie au premier chargement : `ton-site.fr/.well-known/security.txt` doit renvoyer un fichier texte.

---

## 3. Uploader le logo (2 min)

- `Apparence → Personnaliser → Identité du site`
- Logo : uploader le SVG ou PNG (ratio ~3:1 horizontal)
- Site Icon (favicon) : uploader une 512×512 PNG ou SVG carré

Si pas de logo uploadé → le thème affiche un SVG fallback généré automatiquement (text + pictogramme). Si Site Icon non set → favicon par défaut du thème.

---

## 4. Choisir / créer la skin (2 min)

### Option A — utiliser une skin existante

`Apparence → Éditeur → Styles → Parcourir les styles` → choisir `Urban` (ou autre disponible)

### Option B — créer une skin custom

1. SFTP : copier `wp-content/themes/arw-pulse/styles/urban.json` en `wp-content/themes/arw-pulse/styles/<ton-slug>.json`
2. Éditer les tokens :
   - `settings.color.palette[]` (foreground, background, accent, etc.)
   - `settings.typography.fontFamilies[]` (si tu changes de polices)
3. Dans `Éditeur → Styles` : sélectionner ta skin

Une skin = juste des tokens. Zéro CSS custom, zéro PHP. Si tu dois modifier du layout, ça ne passe pas par un skin → c'est un fork ou un nouvel archétype.

---

## 5. Créer le mu-plugin de config (3 min)

1. SFTP : créer le dossier `wp-content/mu-plugins/` s'il n'existe pas
2. Copier `wp-content/themes/arw-pulse/docs/example-site-config.php` vers `wp-content/mu-plugins/<ton-site>-config.php`
3. Éditer les valeurs dans le fichier (voir commentaires)
4. Pas besoin d'activation — les mu-plugins sont chargés automatiquement

---

## 6. Remplir les réglages site (5 min)

### Réglages → Général
- Titre du site, Slogan, Email admin

### Réglages → Permaliens
- Structure : `/%postname%/`

### Réglages → Contenu Accueil ⭐ (nouveau v1.2)
- **Onglet Hero** : eyebrow, titre HTML, description, CTAs, image (via médiathèque), meta footer
- **Onglet Manifesto** : eyebrow, texte HTML avec `<mark>`, signature
- **Plus besoin de toucher aux fichiers PHP pour changer le contenu de la home**

### Réglages → ARW Pulse
- Cocher/décocher **Produits affiliés** (décoche si le site n'est pas affiliation)

### Réglages → Mentions légales
- Publisher, SIRET, host, contact email, adresse

### Réglages → Kit média
- Tagline, audience, thématiques, email presse, couleurs, fonts

### Articles → Catégories
- Créer les catégories principales
- **Remplir la description** de chaque (= meta description SEO auto)

---

## 7. Cloudflare (2 min)

Si le site est derrière Cloudflare :

- **Speed → Optimization → Images** : Polish = Lossy + WebP
- **Caching → Configuration** : Caching Level = Standard
- **Security → Bots** : Bot Fight Mode = ON
- **Security → WAF** : Managed Rules → OWASP Core Rule Set = ON, sensitivity High
- **Rules → Rate Limiting Rules** : créer une règle « /wp-login.php et /xmlrpc.php » → 10 req/min → Challenge
- **SSL/TLS** : Full (strict)

---

## 8. Premier déploiement (1 min)

Visite la home une fois → le thème :
- Purge les templates DB résiduels
- Flush rewrite rules (pour `/manifest.webmanifest` et `/.well-known/security.txt`)

Vérifications immédiates :
- `ton-site.fr/manifest.webmanifest` → JSON valide
- `ton-site.fr/.well-known/security.txt` → texte avec ton contact
- Source HTML de la home → présence `<meta name="description">`, `<meta property="og:*">`, `<script type="application/ld+json">`

---

## 9. Vérifier avec les outils officiels

- [Rich Results Test](https://search.google.com/test/rich-results) — doit détecter Organization, WebSite, BreadcrumbList au minimum
- [PageSpeed Insights](https://pagespeed.web.dev/) — viser ≥ 90 sur mobile
- [Mozilla Observatory](https://observatory.mozilla.org/) — viser grade A
- [Security Headers](https://securityheaders.com/) — viser grade A

---

## 10. Soumettre aux moteurs

- Google Search Console : ajouter la propriété + soumettre `https://ton-site.fr/wp-sitemap.xml`
- Bing Webmaster Tools : idem
- Vérifier dans 48h que l'indexation démarre

---

## Troubleshooting

**Les templates de base éditoriaux ne se chargent pas correctement**
→ Visiter n'importe quelle page une fois après l'upload. Le reset DB auto ne se déclenche qu'au premier chargement.

**`/manifest.webmanifest` renvoie 404**
→ `Réglages → Permaliens` → cliquer Enregistrer (refresh les rewrite rules).

**Les produits n'apparaissent pas dans `[arw_essential]`**
→ Il faut au moins 1 produit publié dans `Produits → Ajouter`. Vérifier aussi qu'il y a une image à la une.

**La skin semble ne pas s'appliquer**
→ Vérifier que la skin est bien sélectionnée dans `Éditeur → Styles`. Purger Cloudflare. Hard refresh (Cmd+Shift+R).
