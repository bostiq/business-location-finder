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

**Current Version**: v2.1.6.2 (October 2025)

**Component Versions:**
- Frontend: stockists.js v2.1.6.2
- Backend: biz-location-finder.php v2.1.6.2
- Admin Interface: v2.1.6.2 (Database CRUD)
- Styles: SASS/CSS v2.1.6.2 (Optimized)

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
├── biz-location-finder.php      # Main plugin file (v2.1.3)
├── README.md                    # Main documentation
├── VERSION_HISTORY.md           # Detailed version changelog
├── BUILD.md                     # Developer build documentation
├── SASS-ARCHITECTURE.md         # SASS development guide
├── HOW-TO-CHANGE-SHEET-URL.md   # Quick setup guide
├── assets/
│   ├── css/                     # Compiled stylesheets
│   │   ├── style.css           # Main frontend styles
│   │   ├── style.min.css       # Minified frontend styles
│   │   ├── admin-styles.css    # Admin interface styles
│   │   ├── admin-styles.min.css # Minified admin styles
│   │   └── *.css.map           # Source maps for debugging
│   ├── js/
│   │   └── stockists.js        # Frontend JavaScript (v2.1.3)
│   └── sass/                   # SASS source files
│       ├── style.sass          # Main frontend SASS
│       ├── admin-styles.sass   # Main admin SASS
│       ├── _colors.sass        # Centralized color system
│       ├── _cards.sass         # Business card styling
│       ├── _counters.sass      # Badge counter styling
│       ├── _fonts.sass         # Typography definitions
│       ├── _search.sass        # Search input styling
│       ├── _tab-Nav.sass       # Tab navigation styling
│       ├── _tabs-content.sass  # Tab panel styling
│       ├── _admin-modal-color-scheme.sass # Admin modal color scheme
│       ├── _admin-modal.sass # Admin modal 
│       ├── _admin-styles-color-scheme.sass # Admin color scheme
│       ├── _import-page.sass   # Import page structure
│       └── _import-page-color-scheme.sass  # Import page colors
├── admin/
│   ├── admin-page.php          # Main settings page
│   └── import-page.php         # Data import/management page
├── templates/
│   └── finder.php              # Shortcode output template
└── dist/                       # Production build artifacts
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

See [VERSION_HISTORY.md](VERSION_HISTORY.md) for detailed version information and changelog.

**Current Version**: v2.1.6.2 (October 2025)

### Recent Updates
- **v2.1.6.2**: Implemented new Facebook handle support for csv googlehseet data set
- **v2.1.6.1**: Heroicons Solid SVG implementation on front-end
- **v2.1.6**: Heroicons Solid SVG implementation replacing emoji icons
- **v2.1.5**: Instagram field conditional rendering fix
- **v2.1.4**: Frontend tab switching functionality bug fix
- **v2.1.3**: Critical bug fix for edit business modal display
- **v2.1.2**: SASS architecture optimization and production deployment
- **v2.1.1**: Empty database handling improvements
- **v2.1.0**: Database implementation with admin interface

---

## **REST API Endpoints**

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