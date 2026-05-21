#!/usr/bin/env bash
# Package the pcs-banners plugin into a zip ready to upload.
# Fixed name (no timestamp), overwrites the previous zip.
set -euo pipefail

cd "$(dirname "$0")"
NAME="pcs-banners"
OUT="../${NAME}.zip"

rm -f "$OUT"

cd ..
zip -r "${NAME}.zip" "${NAME}" \
    -x "${NAME}/package.sh" \
    -x "${NAME}/.git/*" \
    -x "${NAME}/.DS_Store" \
    -x "${NAME}/**/.DS_Store" > /dev/null

echo "✓ Plugin packaged at $(pwd)/${NAME}.zip"
echo "  Install on WP: Plugins → Ajouter → Téléverser → ${NAME}.zip → Activer"
