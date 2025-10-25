# Business Location Finder - Build & Development Guide

## 🛠️ **Development Workflow**

### **Database Integration** ✅
The plugin now supports **dual data sources**:
- **Google Sheets**: External CSV data via spreadsheet export
- **Database Storage**: Local WordPress database with full CRUD operations

### **Admin Interface**
- **Data Source Toggle**: Switch between Google Sheets and Database
- **Business Management**: Add, edit, and delete businesses via admin interface
- **Modal Forms**: User-friendly editing experience
- **Responsive Layout**: Optimized admin interface with side-by-side sections

---

## 🚀 **Production Build System**

### **Quick Commands**
```bash
# Create production build
./build.sh

# Deploy to XAMPP
./deploy-to-xampp.sh

# Build and deploy in one command
./build-and-deploy.sh

# Watch for changes and auto-build
./watch-and-build.sh
```

### **Build Scripts Overview**

#### **1. `build.sh`** - Quick Production Build
- Creates clean production builds
- Auto-extracts version from plugin file
- Incremental numbering with version prefix

#### **2. `deploy-to-xampp.sh`** - XAMPP Deployment
- Automatically finds latest production build
- Deploys to XAMPP WordPress installation
- Cleans up development artifacts

#### **3. `build-and-deploy.sh`** - Combined Workflow
- Runs build + deploy in sequence
- One-command production pipeline
- Error handling for failed builds

#### **4. `watch-and-build.sh`** - File Watcher
- Monitors file changes using `fswatch`
- Auto-creates builds when files are saved
- Watches key directories and files

---

## 📁 **Build Structure**

### **Production Build Features**
- **Version + Counter Naming**: `biz-location-search-2.0.3-production-001`, `002`, etc.
- **Auto-Version Detection**: Extracts version from plugin file automatically
- **Clean Structure**: Only essential files (no git, tests, or dev artifacts)
- **Auto-deployment**: Direct deployment to XAMPP for testing
- **File Watching**: Automatic builds when files change

### **Build Contents**
```
biz-location-search-2.0.3-production-001/
├── biz-location-finder.php     # Main plugin file
├── README.md                   # Documentation
├── admin/                      # Admin interface
│   ├── admin-page.php         # Main admin page
│   └── import-page.php        # Import interface
├── assets/                     # All assets (including SASS)
│   ├── css/                   # Compiled stylesheets
│   ├── js/                    # JavaScript files
│   └── sass/                  # SASS source files
├── templates/                  # Frontend templates
│   └── finder.php             # Main template
├── dist/                      # Distribution files (if exists)
├── PRODUCTION-INFO.txt        # Build information
└── FILES-LIST.txt            # Complete file listing
```

### **Included Files**
- ✅ `biz-location-finder.php` (main plugin)
- ✅ `admin/` (admin interface)
- ✅ `assets/` (CSS, JS, SASS)
- ✅ `templates/` (frontend templates)
- ✅ `README.md` (documentation)
- ✅ `dist/` (if exists)

### **Excluded from Production**
- ❌ `.git/` (version control)
- ❌ `test-*.html` (test files)
- ❌ `.vscode/` (editor settings)
- ❌ `todo.txt` (development notes)
- ❌ `.gitignore` (git configuration)
- ❌ `.DS_Store` (system files)
- ❌ `node_modules/` (if exists)
- ❌ Build scripts themselves

---

## 🔄 **Build Process Details**

### **Version Detection**
The build system automatically extracts the version from the main plugin file:
```php
define('BLF_VERSION', '2.0.3');
```

### **Naming Convention**
- **Pattern**: `{project-name}-{version}-production-{counter}`
- **Example**: `biz-location-search-2.0.3-production-001`
- **Incremental**: Each build increments the counter (001, 002, 003...)
- **Version-specific**: Different versions have separate counter sequences

### **Production Info File**
Each build includes a `PRODUCTION-INFO.txt` file:
```
Production Build Information
============================

Plugin Version: 2.0.3
Build Number: 002
Created: Fri Oct 24 20:45:13 ACDT 2025
Source: biz-location-search-2.0.3
Target: biz-location-search-2.0.3-production-002

Files Included: [list]
Files Excluded: [list]
```

---

## 🎯 **Development Environment**

### **Required Tools**
- **Bash**: For build scripts (macOS/Linux)
- **SASS**: For stylesheet compilation
- **fswatch**: For file watching (install via `brew install fswatch`)

### **XAMPP Integration**
- **Target Path**: `/Applications/XAMPP/xamppfiles/htdocs/wp-dev/wp-content/plugins/biz-location-search-2.0.3/`
- **Auto-deployment**: Scripts automatically deploy to XAMPP
- **Clean deployment**: Removes development artifacts before deployment

### **File Watching Setup**
```bash
# Start file watcher
./watch-and-build.sh

# Watches these paths:
# - biz-location-finder.php
# - admin/
# - assets/
# - templates/
# - README.md
```

---

## 🔧 **Development Commands**

### **SASS Compilation**
```bash
# Compile admin styles
cd assets/sass
sass admin-styles.sass ../css/admin-styles.css

# Compile with compression
sass admin-styles.sass ../css/admin-styles.min.css --style compressed

# Compile frontend styles
sass style.sass ../css/style.css
```

### **Manual Build Steps**
```bash
# 1. Create production folder
mkdir biz-location-search-2.0.3-production-XXX

# 2. Copy essential files
cp -r admin assets templates *.php *.md production-folder/

# 3. Clean development artifacts
rm -rf production-folder/.git
find production-folder -name ".DS_Store" -delete

# 4. Deploy to XAMPP
cp -r production-folder/* /path/to/xampp/plugins/
```

---

## 📋 **Build Checklist**

### **Before Building**
- [ ] All changes committed to git
- [ ] SASS files compiled to CSS
- [ ] Version number updated if needed
- [ ] Admin interface tested
- [ ] Frontend functionality verified

### **After Building**
- [ ] Production build created successfully
- [ ] XAMPP deployment completed
- [ ] Plugin activated in WordPress
- [ ] All features working in test environment
- [ ] Database connections verified

---

## 🚨 **Troubleshooting**

### **Common Issues**

#### **Build Script Fails**
```bash
# Check script permissions
chmod +x *.sh

# Verify plugin file exists
ls -la biz-location-finder.php

# Check version extraction
grep "define('BLF_VERSION'" biz-location-finder.php
```

#### **XAMPP Deployment Issues**
```bash
# Verify XAMPP path
ls -la /Applications/XAMPP/xamppfiles/htdocs/wp-dev/wp-content/plugins/

# Check permissions
sudo chown -R $(whoami) /path/to/xampp/plugins/
```

#### **File Watcher Problems**
```bash
# Install fswatch
brew install fswatch

# Test fswatch
fswatch -o . | head -5
```

---

## 📈 **Build History**

### **Version Progression**
- `biz-location-search-2.0.3-production-001` - Initial versioned build
- `biz-location-search-2.0.3-production-002` - Updated documentation
- `biz-location-search-2.0.3-production-XXX` - Future builds...

### **Legacy Builds**
- `biz-location-search-production-001` - Pre-versioning format
- `biz-location-search-production-002` - Pre-versioning format

---

**Author**: Lorenzo Colen  
**Last Updated**: October 24, 2025  
**Build System Version**: 1.0