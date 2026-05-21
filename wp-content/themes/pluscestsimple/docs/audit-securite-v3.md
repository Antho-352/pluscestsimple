# Audit sécurité ARW Pulse v1.12.0 — V3

## Synthèse

- **Date** : 2026-05-15
- **Périmètre** : thème custom arw-pulse (~5155 LOC PHP, 28 modules `/inc/`, templates FSE, parts, patterns)
- **Décompte** : CRIT=0 | HAUTE=0 | MED=2 | FAIBLE=4 | INFO=3
- **Validation patches v1.11.1 → v1.12.0** : 9/9 OK, aucune régression détectée

Le thème a clairement atteint un palier de maturité. Aucune vuln exploitable trouvée. Les résidus sont de la défense en profondeur ou des optimisations RGPD/durcissement.

---

## Validation des 9 patches récents

| # | Patch | Statut | Justification |
|---|---|---|---|
| 1 | CPT `arw_submission` → `manage_options` | **OK** | `inc/form.php:32-44`. `capability_type=page`, `map_meta_cap=false`, toutes capacités custom hardcodées sur `manage_options`. `create_posts=do_not_allow` empêche la création manuelle dans l'admin. Logique correcte : un author ne voit plus les PII. Pas de blocage admin légitime — `manage_options` est le niveau admin standard. |
| 2 | Nonce wp_unslash featured-post.php:81 | **OK** | `sanitize_text_field( wp_unslash( $_POST['arw_pulse_featured_nonce'] ) )` propre. |
| 3 | Token HMAC anti-spam (form.php) | **OK avec nuance** | Cf. M1 ci-dessous : pas de protection anti-replay (le même token peut être réutilisé dans la fenêtre 3s-30min). Le code lui-même est correct : `hash_equals` utilisé, salt `wp_salt('nonce')` non leaké, bornes de Δt cohérentes. |
| 4 | Bloc `/wp-admin/install.php` (.htaccess + init priority 0) | **OK avec nuance** | Le `.htaccess` est la protection primaire. Le hook PHP `init` priority 0 (performance.php:238) est best-effort : install.php inclut `wp-load.php` → `wp-settings.php`, qui charge le thème via `after_setup_theme` AVANT de fire `init`. Donc le hook DOIT fire avant le rendu HTML de install.php. Vérifié — install.php affiche son markup APRÈS avoir traité ses étapes wizard, donc l'`init` fire en amont. La condition `get_option('siteurl')` garantit aucun blocage du flux d'install initial légitime. |
| 5 | `arw_product_link` réservé `manage_options` | **OK** | Triple guard : `auth_callback` REST (products.php:90), input HTML masqué pour non-admins (products.php:177-190), save guard explicite (products.php:233). Defense in depth correcte. |
| 6 | Rate-limit atomique `arw_pulse_rate_hit()` | **OK** | `inc/security.php:214-226`. `wp_cache_incr` quand object cache présent, fallback transient. Race window résiduelle uniquement sans object cache — connu et documenté. |
| 7 | `esc_attr` sur `$o`/`$opt_name` | **OK** | Vérifié exhaustivement dans `inc/identity.php` (24 occurrences) et `inc/media-kit.php` (60+ occurrences). Tous les `echo $opt_name` / `echo $o` sont wrappés `esc_attr()`. |
| 8 | Trailing-slash 301 whitelist query args (seo.php) | **OK** | `seo.php:339-343`. Whitelist explicite : s, paged, page, p, preview*, utm_*, ref, gclid, fbclid. Bloque les param injection arbitraires dans le redirect 301. |
| 9 | Null-safe `?->publish` (admin-menu.php) | **OK** | Lignes 123, 131, 139, 146. Pattern `wp_count_posts(...)?->publish ?? 0` correct sur PHP 8+. |

---

## CRITIQUES

Aucune.

## HAUTES

Aucune.

## MOYENNES

### M1 — Token HMAC anti-spam : pas de protection anti-replay

