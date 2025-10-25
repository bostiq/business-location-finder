# Business Location Finder WordPress Plugin

A dynamic, interactive business location finder with flexible category support, Google Sheets integration, and responsive design. Perfect for directories, stockist locators, and business listings of any type.

## 🚀 **Key Features**

### ✨ **Universal Flexibility**
- **Any Category Names**: Works with restaurants, services, retail, or any business type
- **Dynamic Tab Generation**: Automatically creates tabs based on your data categories
- **Dual Data Sources**: Choose between Google Sheets or local database storage
- **Database Management**: Full CRUD operations via WordPress admin interface
- **Responsive Design**: Mobile-friendly grid layout that adapts to any screen size

### 🎯 **Core Functionality**
- **Tabbed Navigation**: Organized browsing by business categories
- **Enhanced Search**: Real-time filtering by business name or suburb/location
- **Dynamic Counters**: Badge counters showing businesses per category
- **Interactive Cards**: Hover effects with Google Maps integration
- **Social Media Links**: Direct Instagram profile connections
- **Smooth Animations**: Staggered card reveal animations
- **Admin Interface**: Add, edit, and delete businesses with modal forms

### 🔒 **Enterprise Security**
- Input sanitization and XSS protection
- CSRF protection with WordPress nonces
- Capability-based access control
- Rate limiting and secure headers

---

## 📋 **Quick Setup Guide**

### 1. **Installation**
- Upload the entire plugin folder to: `wp-content/plugins/business-location-finder/`
- OR use WordPress Admin → Plugins → Add New → Upload Plugin

### 2. **Activation**
- Go to **WordPress Admin → Plugins**
- Find "Business Location Finder" and click **"Activate"**

### 3. **Configure Google Sheets**
- Go to **WordPress Admin → Location Finder**
- Add your Google Sheets export URL in the settings form
- Click **"Save Settings"**

### 4. **Use the Shortcode**
- `[biz_location_finder]`


---

## ⚙️ **Configuration & Usage**

### **Changing Your Data Source**

#### WordPress Admin Settings:
1. **Navigate**: WordPress Admin → **Location Finder → Import Data**
2. **Update URL**: Enter your Google Sheets CSV export URL
3. **Save**: Click "Save Google Sheets URL"

#### Getting Google Sheets Export URL:
1. Open your Google Sheet
2. **File** → **Share** → **Publish to web**
3. Choose **"Comma-separated values (.csv)"** format
4. Select specific sheet tab if needed
5. Click **"Publish"** and copy the URL

### **Required Data Format**

Your Google Sheet must have these columns (any order):

| Column | Description | Example |
|--------|-------------|---------|
| `name` | Business name | "Café Luna" |
| `category` | Business category | "Coffee Shops" |
| `suburb` | Location suburb | "Adelaide" |
| `address` | Full address | "123 Main St, Adelaide SA 5000" |
| `instagram` | Handle (without @) | "cafeluna" |

### **Shortcode Options**


# Show all categories from your data
- `[biz_location_finder]`

# Show only specific categories (case-insensitive matching)
- `[biz_location_finder categories="restaurants,cafes"]`

# Data view - no tabs, just search and business cards
- `[biz_location_finder view="data"]`

# Disable search functionality
- `[biz_location_finder search="false"]`

# Hide counter badges
- `[biz_location_finder counters="false"]`

# Minimal version
- `[biz_location_finder search="false" counters="false"]`

# Data view combinations
- `[biz_location_finder view="data" search="false"]`
- `[biz_location_finder view="data" categories="restaurants"]`


**Available Parameters:**
- `categories`: Comma-separated category list (default: all)
- `search`: Enable/disable search (default: true)
- `counters`: Show/hide badges (default: true)
- `view`: Display mode - "default" (tabs) or "data" (no tabs) (default: default)

**Category Matching:**
The `categories` parameter uses simple case-insensitive matching with your exact Google Sheets category names:

