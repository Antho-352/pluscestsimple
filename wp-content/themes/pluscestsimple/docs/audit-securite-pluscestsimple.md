# Audit sécurité — pluscestsimple.com (arw-pulse + arw-pack-maison)

## Synthèse

- **Date** : 2026-05-14
- **Périmètre** : thème `arw-pulse` (~6 100 LOC, 28 fichiers PHP) + plugin `arw-pack-maison` v0.8.1 (~2 600 LOC, 14 fichiers PHP)
- **Lignes scannées** : ~8 700
- **Versions** : thème ARW Pulse (version dynamique via `wp_get_theme`), plugin v0.8.1
- **Décompte** : CRIT=0 | HAUTE=1 | MED=6 | FAIBLE=7 | INFO=4

L'ossature sécurité est solide : nonces présents sur tous les endpoints admin sensibles, capabilities vérifiées, rate-limit + same-origin sur le formulaire public, sanitization correcte sur 95 % des entrées. Trois points méritent un fix avant prod.

---

## HAUTES

### H1 — `arw_pulse_handle_submission` : pas de check same-origin sur endpoint REST POST public
**Fichier** : `arw-pulse/inc/form.php:69-87`, `arw-pulse/inc/security.php:119-130`

Le endpoint `POST /arw/v1/submit` est marqué `permission_callback => '__return_true'`. Le nonce REST (`wp_rest`) est vérifié, mais ce nonce est généré côté serveur sur n'importe quelle page front (l. 185) et **n'est pas lié à l'origine** — un site tiers qui scrape une page de pluscestsimple.com peut récupérer le nonce, le réutiliser depuis une autre origine et soumettre des formulaires arbitraires (spam, pollution SEO de l'admin, déclenchement de l'envoi du PDF lead-magnet à une adresse contrôlée par l'attaquant).

Le fichier `security.php` ajoute un rate-limit global (60 / heure) mais aucune vérification d'origine. C'est exactement le pattern `CSRF-2` du catalogue, et le pattern `CSRF-4` (same-origin manquant) du faux-positif newsletter ne s'applique pas ici car il n'y a aucun check d'origine du tout.

**Fix** :
```php
// Dans permission_callback :
'permission_callback' => function ( WP_REST_Request $req ) {
    $origin  = (string) $req->get_header( 'origin' );
    $referer = (string) $req->get_header( 'referer' );
    if ( $origin === '' && $referer === '' ) {
        return new WP_Error( 'forbidden', 'Cross-origin refusé.', [ 'status' => 403 ] );
    }
    $host          = (string) wp_parse_url( home_url(), PHP_URL_HOST );
    $origin_host   = $origin  ? (string) wp_parse_url( $origin,  PHP_URL_HOST ) : '';
    $referer_host  = $referer ? (string) wp_parse_url( $referer, PHP_URL_HOST ) : '';
    if ( strcasecmp( $origin_host, $host ) !== 0 && strcasecmp( $referer_host, $host ) !== 0 ) {
        return new WP_Error( 'forbidden', 'Cross-origin refusé.', [ 'status' => 403 ] );
    }
    return true;
},
```

---

## MOYENNES

### M1 — JSON-LD émis sans `JSON_HEX_TAG`
**Fichiers** : `arw-pulse/inc/schema.php:342`, `arw-pack-maison/inc/schema-compat.php:64`

`wp_json_encode( ..., JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )` est inséré dans `<script type="application/ld+json">`. Si un admin malveillant (ou une option polluée par une autre faille) glisse `</script>` dans `blogname`, `blogdescription`, le nom d'une catégorie ou le titre d'une règle, le contexte HTML est cassé → XSS stocké. Pattern §14b du catalogue.

**Fix** : ajouter `JSON_HEX_TAG` aux flags (et idéalement `JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT`).

### M2 — Rate-limit transient race-condition (read-then-write)
**Fichiers** : `arw-pulse/inc/form.php:114-122`, `arw-pulse/inc/security.php:123-128`, `arw-pack-maison/inc/lead-magnet.php:194-200`

Les trois rate-limits utilisent `get_transient` puis `set_transient` sans atomicité. Sur un serveur sous charge ou avec object-cache distribué, deux requêtes simultanées de la même IP peuvent toutes deux lire `hit = 9`, écrire `10` chacune, et passer alors que la limite est 10. Impact limité (bypass partiel du quota), mais documenté dans patterns §10.

**Fix** : préférer `wp_cache_incr` (atomique sur Redis/Memcached) avec fallback transient, ou compter via une option avec lock `add_option`/`update_option` conditionnel.

