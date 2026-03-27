#!/bin/bash

# Build and Deploy - One command to rule them all!
# Creates production build and deploys to XAMPP

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo "🔄 Build & Deploy Pipeline"
echo "========================="
echo ""

# Step 1: Create production build
echo "🚀 Step 1: Creating production build..."
"$SCRIPT_DIR/build.sh"

if [[ $? -ne 0 ]]; then
    echo "❌ Build failed!"
    exit 1
fi

echo ""

# Step 2: Deploy to XAMPP
echo "🚀 Step 2: Deploying to XAMPP..."
"$SCRIPT_DIR/deploy-to-xampp.sh"

if [[ $? -ne 0 ]]; then
    echo "❌ Deployment failed!"
    exit 1
fi

echo ""
echo "🎉 Build & Deploy complete!"
echo "✅ Production build created"
echo "✅ Deployed to XAMPP"
echo "🌐 Ready for testing!"