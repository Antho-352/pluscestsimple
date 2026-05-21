#!/usr/bin/env bash
# Package the arw-pack-maison plugin into a zip ready to upload.
set -euo pipefail

cd "$(dirname "$0")"
NAME="arw-pack-maison"
OUT="../${NAME}.zip"

rm -f "$OUT"

cd ..
zip -r "${NAME}.zip" "${NAME}" \
    -x "${NAME}/package.sh" \
    -x "${NAME}/.git/*" \
    -x "${NAME}/.DS_Store" \
    -x "${NAME}/**/.DS_Store" > /dev/null

echo "✓ Pack packaged at $(pwd)/${NAME}.zip"
echo "  Install on WP: Plugins → Ajouter → Téléverser → ${NAME}.zip → Activer"
