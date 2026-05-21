#!/usr/bin/env bash
# Package the theme for WordPress upload.
# Usage: ./package.sh
# Output: ../arw-pulse.zip (fixed name, overwrites previous).

set -euo pipefail

THEME_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PARENT_DIR="$(dirname "$THEME_DIR")"
ZIP_NAME="arw-pulse.zip"
TARGET="$PARENT_DIR/$ZIP_NAME"

cd "$PARENT_DIR"
rm -f "$TARGET"

zip -r "$ZIP_NAME" "arw-pulse" \
  -x "arw-pulse/.git/*" \
  -x "arw-pulse/.DS_Store" \
  -x "arw-pulse/package.sh" \
  -x "arw-pulse/node_modules/*" \
  -x "*.map"

echo ""
echo "✓ Theme packaged at $TARGET"
echo "  Upload via WordPress: Apparence → Thèmes → Ajouter → Téléverser"
