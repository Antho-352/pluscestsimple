#!/usr/bin/env bash
# Crée le zip de déploiement du plugin pcs-directory.
# Usage : bash package.sh (depuis n'importe où)
set -euo pipefail

cd "$(dirname "$0")"
NAME="pcs-directory"
OUT="../${NAME}.zip"

# rm avant rezip OBLIGATOIRE — zip -r AJOUTE par défaut sans remplacer.
rm -f "$OUT"

cd ..
zip -r "${NAME}.zip" "${NAME}" \
    -x "${NAME}/package.sh" \
    -x "${NAME}/.git/*" \
    -x "${NAME}/.DS_Store" \
    -x "${NAME}/**/.DS_Store" > /dev/null

echo "✅ ${NAME}.zip créé dans $(pwd)/"
echo "   WP : Extensions → Ajouter → Téléverser → ${NAME}.zip → Activer"
