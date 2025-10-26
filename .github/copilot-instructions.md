# Copilot Instructions for Business Location Finder Plugin

## Project Overview
- **Purpose:** WordPress plugin for dynamic business location search, supporting Google Sheets and local DB as data sources.
- **Major Components:**
  - `biz-location-finder.php`: Main plugin logic, REST API endpoints, data source switching.
  - `admin/`: Admin UI for settings, data import, and CRUD (see `admin-page.php`, `import-page.php`).
  - `assets/js/stockists.js`: Frontend logic for rendering, search, tabs, and dynamic UI.
  - `assets/sass/`, `assets/css/`: SASS source and compiled CSS for frontend/admin.
  - `templates/finder.php`: Shortcode output template.

## Architecture & Data Flow
- **Data Sources:** Google Sheets (CSV export) or local WordPress DB.
- **Flow:**
  1. Data fetched via REST API (`/wp-json/jq-stockists/v1/get-csv` for Google Sheets)
  2. Parsed/rendered by `stockists.js` (frontend)
  3. Admin CRUD via WordPress backend
- **Tabs, counters, and search** are all dynamically generated from data.

## Developer Workflows
- **Build CSS:**
  - Edit SASS in `assets/sass/`
  - Compile: `sass assets/sass/style.sass assets/css/style.css`
- **Deploy:**
  - Use `build.sh`, `build-and-deploy.sh`, or `create-production-build.sh` for production builds
- **Data Update:**
  - Edit Google Sheet, changes sync automatically (cache: 5 min)
- **Versioning:**
  - Update version in main files and `README.md` for cache busting

## Project Conventions
- **Category Matching:** Case-insensitive, but must match Google Sheets exactly (no fuzzy matching)
- **Shortcodes:**
  - `[biz_location_finder]` (all categories)
  - `[biz_location_finder categories="foo,bar"]` (filter)
  - `[biz_location_finder view="data"]` (no tabs)
- **Security:**
  - All admin actions use WordPress nonces and capability checks
  - Input is sanitized both client and server side
- **Frontend:**
  - No JS frameworks; use ES6+ vanilla JS only
  - Responsive via CSS Grid

## Integration Points
- **REST API:** `/wp-json/jq-stockists/v1/get-csv` (public, rate-limited)
- **Google Sheets:** Must be published as CSV, public sharing enabled
- **Admin UI:** Accessible via WordPress admin menu

## Key Files & References
- `README.md`: Full documentation, setup, and usage
- `BUILD.md`: Build/deployment details
- `SASS-ARCHITECTURE.md`: SASS structure and conventions
- `HOW-TO-CHANGE-SHEET-URL.md`: Data source setup
- `VERSION_HISTORY.md`: Changelog

## Examples
- See `assets/js/stockists.js` for dynamic tab/search logic
- See `admin/import-page.php` for data import workflow
- See `templates/finder.php` for shortcode output structure

---
**For more, see the main README and referenced docs.**