**Examples:**
```
<!-- If your Google Sheets has categories: "Coffee Shops", "Restaurants", "Retail Stores" -->

<!-- Show only Coffee Shops (case-insensitive) -->
[biz_location_finder categories="coffee shops"]

<!-- Show multiple categories -->
[biz_location_finder categories="restaurants,coffee shops"]

<!-- Categories are matched exactly (ignoring case) -->
[biz_location_finder categories="Restaurants,Coffee Shops"]
```

**Important:** Use the exact category names from your Google Sheets. The matching is case-insensitive, so "restaurants" will match "Restaurants" in your data.

**View Modes:**
The `view` parameter controls the display layout:

- **`view="default"`** (default): Full tabbed interface with category navigation
- **`view="data"`**: Clean data view without tabs - just search and business cards

**Data View Benefits:**
- Simpler interface for basic business listings
- Faster loading with no tab switching functionality  
- Perfect for embedding in sidebars or compact spaces
- Still supports all other parameters (search, categories, counters)

### **Category Examples**

The system adapts to ANY category structure:

**Coffee Directory:**
- "Specialty Coffee", "Chain Coffee", "Local Roasters"

**Restaurant Guide:**
- "Fine Dining", "Casual", "Fast Food", "Takeaway"

**Service Directory:**
- "Legal Services", "Medical", "Home Services"

**Retail Locations:**
- "Clothing", "Electronics", "Home & Garden"

---

## 🛠️ **Technical Documentation**

### **System Information**

**Current Version**: v2.1.3 (October 2025)

**Component Versions:**
- Frontend: stockists.js v2.1.3
- Backend: biz-location-finder.php v2.1.3
- Admin Interface: v2.1.3 (Database CRUD)
- Styles: SASS/CSS v2.1.3 (Optimized)

### **Architecture**

**Frontend:**
- Vanilla JavaScript (no dependencies)
- Dynamic DOM manipulation
- ES6+ with modern browser support
- Responsive CSS Grid layout

**Backend:**
- WordPress REST API integration
- Google Sheets CSV proxy
- WordPress options for settings storage
- Security-first development approach

**Data Flow:**
1. Google Sheets → CSV export
2. WordPress REST API proxy
3. JavaScript parsing and rendering
4. Dynamic tab/content generation

### **File Structure**

```
business-location-finder/
├── biz-location-finder.php      # Main plugin file
├── assets/
│   ├── css/style.css           # Compiled styles
│   └── js/stockists.js         # Frontend JavaScript
├── templates/
│   └── finder.php              # Shortcode template
├── admin/
│   ├── admin-page.php          # Settings page
│   └── import-page.php         # Future CSV import
├── sass/                       # SASS source files
│   ├── style.sass             # Main SASS
│   ├── _cards.sass            # Card styling
│   ├── _colors.sass           # Color variables
│   ├── _counters.sass         # Badge styling
│   ├── _fonts.sass            # Typography
│   ├── _search.sass           # Search input
│   ├── _tab-Nav.sass          # Tab navigation
│   └── _tabs-content.sass     # Tab panels
└── HOW-TO-CHANGE-SHEET-URL.md  # Setup guide
```

### **Dependencies**

**Required:**
- WordPress 5.0+ with REST API
- PHP 7.4+
- Modern browser with ES6+ support

**Data Source:**
- Google Sheets with public sharing
- CSV export format capability

**Optional:**
- WordPress caching plugin (recommended)
- Google Fonts for typography

### **Browser Support**

| Browser | Support |
|---------|---------|
| Chrome | ✅ Latest |
| Firefox | ✅ Latest |
| Safari | ✅ Latest |
| Edge | ✅ Latest |
| Mobile | ✅ iOS Safari, Chrome Mobile |

## Version History

