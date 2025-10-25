#!/bin/bash

# Deploy Latest Production Build to XAMPP
# Automatically finds the latest production build and deploys it

PROJECT_NAME="biz-location-search"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PARENT_DIR="$(dirname "$SCRIPT_DIR")"
XAMPP_PATH="/Applications/XAMPP/xamppfiles/htdocs/wp-dev/wp-content/plugins/biz-location-search-2.0.3"

# Extract version from plugin file
VERSION=$(grep "define('BLF_VERSION'" "$SCRIPT_DIR/biz-location-finder.php" | sed "s/.*'\([^']*\)'.*/\1/")
if [[ -z "$VERSION" ]]; then
    echo "❌ Could not extract version from plugin file"
    exit 1
fi

echo "🚀 Deploying latest production build to XAMPP..."

# Find the latest production folder
LATEST_PROD=""
LATEST_NUM=0

for dir in "$PARENT_DIR"/${PROJECT_NAME}-${VERSION}-production-*; do
    if [[ -d "$dir" ]]; then
        # Extract number from folder name
        num=$(basename "$dir" | sed "s/${PROJECT_NAME}-${VERSION}-production-//")
        if [[ "$num" =~ ^[0-9]+$ ]] && [[ $num -gt $LATEST_NUM ]]; then
            LATEST_NUM=$num
            LATEST_PROD="$dir"
        fi
    fi
done

if [[ -z "$LATEST_PROD" ]]; then
    echo "❌ No production builds found!"
    echo "💡 Run ./build.sh first to create a production build"
    exit 1
fi

echo "📁 Latest build: $(basename "$LATEST_PROD")"
echo "📁 Target: $XAMPP_PATH"

# Check if XAMPP directory exists
if [[ ! -d "$(dirname "$XAMPP_PATH")" ]]; then
    echo "❌ XAMPP plugins directory not found!"
    echo "💡 Expected: $(dirname "$XAMPP_PATH")"
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
rm -f "$XAMPP_PATH/PRODUCTION-INFO.txt"
rm -f "$XAMPP_PATH/FILES-LIST.txt"

# Clean up any remaining .DS_Store files
find "$XAMPP_PATH" -name ".DS_Store" -type f -delete 2>/dev/null || true

echo ""
echo "✅ Deployment complete!"
echo "📁 Deployed: $(basename "$LATEST_PROD") (v${VERSION}) → XAMPP"
echo "🌐 Plugin ready at: $XAMPP_PATH"
echo ""
echo "📋 Deployed files:"
ls -la "$XAMPP_PATH"