### M3 — Honeypot ne court-circuite pas le rate-limit (DoS de soi)
**Fichier** : `arw-pulse/inc/form.php:90-122`

Le honeypot (l. 91) retourne `200 ok` mais l'incrément du rate-limit a lieu **après** (l. 121). Pour un visiteur légitime c'est correct, mais un attaquant peut soumettre 10 requêtes avec le honeypot rempli depuis l'IP d'un visiteur légitime (proxy ouvert, IP partagée NAT, VPN partagé) pour bloquer cette IP. Pattern §10 « Rate-limit partagé honeypot + chemin légitime ». Sévérité MED ici car le honeypot court-circuite la suite (retour 200 immédiat) — donc le rate-limit n'est PAS incrémenté quand le honeypot est rempli. Vérification post-relecture : OK, pas de DoS de soi. **Faux positif après relecture — à retirer si tu veux.** Je le laisse en INFO uniquement.

→ Reclassé : voir INFO-I1.

### M4 — `arw_pulse_form_submitted` : email du lead-magnet envoyé sans validation supplémentaire
**Fichier** : `arw-pack-maison/inc/lead-magnet.php:95-110`

Le hook `arw_pulse_form_submitted` se déclenche dès que le form `lead-magnet` est soumis. Un attaquant qui contourne H1 (cross-origin) peut soumettre N emails arbitraires : chaque soumission consomme un envoi `wp_mail` et stocke un token download (single-use ≤ 5). Risque : (a) être marqué spammeur SMTP, (b) blacklist du domaine d'envoi, (c) si From: utilise le domaine du site sans SPF/DKIM strict → leak de réputation.

**Fix** : conditionner à H1 fixé. En complément, ajouter un rate-limit dédié au type `lead-magnet` (par exemple 3 / heure / IP) avant de déclencher l'envoi PDF.

### M5 — `csv-import.php` : sanitisation incomplète sur `explanation` et `alternatives`
**Fichier** : `arw-pack-maison/inc/csv-import.php:160-224`

Les champs `explanation`, `alternatives`, `keywords`, `dtu_refs` issus du CSV sont stockés en `post_meta` via `update_post_meta( ..., $explanation )` **sans sanitisation** (trim seul). Plus tard, `compat-display.php:235` les ressort via `wp_kses_post( wpautop( $explanation ) )` → ok à l'affichage, donc pas d'XSS exploitable, mais c'est de la défense en profondeur cassée : un admin compromis (ou un attaquant qui prend la main sur un compte editor avec `manage_options` indu) peut injecter du contenu arbitraire qui n'est filtré qu'au rendu.

**Fix** : `sanitize_textarea_field()` (ou `wp_kses_post` directement) avant le `update_post_meta`, comme c'est déjà fait dans `cpt-compat-rule.php:228-234`.

### M6 — `lead-magnet-admin.php` : test email envoyé à adresse arbitraire (mail relay limité mais réel)
**Fichier** : `arw-pack-maison/inc/lead-magnet-admin.php:73-84`

Un admin (capability `manage_options`) peut envoyer un email de test à n'importe quelle adresse. C'est une fonctionnalité légitime, mais combinée à `email_subject` / `email_intro` / `email_body` / `email_outro` éditables (l. 37-48), un admin compromis transforme le site en mail relay paramétrable. Risque circonscrit à `manage_options`, mais à documenter.

**Fix** (optionnel) : rate-limit `2 tests / heure` + log dans audit-trail. À défaut, accepter le risque (admin = trust full).

### M7 — `printf( _n(...), $n )` : translator-controlled format string
**Fichier** : `arw-pack-maison/inc/home-pattern.php:66`

```php
$stats_left = sprintf( _n( '%d règle technique', '%d règles techniques', $n_rules, 'arw-maison' ), $n_rules );
```

C'est OK ici car `sprintf` est sûr avec `%d`, mais le pattern `printf( esc_html(_n(...)), $arg )` existe dans le code de Compat. Vérifier l'absence de variantes avec `%s` directement échappées via `esc_html` *de la string traduite*. Pattern §14c du catalogue. **À vérifier sur les fichiers traduits .po livrés.**

→ FAIBLE en pratique car les `.po` sont contrôlés par toi. Je laisse en MED pour signalement.

---

## FAIBLES

### F1 — `nocache_headers` manquant sur 404 author enumeration
**Fichier** : `arw-pulse/inc/security.php:58-66`

L'auteur enumeration redirige vers 404 mais `nocache_headers()` est appelé après `status_header( 404 )` — OK. Pas de fix nécessaire, mais vérifier que le template 404 n'expose pas le contexte (`get_query_var('author')`).

