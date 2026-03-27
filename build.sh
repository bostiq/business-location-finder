#!/bin/bash

# Quick Production Build - Run from project directory
# Usage: ./build.sh

# Get the directory where this script is located
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Run the full production build script
"$SCRIPT_DIR/create-production-build.sh"