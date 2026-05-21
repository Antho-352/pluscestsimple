#!/usr/bin/env bash
# Package the pcs-directory plugin into a zip ready to upload.
set -euo pipefail

cd "$(dirname "$0")"
NAME="pcs-directory"
OUT="../${NAME}.zip"

# Always remove the existing zip first — `zip -r` ADDS to existing archives by
# default, which mixes file structures and produces "extension dispose pas d'un
# entête valide" errors on WP upload.
rm -f "$OUT"

cd ..
zip -r "${NAME}.zip" "${NAME}" \
    -x "${NAME}/package.sh" \
    -x "${NAME}/.git/*" \
    -x "${NAME}/.DS_Store" \
    -x "${NAME}/**/.DS_Store" > /dev/null

echo "Pack packaged at $(pwd)/${NAME}.zip"
echo "Install on WP: Plugins -> Ajouter -> Televerser -> ${NAME}.zip -> Activer"