### v2.1.3 (Current)
**Critical Bug Fix - Edit Business Modal**
- **Bug Fix**: Fixed modal display issue in WordPress admin interface for editing business records
- **CSS Fix**: Added global modal styles with !important declarations to override WordPress admin CSS
- **JavaScript Fix**: Corrected table cell mapping in loadBusinessData function to match 6-column table structure
- **Modal Enhancement**: Improved modal backdrop, positioning, and form sections for better user experience
- **Debugging**: Added comprehensive console logging for troubleshooting modal and edit functionality
- **Production Deployment**: Created production-005 build with complete modal functionality

### v2.1.2 (Previous)
**SASS Architecture Optimization & Production Deployment**
- **Major**: Complete SASS architecture restructure for maintainable development
- **Build System**: Optimized CSS output with two-file structure (`style.min.css` + `admin-styles.min.css`)
- **Modularity**: Separated structure from color schemes with logical file organization
- **Performance**: Eliminated inline admin styles, moved to organized SASS partials
- **Maintainability**: Created centralized color system with reusable mixins and functions
- **File Structure**: 
  - `admin-styles.sass` - Root admin stylesheet with proper imports
  - `_admin-styles-color-scheme.sass` - Admin settings page colors + shared components
  - `_import-page.sass` - Import page structure-only styling
  - `_import-page-color-scheme.sass` - Import page specific color scheme
  - `_colors.sass` - Centralized color system for entire plugin
- **Developer Experience**: Clear separation of concerns, easy color customization, efficient CSS compilation
- **Production Ready**: Minified CSS files optimized for deployment with source maps

### v2.1.1 (Previous)
**Empty Database Handling & Version Consistency**
- **Fix**: Corrected version numbers across all files to maintain consistency
- **Enhancement**: Improved empty database handling with user-friendly messaging
- **Enhancement**: Backend now returns success (200) instead of 404 for empty database
- **Enhancement**: Frontend shows "No businesses found" message instead of HTTP error
- **UI**: Added styled `.blf-no-businesses` component for better empty state display
- **UX**: Provides clear guidance to users on how to add businesses when database is empty

### v2.1.0 (Previous)
**Database Implementation & Admin Interface**
- **Major**: Added complete MySQL database support with dual data source architecture
- **Feature**: Implemented CRUD operations for business records (Create, Read, Update, Delete)
- **Feature**: Built comprehensive admin interface with modal edit forms
- **Feature**: Added AJAX handlers for secure database operations with nonce verification
- **Enhancement**: Unified data source switching between Google Sheets and database
- **Enhancement**: Added description column display in admin business listings
- **Enhancement**: Improved security with capability checks for admin operations
- **Build System**: Created automated production build scripts with version extraction
- **Documentation**: Separated user (README.md) and developer (BUILD.md) documentation

#### **v2.0.3** (October 2025) - **Previous Release**
- ✨ **New View Mode**: Added `view="data"` parameter for tab-free display mode
- 🎯 **Flexible Layout**: Choose between full tabbed interface or clean data-only view
- 🔧 **Multi-Container Support**: Perfect support for multiple shortcodes on same page with unique IDs
- ✅ **Enhanced Shortcode Options**: Complete parameter combinations working flawlessly
- 🛠️ **Improved JavaScript**: Robust container isolation and error handling

#### **v2.0.2** (October 2025) - **Previous Release**
- ✨ **Enhanced Search**: Added business name search functionality - users can now search by both business name and suburb
- 🎯 **Improved UX**: Updated search placeholder to "Search by business name or suburb…" for better user guidance
- 🔧 **Dynamic Categories**: Automatically builds category tabs from CSV data when no specific categories are provided
- ✅ **Better Error Messages**: More intuitive "no matches found" messaging
- 🛠️ **Category Filtering Fix**: Simplified case-insensitive exact matching for shortcode categories