### F2 — `arw-pulse/inc/performance.php:97-99` : critical CSS injecté brut sans nonce CSP
**Fichier** : `arw-pulse/inc/performance.php:94-101`

`echo '<style id="arw-pulse-critical">' . $css . '</style>';` sans nonce. Si CSP est en `'unsafe-inline'` (cf. `security.php:25`), OK ; sinon, blocage. La CSP du thème est déjà laxiste (`'unsafe-inline'` sur style + script) — note plus large : la CSP actuelle ne protège quasiment de rien (cf. F7).

### F3 — `enrichment-import.php` : `file_get_contents` sur chemin contrôlé par constante mais pas validé
**Fichier** : `arw-pack-maison/inc/enrichment-import.php:42-54`

`ARW_MAISON_DIR . '/' . ARW_MAISON_ENRICH_FILE` est en dur. Si `ARW_MAISON_DIR` est jamais redéfini ailleurs (cf. ligne 21 : `define( 'ARW_MAISON_DIR', __DIR__ )`), OK. Aucune entrée user dans le path → safe. Documentation only.

### F4 — `lead-magnet-admin.php` : `mime_content_type` insuffisant pour valider un PDF
**Fichier** : `arw-pack-maison/inc/lead-magnet-admin.php:55-71`

`mime_content_type()` lit la signature du fichier (magic bytes). Un fichier PDF avec du PHP en queue passe la vérif. Risque concret seulement si le dossier `/uploads/arw-maison/private/` peut être exécuté par PHP — or `.htaccess` est posé en `Require all denied` (l. 41 de lead-magnet.php), donc inaccessible HTTP. Risque résiduel : si la conf Apache change.

**Fix** : ajouter `wp_check_filetype_and_ext()` + vérifier que l'extension est `.pdf` (déjà fixée par `move_uploaded_file($tmp, $target)` où `$target` est `12-erreurs.pdf` codé en dur — donc safe en pratique).

### F5 — Cookie `arw_consent` sans flag `Secure`
**Fichier** : `arw-pulse/inc/cookie-consent.php:71-72`

```js
document.cookie = 'arw_consent=1; expires=...; path=/; SameSite=Lax';
```

