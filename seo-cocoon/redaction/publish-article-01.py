#!/usr/bin/env python3
"""
Publie l'article #1 (petite salle de bain) en statut « En attente de relecture »
via l'API REST WordPress.

⚠️  Le mot de passe n'est PAS dans ce fichier. Tu le passes au lancement :

    WP_USER='admin_plu' WP_APP_PASS='xxxx xxxx xxxx xxxx xxxx xxxx' \
      python3 seo-cocoon/redaction/publish-article-01.py

Rien n'est publié en ligne : statut = pending (à relire). Tu relis puis tu publies
toi-même depuis l'admin.
"""
import base64
import json
import os
import pathlib
import sys
import urllib.error
import urllib.request

SITE = "https://pluscestsimple.com"
CAT_SLUG = "decoration-par-piece-salle-de-bain-cat"

TITLE = "Aménager une petite salle de bain : 8 solutions de pro"
SLUG = "amenager-petite-salle-de-bain"
EXCERPT = ("Quelques centimètres bien pensés suffisent à transformer une petite "
           "salle de bain : implantation, douche, rangements et lumière, voici ce "
           "qui gagne vraiment de la place.")

user = os.environ.get("WP_USER")
pw = os.environ.get("WP_APP_PASS")
if not user or not pw:
    sys.exit("Définis WP_USER et WP_APP_PASS dans l'environnement avant de lancer.")

content = (pathlib.Path(__file__).parent / "ARTICLE-01-content.html").read_text(encoding="utf-8")
auth = base64.b64encode(f"{user}:{pw}".encode()).decode()
headers = {"Content-Type": "application/json", "Authorization": "Basic " + auth}


def api(method, path, payload=None):
    data = json.dumps(payload).encode() if payload is not None else None
    req = urllib.request.Request(SITE + path, data=data, method=method, headers=headers)
    try:
        with urllib.request.urlopen(req) as r:
            return json.load(r)
    except urllib.error.HTTPError as e:
        sys.exit(f"Erreur API {e.code} : {e.read().decode()[:500]}")


# 1) Résout l'ID de la catégorie silo (pour que le maillage range bien l'article).
cats = api("GET", f"/wp-json/wp/v2/categories?slug={CAT_SLUG}")
cat_ids = [c["id"] for c in cats] if isinstance(cats, list) else []

# 2) Crée l'article en « pending » (à relire).
payload = {
    "title": TITLE,
    "slug": SLUG,
    "excerpt": EXCERPT,
    "content": content,
    "status": "pending",
}
if cat_ids:
    payload["categories"] = cat_ids

post = api("POST", "/wp-json/wp/v2/posts", payload)
print("✓ Article créé — statut :", post.get("status"))
print("  Éditer / relire :", post.get("link"))
if not cat_ids:
    print("  ⚠️ Catégorie non trouvée : pense à l'assigner (Salle de bain) dans l'éditeur.")