#### **v2.0.1** (October 2025) - **Previous Release**
- ✨ **Enhanced Search**: Added business name search functionality - users can now search by both business name and suburb
- 🎯 **Improved UX**: Updated search placeholder to "Search by business name or suburb…" for better user guidance
- 🔧 **Dynamic Categories**: Automatically builds category tabs from CSV data when no specific categories are provided
- ✅ **Better Error Messages**: More intuitive "no matches found" messaging

#### **v2.0.1** (October 2025) - **Previous Release**
- 🐛 **CSV Parsing Bug Fixed**: Resolved HTML entity double-encoding and escaped quote issues
- ✅ **Clean Data Display**: Business names and addresses now display without extra backslashes or `&amp;amp;` entities
- ✅ **Improved Google Maps Links**: URLs are properly encoded without double-escaping
- ✅ **Enhanced Debugging**: Added comprehensive logging for CSV processing pipeline

#### **v2.0.0** (October 2025) - **Previous Release**
- ✅ **Flexible Categories**: Works with any category names
- ✅ **Dynamic Tab Generation**: Auto-builds interface from data
- ✅ **Admin Settings**: WordPress admin interface for configuration
- ✅ **Enhanced Security**: Enterprise-level protection
- ✅ **Backwards Compatibility**: Existing data continues working

#### **v1.0.0** (October 2025) - **Previous Stable**
- Google Sheets integration
- Hardcoded category system
- Basic WordPress plugin structure
- CSV parsing and rendering

### **REST API Endpoints**

```
GET /wp-json/jq-stockists/v1/get-csv
```
- **Purpose**: Proxy Google Sheets CSV data
- **Authentication**: Public (rate limited)
- **Response**: Raw CSV data
- **Cache**: 5 minutes

---

## 🔧 **Development & Troubleshooting**

### **Common Issues**

**Data Not Loading:**
- Check Google Sheets sharing permissions ("Anyone with link")
- Verify WordPress permalinks (Settings → Permalinks → Save)
- Check browser console for errors

**Category Issues:**
- Ensure category names match exactly in your data
- Check for extra spaces or special characters
- Verify CSV column headers are correct

**Search Not Working:**
- Confirm business names or suburb names match data format
- Check JavaScript console for errors
- Verify search input is enabled in shortcode

### **Performance Optimization**

**Caching:**
- Enable WordPress object caching
- Use hosting-level cache (Cloudflare, etc.)
- Browser cache with version parameters

**Data Optimization:**
- Keep Google Sheets reasonably sized (<1000 rows)
- Use consistent data formatting
- Optimize image references if using custom fields

### **Development Workflow**

**For Content Updates:**
1. Edit Google Sheet data
2. Changes auto-sync (respects cache timing)
3. Clear site cache if needed
4. Verify frontend updates

**For Code Changes:**
1. Modify SASS files in `/sass/` directory
2. Compile CSS: `sass sass/style.sass css/style.css`
3. Update version numbers for cache busting
4. Test across devices and browsers

---

## 🛠️ **For Developers**

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

## �📞 **Support & Maintenance**

### **Current Status**
✅ **Production Ready**: Fully functional and tested  
✅ **Database Integration**: Local storage with Google Sheets fallback  
✅ **Flexible Categories**: Works with any business type  
✅ **Security Hardened**: Enterprise-level protection  
✅ **Mobile Optimized**: Responsive across all devices  
✅ **CRUD Operations**: Full admin management interface

### **Roadmap**
🚧 **Multi-Category Support**: Businesses in multiple categories  
🚧 **CSV Import Interface**: Upload files directly via admin  
🚧 **Advanced Filtering**: Multiple search criteria  
🚧 **Custom Fields**: Additional business metadata  

### **Getting Help**

**WordPress Admin**: Check Location Finder → Import Data for data source settings  
**Browser Console**: Look for JavaScript errors  
**Documentation**: Reference this README and setup guides  

---

**License**: GPL v2 or later  
**Author**: Lorenzo Colen  
**Compatibility**: WordPress 5.0+, PHP 7.4+