- **Fichier** : `inc/form.php:148-165`
- **Sévérité** : MOYENNE
- **Risque** : un bot qui charge la home récupère un couple `(ts, tsig)` valide signé pour Δt ∈ [3s, 30min]. Il peut ensuite POSTer N fois ce MÊME couple pendant 30 minutes vers `/wp-json/arw/v1/submit`. Le HMAC reste valide (le serveur ne stocke pas les tokens consommés). Le rate-limit cap à 10/IP/10min limite l'abus mais ne bloque pas un attaquant avec 100 IP (botnet, proxies).
- **Mitigations existantes** : rate-limit IP + global (60/h) atténue l'impact. Permission callback `Origin/Referer` bloque l'usage cross-origin trivial.
- **Patch** :
```php
// Dans arw_pulse_handle_submission, après la vérif Δt :
$replay_key = 'arw_tsig_' . substr( $tsig, 0, 32 );
if ( get_transient( $replay_key ) ) {
    return new WP_REST_Response( [ 'ok' => false, 'error' => 'token_replayed' ], 409 );
}
set_transient( $replay_key, 1, 30 * MINUTE_IN_SECONDS );
```
- **Référence** : nouveau pattern §15 (token replay window).

### M2 — Critical CSS inline non échappé (theoretical)

- **Fichier** : `inc/performance.php:97-101`
- **Sévérité** : MOYENNE (théorique ; CSS source contrôlée)
- **Code** :
```php
$css = file_get_contents( $path );
if ( $css ) {
    echo '<style id="arw-pulse-critical">' . $css . '</style>' . "\n";
}
```
- **Risque** : le fichier `/assets/css/critical.css` est bundlé dans le thème — source contrôlée. MAIS si un admin compromis remplace le fichier (via FTP, plugin file editor), il peut y injecter `</style><script>...` pour contourner la CSP via `'unsafe-inline'` du script-src. Defense in depth cassée.
- **Patch** :
```php
echo '<style id="arw-pulse-critical">' . str_replace( '</style>', '', $css ) . '</style>' . "\n";
```
Ou plus strict : valider que `$css` ne contient pas `<` après strip BOM.
- **Référence** : pattern §14b adapté (injection `</tag>` dans contexte HTML).

---

## FAIBLES

### F1 — Manifest icon URL leak via SSRF-like attachment

- **Fichier** : `inc/branding.php:118-122`
- **Sévérité** : FAIBLE
- **Risque** : `wp_get_attachment_image_src( $icon_id, 'full' )[0]` est inséré dans le manifest sans validation que c'est bien une image locale (un admin peut uploader un attachement avec une URL externe via REST `attachment_url` filter). Exploitable seulement avec un admin compromis ; impact limité au manifest exposant une URL externe.
- **Patch** : vérifier que l'URL retournée appartient au upload basedir.

### F2 — security.txt : `$contact` injecté sans sanitize

- **Fichier** : `inc/security.php:114-118`
- **Sévérité** : FAIBLE
- **Code** : `echo "Contact: {$contact}\n";` avec `$contact = apply_filters( 'arw_pulse_security_contact', 'mailto:' . admin_email )`.
- **Risque** : la valeur par défaut est sûre (admin_email passé par `sanitize_email` côté option WP). Mais un plugin appliquant le filtre avec une string contenant `\n` casse le format security.txt et permet d'injecter d'autres headers (`Policy:`, `Encryption:`, etc.). Surface : aucune exploitation directe, juste défense en profondeur.
- **Patch** :
```php
echo 'Contact: ' . preg_replace( '/[\r\n]/', '', $contact ) . "\n";
```

### F3 — `arw_pulse_disclosure_markup()` : filtre disclosure_text peut injecter HTML

- **Fichier** : `inc/affiliate.php:78-83`
- **Sévérité** : FAIBLE
- **Code** : `esc_html( $text )` est appliqué, donc l'output est safe. Mais l'option `arw_identity[disclosure_text]` (identity.php:93) est sanitisée via `sanitize_textarea_field` qui PRÉSERVE certains caractères Unicode dangereux (RTL override, zero-width). En contexte plain-text c'est OK ; en preview admin ça peut tromper. Non-exploitable, INFO plutôt que F.
- **Action** : aucune obligatoire. Optionnel : appliquer `arw_pulse_normalize_social_text()` côté sanitize identity.

### F4 — Rate-limit global utilise transient sans object cache

