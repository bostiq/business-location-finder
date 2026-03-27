#!/usr/bin/env bash
set -euo pipefail

PROJECT_NAME="biz-location-search"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PARENT_DIR="$(dirname "$SCRIPT_DIR")"
XAMPP_PATH="/Applications/XAMPP/xamppfiles/htdocs/wp-dev/wp-content/plugins/biz-location-search-2.0.3"

# Extract version from plugin file
VERSION=$(grep "define('BLF_VERSION'" "$SCRIPT_DIR/biz-location-finder.php" | sed "s/.*'\([^']*\)'.*/\1/")
if [[ -z "$VERSION" ]]; then
    echo "❌ Could not extract version from plugin file" >&2
    exit 1
fi

echo "🚀 Deploying latest production build to XAMPP..."

# Find the latest production folder
LATEST_PROD=""
LATEST_NUM=-1

# Make the glob expand to nothing if there are no matches
shopt -s nullglob

for dir in "$PARENT_DIR"/${PROJECT_NAME}-${VERSION}-production-*; do
    if [[ -d "$dir" ]]; then
        # Extract number from folder name
        base=$(basename "$dir")
        num=${base#"${PROJECT_NAME}-${VERSION}-production-"}

        # Skip non-numeric names
        if [[ ! "$num" =~ ^[0-9]+$ ]]; then
            continue
        fi

        # Force base-10 interpretation to avoid octal errors for leading zeros
        num=$((10#$num))

        if [[ $num -gt $LATEST_NUM ]]; then
            LATEST_NUM=$num
            LATEST_PROD="$dir"
        fi
    fi
done

# Restore default glob behavior (optional)
shopt -u nullglob

if [[ -z "$LATEST_PROD" ]]; then
    echo "❌ No production builds found!" >&2
    echo "💡 Run ./build.sh first to create a production build" >&2
    exit 1
fi

echo "📁 Latest build: $(basename "$LATEST_PROD")"
echo "📁 Target: $XAMPP_PATH"

# Check if XAMPP directory exists (parent)
if [[ ! -d "$(dirname "$XAMPP_PATH")" ]]; then
    echo "❌ XAMPP plugins directory not found!" >&2
    echo "💡 Expected: $(dirname "$XAMPP_PATH")" >&2
    exit 1
fi

# Clear existing files in XAMPP
if [[ -d "$XAMPP_PATH" ]]; then
    echo "🧹 Clearing existing XAMPP plugin files..."
    rm -rf "$XAMPP_PATH"/*
else
    echo "📁 Creating XAMPP plugin directory..."
    mkdir -p "$XAMPP_PATH"
fi

# Copy production files to XAMPP
echo "📋 Copying production files..."
cp -r "$LATEST_PROD"/* "$XAMPP_PATH/"

# Remove production info files (not needed in XAMPP)
rm -f "$XAMPP_PATH/PRODUCTION-INFO.txt" "$XAMPP_PATH/FILES-LIST.txt"

# Clean up any remaining .DS_Store files
find "$XAMPP_PATH" -name ".DS_Store" -type f -delete 2>/dev/null || true

echo ""
echo "✅ Deployment complete!"
echo "📁 Deployed: $(basename "$LATEST_PROD") (v${VERSION}) → XAMPP"
echo "🌐 Plugin ready at: $XAMPP_PATH"
echo ""
echo "📋 Deployed files:"
ls -la "$XAMPP_PATH"
