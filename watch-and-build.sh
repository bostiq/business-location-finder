#!/bin/bash

# Auto-build script - watches for file changes and creates production builds
# Usage: ./watch-and-build.sh

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo "👁️  Watching for file changes in: $SCRIPT_DIR"
echo "🔄 Will auto-create production builds when files are saved"
echo "⏹️  Press Ctrl+C to stop"
echo ""

# Check if fswatch is available (macOS file watcher)
if ! command -v fswatch &> /dev/null; then
    echo "❌ fswatch not found. Installing via Homebrew..."
    if command -v brew &> /dev/null; then
        brew install fswatch
    else
        echo "❌ Homebrew not found. Please install fswatch manually:"
        echo "   brew install fswatch"
        exit 1
    fi
fi

# Watch for changes in key files and directories
fswatch -o \
    "$SCRIPT_DIR/biz-location-finder.php" \
    "$SCRIPT_DIR/admin/" \
    "$SCRIPT_DIR/assets/" \
    "$SCRIPT_DIR/templates/" \
    "$SCRIPT_DIR/README.md" \
    --exclude='.*\.git.*' \
    --exclude='.*\.DS_Store' \
    --exclude='.*node_modules.*' \
    --exclude='.*\.log' | while read num
do
    echo ""
    echo "📝 File change detected at $(date)"
    echo "🚀 Creating new production build..."
    "$SCRIPT_DIR/build.sh"
    echo "✅ Auto-build complete!"
    echo "👁️  Watching for more changes..."
done