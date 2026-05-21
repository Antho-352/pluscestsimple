# Changelog — pcs-banners

## 1.0.0 — 2026-05-21

Première release.

- CPT `pcs_banner` avec meta box (URL cible, type, libellé, dates start/end)
- Taxonomy `pcs_banner_slot` (non hiérarchique) + 4 termes seedés par défaut
  (`homepage-mid`, `category-intro`, `category-mid`, `in-article`)
- Bloc Gutenberg server-rendered `pcs/banner-slot` (zéro build, JS inline)
- Shortcode `[pcs_banner slot="..."]`
- Fonction publique `pcs_banner_render( $slot )` exploitable depuis le thème
- Sélection : random pick parmi les bannières actives à l'instant T
- Cache transient 5 min par slot, invalidation auto sur `save_post` / `delete_post`
- Auto `rel="sponsored nofollow noopener"` pour les types `sponsored` et `affilie`
- Étiquette auto au-dessus de l'image : "Sponsorisé", "En partenariat"
  (override via meta `_pcs_banner_label`, type `display` → aucune étiquette par défaut)
- Liste admin enrichie : aperçu 60x40, badges colorés (type, statut temporel),
  URL cliquable, colonnes triables, filtres type/slot/statut
- CSS conditionnel (~50 lignes) — enqueue uniquement si bannière rendue,
  détection via `has_block` + `has_shortcode`, fallback inline footer
- Image `loading="lazy"` + `decoding="async"`
- Hooks d'activation / désactivation (seed des slots + flush cache + flush rewrite)
