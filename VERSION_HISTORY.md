# Business Location Finder - Version History

## Version History

### v2.1.6.2 (Current)
- **Data support**: Implemented new Facebook handle support for csv googlehseet data set

### v2.1.6.1 (Previous)
**Heroicons Solid SVG Implementation - Front-end**
- **UI Enhancement**: Added icons with Heroicons Solid SVG icons throughout Front-end

### v2.1.6 (previous)
**Heroicons Solid SVG Implementation**
- **UI Enhancement**: Replaced all emoji icons with professional Heroicons Solid SVG icons throughout admin interface
- **Icon System**: Created centralized `blf_heroicon()` helper function for consistent icon rendering
- **CSS Framework**: Added comprehensive icon styling system with size variants and semantic classes

### v2.1.5 (Previous)
**Instagram Field Conditional Rendering Fix**
- **Bug Fix**: Fixed Instagram link rendering when no Instagram handle is provided for businesses
- **Frontend Fix**: Instagram section now properly hides when empty instead of showing broken/empty links
- **Conditional Logic**: Added proper conditional rendering to match other contact fields (website, phone, email)
- **JavaScript Enhancement**: Updated renderCards function in stockists.js with proper field validation

### v2.1.4 (Previous)
**Frontend Tab Switching Bug Fix**
- **Bug Fix**:
- - Fixed critical tab switching functionality where content would disappear when clicking category tab.
- - Corrected panel selection logic in setupTabs function using direct ID lookup instead of data attribute queries
- - Optimized filterPanel function to call revealCards only once after filtering completion
- - **DOM Management**: Improved tab panel creation with consistent ID scoping and reliable selectors

### v2.1.3 (Previous)
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

---

## Change Management

### Version Numbering
- **Major.Minor.Patch** format (e.g., 2.1.3)
- **Major**: Breaking changes or significant new features
- **Minor**: New features, improvements, backwards compatible
- **Patch**: Bug fixes, small improvements

### Release Process
1. Development and testing in feature branches
2. Version number updates across all relevant files
3. Production build creation and deployment
4. Git commit with version tag
5. Documentation updates

### Current Development Focus
- Frontend tab switching functionality
- Admin interface improvements
- Performance optimization
- User experience enhancements