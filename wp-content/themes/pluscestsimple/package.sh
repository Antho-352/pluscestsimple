#!/usr/bin/env bash
# Génère le ZIP du thème pour upload via WP admin.
# Usage : ./package.sh
# Sortie : ../pluscestsimple.zip (overwrite)

set -euo pipefail

cd "$(dirname "$0")"
THEME_DIR="$(basename "$PWD")"
OUTPUT="../${THEME_DIR}.zip"

# Nettoyage du zip précédent (sinon zip -r ajoute au lieu de remplacer).
rm -f "$OUTPUT"

# On zippe le dossier parent en n'incluant que le dossier courant,
# de sorte que l'archive contienne pluscestsimple/style.css, etc.
cd ..
zip -r "${THEME_DIR}.zip" "$THEME_DIR" \
	-x "${THEME_DIR}/.git/*" \
	-x "${THEME_DIR}/.DS_Store" \
	-x "${THEME_DIR}/**/.DS_Store" \
	-x "${THEME_DIR}/node_modules/*" \
	-x "${THEME_DIR}/*.zip" \
	-x "${THEME_DIR}/package.sh" \
	> /dev/null

SIZE=$(du -h "${THEME_DIR}.zip" | cut -f1)
echo "✓ ${THEME_DIR}.zip généré (${SIZE})"
echo "  → Upload via WP admin → Apparence → Thèmes → Ajouter → Téléverser"