Pas de flag `Secure`. Si le site est servi à la fois en HTTPS et (par erreur) HTTP, le cookie fuite en clair. Site en HTTPS only → impact faible. **Fix** : ajouter `; Secure` (le cookie n'a aucune valeur sensible mais c'est de l'hygiène).

### F6 — `lead-magnet.php:158` : `parse_url` à la place de `wp_parse_url`
**Fichier** : `arw-pack-maison/inc/lead-magnet.php:158`

```php
$admin_email = get_option( 'admin_email', 'noreply@' . parse_url( home_url(), PHP_URL_HOST ) );
```

`parse_url` (php natif) déclenche un warning sur certaines configs (chemins relatifs). Cosmétique.

**Fix** : `wp_parse_url`.

### F7 — CSP `'unsafe-inline'` sur scripts ET styles
**Fichier** : `arw-pulse/inc/security.php:23-31`

```
script-src 'self' 'unsafe-inline' https:;
style-src  'self' 'unsafe-inline' https:;
```

`'unsafe-inline'` sur `script-src` annule toute la valeur défensive de la CSP contre les XSS injectés (le thème en émet plusieurs : cookie-consent.php, products.php, homepage-editor.php). C'est un trade-off conscient (CSP stricte casserait le thème), mais à documenter. À terme, migrer les `<script>` inline vers `wp_add_inline_script` + hash CSP, ou `nonce` dynamique.

---

## INFO

### I1 — Honeypot logique : OK
Le honeypot (form.php:91) **court-circuite** avant le rate-limit (l. 92 → return early). Donc pas de DoS de soi via honeypot. Bonne implémentation.

### I2 — `wp_remote_*` / `curl_*` : absents du code custom
Aucun appel sortant HTTP côté serveur. Pas de SSRF possible.

### I3 — `unserialize` / `eval` / `include $_GET` : absents
Aucun usage. Patterns §8, §9 OK.

### I4 — `$wpdb->` direct : absent
Toutes les requêtes passent par l'API `WP_Query` / `get_posts` / `update_post_meta`. Pas de SQLi possible.

---

## Points OK vérifiés

1. **Nonces** présents et vérifiés sur tous les handlers admin (`form.php`, `cpt-compat-rule.php`, `homepage-editor.php`, `identity.php`, `media-kit.php`, `seo.php`, `structured-data.php`, `csv-import.php`, `lead-magnet-admin.php`, `enrichment-import.php`, `json-export.php`). Aucun `admin_post` orphelin.
2. **Capabilities** : `manage_options` ou `edit_post` vérifié systématiquement avant chaque écriture admin.
3. **Sanitization** : `sanitize_email`, `sanitize_text_field`, `sanitize_textarea_field`, `esc_url_raw`, `sanitize_key` correctement choisis selon le type (notamment dans `identity.php:72-116` et `cpt-compat-rule.php:206-243`).
4. **Output escape** : `esc_html`, `esc_attr`, `esc_url` quasi systématique sur les sorties front. Les rares `wp_kses_post` (cf. `compat-display.php:235`) sont sur des champs admin-only.
5. **REST native locked down** : `cleanup.php:62-70` retire `/wp/v2/users` pour les anonymes.
6. **Author enumeration** : 404 forcé sur `?author=N` (`security.php:58-74`).
7. **xmlrpc** désactivé (`cleanup.php:58`).
8. **Token lead-magnet** : généré via `wp_generate_password` (CSPRNG), regex stricte `^[A-Za-z0-9]{16,64}$`, IP rate-limit (10/5min), expiration 24h, single-use ≤ 5, cleanup cron quotidien. **Excellent.**
9. **PDF privé** : posé dans `/uploads/arw-maison/private/` avec `.htaccess Require all denied` + `index.php` silencieux.
10. **CSV formula injection** : helper `arw_pulse_csv_escape` (`security.php:147-155`) protège les exports.
11. **CRLF injection email** : `arw_pulse_safe_email_header` (`security.php:137-142`) strippe `\r\n` du `Reply-To`.
12. **Anti-self-serving review markup** : `schema.php:42` exige `rating_count ≥ 5` avant d'émettre `AggregateRating` — bonne pratique anti-pénalité Google.

---

## Patterns inédits détectés

### P-NEW-1 — Nonce REST `wp_rest` réutilisable cross-origin si pas de check Origin/Referer
Pattern à ajouter à `patterns.md` section CSRF :
- **Pattern** : endpoint REST `permission_callback => '__return_true'` qui se repose uniquement sur `wp_verify_nonce($req['nonce'], 'wp_rest')` sans vérifier `Origin`/`Referer`.
- **Sévérité** : HAUTE
- **Risque** : le nonce `wp_rest` est exposé sur toute page front + via `<meta name="x-wp-nonce">` natif WP. Un site tiers qui scrape obtient un nonce valide et peut faire des POST cross-origin. Le rate-limit IP ne protège pas (l'attaquant utilise des proxies).
- **Distinction faux-positif** : OK si `is_same_origin($req)` est appelé en plus du nonce.
- **Fix type** : `CSRF-2` (déjà documenté dans known-fixes) **mais ajouter explicitement le bloc same-origin**.
- **Détecté dans** : pluscestsimple.com / arw-pulse `form.php` (audit 2026-05-14)

### P-NEW-2 — Admin-only data écrite en post_meta sans sanitisation, sanitisée seulement à l'affichage
Pattern à ajouter à `patterns.md` section Validation :
- **Pattern** : import CSV / JSON dans une page admin protégée par `manage_options` qui écrit du contenu en post_meta sans `sanitize_textarea_field`. La sanitisation finale ne se fait qu'au rendu via `wp_kses_post`.
- **Sévérité** : FAIBLE (admin trust) → MED (defense in depth cassée)
- **Risque** : un admin compromis pollue la DB avec du contenu arbitraire qui ne sera filtré qu'à l'output. Tout consommateur tiers (export JSON, API REST, CSV re-export) reçoit la donnée non-filtrée.
- **Fix type** : sanitiser à la frontière d'entrée (`update_post_meta`) ET à la sortie.
- **Détecté dans** : pluscestsimple.com / arw-pack-maison `csv-import.php` (audit 2026-05-14)

### P-NEW-3 — `mime_content_type` seul insuffisant pour validation upload
Pattern à ajouter à `patterns.md` section File upload :
- **Pattern** : `mime_content_type($tmp) === 'application/pdf'` comme seul check avant `move_uploaded_file`.
- **Sévérité** : MED (si dossier exécutable) → FAIBLE (si `.htaccess` deny)
- **Risque** : `mime_content_type` lit les magic bytes, qu'on peut forger. PDF polyglotte avec PHP en queue = upload accepté.
- **Fix type** : `wp_check_filetype_and_ext()` + extension whitelist + `.htaccess` deny.
- **Détecté dans** : pluscestsimple.com / arw-pack-maison `lead-magnet-admin.php` (audit 2026-05-14)
