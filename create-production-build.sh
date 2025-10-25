#!/bin/bash

# Production Build Script for Business Location Finder
# Creates incremental production builds with only essential files

# Configuration
PROJECT_NAME="biz-location-search"
BASE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PARENT_DIR="$(dirname "$BASE_DIR")"

# Extract version from plugin file
VERSION=$(grep "define('BLF_VERSION'" "$BASE_DIR/biz-location-finder.php" | sed "s/.*'\([^']*\)'.*/\1/")
if [[ -z "$VERSION" ]]; then
    echo "❌ Could not extract version from plugin file"
    exit 1
fi

# Function to get next production folder number
get_next_production_number() {
    local max_num=0
    
    # Find existing production folders and get the highest number
    for dir in "$PARENT_DIR"/${PROJECT_NAME}-${VERSION}-production-*; do
        if [[ -d "$dir" ]]; then
            # Extract number from folder name
            num=$(basename "$dir" | sed "s/${PROJECT_NAME}-${VERSION}-production-//")
            if [[ "$num" =~ ^[0-9]+$ ]] && [[ $num -gt $max_num ]]; then
                max_num=$num
            fi
        fi
    done
    
    # Return next number with leading zeros
    printf "%03d" $((max_num + 1))
}

# Get the next production folder number
PROD_NUMBER=$(get_next_production_number)
PROD_FOLDER="${PROJECT_NAME}-${VERSION}-production-${PROD_NUMBER}"
PROD_PATH="$PARENT_DIR/$PROD_FOLDER"

echo "🚀 Creating production build: $PROD_FOLDER (v${VERSION})"
echo "📁 Source: $BASE_DIR"
echo "📁 Target: $PROD_PATH"

# Create production directory
mkdir -p "$PROD_PATH"

# Copy essential files and folders
echo "📋 Copying files..."

# Copy main plugin file
if [[ -f "$BASE_DIR/biz-location-finder.php" ]]; then
    cp "$BASE_DIR/biz-location-finder.php" "$PROD_PATH/"
    echo "✅ Copied: biz-location-finder.php"
else
    echo "❌ Missing: biz-location-finder.php"
fi

# Copy README
if [[ -f "$BASE_DIR/README.md" ]]; then
    cp "$BASE_DIR/README.md" "$PROD_PATH/"
    echo "✅ Copied: README.md"
else
    echo "❌ Missing: README.md"
fi

# Copy BUILD documentation
if [[ -f "$BASE_DIR/BUILD.md" ]]; then
    cp "$BASE_DIR/BUILD.md" "$PROD_PATH/"
    echo "✅ Copied: BUILD.md"
else
    echo "❌ Missing: BUILD.md"
fi

# Copy assets folder (including SASS)
if [[ -d "$BASE_DIR/assets" ]]; then
    cp -r "$BASE_DIR/assets" "$PROD_PATH/"
    echo "✅ Copied: assets/ (including SASS files)"
else
    echo "❌ Missing: assets/"
fi

# Copy admin folder
if [[ -d "$BASE_DIR/admin" ]]; then
    cp -r "$BASE_DIR/admin" "$PROD_PATH/"
    echo "✅ Copied: admin/"
else
    echo "❌ Missing: admin/"
fi

# Copy templates folder
if [[ -d "$BASE_DIR/templates" ]]; then
    cp -r "$BASE_DIR/templates" "$PROD_PATH/"
    echo "✅ Copied: templates/"
else
    echo "❌ Missing: templates/"
fi

# Copy dist folder (if exists)
if [[ -d "$BASE_DIR/dist" ]]; then
    cp -r "$BASE_DIR/dist" "$PROD_PATH/"
    echo "✅ Copied: dist/"
else
    echo "⚠️  Optional: dist/ (not found)"
fi

# Create production info file
cat > "$PROD_PATH/PRODUCTION-INFO.txt" << EOF
Production Build Information
============================

Plugin Version: ${VERSION}
Build Number: ${PROD_NUMBER}
Created: $(date)
Source: $(basename "$BASE_DIR")
Target: ${PROD_FOLDER}

Files Included:
- biz-location-finder.php (main plugin file)
- README.md (user documentation)
- BUILD.md (development documentation)
- assets/ (CSS, JS, SASS files)
- admin/ (admin interface)
- templates/ (frontend templates)
- dist/ (distribution files, if exists)

Files Excluded:
- .git/ (version control)
- .gitignore
- .vscode/ (editor settings)
- test-*.html (test files)
- todo.txt (development notes)
- node_modules/ (if exists)
- *.log files
- .DS_Store files

This is a clean production build ready for deployment.
EOF

# Remove any development artifacts that might have been copied
echo "🧹 Cleaning development artifacts..."

# Remove git files if they exist
rm -rf "$PROD_PATH/.git"
rm -f "$PROD_PATH/.gitignore"

# Remove editor files
rm -rf "$PROD_PATH/.vscode"

# Remove test files
rm -f "$PROD_PATH"/test-*.html

# Remove development notes
rm -f "$PROD_PATH/todo.txt"

# Remove system files
find "$PROD_PATH" -name ".DS_Store" -type f -delete 2>/dev/null || true

# Set permissions
chmod -R 755 "$PROD_PATH"

# Generate file list
echo "📄 Generating file list..."
find "$PROD_PATH" -type f | sort > "$PROD_PATH/FILES-LIST.txt"

# Calculate folder size
FOLDER_SIZE=$(du -sh "$PROD_PATH" | cut -f1)

echo ""
echo "🎉 Production build complete!"
echo "📁 Location: $PROD_PATH"
echo "📊 Size: $FOLDER_SIZE"
echo "📋 Files: $(find "$PROD_PATH" -type f | wc -l | tr -d ' ') files"
echo ""
echo "📋 Build contents:"
ls -la "$PROD_PATH"
echo ""
echo "🚀 Ready for deployment!"