- **Fichier** : `inc/security.php:135-143`
- **Sévérité** : FAIBLE
- **Risque** : sans object cache externe (Redis/Memcached), le rate-limit global utilise `arw_pulse_rate_hit()` qui retombe sur `get_transient + set_transient` non-atomique. Race window connue (déjà documentée), mais le code prétend être atomique. Confusion potentielle pour le mainteneur.
- **Patch** : aucune urgence. Commentaire à clarifier : « atomique uniquement si object cache externe présent ».

---

## INFO

### I1 — `print_r`/`var_dump` : aucun trouvé en prod

Vérifié : grep négatif sur tous les modules. OK.

### I2 — `wp_remote_*` : aucun appel sortant dans le thème

Pas de SSRF possible côté thème. OK.

### I3 — `wp_kses` au save manquant pour `disclosure_text`

`identity.php:93-95` utilise `sanitize_textarea_field` qui strip les tags. Cohérent avec l'usage `esc_html` au rendu (affiliate.php:82). OK.

---

## Points OK vérifiés

1. **CSRF protection** : tous les `save_post` / `admin_post_*` handlers vérifient nonce + capability + `DOING_AUTOSAVE` (form.php:292-293, products.php:203, featured-post.php:81-84, seo.php:560, structured-data.php:215).

2. **REST permission** : `/arw/v1/submit` exige Origin OU Referer aligné `home_url()` (form.php:108-121). Bloque le CSRF-5 (nonce `wp_rest` réutilisable cross-origin). Bonne pratique.

3. **JSON-LD safety** : `wp_json_encode` avec `JSON_HEX_TAG | JSON_HEX_AMP` dans schema.php:342 ET branding.php:136 (manifest). §14b couvert.

4. **IP CDN-aware + pseudonymisée** : `arw_pulse_client_ip()` lit CF-Connecting-IP → X-Forwarded-For → REMOTE_ADDR avec `FILTER_VALIDATE_IP`. Stockage uniquement en HMAC-SHA256 (form.php:212). RGPD compliant.

5. **Login/author leak prevention** : `login_errors` neutralisé (security.php:87-89), `?author=N` 404 pour anonymes (security.php:67-83), REST `/wp/v2/users` désactivé anon (cleanup.php:62-70).

6. **HTTP headers durcis** : HSTS+X-Frame+X-CTO+Referrer-Policy+Permissions-Policy+CSP — tous présents (security.php:42-60). Le trade-off `'unsafe-inline'` est documenté.

---

## Patterns inédits détectés (à proposer pour KB)

### Pattern §15 (nouveau) — Token HMAC sans tracking anti-replay

**Détecté dans** : arw-pulse `inc/form.php` (audit v3, 2026-05-15)

**Sévérité par défaut** : MOYENNE

**Description** : un token horodaté HMAC-signé sans mémoire serveur des tokens consommés permet le replay illimité dans la fenêtre de validité (typique : 30 min). Mitigation par rate-limit IP cap l'abus depuis une IP unique mais ne bloque pas botnet/proxy rotatif.

**Distinction faux-positif** : OK si la fenêtre est <60s (replay trivialisé par bot anyway) OU si rate-limit global suffisamment serré (<5/min total).

**Fix type** : `set_transient('arw_tsig_'.substr($tsig,0,32), 1, $window_seconds)` avant traitement, refuse si déjà présent.

### Pattern §14b adapté — `</style>` injection dans CSS inline

**Détecté dans** : arw-pulse `inc/performance.php` (audit v3)

**Sévérité par défaut** : FAIBLE→MOYENNE (admin compromis requis pour exploiter)

**Pattern** : `file_get_contents` d'un fichier CSS bundlé suivi de `echo '<style>' . $css . '</style>'` sans strip des séquences `</style>` ou `</script>`. Le fichier source est contrôlé MAIS un admin compromis (FTP, file editor plugin) peut y injecter des séquences cassant le contexte HTML.

**Fix type** : `str_replace(['</style>', '</STYLE>'], '', $css)` minimum, ou validation regex stricte.

---

## Recommandations prioritaires

1. **M1 (replay token)** : à patcher rapidement (10 lignes). Augmente significativement le coût du spam botnet.
2. **M2 (critical CSS strip)** : 1 ligne de défense en profondeur.
3. **F2 (security.txt CRLF strip)** : 1 ligne, gratuit.
4. **F4 (commentaire atomicité)** : doc-only.

Aucun blocker pour v1.12.0 → prod. Le thème est en très bon état sécurité.
